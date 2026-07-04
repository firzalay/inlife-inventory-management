<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventaris') }}{{ isset($title) ? ' — '.$title : '' }}</title>

        <!-- IBM Plex Sans — Carbon Design System typeface -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* ============================================================
               Carbon Shell — App Layout Tokens
               ============================================================ */
            :root {
                --shell-sidebar-width: 256px;
                --shell-header-height: 48px;
            }

            * { border-radius: 0 !important; box-sizing: border-box; }

            body {
                font-family: 'IBM Plex Sans', 'Helvetica Neue', Arial, sans-serif;
                background-color: #f4f4f4;
                color: #000;
                letter-spacing: 0.16px;
                margin: 0;
            }

            /* ── Top Header Bar ─────────────────────────────────────── */
            .c-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: var(--shell-header-height);
                background: #000;
                color: #fff;
                display: flex;
                align-items: center;
                padding: 0 16px;
                z-index: 200;
                border-bottom: 1px solid #262626;
            }

            .c-header__brand {
                font-size: 14px;
                font-weight: 600;
                color: #fff;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 8px;
                letter-spacing: 0.16px;
            }

            .c-header__brand-mark {
                color: #ff0d00;
                font-size: 18px;
            }

            .c-header__spacer { flex: 1; }

            .c-header__user {
                font-size: 14px;
                color: #b3b3b3;
                letter-spacing: 0.16px;
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .c-header__user a {
                color: #b3b3b3;
                text-decoration: none;
                font-size: 14px;
            }

            .c-header__user a:hover { color: #fff; }

            /* ── Left Sidebar ───────────────────────────────────────── */
            .c-sidebar {
                position: fixed;
                top: var(--shell-header-height);
                left: 0;
                bottom: 0;
                width: var(--shell-sidebar-width);
                background: #ffffff;
                border-right: 1px solid #e0e0e0;
                z-index: 100;
                overflow-y: auto;
            }

            .c-sidebar__section {
                padding: 16px 0 8px;
            }

            .c-sidebar__label {
                font-size: 12px;
                color: #8c8c8c;
                letter-spacing: 0.32px;
                padding: 0 16px;
                margin-bottom: 4px;
                text-transform: uppercase;
            }

            .c-sidebar__link {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                font-size: 14px;
                color: #4d4d4d;
                text-decoration: none;
                letter-spacing: 0.16px;
                transition: background 0.1s, color 0.1s;
                border-left: 3px solid transparent;
            }

            .c-sidebar__link:hover {
                background: #f4f4f4;
                color: #000;
            }

            .c-sidebar__link.active {
                background: #f4f4f4;
                color: #000;
                border-left-color: #ff0d00;
                font-weight: 600;
            }

            .c-sidebar__icon { font-size: 16px; width: 20px; text-align: center; }

            /* ── Main Content Area ──────────────────────────────────── */
            .c-main {
                margin-left: var(--shell-sidebar-width);
                margin-top: var(--shell-header-height);
                min-height: calc(100vh - var(--shell-header-height));
                padding: 32px;
            }

            .c-page-header {
                margin-bottom: 24px;
                padding-bottom: 16px;
                border-bottom: 1px solid #e0e0e0;
            }

            .c-page-header h1 {
                font-size: 20px;
                font-weight: 400;
                color: #000;
                margin: 0 0 4px;
                letter-spacing: 0;
            }

            .c-page-header p {
                font-size: 14px;
                color: #4d4d4d;
                margin: 0;
            }

            /* ── Notification Toasts ────────────────────────────────── */
            .c-toast {
                padding: 12px 16px;
                margin-bottom: 24px;
                font-size: 14px;
                letter-spacing: 0.16px;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .c-toast--success {
                background: #defbe6;
                border-left: 3px solid #24a148;
                color: #0e6027;
            }

            .c-toast--error {
                background: #fff1f1;
                border-left: 3px solid #da1e28;
                color: #750e13;
            }

            /* ── Responsive ─────────────────────────────────────────── */
            @media (max-width: 672px) {
                .c-sidebar { display: none; }
                .c-main { margin-left: 0; padding: 16px; }
            }
        </style>
    </head>
    <body>
        <!-- Carbon Header -->
        <header class="c-header">
            <a href="{{ route('dashboard') }}" class="c-header__brand">
                <span class="c-header__brand-mark">&#9632;</span>
                InLife Inventory
            </a>
            <span class="c-header__spacer"></span>
            <div class="c-header__user">
                <span>{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        Sign out
                    </a>
                </form>
            </div>
        </header>

        <!-- Carbon Sidebar -->
        <aside class="c-sidebar">
            <div class="c-sidebar__section">
                <div class="c-sidebar__label">Main</div>
                <a href="{{ route('dashboard') }}"
                   class="c-sidebar__link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="c-sidebar__icon">&#9689;</span>
                    Dashboard
                </a>
            </div>

            @role('Admin|Staff|Manager')
            <div class="c-sidebar__section">
                <div class="c-sidebar__label">Inventaris</div>
                <a href="{{ route('products.index') }}"
                   class="c-sidebar__link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="c-sidebar__icon">&#9723;</span>
                    Data Barang
                </a>
            </div>
            @endrole

            <div class="c-sidebar__section">
                <div class="c-sidebar__label">Akun</div>
                <a href="{{ route('profile.edit') }}"
                   class="c-sidebar__link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="c-sidebar__icon">&#9675;</span>
                    Profil
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="c-main">
            @if (session('success'))
                <div class="c-toast c-toast--success">
                    &#10003; {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="c-toast c-toast--error">
                    &#10005; {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
