<nav class="navbar">
  <div class="navbar-content">

    <div class="logo-mini-wrapper">
      <img src="{{ url('build/images/logo-mini-light.png') }}" class="logo-mini logo-mini-light" alt="logo">
      <img src="{{ url('build/images/logo-mini-dark.png') }}" class="logo-mini logo-mini-dark" alt="logo">
    </div>

    <form class="search-form">
      <div class="input-group">
        <div class="input-group-text border-0 bg-transparent">
          <i data-lucide="search"></i>
        </div>
    <input
    type="text"
    class="form-control"
    id="navbarForm"
    placeholder="Search menu..."
    autocomplete="off">
      </div>
    </form>

    <ul class="navbar-nav pd-toolbar">
      <li class="theme-switcher-wrapper nav-item">
        <input type="checkbox" value="" id="theme-switcher">
        <label for="theme-switcher">
          <div class="box">
            <div class="ball"></div>
            <div class="icons">
              <i class="link-icon" data-lucide="sun"></i>
              <i class="link-icon" data-lucide="moon"></i>
            </div>
          </div>
        </label>
      </li>

      {{-- Notifications --}}
      @php
        $unreadNotificationsCount = \App\Models\Notification::unreadCount();
        $notifications = \App\Models\Notification::unread()->latest()->take(5)->get();
        $notifyBadge = $unreadNotificationsCount > 99 ? '99+' : (string) $unreadNotificationsCount;
      @endphp
      <li class="nav-item dropdown pd-notify" data-unread-count="{{ $unreadNotificationsCount }}">
        <a class="nav-link pd-icon-btn dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="bell"></i>
          <span class="pd-notify-count" aria-label="{{ $unreadNotificationsCount }} unread" @if($unreadNotificationsCount < 1) hidden @endif>{{ $notifyBadge }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" aria-labelledby="notificationDropdown">
          <div class="notification-dropdown__header">
            <div class="notification-dropdown__heading">
              <h6 class="notification-dropdown__title mb-0">Notifications</h6>
              <span class="notification-count-badge" @if($unreadNotificationsCount < 1) hidden @endif>{{ $unreadNotificationsCount }} new</span>
            </div>
            <a href="{{ route('notifications.clear') }}" class="notification-clear-link" @if($notifications->isEmpty()) hidden @endif>
              Clear all
            </a>
          </div>

          <div class="notification-dropdown__body">
            @foreach($notifications as $notification)
              @php
                $notificationType = strtolower($notification->type ?? 'info');
                $notificationTone = in_array($notificationType, ['success', 'warning', 'danger', 'info'], true)
                  ? $notificationType
                  : 'info';
                $notificationIcon = match ($notificationType) {
                  'success' => 'check-circle-2',
                  'warning', 'danger' => 'alert-triangle',
                  default => 'bell',
                };
              @endphp

              <div class="notification-dropdown__card notification-dropdown__card--{{ $notificationTone }} is-unread"
                   data-notification-id="{{ $notification->id }}">
                <a href="{{ route('notifications.show', $notification) }}"
                   class="notification-dropdown__main js-notification-open"
                   data-read-url="{{ route('notifications.read', $notification) }}">
                  <span class="notification-dropdown__icon notification-dropdown__icon--{{ $notificationTone }}">
                    <i class="icon-sm" data-lucide="{{ $notificationIcon }}"></i>
                  </span>
                  <span class="notification-dropdown__content">
                    <span class="notification-dropdown__top">
                      <span class="notification-dropdown__item-title">{{ $notification->title }}</span>
                      <span class="notification-dropdown__time">
                        {{ $notification->created_at->diffForHumans(['short' => true, 'parts' => 1]) }}
                      </span>
                    </span>
                    <span class="notification-dropdown__message">
                      {{ \Illuminate\Support\Str::limit($notification->message, 88) }}
                    </span>
                  </span>
                </a>
                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="notification-dropdown__delete js-notification-dismiss">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="notification-delete-btn" title="Dismiss" aria-label="Dismiss notification">
                    <i data-lucide="x" class="icon-xs"></i>
                  </button>
                </form>
              </div>
            @endforeach
            <div class="notification-empty-state js-notification-empty" @if($notifications->isNotEmpty()) hidden @endif>
              <span class="notification-empty-state__icon">
                <i data-lucide="bell-off"></i>
              </span>
              <p class="mb-1 fw-semibold">No alerts yet</p>
              <span>You are all caught up.</span>
            </div>
          </div>

          <div class="notification-dropdown__footer">
            <a href="{{ route('notifications.index') }}" class="notification-view-all">
              View all history
              <i data-lucide="arrow-right" class="icon-xs"></i>
            </a>
          </div>
        </div>
      </li>

      {{-- Profile --}}
      <li class="nav-item dropdown pd-profile">
        <a class="nav-link pd-profile-chip dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span class="pd-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
          <span class="pd-profile-meta">
            <span class="pd-profile-name">{{ auth()->user()->name }}</span>
          </span>
          <i class="pd-profile-caret" data-lucide="chevron-down"></i>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
          <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
            <div class="mb-2">
             <div class="w-60px h-60px rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size:22px;">
    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
</div>
            </div>
<div class="text-center">
    <p class="fs-16px fw-bolder mb-0">
        {{ Auth::user()->name }}
    </p>

    <p class="fs-12px text-secondary">
        {{ Auth::user()->email }}
    </p>
</div>
          </div>
          <ul class="list-unstyled p-1">
            <li>
              <a href="{{ route('general.profile') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="user"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <a href="{{ route('login') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="log-out"></i>
                <span>Log Out</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>

    <a href="#" class="sidebar-toggler">
      <i data-lucide="menu"></i>
    </a>

  </div>
</nav>
