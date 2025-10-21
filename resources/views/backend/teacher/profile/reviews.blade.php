@extends('backend.layouts.master')
@section('title', 'Teacher Reviews & Conversations')
@section('content')
    <style>
        .review-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fff;
        }
        .review-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 8px 8px 0 0;
        }
        .review-body {
            padding: 15px;
        }
        .rating-stars {
            color: #f39c12;
            font-size: 18px;
        }
        .conversation-thread {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .conversation-message {
            background: #fff;
            border-left: 3px solid #ddd;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .conversation-message.student-message {
            border-left-color: #3498db;
        }
        .conversation-message.teacher-message {
            border-left-color: #2ecc71;
        }
        .conversation-message.admin-message {
            border-left-color: #e74c3c;
        }
        .user-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 8px;
        }
        .badge-student {
            background: #3498db;
            color: #fff;
        }
        .badge-teacher {
            background: #2ecc71;
            color: #fff;
        }
        .badge-admin {
            background: #e74c3c;
            color: #fff;
        }
        .message-time {
            color: #7e7e7e;
            font-size: 12px;
        }
        .reply-form {
            margin-top: 15px;
            border-top: 2px dashed #ddd;
            padding-top: 15px;
        }
        .exam-info {
            background: #e8f4f8;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .message-actions {
            float: right;
            margin-top: -5px;
        }
        .message-actions .btn {
            padding: 2px 8px;
            font-size: 11px;
            margin-left: 5px;
        }
        .edit-message-form {
            display: none;
            margin-top: 10px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Reviews & Conversations</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('teacher.index') }}">Teacher</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('teacher.show', $teacher->id) }}">{{ $teacher->name }}</a></li>
                        <li class="breadcrumb-item active">Reviews</li>
                    </ol>
                </div>
                <div class="page_title_right">
                    <a href="{{ route('teacher.show', $teacher->id) }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_header">
                    <div class="box_header m-0">
                        <div class="main-title">
                            <h3 class="m-0">
                                <i class="fas fa-user-tie"></i> {{ $teacher->name }}'s Reviews
                            </h3>
                            <p class="mb-0 mt-2">
                                Total Reviews: <strong>{{ $reviews->total() }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="white_card_body">
                    @if($reviews->count() > 0)
                        @foreach($reviews as $review)
                            <div class="review-card">
                                <!-- Review Header -->
                                <div class="review-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-1">
                                                <i class="fas fa-user-graduate"></i> {{ $review->user->name }}
                                            </h5>
                                            <div class="rating-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        ⭐
                                                    @else
                                                        ☆
                                                    @endif
                                                @endfor
                                                <span style="color: #333; font-size: 16px; margin-left: 8px;">
                                                    {{ $review->rating }}/5
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                {{ $review->created_at->format('d M Y, h:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                    
                                    @if($review->writtenAnswer && $review->writtenAnswer->written)
                                        <div class="exam-info mt-2">
                                            <i class="fas fa-file-alt"></i> 
                                            <strong>Exam:</strong> {{ $review->writtenAnswer->written->title }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Review Body -->
                                <div class="review-body">
                                    <h6><i class="fas fa-comment-dots"></i> Student's Review:</h6>
                                    <p style="font-size: 15px; line-height: 1.6;">
                                        {{ $review->comment }}
                                    </p>

                                    <!-- Conversation Thread -->
                                    @if($review->conversations->count() > 0)
                                        <div class="conversation-thread">
                                            <h6 class="mb-3">
                                                <i class="fas fa-comments"></i> Conversation Thread 
                                                <span class="badge badge-info">{{ $review->conversations->count() }} {{ $review->conversations->count() == 1 ? 'message' : 'messages' }}</span>
                                            </h6>

                                            @foreach($review->conversations as $message)
                                                <div class="conversation-message {{ $message->user_type }}-message" id="message-{{ $message->id }}">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <span class="user-badge badge-{{ $message->user_type }}">
                                                                @if($message->user_type == 'student')
                                                                    👨‍🎓 Student
                                                                @elseif($message->user_type == 'teacher')
                                                                    👨‍🏫 Teacher
                                                                @else
                                                                    👑 Admin
                                                                @endif
                                                            </span>
                                                            <strong>{{ $message->user->name }}</strong>
                                                        </div>
                                                        <div>
                                                            <span class="message-time">
                                                                {{ $message->created_at->diffForHumans() }}
                                                            </span>
                                                            
                                                            @if($message->user_type == 'admin' && $message->user_id == auth()->id())
                                                                <div class="message-actions">
                                                                    <button class="btn btn-sm btn-info" onclick="showEditForm({{ $message->id }})">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </button>
                                                                    <form action="{{ route('teacher.deleteConversationMessage') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                                        @csrf
                                                                        <input type="hidden" name="message_id" value="{{ $message->id }}">
                                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                                            <i class="fas fa-trash"></i> Delete
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 mt-2 message-text-{{ $message->id }}" style="font-size: 14px; line-height: 1.6;">
                                                        {{ $message->message }}
                                                    </p>
                                                    
                                                    @if($message->user_type == 'admin' && $message->user_id == auth()->id())
                                                        <div class="edit-message-form" id="edit-form-{{ $message->id }}">
                                                            <form action="{{ route('teacher.editConversationMessage') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="message_id" value="{{ $message->id }}">
                                                                <div class="form-group">
                                                                    <textarea name="message" class="form-control" rows="3" maxlength="2000" required>{{ $message->message }}</textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-save"></i> Update
                                                                </button>
                                                                <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditForm({{ $message->id }})">
                                                                    <i class="fas fa-times"></i> Cancel
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle"></i> No conversation yet. Be the first to reply!
                                        </div>
                                    @endif

                                    <!-- Admin Reply Form -->
                                    <div class="reply-form">
                                        <h6><i class="fas fa-reply"></i> Add Admin Reply</h6>
                                        <form action="{{ route('teacher.addAdminReply') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="review_id" value="{{ $review->id }}">
                                            <div class="form-group">
                                                <textarea 
                                                    name="message" 
                                                    class="form-control" 
                                                    rows="3" 
                                                    placeholder="Type your reply here... (Max 2000 characters)"
                                                    maxlength="2000"
                                                    required
                                                ></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-paper-plane"></i> Send Reply
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <h5>No Reviews Yet</h5>
                            <p>This teacher hasn't received any reviews from students yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function showEditForm(messageId) {
            // Hide the message text
            document.querySelector('.message-text-' + messageId).style.display = 'none';
            // Hide the edit/delete buttons
            document.querySelector('#message-' + messageId + ' .message-actions').style.display = 'none';
            // Show the edit form
            document.getElementById('edit-form-' + messageId).style.display = 'block';
        }

        function hideEditForm(messageId) {
            // Show the message text
            document.querySelector('.message-text-' + messageId).style.display = 'block';
            // Show the edit/delete buttons
            document.querySelector('#message-' + messageId + ' .message-actions').style.display = 'inline-block';
            // Hide the edit form
            document.getElementById('edit-form-' + messageId).style.display = 'none';
        }
    </script>
@endsection

