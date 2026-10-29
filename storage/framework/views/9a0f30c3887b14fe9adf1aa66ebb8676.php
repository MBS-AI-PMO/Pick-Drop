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
  <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/color-modes.js']); ?>
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
  <meta name="_token" content="<?php echo e(csrf_token()); ?>">
  
  <link rel="shortcut icon" href="<?php echo e(asset('/favicon.ico')); ?>">

  <!-- Splash Screen -->
  <link href="<?php echo e(asset('splash-screen.css')); ?>" rel="stylesheet" />

  <!-- plugin css -->
  <link href="<?php echo e(asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css')); ?>" rel="stylesheet" />

  <?php echo $__env->yieldPushContent('plugin-styles'); ?>

  <!-- CSS for LTR layout-->
  <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.scss', 'resources/css/custom.css']); ?>

  <!-- SweetAlert CSS Include Global -->
  <link href="<?php echo e(asset('build/plugins/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />

  <!-- Global Custom Styling for Pick-Drop -->
  <style>
    /* SweetAlert2 loads after app.css and would otherwise force a white popup in dark mode. */
    html .swal2-popup {
      --swal2-background: var(--bs-body-bg);
      --swal2-color: var(--bs-body-color);
      background: var(--bs-body-bg);
      color: var(--bs-body-color);
    }
    html .swal2-popup .swal2-title,
    html .swal2-popup .swal2-html-container {
      color: var(--bs-body-color);
    }
    /* Bootstrap's "dark" buttons are near-black: invisible text/border (btn-outline-dark) and a heavy
       black block (btn-dark) on dark cards. Use light-on-dark equivalents in dark mode only. */
    html[data-bs-theme="dark"] .btn-outline-dark {
      --bs-btn-color: #f4f7fb;
      --bs-btn-border-color: rgba(255, 255, 255, 0.35);
      --bs-btn-hover-color: #ffffff;
      --bs-btn-hover-bg: rgba(255, 255, 255, 0.12);
      --bs-btn-hover-border-color: rgba(255, 255, 255, 0.55);
      --bs-btn-active-color: #ffffff;
      --bs-btn-active-bg: rgba(255, 255, 255, 0.18);
      --bs-btn-active-border-color: rgba(255, 255, 255, 0.6);
      --bs-btn-disabled-color: rgba(244, 247, 251, 0.45);
      --bs-btn-disabled-border-color: rgba(255, 255, 255, 0.2);
    }
    /* Outline primary / secondary: the default blue and gray are too dim on dark surfaces
       (text under 4.5:1, border nearly invisible). */
    /* custom.css sets .btn-outline-primary's colour/border directly (32% opacity border), which
       beats Bootstrap's variables, so these set the properties directly. Both themes. */
    html .btn-outline-primary {
      color: #4a58e8;
      border-color: #6f7cf0;
    }
    html .btn-outline-primary:hover,
    html .btn-outline-primary:focus-visible {
      color: #ffffff;
      background: #5b6cff;
      border-color: #5b6cff;
    }
    html .btn-outline-secondary {
      --bs-btn-color: #566074;
      --bs-btn-border-color: #8a94a6;
      --bs-btn-hover-color: #1d2b4a;
      --bs-btn-hover-bg: #eef1f6;
      --bs-btn-hover-border-color: #6e788c;
      --bs-btn-active-color: #1d2b4a;
      --bs-btn-active-bg: #e3e8f0;
      --bs-btn-active-border-color: #6e788c;
    }
    html[data-bs-theme="dark"] .btn-outline-primary {
      color: #9aa6ff;
      border-color: rgba(154, 166, 255, 0.6);
    }
    html[data-bs-theme="dark"] .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .btn-outline-primary:focus-visible {
      color: #ffffff;
      background: rgba(91, 108, 255, 0.4);
      border-color: rgba(154, 166, 255, 0.9);
    }
    html[data-bs-theme="dark"] .btn-outline-secondary {
      --bs-btn-color: #c3cbdb;
      --bs-btn-border-color: rgba(255, 255, 255, 0.4);
      --bs-btn-hover-color: #ffffff;
      --bs-btn-hover-bg: rgba(255, 255, 255, 0.12);
      --bs-btn-hover-border-color: rgba(255, 255, 255, 0.55);
      --bs-btn-active-color: #ffffff;
      --bs-btn-active-bg: rgba(255, 255, 255, 0.18);
      --bs-btn-active-border-color: rgba(255, 255, 255, 0.6);
      --bs-btn-disabled-color: rgba(195, 203, 219, 0.5);
      --bs-btn-disabled-border-color: rgba(255, 255, 255, 0.18);
    }
    /* SweetAlert input validation message ("Reason is required"): default is a bright light-gray strip. */
    html[data-bs-theme="dark"] .swal2-popup .swal2-validation-message {
      background: rgba(255, 255, 255, 0.06);
      color: #ff9fb2;
    }
    html[data-bs-theme="dark"] .btn-dark {
      --bs-btn-color: #ffffff;
      --bs-btn-bg: #2b3345;
      --bs-btn-border-color: #3a4358;
      --bs-btn-hover-color: #ffffff;
      --bs-btn-hover-bg: #364058;
      --bs-btn-hover-border-color: #46506a;
      --bs-btn-active-color: #ffffff;
      --bs-btn-active-bg: #3f4a66;
      --bs-btn-active-border-color: #4c577a;
      --bs-btn-disabled-color: rgba(255, 255, 255, 0.55);
      --bs-btn-disabled-bg: #2b3345;
      --bs-btn-disabled-border-color: #3a4358;
    }
    /* Toasts: the app-wide 25px dialog title made every toast a big card. Keep them compact. */
    html .swal2-popup.swal2-toast {
      width: auto;
      max-width: min(380px, calc(100vw - 32px));
      padding: 10px 14px;
      align-items: center;
      border-radius: 10px;
      border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.08));
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.14);
    }
    html .swal2-popup.swal2-toast .swal2-title {
      margin: 0 8px;
      padding: 0;
      font-size: 14px;
      line-height: 1.3;
      font-weight: 600;
      text-align: left;
    }
    html .swal2-popup.swal2-toast .swal2-html-container {
      margin: 2px 8px 0;
      padding: 0;
      font-size: 13px;
      line-height: 1.35;
      text-align: left;
      color: var(--bs-secondary-color, #6c757d);
    }
    html .swal2-popup.swal2-toast .swal2-icon {
      width: 1.9em;
      height: 1.9em;
      min-width: 1.9em;
      margin: 0 4px 0 0;
    }
    html .swal2-popup.swal2-toast .swal2-icon .swal2-icon-content {
      font-size: 1.3em;
    }
    /* Select2 single selects: select2.css sizes the box at 28px while app.css adds form-control
       padding to the text area, so the placeholder/value sat low and was clipped. Match the
       app's 36px inputs and centre the text. */
    html .select2-container--default .select2-selection--single {
        height: 36px !important;
        display: flex;
        align-items: center;
    }
    html .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100%;
        line-height: 34px !important;
        padding: 0 30px 0 12px !important;
    }
    html .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--bs-secondary-color, #6c757d);
    }
    html .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        top: 0 !important;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--multiple {
        background-color: #171a21 !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f4f7fb;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9aa7bb;
    }
    /* Opened Select2 dropdown: was white with near-white option text in dark mode. */
    html[data-bs-theme="dark"] .select2-dropdown {
        background-color: #171a21 !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
        color: #f4f7fb;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: #12151b !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
        color: #f4f7fb;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option {
        color: #f4f7fb;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--disabled {
        color: #7c8799;
    }

    /* Select2 dropdown rows, chips, clear button and scrollbar: themed instead of the default
       light-gray look (low-contrast selected items/chips, unstyled scrollbar). */
    html .select2-dropdown {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }
    html .select2-container--default .select2-results__option {
        padding: 8px 12px;
        font-size: 13px;
        line-height: 1.4;
    }
    /* selected rows */
    html .select2-container--default .select2-results__option[aria-selected=true],
    html .select2-container--default .select2-results__option--selected {
        background-color: rgba(63, 111, 217, 0.12) !important;
        color: #2f56b8 !important;
        font-weight: 600;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true],
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--selected {
        background-color: rgba(99, 140, 255, 0.16) !important;
        color: #cfdcff !important;
    }
    /* hovered / keyboard-highlighted row (declared after the selected rules so it wins) */
    html .select2-container--default .select2-results__option--highlighted[aria-selected],
    html .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #3f6fd9 !important;
        color: #ffffff !important;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected],
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: rgba(99, 140, 255, 0.34) !important;
        color: #ffffff !important;
    }
    /* chips in multi-area mode */
    html .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(63, 111, 217, 0.12) !important;
        border: 1px solid rgba(63, 111, 217, 0.35) !important;
        color: #2f56b8 !important;
        border-radius: 6px !important;
        /* left padding leaves room for the absolutely positioned remove button */
        padding: 2px 8px 2px 24px !important;
        font-size: 12.5px;
        font-weight: 500;
    }
    html .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: inherit !important;
        opacity: 0.65;
        border: 0 !important;
        background: transparent !important;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 100%;
    }
    html .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        opacity: 1;
        color: #dc2626 !important;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(99, 140, 255, 0.2) !important;
        border-color: rgba(99, 140, 255, 0.45) !important;
        color: #dbe6ff !important;
    }
    html[data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff8a8a !important;
    }
    /* "clear all" button */
    html .select2-container--default .select2-selection__clear {
        color: var(--bs-secondary-color, #6c757d);
        font-weight: 400;
    }
    html .select2-container--default .select2-selection__clear:hover {
        color: #dc2626;
    }
    /* thin themed scrollbar in the options list */
    html .select2-results__options {
        max-height: 260px;
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 116, 139, 0.45) transparent;
    }
    html .select2-results__options::-webkit-scrollbar {
        width: 8px;
    }
    html .select2-results__options::-webkit-scrollbar-track {
        background: transparent;
    }
    html .select2-results__options::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.45);
        border-radius: 8px;
    }
    html .select2-results__options::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.7);
    }
    html[data-bs-theme="dark"] .select2-results__options {
        scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
    }
    html[data-bs-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.22);
    }
    html[data-bs-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.38);
    }
    /* Dark-mode search/input groups: the :root block below re-applies light --pd-* values, which
       left a white outline and a dim icon. Use the dark palette explicitly for these groups. */
    html[data-bs-theme="dark"] :not(.search-form) > .input-group > .input-group-text,
    html[data-bs-theme="dark"] :not(.search-form) > .input-group > .form-control {
        background-color: #171a21 !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
        color: #f4f7fb;
    }
    html[data-bs-theme="dark"] :not(.search-form) > .input-group > .input-group-text {
        color: #9aa7bb;
    }
    html[data-bs-theme="dark"] :not(.search-form) > .input-group > .input-group-text svg {
        color: #9aa7bb;
        stroke: currentColor;
    }
    html[data-bs-theme="dark"] .input-group > .form-control::placeholder {
        color: #9aa7bb;
        opacity: 1;
    }
    html[data-bs-theme="dark"] :not(.search-form) > .input-group:focus-within > .input-group-text,
    html[data-bs-theme="dark"] :not(.search-form) > .input-group:focus-within > .form-control {
        border-color: rgba(99, 140, 255, 0.55) !important;
    }
    html[data-bs-theme="dark"] .input-group > .form-control:focus {
        box-shadow: none;
    }
    /* Icon + input groups: join the two parts into one field (the global .form-control radius
       rounds the input's left corners and leaves a notch at the seam). */
    .input-group > .input-group-text:first-child:not(:last-child) {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .input-group > .input-group-text:first-child + .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    /* Header "Search menu..." pill: it is hard-coded white; give it a dark-theme look. */
    html[data-bs-theme="dark"] .navbar .search-form .input-group {
        background: #171a21 !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
    }
    html[data-bs-theme="dark"] .navbar .search-form .input-group:focus-within {
        border-color: rgba(99, 140, 255, 0.55) !important;
        box-shadow: 0 0 0 3px rgba(99, 140, 255, 0.14) !important;
    }
    html[data-bs-theme="dark"] .navbar .search-form .input-group-text {
        color: #9aa7bb;
    }
    html[data-bs-theme="dark"] .navbar .search-form .form-control {
        color: #f4f7fb;
    }
    :root {
        --pd-surface: #ffffff;
        --pd-muted-surface: #eef3fb;
        --pd-border: #e4eaf4;
        --pd-text: #1d2b4a;
        --pd-muted: #7a8699;
        --pd-primary: #5b6cff;
        --pd-primary-dark: #4a58e8;
        --pd-primary-rgb: 91, 108, 255;
        --pd-primary-soft: rgba(91, 108, 255, 0.14);
        --pd-sidebar: #ffffff;
        --pd-sidebar-soft: #eef0ff;
        --pd-sidebar-text: #2a3550;
    }
    body {
        font-family: 'Inter', sans-serif;
        color: var(--pd-text);
        background: linear-gradient(180deg, #eaf1fb 0%, #f4f2fb 48%, #eef4fc 100%) !important;
    }
    h1, h2, h3, h4, h5, h6, .navbar-brand, .sidebar-brand, .card-title, .fw-bold {
        font-family: 'Outfit', sans-serif;
    }

    nav.sidebar,
    nav.sidebar .sidebar-header,
    nav.sidebar .sidebar-body,
    nav.sidebar .sidebar-footer {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar.pd-sidebar {
        --pd-sb-bg: #f6f5f3;
        --pd-sb-ink: #171717;
        --pd-sb-muted: #6b7280;
        --pd-sb-line: #e6e4df;
        --pd-sb-hover: rgba(23, 23, 23, 0.05);
        --pd-sb-active: #ffffff;
        --pd-sb-accent: #e63946;
        display: flex !important;
        flex-direction: column;
        background: var(--pd-sb-bg) !important;
        box-shadow: none !important;
        border-right: 1px solid var(--pd-sb-line) !important;
    }
    nav.sidebar.pd-sidebar .sidebar-header {
        flex: 0 0 64px;
        height: 64px;
        padding: 0 14px 0 16px;
        border-bottom: 1px solid var(--pd-sb-line) !important;
        background: transparent !important;
    }
    nav.sidebar.pd-sidebar .pd-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none !important;
        min-width: 0;
    }
    nav.sidebar.pd-sidebar .pd-brand__text {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: var(--pd-sb-ink) !important;
        letter-spacing: -0.04em;
        line-height: 1;
    }
    nav.sidebar.pd-sidebar .pd-brand__text span {
        color: var(--pd-sb-accent) !important;
        margin-left: 1px;
    }
    nav.sidebar.pd-sidebar .sidebar-header .sidebar-toggler span {
        background: #9ca3af !important;
    }
    nav.sidebar.pd-sidebar .sidebar-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        height: auto !important;
        max-height: none !important;
        overflow: hidden;
        position: relative;
        border-right: 0 !important;
        background: transparent !important;
        padding: 14px 12px 8px;
    }
    nav.sidebar.pd-sidebar .pd-nav-eyebrow {
        padding: 0 10px 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav#sidebarNav {
        flex: 1 1 auto;
        min-height: 0;
        padding: 0 !important;
        gap: 4px;
        display: flex !important;
        flex-direction: column;
        justify-content: flex-start;
        /* Bootstrap's .nav is flex-wrap: wrap. In a fixed-height column that pushes the items that don't fit
           into a second, hidden column (Payments / Driver Payments vanished once a group was expanded).
           Keep one column and let it scroll instead. */
        flex-wrap: nowrap;
        overflow-y: auto;
        overflow-x: hidden;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav#sidebarNav > li {
        flex: 0 0 auto;
    }
    nav.sidebar.pd-sidebar .sidebar-footer {
        flex: 0 0 auto;
        padding: 10px 12px 12px;
        border-top: 1px solid var(--pd-sb-line) !important;
        background: transparent !important;
    }
    nav.sidebar.pd-sidebar .pd-footer-nav {
        padding: 0 !important;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav > .nav-item + .nav-item,
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item + .nav-item,
    nav.sidebar.pd-sidebar .pd-footer-nav > .nav-item + .nav-item {
        margin-top: 0;
    }

    nav.sidebar.pd-sidebar .pd-nav-section {
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 0;
        background: transparent;
        border: 0;
        box-shadow: none;
        overflow: visible;
    }
    nav.sidebar.pd-sidebar .pd-nav-section.is-open,
    nav.sidebar.pd-sidebar .pd-nav-section:has(> .pd-nav-section-toggle[aria-expanded="true"]) {
        background: transparent;
        border: 0;
        box-shadow: none;
        padding: 0;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        height: 40px;
        padding: 0 10px 0 8px !important;
        border-radius: 10px !important;
        color: var(--pd-sb-ink) !important;
        font-size: 13.5px;
        font-weight: 600;
        letter-spacing: -0.015em;
        text-decoration: none !important;
        background: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease;
        cursor: pointer;
        box-shadow: none !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle:hover {
        background: var(--pd-sb-hover) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle.active,
    nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"] {
        background: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section--link .pd-nav-section-toggle.active {
        background: var(--pd-sb-hover) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section--link .pd-nav-section-toggle.active .link-icon {
        color: var(--pd-sb-accent) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle.active:hover,
    nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"]:hover {
        background: var(--pd-sb-hover) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-ico {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"] .pd-nav-ico,
    nav.sidebar.pd-sidebar .pd-nav-section-toggle.active .pd-nav-ico {
        background: transparent;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle .link-icon {
        position: static !important;
        width: 16px !important;
        height: 16px !important;
        flex: 0 0 16px;
        color: #52525b !important;
        stroke-width: 1.75;
        fill: none !important;
        opacity: 1;
        margin: 0 !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"] .link-icon,
    nav.sidebar.pd-sidebar .pd-nav-section-toggle.active .link-icon {
        color: var(--pd-sb-accent) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle .link-title {
        margin-left: 0 !important;
        flex: 1;
        min-width: 0;
        line-height: 1.2;
        font-weight: 600;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle .link-arrow {
        margin-left: auto;
        width: 14px;
        height: 14px;
        opacity: 0.35;
        flex: 0 0 14px;
        color: #71717a !important;
        transition: transform 0.18s ease;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"] .link-arrow {
        transform: rotate(180deg);
        opacity: 0.55;
    }
    nav.sidebar.pd-sidebar .pd-nav-badge {
        display: none !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list {
        list-style: none;
        margin: 2px 0 8px 18px;
        padding: 2px 0 2px 10px;
        display: block;
        border-left: 1px solid #e4e0d8;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        height: 32px !important;
        padding: 0 8px !important;
        border-radius: 7px !important;
        color: #57534e !important;
        font-size: 12.75px !important;
        font-weight: 500 !important;
        background: transparent !important;
        box-shadow: none !important;
        text-decoration: none !important;
        transition: background-color 0.12s ease, color 0.12s ease;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link .link-title {
        margin-left: 0 !important;
        flex: 1;
        min-width: 0;
        font-weight: 500;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link .link-arrow {
        width: 12px;
        height: 12px;
        opacity: 0.35;
        margin-left: auto;
        flex: 0 0 12px;
        transition: transform 0.18s ease;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link[aria-expanded="true"] .link-arrow {
        transform: rotate(180deg);
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link:hover {
        color: var(--pd-sb-ink) !important;
        background: rgba(23, 23, 23, 0.04) !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item.active > .nav-link,
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link.active {
        color: var(--pd-sb-ink) !important;
        background: rgba(23, 23, 23, 0.06) !important;
        font-weight: 600 !important;
        box-shadow: none !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item.active > .nav-link::before,
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link.active::before {
        content: '';
        position: absolute;
        left: -11px;
        top: 50%;
        width: 5px;
        height: 5px;
        margin-top: -2.5px;
        border-radius: 50%;
        background: var(--pd-sb-accent);
        display: block !important;
    }

    nav.sidebar.pd-sidebar .sidebar-body .nav .nav-item .nav-link,
    nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        height: 38px !important;
        padding: 0 10px !important;
        border-radius: 10px !important;
        color: var(--pd-sb-ink) !important;
        font-size: 13.5px;
        font-weight: 500;
        letter-spacing: -0.01em;
        background: transparent !important;
        box-shadow: none !important;
        transform: none !important;
        transition: background-color 0.15s ease, color 0.15s ease;
        text-decoration: none !important;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item .nav-link .link-icon {
        position: static !important;
        width: 16px !important;
        height: 16px !important;
        flex: 0 0 16px;
        color: #4b5563 !important;
        stroke-width: 1.8;
        fill: none !important;
        opacity: 1;
        margin: 0 !important;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item .nav-link .link-title {
        margin-left: 0 !important;
        flex: 1;
        font-weight: 500;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav-item > .nav-link:hover {
        background: var(--pd-sb-hover) !important;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav-item.active > .nav-link,
    nav.sidebar.pd-sidebar .sidebar-footer .nav-item > .nav-link.active {
        background: #fff !important;
        box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
        font-weight: 600;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav .nav-item.active > .nav-link::before,
    nav.sidebar.pd-sidebar .sidebar-body .nav .nav-item > .nav-link.active::before,
    nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item.active > .nav-link::before,
    nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item > .nav-link.active::before {
        display: none !important;
    }
    nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item.has-sub,
    nav.sidebar.pd-sidebar .sidebar-body .nav > .nav-item.has-sub {
        background: transparent;
        border-radius: 8px;
        padding: 0;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu {
        position: relative;
        padding: 2px 0 4px !important;
        margin: 0 0 2px 6px !important;
        border-left: 1px solid #e4e0d8;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        height: 28px !important;
        padding: 0 8px !important;
        border-radius: 6px !important;
        color: #78716c !important;
        font-size: 12px;
        font-weight: 500;
        background: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        display: none !important;
        content: none !important;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover {
        color: var(--pd-sb-ink) !important;
        background: rgba(23, 23, 23, 0.04) !important;
    }
    nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        color: var(--pd-sb-ink) !important;
        background: rgba(23, 23, 23, 0.06) !important;
        font-weight: 600;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav-item-logout .nav-link:hover {
        color: var(--pd-sb-accent) !important;
        background: rgba(230, 57, 70, 0.08) !important;
    }
    nav.sidebar.pd-sidebar .sidebar-footer .nav-item-logout .nav-link:hover .link-icon {
        color: var(--pd-sb-accent) !important;
    }
    @media (min-width: 992px) {
        body.sidebar-folded nav.sidebar .pd-nav-eyebrow,
        body.sidebar-folded nav.sidebar .link-title,
        body.sidebar-folded nav.sidebar .link-arrow,
        body.sidebar-folded nav.sidebar .pd-nav-badge,
        body.sidebar-folded nav.sidebar .nav-category,
        body.sidebar-folded nav.sidebar .pd-nav-section-label,
        body.sidebar-folded nav.sidebar .collapse,
        body.sidebar-folded nav.sidebar .sub-menu {
            display: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section {
            padding: 0 !important;
            margin: 0 0 4px !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section.is-open,
        body.sidebar-folded nav.sidebar .pd-nav-section:has(> .pd-nav-section-toggle[aria-expanded="true"]) {
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle {
            justify-content: center;
            width: 44px;
            margin: 0 auto;
            padding: 0 !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-ico {
            margin: 0;
        }
        body.sidebar-folded nav.sidebar .sidebar-header {
            justify-content: center !important;
            padding: 0 !important;
        }
        body.sidebar-folded nav.sidebar .pd-brand,
        body.sidebar-folded nav.sidebar .pd-brand__text {
            display: none !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-header .sidebar-toggler {
            display: block !important;
            width: 20px;
            opacity: 1 !important;
            visibility: visible !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-footer {
            padding: 8px;
        }
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link {
            justify-content: center;
            width: 44px;
            margin: 0 auto;
            padding: 0 !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link.active,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link:hover,
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle:hover {
            background: rgba(17, 24, 39, 0.06) !important;
            box-shadow: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle.active,
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle[aria-expanded="true"] {
            background: transparent !important;
            box-shadow: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle.active:hover,
        body.sidebar-folded nav.sidebar .pd-nav-section-toggle[aria-expanded="true"]:hover {
            background: rgba(17, 24, 39, 0.06) !important;
        }
    }

    [data-bs-theme="dark"] nav.sidebar.pd-sidebar {
        --pd-sb-bg: #141414;
        --pd-sb-ink: #f3f4f6;
        --pd-sb-muted: #9ca3af;
        --pd-sb-line: #2a2a2a;
        --pd-sb-hover: rgba(255, 255, 255, 0.06);
        --pd-sb-active: #1c1c1c;
        --pd-sb-accent: #ff4d6d;
        background: var(--pd-sb-bg) !important;
        border-right: 1px solid var(--pd-sb-line) !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-header,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-body,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-footer {
        background: transparent !important;
        border-color: var(--pd-sb-line) !important;
        box-shadow: none !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-brand__text {
        color: #f3f4f6 !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-brand__text span {
        color: #ff4d6d !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-ico {
        background: rgba(255, 255, 255, 0.06);
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-toggle .link-icon {
        color: #d1d5db !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-toggle[aria-expanded="true"] .link-icon,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-toggle.active .link-icon {
        color: #ff4d6d !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-list,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu {
        border-left-color: #333;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link {
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .pd-nav-section-list > .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-footer .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-footer .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        background: rgba(255, 255, 255, 0.06) !important;
        color: #f9fafb !important;
        box-shadow: none;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item .nav-link {
        color: #e5e7eb !important;
    }
    [data-bs-theme="dark"] nav.sidebar.pd-sidebar .sidebar-footer .nav .nav-item .nav-link .link-icon {
        color: #9ca3af !important;
    }
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-label,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        color: #8e8ea0 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav .nav-item .nav-link {
        color: #e5e7eb !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-icon,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav .nav-item .nav-link .link-icon {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item > .nav-link:hover {
        color: #f9fafb !important;
        background: rgba(255, 255, 255, 0.07) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item > .nav-link.active {
        color: #f9fafb !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.active > .nav-link .link-icon,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item > .nav-link.active .link-icon {
        color: #ff4d6d !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]),
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]) {
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"],
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active),
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link[aria-expanded="true"],
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active) {
        color: #e5e7eb !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link.active {
        color: #f9fafb !important;
        background: rgba(255, 255, 255, 0.07) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu {
        border-left-color: #333;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        color: #94a3b8 !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        display: none !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        color: #f9fafb !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item-logout .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item-logout .nav-link:hover {
        color: #ff8a9a !important;
        background: rgba(255, 77, 109, 0.12) !important;
    }
    @media (min-width: 992px) {
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item.active > .nav-link,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link.active,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item.active > .nav-link,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item > .nav-link.active,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item > .nav-link:hover,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link.active,
        [data-bs-theme="dark"] body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link:hover {
            color: #ececec !important;
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
        height: 56px !important;
        background: rgba(255, 255, 255, 0.72) !important;
        backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(228, 234, 244, 0.8) !important;
        box-shadow: none !important;
    }
    [data-bs-theme="dark"] .navbar {
        background: rgba(30, 33, 41, 0.86) !important;
        border-bottom-color: var(--pd-theme-border, rgba(255, 255, 255, 0.08)) !important;
    }
    .card {
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(80, 100, 160, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: var(--pd-theme-card, var(--pd-surface, #ffffff));
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease, background-color 0.16s ease;
    }
    [data-bs-theme="dark"] .card {
        border-color: var(--pd-theme-border, rgba(255,255,255,0.08));
        background: var(--pd-theme-card, #1e2129);
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
    }
    .page-wrapper,
    .page-content {
        background: transparent !important;
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
        background: transparent !important;
        border-top: 0 !important;
    }
    [data-bs-theme="dark"] body {
        background: #151821 !important;
    }
    [data-bs-theme="dark"] .page-wrapper,
    [data-bs-theme="dark"] .page-content {
        background: transparent !important;
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

    /* System-wide UI polish — visual only, no flow change */
    @keyframes pdPageIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pdBarFill {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }
    .page-content.pd-page > * {
        animation: pdPageIn 0.42s ease both;
    }
    .page-content.pd-page > *:nth-child(1) { animation-delay: 0.02s; }
    .page-content.pd-page > *:nth-child(2) { animation-delay: 0.07s; }
    .page-content.pd-page > *:nth-child(3) { animation-delay: 0.12s; }
    .page-content.pd-page > *:nth-child(4) { animation-delay: 0.16s; }
    .page-content.pd-page > *:nth-child(n+5) { animation-delay: 0.2s; }

    .grid-margin {
        margin-bottom: 16px !important;
    }
    .grid-margin h4,
    .page-content h4 {
        font-size: 18px !important;
        letter-spacing: -0.02em;
        margin-bottom: 4px !important;
    }
    .grid-margin p {
        font-size: 12.5px;
        line-height: 1.45;
    }
    .page-wrapper .page-content,
    .page-content.container-fluid {
        padding-top: 18px !important;
        padding-bottom: 20px !important;
    }
    .page-content .card {
        border-radius: 18px !important;
        border-color: rgba(255, 255, 255, 0.9) !important;
        box-shadow: 0 10px 30px rgba(80, 100, 160, 0.08) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .page-content .card:hover {
        border-color: #e2eaf3 !important;
        box-shadow: 0 4px 10px rgba(16, 24, 40, 0.04), 0 16px 36px rgba(16, 24, 40, 0.06) !important;
    }
    .dashboard-stat-card:hover,
    .dashboard-card:hover {
        transform: translateY(-3px);
    }

    .page-content .card .card-body {
        padding: 16px 18px;
    }
    .page-content .card .card-body.p-0 {
        padding: 0 !important;
    }
    .page-content .card .card-body .row {
        --bs-gutter-x: 12px;
        --bs-gutter-y: 10px;
    }
    .page-content .card + .card {
        margin-top: 4px;
    }
    .btn {
        min-height: 34px;
        padding: 0.35rem 0.85rem;
        font-size: 12.5px !important;
        border-radius: 8px !important;
        letter-spacing: 0.01em;
        transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, border-color 0.16s ease, filter 0.16s ease;
    }
    .btn:hover {
        transform: translateY(-1px);
    }
    .btn:active {
        transform: translateY(0);
    }
    .btn-primary {
        background: #5b6cff !important;
        border-color: #5b6cff !important;
        box-shadow: 0 8px 18px rgba(91, 108, 255, 0.22);
    }
    .btn-success {
        background: #16a34a;
        border-color: #16a34a;
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.16);
    }

    .form-control,
    .form-select {
        min-height: 36px !important;
        height: 36px;
        font-size: 13px !important;
        border-radius: 8px !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .navbar .search-form {
        max-width: 360px !important;
        margin-right: 16px !important;
    }
    .navbar .search-form .input-group {
        height: 36px !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        border: 1px solid #e6ebf5 !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .navbar .search-form .input-group:focus-within {
        border-color: rgba(63, 111, 217, 0.4) !important;
        box-shadow: 0 0 0 4px rgba(63, 111, 217, 0.1) !important;
    }

    .table thead th {
        letter-spacing: 0.04em !important;
    }
    .table tbody tr {
        transition: background-color 0.16s ease;
    }
    .table tbody tr:hover {
        background: #f6f8fc !important;
    }
    .dashboard-table tbody tr[role="button"]:hover {
        background: #f3f6fb !important;
    }

    .progress {
        overflow: hidden;
        border-radius: 999px !important;
        background: #edf2f7;
    }
    .progress-bar {
        transform-origin: left center;
        animation: pdBarFill 0.85s cubic-bezier(0.22, 1, 0.36, 1) both;
        border-radius: 999px;
    }

    .badge {
        letter-spacing: 0.01em;
    }

    .sidebar .sidebar-body .nav .nav-item .nav-link {
        transition: background-color 0.12s ease, color 0.12s ease;
    }
    .sidebar .sidebar-body .nav > .nav-item > .nav-link:hover {
        transform: none;
    }
    .sidebar .collapse {
        transition: height 0.24s ease;
    }

    .dropdown-menu {
        border-radius: 12px !important;
        box-shadow: 0 16px 40px rgba(16, 24, 40, 0.12) !important;
        padding: 6px !important;
    }
    .dropdown-item {
        border-radius: 8px;
        transition: background-color 0.14s ease, color 0.14s ease;
    }

    .modal-content {
        border-radius: 16px !important;
        box-shadow: 0 24px 64px rgba(16, 24, 40, 0.16) !important;
    }
    .modal.fade .modal-dialog {
        transform: translateY(16px) scale(0.98);
        transition: transform 0.22s ease;
    }
    .modal.show .modal-dialog {
        transform: none;
    }

    .pagination .page-link {
        border-radius: 10px !important;
    }

    .action-btn {
        transition: transform 0.14s ease, filter 0.14s ease, box-shadow 0.14s ease;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(16, 24, 40, 0.1);
    }

    .dashboard-schedule-item,
    .dashboard-alert-item {
        border-radius: 10px;
        transition: transform 0.18s ease, background-color 0.18s ease;
    }
    .dashboard-schedule-item:hover,
    .dashboard-alert-item:hover {
        transform: translateX(3px);
    }

    [data-bs-theme="dark"] .page-content .card:hover {
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28) !important;
    }
    [data-bs-theme="dark"] .table tbody tr:hover,
    [data-bs-theme="dark"] .dashboard-table tbody tr[role="button"]:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    [data-bs-theme="dark"] .progress {
        background: rgba(255, 255, 255, 0.08);
    }

    /* Header toolbar */
    .navbar .navbar-content {
        display: flex !important;
        align-items: center !important;
        gap: 12px;
        min-width: 0;
        overflow: visible;
    }
    .navbar .search-form {
        flex: 1 1 auto;
        max-width: 320px !important;
        min-width: 0 !important;
        margin-right: 0 !important;
    }
    .navbar .navbar-nav.pd-toolbar {
        display: flex !important;
        flex: 0 0 auto;
        align-items: center !important;
        gap: 10px !important;
        margin-left: auto !important;
        padding-left: 8px;
    }
    .navbar .navbar-nav .nav-item {
        margin: 0 !important;
        min-width: 0 !important;
        display: flex !important;
        align-items: center !important;
        flex: 0 0 auto;
    }
    .navbar .navbar-nav .nav-item .nav-link.dropdown-toggle::after,
    .navbar .navbar-nav .nav-item .nav-link::after {
        display: none !important;
        content: none !important;
        border: 0 !important;
        margin: 0 !important;
    }
    .navbar .navbar-nav .nav-item.dropdown .dropdown-menu {
        right: 0 !important;
        left: auto !important;
        max-width: min(360px, calc(100vw - 24px));
        margin-top: 10px !important;
    }
    .navbar .navbar-nav .nav-item.dropdown .dropdown-menu::before {
        display: none !important;
    }
    .navbar .theme-switcher-wrapper {
        margin: 0 !important;
        width: auto !important;
    }
    .navbar .theme-switcher-wrapper label {
        margin: 0 !important;
        display: flex;
        align-items: center;
    }
    .navbar .theme-switcher-wrapper .box {
        width: 46px !important;
        height: 26px !important;
        background: #ffffff !important;
        border: 1px solid #e6ebf5 !important;
        border-radius: 999px !important;
        box-shadow: 0 4px 12px rgba(80, 100, 160, 0.08);
    }
    .navbar .theme-switcher-wrapper .box .ball {
        width: 20px !important;
        height: 20px !important;
        border-width: 2px !important;
        top: 3px !important;
        left: 3px !important;
        background: #5b6cff !important;
        border-color: #ffffff !important;
    }
    .navbar .theme-switcher-wrapper .box.dark .ball {
        transform: translateX(20px) !important;
        background: #fbbf24 !important;
        border-color: #1e2129 !important;
    }
    .navbar .theme-switcher-wrapper .box .icons svg {
        width: 12px !important;
        height: 12px !important;
        color: #8a96a8 !important;
    }
    .navbar .pd-icon-btn {
        position: relative !important;
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 999px !important;
        overflow: visible !important;
        color: #5a6780 !important;
        background: #ffffff !important;
        border: 1px solid #e6ebf5 !important;
        box-shadow: 0 4px 12px rgba(80, 100, 160, 0.08);
    }
    .navbar .pd-icon-btn:hover,
    .navbar .pd-icon-btn[aria-expanded="true"] {
        color: #5b6cff !important;
        background: #eceeff !important;
        border-color: #d9e0f5 !important;
    }
    .navbar .pd-icon-btn svg {
        width: 17px !important;
        height: 17px !important;
    }
    .navbar .pd-notify-count {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #e11d48;
        color: #fff;
        border: 2px solid #ffffff;
        font-size: 10px;
        font-weight: 800;
        line-height: 14px;
        text-align: center;
        z-index: 2;
        pointer-events: none;
    }
    .navbar .pd-profile {
        padding-left: 10px;
        margin-left: 2px;
        border-left: 1px solid #e6ebf5;
    }
    .navbar .pd-profile-chip {
        height: 38px !important;
        min-height: 38px !important;
        max-width: 180px;
        padding: 3px 10px 3px 3px !important;
        margin: 0 !important;
        gap: 8px !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        color: #1d2b4a !important;
        border: 1px solid #e6ebf5 !important;
        box-shadow: 0 4px 12px rgba(80, 100, 160, 0.08);
        overflow: hidden !important;
    }
    .navbar .pd-profile-chip:hover,
    .navbar .pd-profile-chip[aria-expanded="true"] {
        background: #f5f7ff !important;
        color: #1d2b4a !important;
        border-color: #d9e0f5 !important;
    }
    .navbar .pd-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #5b6cff;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        line-height: 30px;
        text-align: center;
        flex: 0 0 30px;
        display: inline-block;
    }
    .navbar .pd-profile-meta {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        line-height: 1;
    }
    .navbar .pd-profile-name {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.1;
        color: inherit;
        max-width: 110px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .navbar .pd-profile-caret {
        width: 14px !important;
        height: 14px !important;
        opacity: 0.45;
        flex: 0 0 14px;
    }
    [data-bs-theme="dark"] .navbar .theme-switcher-wrapper .box,
    [data-bs-theme="dark"] .navbar .pd-icon-btn,
    [data-bs-theme="dark"] .navbar .pd-profile-chip {
        background: rgba(255, 255, 255, 0.06) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #e8eef7 !important;
    }
    [data-bs-theme="dark"] .navbar .pd-icon-btn:hover,
    [data-bs-theme="dark"] .navbar .pd-icon-btn[aria-expanded="true"],
    [data-bs-theme="dark"] .navbar .pd-profile-chip:hover,
    [data-bs-theme="dark"] .navbar .pd-profile-chip[aria-expanded="true"] {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .navbar .pd-profile {
        border-left-color: rgba(255, 255, 255, 0.1);
    }
    [data-bs-theme="dark"] .navbar .pd-notify-count {
        border-color: #1e2129;
    }
    @media (max-width: 1280px) {
        .navbar .search-form { max-width: 220px !important; }
        .navbar .pd-profile-name { max-width: 80px; }
    }
    @media (max-width: 1100px) {
        .navbar .search-form { max-width: 160px !important; }
        .navbar .pd-profile-meta,
        .navbar .pd-profile-caret { display: none !important; }
        .navbar .pd-profile-chip {
            width: 38px;
            max-width: 38px;
            padding: 3px !important;
            justify-content: center;
        }
        .navbar .pd-profile { border-left: 0; padding-left: 0; }
    }
    @media (max-width: 767.98px) {
        .navbar .search-form { display: none !important; }
        .navbar .navbar-nav.pd-toolbar { gap: 8px !important; }
    }

    /* Dashboard stat cards */
    .dashboard-header h4 {
        font-size: 18px !important;
        letter-spacing: -0.02em;
    }
    .dashboard-stat-card .card-body {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 18px;
        min-height: 108px !important;
        padding: 20px 22px !important;
    }
    .dashboard-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0;
        flex: 0 0 42px;
        box-shadow: none !important;
    }
    .dashboard-stat-icon svg {
        width: 18px;
        height: 18px;
    }
    .dashboard-stat-icon.is-blue { background: #eceeff; color: #5b6cff; }
    .dashboard-stat-icon.is-green { background: #e6fbf6; color: #14b8a6; }
    .dashboard-stat-icon.is-teal { background: #e8f7ff; color: #0ea5e9; }
    .dashboard-stat-icon.is-red { background: #fff1e8; color: #f97316; }
    .dashboard-stat-label {
        margin: 4px 0 0 !important;
        color: #7a8699 !important;
        font-size: 13px !important;
        font-weight: 500;
        letter-spacing: 0;
    }
    .dashboard-stat-card h3 {
        margin: 0 !important;
        font-size: 28px !important;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        color: #1d2b4a;
    }
    .dashboard-stat-meta {
        color: #8a96a8 !important;
        font-size: 11.5px !important;
    }
    .dashboard-card-header .card-title {
        font-size: 13.5px !important;
        letter-spacing: -0.01em;
    }
    .table {
        --bs-table-cell-padding-x: 16px;
    }
    .table thead th {
        font-size: 11px !important;
        padding: 12px 16px !important;
        letter-spacing: 0.04em !important;
    }
    .table thead th.ps-4,
    .table tbody td.ps-4 {
        padding-left: 20px !important;
    }
    .table tbody td {
        font-size: 13px !important;
        padding: 14px 16px !important;
        line-height: 1.4;
        vertical-align: middle !important;
    }
    .table tbody td .fw-semibold,
    .table tbody td p.mb-0,
    .table tbody td p.fw-semibold {
        margin-bottom: 0;
        line-height: 1.35;
    }
    .table tbody td .fw-semibold + small,
    .table tbody td p.mb-0 + small,
    .table tbody td .fw-semibold + .text-muted,
    .table tbody td small.text-muted,
    .table tbody td small.text-secondary {
        display: block;
        margin-top: 4px;
        line-height: 1.35;
    }
    .table .badge {
        padding: 0.4em 0.75em !important;
        font-weight: 600;
    }
    .table code {
        color: #3f4f68;
        background: #f3f6fa;
        padding: 3px 7px;
        border-radius: 6px;
        font-size: 12px;
    }
    [data-bs-theme="dark"] .table code {
        color: #d5deea;
        background: rgba(255,255,255,0.06);
    }
    .app-pagination-footer,
    .notification-pagination {
        padding: 14px 20px !important;
    }
    [data-bs-theme="dark"] .dashboard-stat-card h3 {
        color: #f4f7fb !important;
    }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-blue { background: rgba(63, 111, 217, 0.18); color: #93b4ff; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-green { background: rgba(22, 163, 74, 0.18); color: #86efac; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-teal { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-red { background: rgba(225, 29, 72, 0.18); color: #fda4af; }

    .pd-stat-ring {
        position: relative;
        width: 74px;
        height: 74px;
        flex: 0 0 74px;
    }
    .pd-stat-ring svg {
        width: 74px;
        height: 74px;
        display: block;
        transform: rotate(-90deg);
    }
    .pd-stat-ring-track,
    .pd-stat-ring-bar {
        fill: none;
        stroke-width: 3.6;
    }
    .pd-stat-ring-track { stroke: #edf0f7; }
    .pd-stat-ring-bar {
        stroke-linecap: round;
        animation: pdBarFill 0.85s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .pd-stat-ring.is-blue .pd-stat-ring-track { stroke: #eceeff; }
    .pd-stat-ring.is-blue .pd-stat-ring-bar { stroke: #5b6cff; }
    .pd-stat-ring.is-green .pd-stat-ring-track { stroke: #e6fbf6; }
    .pd-stat-ring.is-green .pd-stat-ring-bar { stroke: #14b8a6; }
    .pd-stat-ring.is-teal .pd-stat-ring-track { stroke: #e8f7ff; }
    .pd-stat-ring.is-teal .pd-stat-ring-bar { stroke: #0ea5e9; }
    .pd-stat-ring.is-orange .pd-stat-ring-track { stroke: #fff1e8; }
    .pd-stat-ring.is-orange .pd-stat-ring-bar { stroke: #f97316; }
    .pd-stat-ring.is-purple .pd-stat-ring-track { stroke: #f3e8ff; }
    .pd-stat-ring.is-purple .pd-stat-ring-bar { stroke: #a855f7; }
    .pd-stat-ring span {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: Outfit, sans-serif;
        font-size: 13px;
        font-weight: 800;
        color: #1d2b4a;
        line-height: 1;
    }
    [data-bs-theme="dark"] .pd-stat-ring-track { stroke: rgba(255,255,255,0.08); }
    [data-bs-theme="dark"] .pd-stat-ring.is-blue .pd-stat-ring-track { stroke: rgba(91,108,255,0.18); }
    [data-bs-theme="dark"] .pd-stat-ring.is-green .pd-stat-ring-track { stroke: rgba(20,184,166,0.18); }
    [data-bs-theme="dark"] .pd-stat-ring.is-teal .pd-stat-ring-track { stroke: rgba(14,165,233,0.18); }
    [data-bs-theme="dark"] .pd-stat-ring.is-orange .pd-stat-ring-track { stroke: rgba(249,115,22,0.18); }
    [data-bs-theme="dark"] .pd-stat-ring.is-purple .pd-stat-ring-track { stroke: rgba(168,85,247,0.18); }
    [data-bs-theme="dark"] .pd-stat-ring span { color: #f4f7fb; }

    @media (prefers-reduced-motion: reduce) {
        .page-content.pd-page > *,
        .progress-bar,
        .dashboard-stat-card,
        .dashboard-card {
            animation: none !important;
        }
        .btn:hover,
        .dashboard-stat-card:hover,
        .dashboard-card:hover,
        .sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
        .dashboard-schedule-item:hover,
        .dashboard-alert-item:hover,
        .action-btn:hover {
            transform: none !important;
        }
    }
  </style>

  <?php echo $__env->yieldPushContent('style'); ?>
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
            'dashboard': "<?php echo e(route('dashboard')); ?>",

            'user': "<?php echo e(route('users.index')); ?>",
            'users': "<?php echo e(route('users.index')); ?>",
            'login logs': "<?php echo e(route('login-logs.index')); ?>",
            'login log': "<?php echo e(route('login-logs.index')); ?>",
            'logs': "<?php echo e(route('login-logs.index')); ?>",

            'vehicle': "<?php echo e(route('vehicles.index')); ?>",
            'vehicles': "<?php echo e(route('vehicles.index')); ?>",

            'vehicle category': "<?php echo e(route('vehicle-categories.index')); ?>",
            'categories': "<?php echo e(route('vehicle-categories.index')); ?>",

            'city': "<?php echo e(route('locations.cities.index')); ?>",
            'cities': "<?php echo e(route('locations.cities.index')); ?>",

            'area': "<?php echo e(route('locations.areas.index')); ?>",
            'areas': "<?php echo e(route('locations.areas.index')); ?>",

            'route': "<?php echo e(route('routes.index')); ?>",
            'routes': "<?php echo e(route('routes.index')); ?>",

            'charges': "<?php echo e(route('charges.index')); ?>",

            'payments': "<?php echo e(route('payments.index')); ?>",

            'payroll': "<?php echo e(route('driver-payroll.index')); ?>",
            'payrolls': "<?php echo e(route('payrolls.index')); ?>",
            'payouts': "<?php echo e(route('driver-payroll.index')); ?>",
            'driver payments': "<?php echo e(route('driver-payroll.index')); ?>",
            'driver payment': "<?php echo e(route('driver-payroll.index')); ?>",
            'driver payroll': "<?php echo e(route('payrolls.index')); ?>",

            'calendar': "<?php echo e(route('holidays.index')); ?>",
            'holiday': "<?php echo e(route('holidays.index')); ?>",
            'holidays': "<?php echo e(route('holidays.index')); ?>",
            'chutti': "<?php echo e(route('holidays.index')); ?>",

            'complaints': "<?php echo e(route('issues.index')); ?>",
            'complaint': "<?php echo e(route('issues.index')); ?>",
            'issues': "<?php echo e(route('issues.index')); ?>",

            'reports': "<?php echo e(route('reports.index')); ?>",

            'profile': "<?php echo e(route('general.profile')); ?>"
        };

        if (routes[keyword]) {

            window.location.href = routes[keyword];

            return;
        }

        alert('No matching menu found.');

    });

});
</script>
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
    <?php echo $__env->make('layout.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="page-wrapper">
      <?php echo $__env->make('layout.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="page-content pd-page <?php echo $__env->yieldContent('page-content-class', 'container-fluid'); ?>">
        <?php echo $__env->yieldContent('content'); ?>
      </div>
      <?php echo $__env->make('layout.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
  </div>

    <!-- base js -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <script src="<?php echo e(asset('build/plugins/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('build/plugins/lucide/lucide.min.js')); ?>"></script>
    <script src="<?php echo e(asset('build/plugins/perfect-scrollbar/perfect-scrollbar.min.js')); ?>"></script>
    <!-- end base js -->

    <!-- plugin js -->
    <?php echo $__env->yieldPushContent('plugin-scripts'); ?>
    <!-- end plugin js -->

    <!-- common js -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/template.js']); ?>
    <!-- end common js -->

    <!-- SweetAlert Base Included in Master for global use -->
    <script src="<?php echo e(asset('build/plugins/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if(session('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: <?php echo json_encode(session('success'), 15, 512) ?>,
                timer: 3000,
                showConfirmButton: false,
                scrollbarPadding: false,
                customClass: { popup: 'rounded-4' }
            });
        <?php endif; ?>
        <?php if(session('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: <?php echo json_encode(session('error'), 15, 512) ?>,
                timer: 3000,
                showConfirmButton: true,
                scrollbarPadding: false,
                customClass: { popup: 'rounded-4' }
            });
        <?php endif; ?>
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const nav = document.getElementById('sidebarNav');
        if (!nav) return;

        const sectionIds = ['nav-people', 'nav-locations', 'nav-vehicles', 'nav-operations', 'nav-payments'];

        sectionIds.forEach(function (id) {
            const panel = document.getElementById(id);
            if (!panel) return;

            panel.addEventListener('show.bs.collapse', function () {
                sectionIds.forEach(function (otherId) {
                    if (otherId === id) return;
                    const other = document.getElementById(otherId);
                    if (other && other.classList.contains('show') && window.bootstrap) {
                        bootstrap.Collapse.getOrCreateInstance(other, { toggle: false }).hide();
                    }
                });
            });

            panel.addEventListener('shown.bs.collapse', function () {
                const section = panel.closest('.pd-nav-section');
                if (section) section.classList.add('is-open');
            });

            panel.addEventListener('hidden.bs.collapse', function () {
                const section = panel.closest('.pd-nav-section');
                if (section) section.classList.remove('is-open');
            });
        });
    });
    </script>

    <?php echo $__env->yieldPushContent('custom-scripts'); ?>
    <?php echo $__env->make('layout.partials.password-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layout.partials.form-errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/layout/master.blade.php ENDPATH**/ ?>