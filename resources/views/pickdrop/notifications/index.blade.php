@extends('layout.master')

@section('content')

<div class="notification-page">
    <div class="notification-list-card">
        <div class="notification-list-card__header">
            <h4 class="notification-page__title mb-0">Notification History</h4>
            @if($notifications->total() > 0)
                <a href="{{ route('notifications.clear') }}"
                    class="btn btn-sm btn-outline-danger notification-clear-btn"
                    onclick="return confirm('Clear all notifications?')">
                    <i data-lucide="trash-2" class="icon-xs"></i>
                    Clear all
                </a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table notification-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($notifications as $notification)
                        @php
                            $notificationType = strtolower($notification->type ?? 'info');
                        @endphp

                        <tr class="{{ $notification->is_read ? '' : 'notification-table__row--unread' }}">
                            <td>
                                <div class="notification-title-cell">
                                    @unless($notification->is_read)
                                        <span class="notification-row-dot"></span>
                                    @endunless
                                    <span>{{ $notification->title }}</span>
                                </div>
                            </td>
                            <td class="notification-message-cell">{{ $notification->message }}</td>
                            <td>
                                <span class="notification-type-pill notification-type-pill--{{ $notificationType }}">
                                    {{ ucfirst($notification->type ?? 'info') }}
                                </span>
                            </td>
                            <td>
                                <span class="notification-status-pill {{ $notification->is_read ? 'is-read' : 'is-unread' }}">
                                    {{ $notification->is_read ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td>
                                <div class="notification-date">
                                    <strong>{{ $notification->created_at->format('d M Y') }}</strong>
                                    <span>{{ $notification->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="notification-empty-state notification-empty-state--page">
                                    <span class="notification-empty-state__icon">
                                        <i data-lucide="bell-off"></i>
                                    </span>
                                    <p class="mb-1 fw-semibold">No notifications found</p>
                                    <span>New alerts will appear here when there is activity.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app-pagination :paginator="$notifications" label="notifications" class="notification-pagination" />
    </div>
</div>

@endsection
