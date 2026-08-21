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
        --pd-primary-dark: #244a9b;
        --pd-sidebar: #ffffff;
        --pd-sidebar-soft: #fff5f5;
        --pd-sidebar-text: #1d3557;
    }
    body {
        font-family: 'Inter', sans-serif;
        color: var(--pd-text);
        background: var(--pd-muted-surface);
    }
    h1, h2, h3, h4, h5, h6, .navbar-brand, .sidebar-brand, .card-title, .fw-bold {
        font-family: 'Outfit', sans-serif;
    }

    nav.sidebar,
    nav.sidebar .sidebar-header,
    nav.sidebar .sidebar-body {
        background: #ffffff !important;
        border-color: #eef1f6 !important;
        box-shadow: none !important;
    }
    nav.sidebar {
        background: #ffffff !important;
        box-shadow: 8px 0 28px rgba(29, 53, 87, 0.04) !important;
        border-right: 1px solid #eef1f6 !important;
    }
    nav.sidebar .sidebar-header {
        height: 72px;
        padding: 0 22px;
        border-bottom: 1px solid #eef1f6 !important;
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
        border-right: 1px solid #eef1f6 !important;
    }
    nav.sidebar .sidebar-body .nav {
        flex: 1;
        padding: 22px 14px 22px !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        height: auto !important;
        margin: 18px 12px 8px !important;
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
        height: 42px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        color: #1d3557 !important;
        font-size: 13.5px;
        font-weight: 600;
        background: transparent !important;
        box-shadow: none !important;
        transition: background-color 0.18s ease, color 0.18s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-icon {
        position: static !important;
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 18px;
        color: inherit !important;
        fill: none !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-title {
        margin-left: 0 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-arrow {
        margin-left: auto;
        opacity: 0.55;
    }
    nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
    nav.sidebar .sidebar-body .nav .nav-item .nav-link:hover {
        color: #e63946 !important;
        background: #fff5f5 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link,
    nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active,
    nav.sidebar .sidebar-body .nav .nav-item .nav-link[aria-expanded="true"] {
        color: #e63946 !important;
        background: #fff1f2 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link::before,
    nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active::before {
        display: none !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu {
        padding: 4px 0 8px 16px !important;
        margin-left: 18px;
        border-left: 1px solid #edf1f7;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link {
        height: 34px !important;
        color: #5b6b82 !important;
        font-size: 12.5px;
        font-weight: 500;
        background: transparent !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link:hover,
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link.active {
        color: #e63946 !important;
        background: #fff5f5 !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-link::before {
        display: none !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item-logout {
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #eef1f6;
    }
    nav.sidebar .sidebar-body .nav .nav-item-logout .nav-link:hover {
        color: #e63946 !important;
        background: #fff5f5 !important;
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
            color: #e63946 !important;
            background: #fff1f2 !important;
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
    .card {
        border-radius: 8px;
        box-shadow: 0 8px 22px rgba(16, 24, 40, 0.04);
        border: 1px solid var(--pd-border);
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease;
    }
    [data-bs-theme="dark"] .card {
        border-color: rgba(255,255,255,0.08);
        background: #1e2129;
    }
    .badge {
        border-radius: 999px;
        padding: 0.38em 0.72em;
        font-weight: 700;
    }
    .rounded-circle {
        border-radius: 50% !important;
    }
    .bg-primary-subtle, .bg-primary.bg-opacity-10 { background-color: rgba(63, 111, 217, 0.1) !important; color: var(--pd-primary) !important;}
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
      <div class="page-content container-xxl">
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
