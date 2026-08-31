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
        height: 56px;
        padding: 0 16px;
        border-bottom: 1px solid var(--pd-theme-border, #eef1f6) !important;
    }
    nav.sidebar .sidebar-header .sidebar-brand {
        font-weight: 800;
        font-size: 18px;
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
        padding: 10px 10px 16px !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item + .nav-item {
        margin-top: 1px;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        height: auto !important;
        margin: 12px 8px 4px !important;
        color: #9aa6b8 !important;
        font-size: 9px !important;
        font-weight: 700 !important;
        letter-spacing: 0.12em !important;
        text-transform: uppercase;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category:first-child {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 9px;
        height: 32px !important;
        padding: 0 8px !important;
        border-radius: 8px !important;
        color: #1d3557 !important;
        font-size: 13px;
        font-weight: 600;
        background: transparent !important;
        box-shadow: none !important;
        transition: background-color 0.16s ease, color 0.16s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-icon {
        position: static !important;
        width: 16px !important;
        height: 16px !important;
        flex: 0 0 16px;
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
        color: #3f6fd9 !important;
        background: rgba(63, 111, 217, 0.08) !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub).active > .nav-link,
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub) > .nav-link.active,
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active {
        color: #3f6fd9 !important;
        background: rgba(63, 111, 217, 0.12) !important;
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
        background: var(--pd-sidebar-bg-soft, #f3f5f8);
    }
    nav.sidebar .sidebar-body .nav.sub-menu {
        position: relative;
        padding: 4px 6px 6px 0 !important;
        margin: 0 4px 2px 18px !important;
        border-left: 1px solid #e6ebf2;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        height: 28px !important;
        padding: 0 8px 0 16px !important;
        border-radius: 7px !important;
        color: #5b6b82 !important;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        content: "" !important;
        display: block !important;
        position: absolute !important;
        left: 4px !important;
        top: 50% !important;
        right: auto !important;
        bottom: auto !important;
        width: 6px !important;
        height: 6px !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 50% !important;
        background: #c5cedb !important;
        transform: translateY(-50%) !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover {
        color: #111827 !important;
        background: #eef2f7 !important;
        font-weight: 600;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover::before,
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active::before {
        background: #1d3557 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
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
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]) {
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"],
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active) {
        color: #d5deea !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu {
        border-left-color: rgba(255, 255, 255, 0.12);
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        color: #c8d0dc !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: rgba(255, 255, 255, 0.4) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.12) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover::before,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active::before {
        background: #ffffff !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
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
        height: 56px !important;
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
        border-radius: 10px !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.03), 0 10px 28px rgba(16, 24, 40, 0.04) !important;
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
        box-shadow: 0 8px 18px rgba(63, 111, 217, 0.18);
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
        max-width: 280px !important;
        margin-right: 16px !important;
    }
    .navbar .search-form .input-group {
        height: 34px !important;
        border-radius: 8px !important;
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
        transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }
    .sidebar .sidebar-body .nav > .nav-item > .nav-link:hover {
        transform: translateX(1px);
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

    /* Header: hide bootstrap caret + align profile / bell */
    .navbar .navbar-nav {
        align-items: center !important;
        gap: 8px;
    }
    .navbar .navbar-nav .nav-item {
        margin: 0 !important;
        min-width: 0 !important;
        display: flex !important;
        align-items: center !important;
    }
    .navbar .navbar-nav .nav-item .nav-link.dropdown-toggle::after,
    .navbar .navbar-nav .nav-item .nav-link::after {
        display: none !important;
        content: none !important;
        border: 0 !important;
        margin: 0 !important;
    }
    .navbar .navbar-nav .nav-item.dropdown .dropdown-menu::before {
        display: none !important;
    }
    .navbar .pd-icon-btn {
        position: relative !important;
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 9px !important;
        overflow: visible !important;
        color: #4b5b73 !important;
        background: #f4f6fa !important;
    }
    .navbar .pd-icon-btn:hover,
    .navbar .pd-icon-btn[aria-expanded="true"] {
        color: #3f6fd9 !important;
        background: #e8eefc !important;
    }
    .navbar .pd-icon-btn svg {
        width: 18px !important;
        height: 18px !important;
    }
    .navbar .pd-notify-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #e11d48;
        border: 2px solid #ffffff;
        box-shadow: none;
        z-index: 2;
        pointer-events: none;
    }
    .navbar .pd-profile-chip {
        height: 34px !important;
        min-height: 34px !important;
        padding: 3px 8px 3px 3px !important;
        margin: 0 !important;
        gap: 7px !important;
        border-radius: 999px !important;
        background: #f4f6fa !important;
        color: #1d3557 !important;
        overflow: visible !important;
    }
    .navbar .pd-profile-chip:hover,
    .navbar .pd-profile-chip[aria-expanded="true"] {
        background: #e8eefc !important;
        color: #1d3557 !important;
    }
    .navbar .pd-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #3f6fd9;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        line-height: 26px;
        text-align: center;
        flex: 0 0 26px;
        display: inline-block;
    }
    .navbar .pd-profile-meta {
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        line-height: 1;
    }
    .navbar .pd-profile-name {
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.1;
        color: inherit;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .navbar .pd-profile-caret {
        width: 14px !important;
        height: 14px !important;
        opacity: 0.5;
        flex: 0 0 14px;
    }

    /* Dashboard stat cards */
    .dashboard-header h4 {
        font-size: 18px !important;
        letter-spacing: -0.02em;
    }
    .dashboard-stat-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 0 !important;
        padding: 14px 16px !important;
    }
    .dashboard-stat-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        box-shadow: none !important;
    }
    .dashboard-stat-icon svg {
        width: 14px;
        height: 14px;
    }
    .dashboard-stat-icon.is-blue { background: #eef4ff; color: #3f6fd9; }
    .dashboard-stat-icon.is-green { background: #ecfdf3; color: #16a34a; }
    .dashboard-stat-icon.is-teal { background: #eefafc; color: #0ea5e9; }
    .dashboard-stat-icon.is-red { background: #fff1f2; color: #e11d48; }
    .dashboard-stat-label {
        margin: 0 0 2px !important;
        color: #6b7a90 !important;
        font-size: 11.5px !important;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .dashboard-stat-card h3 {
        margin: 0 0 2px !important;
        font-size: 20px !important;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.15;
        color: #152238;
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

    [data-bs-theme="dark"] .navbar .pd-icon-btn,
    [data-bs-theme="dark"] .navbar .pd-profile-chip {
        background: rgba(255, 255, 255, 0.06) !important;
        color: #e8eef7 !important;
    }
    [data-bs-theme="dark"] .navbar .pd-icon-btn:hover,
    [data-bs-theme="dark"] .navbar .pd-icon-btn[aria-expanded="true"],
    [data-bs-theme="dark"] .navbar .pd-profile-chip:hover,
    [data-bs-theme="dark"] .navbar .pd-profile-chip[aria-expanded="true"] {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .navbar .pd-notify-dot {
        border-color: #1e2129;
    }
    [data-bs-theme="dark"] .dashboard-stat-card h3 {
        color: #f4f7fb !important;
    }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-blue { background: rgba(63, 111, 217, 0.18); color: #93b4ff; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-green { background: rgba(22, 163, 74, 0.18); color: #86efac; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-teal { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; }
    [data-bs-theme="dark"] .dashboard-stat-icon.is-red { background: rgba(225, 29, 72, 0.18); color: #fda4af; }

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
      <div class="page-content pd-page @yield('page-content-class', 'container-fluid')">
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
