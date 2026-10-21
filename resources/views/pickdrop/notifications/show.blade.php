@extends('layout.master')

@section('content')

<div class="notification-page">
    <div class="notification-list-card">
        <div class="notification-list-card__header">
            <h4 class="notification-page__title mb-0">Notification</h4>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                <i data-lucide="arrow-left" class="icon-xs"></i>
                Back to history
            </a>
        </div>

        <div class="p-4">
            @php
                $notificationType = strtolower($notification->type ?? 'info');
            @endphp

            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="notification-type-pill notification-type-pill--{{ $notificationType }}">
                    {{ ucfirst($notification->type ?? 'info') }}
                </span>
                <span class="notification-status-pill {{ $notification->is_read ? 'is-read' : 'is-unread' }}">
                    {{ $notification->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>

            <h5 class="fw-bold mb-2">{{ $notification->title }}</h5>
            <p class="text-secondary mb-4">{{ $notification->message }}</p>

            <div class="notification-date">
                <strong>{{ $notification->created_at->format('d M Y') }}</strong>
                <span>{{ $notification->created_at->format('h:i A') }}</span>
            </div>

            <div class="mt-4">
                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger notification-clear-btn">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
