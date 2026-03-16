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
    body {
        font-family: 'Inter', sans-serif;
    }
    h1, h2, h3, h4, h5, h6, .navbar-brand, .sidebar-brand, .card-title, .fw-bold {
        font-family: 'Outfit', sans-serif;
    }
    .sidebar .sidebar-header .sidebar-brand {
        font-weight: 800;
        font-size: 26px;
        color: #1d3557;
        text-decoration: none;
        display: inline-block;
    }
    .sidebar .sidebar-header .sidebar-brand span {
        color: #e63946;
    }
    [data-bs-theme="dark"] .sidebar .sidebar-header .sidebar-brand {
        color: #f1faee;
    }
    [data-bs-theme="dark"] .sidebar .sidebar-header .sidebar-brand span {
        color: #ff4d6d;
    }
    .btn-primary {
    background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
    border: none;
    box-shadow: 0 4px 10px rgba(29, 53, 87, 0.25);
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-primary:hover{
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(29, 53, 87, 0.35);
}
    .card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    [data-bs-theme="dark"] .card {
        border-color: rgba(255,255,255,0.05);
        background: #1e2129;
    }
    .badge {
        border-radius: 8px;
        padding: 0.4em 0.8em;
        font-weight: 500;
    }
    .rounded-circle {
        border-radius: 50% !important;
    }
    .bg-primary-subtle, .bg-primary.bg-opacity-10 { background-color: rgba(230, 57, 70, 0.1) !important; color: #e63946 !important;}
  </style>

  @stack('style')
</head>
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