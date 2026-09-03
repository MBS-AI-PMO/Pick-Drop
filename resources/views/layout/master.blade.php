<!DOCTYPE html>
<!--
Template Name: PickDrop - Laravel Admin Dashboard
Author: PickDrop Team
-->
<html>
<head>
  <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="PickDrop Admin Dashboard Template">
	<meta name="author" content="PickDrop">

  <title>PickDrop Admin Panel</title>

  <!-- color-modes:js -->
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
      localStorage.removeItem('pd-accent');
      [
        '--pd-theme-page', '--pd-theme-card', '--pd-theme-sidebar',
        '--pd-theme-navbar', '--pd-theme-footer', '--pd-theme-border',
        '--pd-muted-surface', '--pd-surface', '--pd-app-bg', '--pd-card-bg', '--pd-border'
      ].forEach(function (prop) {
        document.documentElement.style.removeProperty(prop);
      });
    })();
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- End fonts -->
  
  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">
  
  <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

  <!-- Splash Screen -->
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />

  <!-- plugin css -->
  <link href="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />

  @stack('plugin-styles')

  <!-- CSS for LTR layout-->
  @vite(['resources/sass/app.scss', 'resources/css/custom.css'])

  <!-- SweetAlert CSS Include Global -->
  <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />

  <!-- Global Custom Styling for Pick-Drop -->
  <style>
    :root {
        --pd-surface: #ffffff;
        --pd-muted-surface: #f7f9fc;
        --pd-border: #e6ebf2;
        --pd-text: #1f2937;
        --pd-muted: #697586;
        --pd-primary: #3f6fd9;
        --pd-primary-dark: #355fc4;
        --pd-primary-rgb: 63, 111, 217;
        --pd-primary-soft: rgba(63, 111, 217, 0.12);
        --pd-sidebar: #ffffff;
        --pd-sidebar-soft: #f3f5f8;
        --pd-sidebar-text: #1d3557;
    }
    body {
        font-family: 'Inter', sans-serif;
        color: var(--pd-text);
        background: var(--pd-theme-page, var(--pd-muted-surface));
    }
    h1, h2, h3, h4, h5, h6, .navbar-brand, .sidebar-brand, .card-title, .fw-bold {
        font-family: 'Outfit', sans-serif;
    }

    nav.sidebar,
    nav.sidebar .sidebar-header,
    nav.sidebar .sidebar-body {
        background: var(--pd-theme-sidebar, #ffffff) !important;
        border-color: var(--pd-theme-border, #eef1f6) !important;
        box-shadow: none !important;
    }
    nav.sidebar {
        background: var(--pd-theme-sidebar, #ffffff) !important;
        box-shadow: 8px 0 28px rgba(29, 53, 87, 0.04) !important;
        border-right: 1px solid var(--pd-theme-border, #eef1f6) !important;
    }
    nav.sidebar .sidebar-header {
        height: 72px;
        padding: 0 22px;
        border-bottom: 1px solid var(--pd-theme-border, #eef1f6) !important;
    }
    nav.sidebar .sidebar-header .sidebar-brand {
        font-weight: 800;
        font-size: 22px;
        color: #1d3557 !important;
        text-decoration: none;
        display: inline-block;
        letter-spacing: 0;
        margin-bottom: 0;
        padding: 4px 0;
    }
    nav.sidebar .sidebar-header .sidebar-brand span {
        color: #e63946 !important;
        font-weight: 800;
        margin-left: 4px;
    }
    nav.sidebar .sidebar-header .sidebar-toggler span {
        background: #8a96a8 !important;
    }
    nav.sidebar .sidebar-body {
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--pd-theme-border, #eef1f6) !important;
    }
    nav.sidebar .sidebar-body .nav {
        flex: 1;
        padding: 16px 12px 20px !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item + .nav-item {
        margin-top: 2px;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        height: auto !important;
        margin: 16px 10px 6px !important;
        color: #9aa6b8 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: 0.14em !important;
        text-transform: uppercase;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category:first-child {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        height: 40px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        color: #1d3557 !important;
        font-size: 13.5px;
        font-weight: 600;
        background: transparent !important;
        box-shadow: none !important;
        transition: background-color 0.16s ease, color 0.16s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-icon {
        position: static !important;
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 18px;
        color: inherit !important;
        fill: none !important;
        opacity: 0.88;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-title {
        margin-left: 0 !important;
        flex: 1;
        min-width: 0;
        line-height: 1.2;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-arrow {
        margin-left: auto;
        width: 16px;
        height: 16px;
        opacity: 0.5;
        flex: 0 0 16px;
        transition: transform 0.2s ease, opacity 0.16s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link[aria-expanded="true"] .link-arrow {
        transform: rotate(180deg);
        opacity: 0.8;
    }
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub) > .nav-link:hover,
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link:hover {
        color: #111827 !important;
        background: #eef2f7 !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub).active > .nav-link,
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub) > .nav-link.active,
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active {
        color: #111827 !important;
        background: #eef2f7 !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active) {
        background: transparent !important;
        color: #1d3557 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link::before,
    nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active::before {
        display: none !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub {
        border-radius: 12px;
        padding: 2px;
        transition: background-color 0.16s ease;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]) {
        background: #f3f5f8;
    }
    nav.sidebar .sidebar-body .nav.sub-menu {
        position: relative;
        padding: 2px 6px 6px 14px !important;
        margin: 0 4px 2px 16px !important;
        border-left: 1px solid #e6ebf2;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link {
        height: 34px !important;
        padding: 0 10px 0 12px !important;
        border-radius: 8px !important;
        color: #5b6b82 !important;
        font-size: 13px;
        font-weight: 500;
        background: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link::before {
        content: "" !important;
        display: block !important;
        position: absolute;
        left: 0;
        top: 50%;
        width: 5px;
        height: 5px;
        margin-top: -2.5px;
        border-radius: 50%;
        background: #c5cedb;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link:hover {
        color: #111827 !important;
        background: #eef2f7 !important;
        font-weight: 600;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link:hover::before,
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link.active::before {
        background: #1d3557;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link.active {
        color: #111827 !important;
        background: #eef2f7 !important;
        font-weight: 700;
    }
    nav.sidebar .sidebar-body .nav .nav-item-logout {
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #eef1f6;
    }
    nav.sidebar .sidebar-body .nav .nav-item-logout .nav-link:hover {
        color: #111827 !important;
        background: #eef2f7 !important;
    }
    @media (min-width: 992px) {
        body.sidebar-folded nav.sidebar .sidebar-brand,
        body.sidebar-folded nav.sidebar .link-title,
        body.sidebar-folded nav.sidebar .link-arrow,
        body.sidebar-folded nav.sidebar .nav-category,
        body.sidebar-folded nav.sidebar .collapse,
        body.sidebar-folded nav.sidebar .sub-menu {
            display: none !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link {
            justify-content: center;
            width: 42px;
            margin: 0 auto;
        }
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item.active > .nav-link,
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link.active,
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover {
            color: #111827 !important;
            background: #eef2f7 !important;
        }
    }

    [data-bs-theme="dark"] nav.sidebar,
    [data-bs-theme="dark"] nav.sidebar .sidebar-header,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body {
        background: var(--pd-theme-sidebar, #1e2129) !important;
        border-color: var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
        box-shadow: none !important;
    }
    [data-bs-theme="dark"] nav.sidebar {
        background: var(--pd-theme-sidebar, #1e2129) !important;
        box-shadow: 8px 0 28px rgba(0, 0, 0, 0.28) !important;
        border-right: 1px solid var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header {
        border-bottom: 1px solid var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-brand {
        color: #f4f7fb !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-brand span {
        color: #ff4d6d !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-toggler span {
        background: #9aa7bb !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body {
        border-right: 1px solid var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        color: #8b97ab !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item .nav-link {
        color: #d5deea !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item .nav-link[aria-expanded="true"] {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu {
        border-left-color: transparent;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-link {
        color: #e5ebf3 !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-link.active {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.12) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item-logout {
        border-top-color: rgba(255, 255, 255, 0.08);
    }
    @media (min-width: 992px) {
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item.active > .nav-link,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link.active,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1) !important;
        }
    }
    .btn-primary {
        background: var(--pd-primary);
        border-color: var(--pd-primary);
        box-shadow: 0 8px 18px rgba(63, 111, 217, 0.18);
        transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
    }
    .btn-primary:hover{
        background: var(--pd-primary-dark);
        border-color: var(--pd-primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(63, 111, 217, 0.24);
    }
    .navbar {
        background: var(--pd-theme-navbar, var(--bs-body-bg, #ffffff)) !important;
        border-bottom-color: var(--pd-theme-border, #eef1f6) !important;
    }
    [data-bs-theme="dark"] .navbar {
        background: var(--pd-theme-navbar, #1e2129) !important;
        border-bottom-color: var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 8px 22px rgba(16, 24, 40, 0.04);
        border: 1px solid var(--pd-theme-border, var(--pd-border));
        background: var(--pd-theme-card, var(--pd-surface, #ffffff));
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease, background-color 0.16s ease;
    }
    [data-bs-theme="dark"] .card {
        border-color: var(--pd-theme-border, rgba(255,255,255,0.08));
        background: var(--pd-theme-card, #1e2129);
    }
    .page-wrapper,
    .page-content {
        background: var(--pd-theme-page, var(--pd-muted-surface, #f7f9fc));
    }
    .page-wrapper .page-content,
    .page-content.container-xxl,
    .page-content.container-fluid {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 18px !important;
        padding-right: 18px !important;
    }
    .footer {
        background: var(--pd-theme-footer, #ffffff) !important;
        border-top: 1px solid var(--pd-theme-border, #eef1f6) !important;
    }
    [data-bs-theme="dark"] .page-wrapper,
    [data-bs-theme="dark"] .page-content {
        background: var(--pd-theme-page, #151821);
    }
    [data-bs-theme="dark"] .footer {
        background: var(--pd-theme-footer, #1e2129) !important;
        border-top: 1px solid var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
        color: #9aa7bb;
    }
    [data-bs-theme="dark"] .footer .text-secondary {
        color: #9aa7bb !important;
    }
    [data-bs-theme="dark"] .footer a {
        color: #93b4ff !important;
    }
    [data-bs-theme="dark"] .footer .text-primary {
        color: #ff4d6d !important;
    }
    .table,
    .table-light,
    thead.table-light,
    .table > thead {
        --bs-table-bg: var(--pd-theme-card, transparent);
        --bs-table-striped-bg: var(--pd-theme-sidebar, transparent);
    }
    .dropdown-menu {
        background: var(--pd-theme-card, #ffffff);
        border-color: var(--pd-theme-border, #e6ebf2);
    }
    [data-bs-theme="dark"] .dropdown-menu {
        background: var(--pd-theme-card, #1e2129);
        border-color: var(--pd-theme-border, rgba(255,255,255,0.08));
    }
    .modal-content {
        background: var(--pd-theme-card, #ffffff);
        border-color: var(--pd-theme-border, #e6ebf2);
    }
    [data-bs-theme="dark"] .modal-content {
        background: var(--pd-theme-card, #1e2129);
        border-color: var(--pd-theme-border, rgba(255,255,255,0.08));
    }

    .action-btns {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .action-btns form {
        display: inline-flex;
        margin: 0;
        padding: 0;
    }
    .action-btn {
        width: 36px;
        height: 36px;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        text-decoration: none !important;
        box-shadow: none !important;
        flex: 0 0 36px;
        transition: transform 0.12s ease, filter 0.12s ease, background-color 0.12s ease;
    }
    .action-btn:hover {
        transform: translateY(-1px);
        filter: brightness(0.97);
    }
    .action-btn i,
    .action-btn svg {
        width: 16px !important;
        height: 16px !important;
        stroke-width: 2;
    }
    .action-btn-view,
    .action-btn-edit,
    .action-btn-extra {
        background: #eef1f4 !important;
        color: #1f2937 !important;
    }
    .action-btn-warn {
        background: #f5c400 !important;
        color: #111827 !important;
    }
    .action-btn-add {
        background: #3f6fd9 !important;
        color: #ffffff !important;
    }
    .action-btn-delete {
        background: #e11d48 !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .action-btn-view,
    [data-bs-theme="dark"] .action-btn-edit,
    [data-bs-theme="dark"] .action-btn-extra {
        background: #2b3140 !important;
        color: #e8eef7 !important;
    }
    .badge {
        border-radius: 999px;
        padding: 0.38em 0.72em;
        font-weight: 700;
    }
    .rounded-circle {
        border-radius: 50% !important;
    }
    .dashboard-stat-card,
    .dashboard-card {
        background: var(--pd-theme-card, #ffffff) !important;
        border-color: var(--pd-theme-border, #edf1f7) !important;
    }
    [data-bs-theme="dark"] .dashboard-stat-card,
    [data-bs-theme="dark"] .dashboard-card {
        background: var(--pd-theme-card, #1e2129) !important;
        border-color: var(--pd-theme-border, rgba(255,255,255,0.08)) !important;
    }
    .bg-primary-subtle, .bg-primary.bg-opacity-10 { background-color: rgba(63, 111, 217, 0.1) !important; color: var(--pd-primary) !important;}
    .navbar .search-form .input-group-text,
    .navbar .search-form .form-control {
        background: transparent !important;
        background-color: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }
  </style>

  @stack('style')
</head>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('navbarForm');

    if (!input) return;

    input.addEventListener('keydown', function (e) {

        if (e.key !== 'Enter') return;

        e.preventDefault();

        const keyword = this.value.trim().toLowerCase();

        const routes = {
            'dashboard': "{{ route('dashboard') }}",

            'user': "{{ route('users.index') }}",
            'users': "{{ route('users.index') }}",

            'vehicle': "{{ route('vehicles.index') }}",
            'vehicles': "{{ route('vehicles.index') }}",

            'vehicle category': "{{ route('vehicle-categories.index') }}",
            'categories': "{{ route('vehicle-categories.index') }}",

            'city': "{{ route('locations.cities.index') }}",
            'cities': "{{ route('locations.cities.index') }}",

            'area': "{{ route('locations.areas.index') }}",
            'areas': "{{ route('locations.areas.index') }}",

            'route': "{{ route('routes.index') }}",
            'routes': "{{ route('routes.index') }}",

            'charges': "{{ route('charges.index') }}",

            'payments': "{{ route('payments.index') }}",

            'calendar': "{{ route('holidays.index') }}",
            'holiday': "{{ route('holidays.index') }}",
            'holidays': "{{ route('holidays.index') }}",
            'chutti': "{{ route('holidays.index') }}",

            'complaints': "{{ route('issues.index') }}",
            'complaint': "{{ route('issues.index') }}",
            'issues': "{{ route('issues.index') }}",

            'reports': "{{ route('reports.index') }}",

            'profile': "{{ route('general.profile') }}"
        };

        if (routes[keyword]) {

            window.location.href = routes[keyword];

            return;
        }

        alert('No matching menu found.');

    });

});
</script>
<body data-base-url="{{route('dashboard')}}">

  <script>
    // Create splash screen container
    var splash = document.createElement("div");
    splash.innerHTML = `
      <div class="splash-screen">
        <div class="logo"></div>
        <div class="spinner"></div>
      </div>`;
    
    // Insert splash screen as the first child of the body
    document.body.insertBefore(splash, document.body.firstChild);

    // Add 'loaded' class to body once DOM is fully loaded
    document.addEventListener("DOMContentLoaded", function () {
      document.body.classList.add("loaded");
    });
  </script>

  <div class="main-wrapper" id="app">
    @include('layout.partials.sidebar')
    <div class="page-wrapper">
      @include('layout.partials.header')
      <div class="page-content @yield('page-content-class', 'container-fluid')">
        @yield('content')
      </div>
      @include('layout.partials.footer')
    </div>
  </div>

    <!-- base js -->
    @vite(['resources/js/app.js'])
    <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
    <script src="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <!-- end base js -->

    <!-- plugin js -->
    @stack('plugin-scripts')
    <!-- end plugin js -->

    <!-- common js -->
    @vite(['resources/js/pages/template.js'])
    <!-- end common js -->

    <!-- SweetAlert Base Included in Master for global use -->
    <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{!! session('success') !!}',
                timer: 3000,
                showConfirmButton: false,
                scrollbarPadding: false,
                customClass: { popup: 'rounded-4' }
            });
        @endif
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{!! session('error') !!}',
                timer: 3000,
                showConfirmButton: true,
                scrollbarPadding: false,
                customClass: { popup: 'rounded-4' }
            });
        @endif
    });
    </script>

    @stack('custom-scripts')
</body>
</html>
