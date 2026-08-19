<nav class="navbar">
  <div class="navbar-content">

    <div class="logo-mini-wrapper">
      <img src="{{ url('build/images/logo-mini-light.png') }}" class="logo-mini logo-mini-light" alt="logo">
      <img src="{{ url('build/images/logo-mini-dark.png') }}" class="logo-mini logo-mini-dark" alt="logo">
    </div>

    <form class="search-form">
      <div class="input-group">
        <div class="input-group-text">
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

    <ul class="navbar-nav">
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
      <li class="nav-item dropdown">
        @php
          $notifications = \App\Models\Notification::latest()->take(4)->get();
          $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
        @endphp
        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="bell"></i>
          @if($unreadNotificationsCount > 0)
            <div class="indicator">
              <div class="circle"></div>
            </div>
          @endif
        </a>
        <div class="dropdown-menu notification-dropdown p-0" aria-labelledby="notificationDropdown">
          <div class="notification-dropdown__header">
            <h6 class="notification-dropdown__title mb-0">Alerts</h6>
            <div class="d-flex align-items-center gap-2">
              @if($unreadNotificationsCount > 0)
                <span class="notification-count-badge">{{ $unreadNotificationsCount }} new</span>
              @endif
              <a href="{{ route('notifications.clear') }}"
                class="notification-clear-link"
                onclick="return confirm('Clear all notifications?')">
                Clear all
              </a>
            </div>
          </div>

          <div class="notification-dropdown__body">
            @forelse($notifications as $notification)
              @php
                $notificationType = strtolower($notification->type ?? 'info');
                $notificationIcon = $notificationType === 'success' ? 'check-circle' : ($notificationType === 'warning' ? 'alert-triangle' : 'bell');
              @endphp

              <a href="{{ route('notifications.index') }}" class="notification-dropdown__item {{ $notification->is_read ? '' : 'is-unread' }}">
                <span class="notification-dropdown__icon notification-dropdown__icon--{{ $notificationType }}">
                  <i class="icon-sm" data-lucide="{{ $notificationIcon }}"></i>
                </span>

                <span class="notification-dropdown__content">
                  <span class="notification-dropdown__item-title">{{ $notification->title }}</span>
                  <span class="notification-dropdown__message">
                    {{ \Illuminate\Support\Str::limit($notification->message, 48) }}
                  </span>
                  <span class="notification-dropdown__time">
                    <i data-lucide="clock" class="icon-xs"></i>
                    {{ $notification->created_at->diffForHumans() }}
                  </span>
                </span>
              </a>
            @empty
              <div class="notification-empty-state">
                <span class="notification-empty-state__icon">
                  <i data-lucide="bell-off"></i>
                </span>
                <p class="mb-1 fw-semibold">No alerts yet</p>
                <span>You are all caught up.</span>
              </div>
            @endforelse
          </div>

          <div class="notification-dropdown__footer">
            <a href="{{ route('notifications.index') }}" class="notification-view-all">
              View history
              <i data-lucide="arrow-right" class="icon-xs"></i>
            </a>
          </div>
        </div>
      </li>

      {{-- Profile --}}
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <div class="w-30px h-30px rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size:13px;">
    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
</div>
          <span class="d-none d-md-inline-block fw-semibold" style="font-size:14px;">
    {{ auth()->user()->name }}
</span>
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
              <a href="{{ route('auth.login') }}" class="dropdown-item py-2 text-body ms-0">
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
