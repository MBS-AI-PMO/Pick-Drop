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
        <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
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
        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="bell"></i>
          <div class="indicator">
            <div class="circle"></div>
          </div>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
            <p class="mb-0 fw-bold">Alerts</p>
            <a href="javascript:;" class="text-secondary">Clear all</a>
          </div>
          <div class="p-1">
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-danger rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="alert-triangle"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>Vehicle Breakdown - Bus #2415</p>
                <p class="fs-12px text-secondary">10 mins ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-warning rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="clock"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>Route Delay - Route #1002</p>
                <p class="fs-12px text-secondary">26 mins ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-info rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="wifi-off"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>GPS Signal Lost - Bus #2410</p>
                <p class="fs-12px text-secondary">1 hour ago</p>
              </div>
            </a>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
            <a href="javascript:;">View all alerts</a>
          </div>
        </div>
      </li>

      {{-- Profile --}}
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <div class="w-30px h-30px rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size:13px;">A</div>
          <span class="d-none d-md-inline-block fw-semibold" style="font-size:14px;">Admin</span>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
          <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
            <div class="mb-2">
              <div class="w-60px h-60px rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size:22px;">A</div>
            </div>
            <div class="text-center">
              <p class="fs-16px fw-bolder mb-0">Admin</p>
              <p class="fs-12px text-secondary">admin@pickdrop.com</p>
            </div>
          </div>
          <ul class="list-unstyled p-1">
            <li>
              <a href="{{ url('/general/profile') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="user"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <a href="{{ url('/auth/login') }}" class="dropdown-item py-2 text-body ms-0">
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