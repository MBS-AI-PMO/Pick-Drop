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
    nav.sidebar {
        display: flex !important;
        flex-direction: column;
        background: #f7f7f8 !important;
        box-shadow: none !important;
        border-right: 1px solid #ececec !important;
    }
    nav.sidebar .sidebar-header {
        flex: 0 0 52px;
        height: 52px;
        padding: 0 12px 0 14px;
        border-bottom: 0 !important;
    }
    nav.sidebar .sidebar-header .sidebar-brand {
        font-weight: 700;
        font-size: 16px;
        color: #0d0d0d !important;
        text-decoration: none;
        display: inline-block;
        letter-spacing: -0.02em;
        margin-bottom: 0;
        padding: 4px 0;
    }
    nav.sidebar .sidebar-header .sidebar-brand span {
        color: #e63946 !important;
        font-weight: 700;
        margin-left: 3px;
    }
    nav.sidebar .sidebar-header .sidebar-toggler span {
        background: #8e8ea0 !important;
    }
    nav.sidebar .sidebar-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        height: auto !important;
        max-height: none !important;
        overflow: hidden;
        position: relative;
        border-right: 0 !important;
    }
    nav.sidebar .sidebar-body .nav {
        flex: 0 0 auto;
        padding: 4px 8px 12px !important;
    }
    nav.sidebar .sidebar-footer {
        flex: 0 0 auto;
        padding: 8px;
        border-top: 1px solid #ececec !important;
        background: transparent !important;
    }
    nav.sidebar .sidebar-footer .nav {
        padding: 0 !important;
        display: flex;
        flex-direction: column;
    }
    nav.sidebar .sidebar-body .nav > .nav-item + .nav-item,
    nav.sidebar .sidebar-body .pd-nav-section-list > .nav-item + .nav-item,
    nav.sidebar .sidebar-footer .pd-nav-section-list > .nav-item + .nav-item {
        margin-top: 1px;
    }
    nav.sidebar .pd-nav-section {
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 10px 0 2px;
        background: transparent;
        border: 0;
        border-radius: 0;
    }
    nav.sidebar .pd-nav-section + .pd-nav-section {
        margin-top: 2px;
        padding-top: 10px;
        border-top: 0;
    }
    nav.sidebar .pd-nav-section:first-child {
        padding-top: 2px;
    }
    nav.sidebar .pd-nav-section-label {
        display: block;
        padding: 0 10px 6px;
        color: #8e8ea0;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: none;
        line-height: 1;
    }
    nav.sidebar .pd-nav-section-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: block;
    }
    nav.sidebar .sidebar-body .nav .nav-item.nav-category:first-child {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link,
    nav.sidebar .sidebar-footer .nav .nav-item .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        height: 36px !important;
        padding: 0 10px !important;
        border-radius: 10px !important;
        color: #0d0d0d !important;
        font-size: 14px;
        font-weight: 400;
        background: transparent !important;
        box-shadow: none !important;
        transform: none !important;
        transition: background-color 0.12s ease, color 0.12s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-icon,
    nav.sidebar .sidebar-footer .nav .nav-item .nav-link .link-icon {
        position: static !important;
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 18px;
        color: inherit !important;
        stroke-width: 1.75;
        fill: none !important;
        opacity: 0.78;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-title,
    nav.sidebar .sidebar-footer .nav .nav-item .nav-link .link-title {
        margin-left: 0 !important;
        flex: 1;
        min-width: 0;
        line-height: 1.2;
        font-weight: 400;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link .link-arrow {
        margin-left: auto;
        width: 14px;
        height: 14px;
        opacity: 0.4;
        flex: 0 0 14px;
        transition: transform 0.18s ease, opacity 0.12s ease;
    }
    nav.sidebar .sidebar-body .nav .nav-item .nav-link[aria-expanded="true"] .link-arrow {
        transform: rotate(180deg);
        opacity: 0.7;
    }
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub) > .nav-link:hover,
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link:hover,
    nav.sidebar .pd-nav-section-list > .nav-item:not(.has-sub) > .nav-link:hover,
    nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link:hover,
    nav.sidebar .sidebar-footer .nav-item > .nav-link:hover {
        color: #0d0d0d !important;
        background: rgba(0, 0, 0, 0.05) !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub).active > .nav-link,
    nav.sidebar .sidebar-body .nav > .nav-item:not(.has-sub) > .nav-link.active,
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active,
    nav.sidebar .pd-nav-section-list > .nav-item:not(.has-sub).active > .nav-link,
    nav.sidebar .pd-nav-section-list > .nav-item:not(.has-sub) > .nav-link.active,
    nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link.active,
    nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
    nav.sidebar .sidebar-footer .nav-item > .nav-link.active {
        color: #0d0d0d !important;
        background: rgba(0, 0, 0, 0.07) !important;
        font-weight: 500;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active),
    nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link[aria-expanded="true"]:not(:hover):not(.active) {
        background: transparent !important;
        color: #0d0d0d !important;
    }
    nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link::before,
    nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active::before,
    nav.sidebar .sidebar-footer .nav .nav-item.active > .nav-link::before,
    nav.sidebar .sidebar-footer .nav .nav-item > .nav-link.active::before {
        display: none !important;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub,
    nav.sidebar .pd-nav-section-list > .nav-item.has-sub {
        border-radius: 10px;
        padding: 0;
        background: transparent;
    }
    nav.sidebar .sidebar-body .nav > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]),
    nav.sidebar .pd-nav-section-list > .nav-item.has-sub:has(> .nav-link[aria-expanded="true"]) {
        background: transparent;
    }
    nav.sidebar .sidebar-body .nav.sub-menu {
        position: relative;
        padding: 2px 0 4px !important;
        margin: 0 0 2px 18px !important;
        border-left: 0;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item {
        margin-top: 0 !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        height: 32px !important;
        padding: 0 10px !important;
        border-radius: 10px !important;
        color: #5d5d5d !important;
        font-size: 13.5px;
        font-weight: 400;
        line-height: 1 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        display: none !important;
        content: none !important;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover {
        color: #0d0d0d !important;
        background: rgba(0, 0, 0, 0.05) !important;
        font-weight: 400;
    }
    nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        color: #0d0d0d !important;
        background: rgba(0, 0, 0, 0.07) !important;
        font-weight: 500;
    }
    nav.sidebar .sidebar-body .nav .nav-item-logout .nav-link:hover,
    nav.sidebar .sidebar-footer .nav-item-logout .nav-link:hover {
        color: #e63946 !important;
        background: rgba(230, 57, 70, 0.08) !important;
    }
    @media (min-width: 992px) {
        body.sidebar-folded nav.sidebar .sidebar-brand,
        body.sidebar-folded nav.sidebar .link-title,
        body.sidebar-folded nav.sidebar .link-arrow,
        body.sidebar-folded nav.sidebar .nav-category,
        body.sidebar-folded nav.sidebar .pd-nav-section-label,
        body.sidebar-folded nav.sidebar .collapse,
        body.sidebar-folded nav.sidebar .sub-menu {
            display: none !important;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section {
            padding: 0;
            margin: 0 0 4px;
            border: 0;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section + .pd-nav-section {
            border-top: 0;
            padding-top: 0;
        }
        body.sidebar-folded nav.sidebar .pd-nav-section-list {
            display: block !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-footer {
            padding: 6px 8px 10px;
        }
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link,
        body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item > .nav-link,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link {
            justify-content: center;
            width: 42px;
            margin: 0 auto;
            padding: 0 !important;
        }
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item.active > .nav-link,
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link.active,
        body.sidebar-folded nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
        body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item.active > .nav-link,
        body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item > .nav-link.active,
        body.sidebar-folded nav.sidebar .pd-nav-section-list > .nav-item > .nav-link:hover,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link.active,
        body.sidebar-folded nav.sidebar .sidebar-footer .nav-item > .nav-link:hover {
            color: #0d0d0d !important;
            background: rgba(0, 0, 0, 0.06) !important;
        }
    }

    [data-bs-theme="dark"] nav.sidebar,
    [data-bs-theme="dark"] nav.sidebar .sidebar-header,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }
    [data-bs-theme="dark"] nav.sidebar {
        background: #171717 !important;
        box-shadow: none !important;
        border-right: 1px solid #2f2f2f !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header {
        border-bottom: 0 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-brand {
        color: #ececec !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-brand span {
        color: #ff4d6d !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-header .sidebar-toggler span {
        background: #8e8ea0 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body {
        border-right: 0 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer {
        border-top-color: #2f2f2f !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section {
        background: transparent;
        border-color: transparent;
    }
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section + .pd-nav-section {
        border-top-color: transparent;
    }
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-label,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.nav-category {
        color: #8e8ea0 !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav .nav-item .nav-link {
        color: #ececec !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item > .nav-link:hover {
        color: #ececec !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav .nav-item > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item.active > .nav-link,
    [data-bs-theme="dark"] nav.sidebar .sidebar-footer .nav-item > .nav-link.active {
        color: #ececec !important;
        background: rgba(255, 255, 255, 0.1) !important;
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
        color: #ececec !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav > .nav-item.has-sub > .nav-link.active,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .pd-nav-section-list > .nav-item.has-sub > .nav-link.active {
        color: #ececec !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu {
        border-left-color: transparent;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
        color: #b4b4b4 !important;
        background: transparent !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link::before {
        display: none !important;
    }
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link:hover,
    [data-bs-theme="dark"] nav.sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link.active {
        color: #ececec !important;
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
    .navbar .pd-notify-dot {
        position: absolute;
        top: 5px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #e11d48;
        border: 2px solid #ffffff;
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
    [data-bs-theme="dark"] .navbar .pd-notify-dot {
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
