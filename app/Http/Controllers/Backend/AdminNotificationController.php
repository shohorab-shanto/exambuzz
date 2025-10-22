<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminNotificationController extends Controller
{
    /**
     * Display notification sending page
     */
    public function index()
    {
        return view('backend.notification.index');
    }

    /**
     * Send custom notification to users
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:500',
            'target_audience' => 'required|in:all,user,teacher,specific',
            'user_ids' => 'required_if:target_audience,specific|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $successCount = 0;
            $failureCount = 0;
            $totalUsers = 0;

            if ($request->target_audience === 'specific') {
                // Send to specific users
                $users = User::whereIn('id', $request->user_ids)->where('status', 1)->get();
                
                foreach ($users as $user) {
                    $this->sendToUser($user, $request->title, $request->message, $successCount, $failureCount);
                    $totalUsers++;
                }
            } else {
                // Send to user type or all
                $query = User::where('status', 1);
                
                if ($request->target_audience === 'user') {
                    $query->where('type', 'user');
                } elseif ($request->target_audience === 'teacher') {
                    $query->where('type', 'teacher');
                }
                
                $query->chunk(200, function ($users) use ($request, &$successCount, &$failureCount, &$totalUsers) {
                    foreach ($users as $user) {
                        $this->sendToUser($user, $request->title, $request->message, $successCount, $failureCount);
                        $totalUsers++;
                    }
                });
            }

            Log::info('Admin notification sent', [
                'title' => $request->title,
                'target' => $request->target_audience,
                'total_users' => $totalUsers,
                'push_success' => $successCount,
                'push_failed' => $failureCount
            ]);

            return back()->withToastSuccess("Notification sent to {$totalUsers} users. Push: {$successCount} successful, {$failureCount} failed.");

        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withToastError('Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a single user
     */
    private function sendToUser(User $user, string $title, string $message, &$successCount, &$failureCount)
    {
        // Create in-app notification
        Notification::create([
            'name' => $title,
            'details' => $message,
            'user_id' => $user->id,
            'to' => $user->type,
        ]);

        // Send push notification if user has FCM token
        if (!empty($user->fcm_token)) {
            $sent = FCMService::send(
                $user->fcm_token,
                [
                    'title' => $title,
                    'body' => $message,
                ]
            );

            if ($sent) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
    }

    /**
     * Get notification statistics
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::where('status', 1)->count(),
            'users_with_fcm' => User::where('status', 1)->whereNotNull('fcm_token')->count(),
            'total_students' => User::where('status', 1)->where('type', 'user')->count(),
            'total_teachers' => User::where('status', 1)->where('type', 'teacher')->count(),
            'notifications_today' => Notification::whereDate('created_at', today())->count(),
            'notifications_this_week' => Notification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'notifications_this_month' => Notification::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get user list for targeted notifications
     */
    public function getUserList(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $query = User::where('status', 1)->select('id', 'name', 'email', 'phone', 'type');
        
        if ($type === 'user') {
            $query->where('type', 'user');
        } elseif ($type === 'teacher') {
            $query->where('type', 'teacher');
        }
        
        $users = $query->paginate(50);

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }
}
