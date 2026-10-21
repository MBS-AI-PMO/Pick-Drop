<!DOCTYPE html>
<!--
Template Name: NobleUI - Laravel Admin Dashboard Template
Author: NobleUI
Website: https://nobleui.com
Contact: nobleui.team@gmail.com
Purchase: https://1.envato.market/nobleui_laravel
License: You must have a valid license to legally use this template for your project.
-->
<html>
<head>
  <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="Responsive Laravel Admin Dashboard Template based on Bootstrap 5">
	<meta name="author" content="NobleUI">
	<meta name="keywords" content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, laravel, theme, front-end, ui kit, web">

  <title>PickDrop Admin Dashboard </title>

  <!-- color-modes:js -->
  <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/color-modes.js']); ?>
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <!-- End fonts -->
  
  <!-- CSRF Token -->
  <meta name="_token" content="<?php echo e(csrf_token()); ?>">
  
  <link rel="shortcut icon" href="<?php echo e(asset('/favicon.ico')); ?>">

  <!-- Splash Screen -->
  <link href="<?php echo e(asset('splash-screen.css')); ?>" rel="stylesheet" />

  <!-- plugin css -->
  <link href="<?php echo e(asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css')); ?>" rel="stylesheet" />

  <?php echo $__env->yieldPushContent('plugin-styles'); ?>

  <!-- CSS for LTR layout-->
  <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.scss', 'resources/css/custom.css']); ?>

  <style>
    @media (max-width: 767.98px) {
      .page-wrapper.full-page .page-content {
        width: 100%;
        min-width: 0;
        padding: 16px;
      }

      .auth-page {
        width: calc(100vw - 32px) !important;
        max-width: calc(100vw - 32px);
        min-width: 0;
      }

      .auth-page > [class*="col-"] {
        width: calc(100vw - 32px);
        max-width: calc(100vw - 32px);
        min-width: 0;
        padding-left: 0;
        padding-right: 0;
      }

      .auth-page .auth-card {
        width: calc(100vw - 32px);
        max-width: calc(100vw - 32px);
      }

      .auth-page .auth-form-wrapper {
        padding: 2rem 1.25rem;
      }

      .auth-page .input-group,
      .auth-page .form-control {
        max-width: 100%;
        min-width: 0;
      }
    }
  </style>

  <!-- CSS for RTL layout-->
  <!-- <?php echo app('Illuminate\Foundation\Vite')(['resources/rtl-css/app-rtl.css', 'resources/rtl-css/custom-rtl.css']); ?> -->

  <?php echo $__env->yieldPushContent('style'); ?>
</head>
<body data-base-url="<?php echo e(route('dashboard')); ?>">

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
    <div class="page-wrapper full-page">
      <div class="page-content container-xxl d-flex align-items-center justify-content-center">
        <?php echo $__env->yieldContent('content'); ?>
      </div>
    </div>
  </div>

    <!-- base js -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <script src="<?php echo e(asset('build/plugins/lucide/lucide.min.js')); ?>"></script>
    <!-- end base js -->

    <!-- plugin js -->
    <?php echo $__env->yieldPushContent('plugin-scripts'); ?>
    <!-- end plugin js -->

    <!-- common js -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/template.js']); ?>
    <!-- end common js -->

    <?php echo $__env->yieldPushContent('custom-scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/layout/master2.blade.php ENDPATH**/ ?>