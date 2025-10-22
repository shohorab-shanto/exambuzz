<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NoticeBoard;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NoticeBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function all_notice_board()
    {
        $material = NoticeBoard::where('status', 1)->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $material,
        ]);
    }

    public function index()
    {
        $material = NoticeBoard::orderBy('created_at', 'desc')->paginate(15);

        return view('backend.notice-board.index', compact('material'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.notice-board.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'status' => 'required|string',
            'send_notification' => 'nullable|boolean',
            'target_audience' => 'nullable|in:all,user,teacher',
        ]);

        $notice = NoticeBoard::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'send_notification' => $request->get('send_notification', true),
            'target_audience' => $request->get('target_audience', 'all'),
        ]);

        // Send notifications if enabled and status is active
        if ($notice->send_notification && $notice->status == 1) {
            $this->sendNoticeNotification($notice);
        }

        return redirect()->route('notice-board.index')->withToastSuccess('Notice created successfully');
    }

    /**
     * Send notification to users based on target audience
     */
    private function sendNoticeNotification(NoticeBoard $notice)
    {
        try {
            // Determine target users based on audience
            $query = User::query();
            
            if ($notice->target_audience === 'user') {
                $query->where('type', 'user');
            } elseif ($notice->target_audience === 'teacher') {
                $query->where('type', 'teacher');
            }
            // 'all' will not add any where clause
            
            $successCount = 0;
            $failureCount = 0;

            // Process in chunks to avoid memory issues
            $query->where('status', 1)->chunk(200, function ($users) use ($notice, &$successCount, &$failureCount) {
                foreach ($users as $user) {
                    // Create in-app notification
                    Notification::create([
                        'name' => $notice->title,
                        'details' => $notice->description,
                        'user_id' => $user->id,
                        'to' => $user->type,
                    ]);

                    // Send push notification if user has FCM token
                    if (!empty($user->fcm_token)) {
                        $sent = FCMService::send(
                            $user->fcm_token,
                            [
                                'title' => $notice->title,
                                'body' => strip_tags(substr($notice->description, 0, 150)),
                            ]
                        );

                        if ($sent) {
                            $successCount++;
                        } else {
                            $failureCount++;
                        }
                    }
                }
            });

            // Update notice with notification status
            $notice->update([
                'notification_sent' => true,
                'notification_sent_at' => now(),
            ]);

            Log::info('Notice notification sent', [
                'notice_id' => $notice->id,
                'success' => $successCount,
                'failed' => $failureCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send notice notification', [
                'notice_id' => $notice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $material = NoticeBoard::where('id', $id)->first();

        if (!isset($material)) {
            return back()->withToastSuccess('Material folder not found');
        }

        return view('backend.notice-board.create', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'status' => 'required|string',
            'send_notification' => 'nullable|boolean',
            'target_audience' => 'nullable|in:all,user,teacher',
        ]);

        $notice = NoticeBoard::where('id', $id)->first();

        if (!isset($notice)) {
            return back()->withToastError('Notice not found');
        }

        // Check if notification should be sent (new notice being activated or re-sent)
        $shouldSendNotification = false;
        if ($request->get('send_notification', false) && $request->status == 1) {
            // Send notification if explicitly requested or if status changed to active
            $shouldSendNotification = ($notice->status != 1 || !$notice->notification_sent);
        }

        $notice->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'send_notification' => $request->get('send_notification', false),
            'target_audience' => $request->get('target_audience', 'all'),
        ]);

        // Send notifications if conditions met
        if ($shouldSendNotification) {
            $this->sendNoticeNotification($notice);
        }

        return redirect()->route('notice-board.index')->withToastSuccess('Notice updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        NoticeBoard::destroy($id);
        return redirect()->route('notice-board.index')->withToastSuccess('Material folder deleted successfully');
    }
}
