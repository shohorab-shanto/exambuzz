<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status'); // null = all, 0 = unread, 1 = read

            $query = Notification::where('user_id', Auth::id())
                ->with('user', 'written', 'package')
                ->orderBy('created_at', 'desc');

            // Filter by read/unread status if provided
            if ($status !== null) {
                $query->where('status', $status);
            }

            $notifications = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications,
                'unread_count' => Notification::where('user_id', Auth::id())
                    ->where('status', 0)
                    ->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->update([
                'status' => 1,
                'read_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $updated = Notification::where('user_id', Auth::id())
                ->where('status', 0)
                ->update([
                    'status' => 1,
                    'read_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        try {
            $count = Notification::where('user_id', Auth::id())
                ->where('status', 0)
                ->count();

            return response()->json([
                'status' => true,
                'unread_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'status' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store FCM token for push notifications
     */
    public function storeFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|min:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find(Auth::id());
            $user->fcm_token = $request->fcm_token;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'FCM token stored successfully',
                'data' => [
                    'user_id' => $user->id,
                    'fcm_token_set' => !empty($user->fcm_token)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to store FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove FCM token (e.g., on logout)
     */
    public function removeFcmToken()
    {
        try {
            $user = User::find(Auth::id());
            $user->fcm_token = null;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'FCM token removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
