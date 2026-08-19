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
        --pd-sidebar-soft: #eef4ff;
        --pd-sidebar-text: #405066;
    }
    body {
        font-family: 'Inter', sans-serif;
        color: var(--pd-text);
        background: var(--pd-muted-surface);
    }
    h1, h2, h3, h4, h5, h6, .navbar-brand, .sidebar-brand, .card-title, .fw-bold {
        font-family: 'Outfit', sans-serif;
    }
    .sidebar .sidebar-header .sidebar-brand {
        font-weight: 800;
        font-size: 18px;
        color: #1f2937;
        text-decoration: none;
        display: inline-block;
        letter-spacing: 0;
    }
    .sidebar .sidebar-header .sidebar-brand span {
        color: var(--pd-primary);
    }
    [data-bs-theme="dark"] .sidebar .sidebar-header .sidebar-brand {
        color: #ffffff;
    }
    [data-bs-theme="dark"] .sidebar .sidebar-header .sidebar-brand span {
        color: #93b4ff;
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
