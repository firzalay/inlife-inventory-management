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
        <script>
            function applyTheme(isDark) {
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                window.dispatchEvent(new CustomEvent('dark-mode-toggled', { detail: isDark }));
            }

            function toggleThemeGlobal() {
                const isDark = !document.documentElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDark);
                applyTheme(isDark);
            }

            // Initial check
            const savedDarkMode = localStorage.getItem('darkMode');
            const isSystemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDarkTheme = savedDarkMode === 'true' || (savedDarkMode === null && isSystemDark);
            applyTheme(isDarkTheme);
        </script>

        <!-- Chart.js for dashboard graphs -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            .theme-icon-moon { display: inline-block; }
            .theme-icon-sun { display: none; }
            .dark .theme-icon-moon { display: none; }
            .dark .theme-icon-sun { display: inline-block; }

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
                background-color: var(--color-surface-1);
                color: var(--color-ink);
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
                background: var(--color-canvas);
                border-right: 1px solid var(--color-hairline);
                z-index: 100;
                overflow-y: auto;
            }

            .c-sidebar__section {
                padding: 16px 0 8px;
            }

            .c-sidebar__label {
                font-size: 12px;
                color: var(--color-ink-subtle);
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
                color: var(--color-ink-muted);
                text-decoration: none;
                letter-spacing: 0.16px;
                transition: background 0.1s, color 0.1s;
                border-left: 3px solid transparent;
            }

            .c-sidebar__link:hover {
                background: var(--color-surface-1);
                color: var(--color-ink);
            }

            .c-sidebar__link.active {
                background: var(--color-surface-1);
                color: var(--color-ink);
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
                border-bottom: 1px solid var(--color-hairline);
            }

            .c-page-header h1 {
                font-size: 20px;
                font-weight: 400;
                color: var(--color-ink);
                margin: 0 0 4px;
                letter-spacing: 0;
            }

            .c-page-header p {
                font-size: 14px;
                color: var(--color-ink-muted);
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
    <body x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
          x-init="$watch('darkMode', val => { 
              localStorage.setItem('darkMode', val); 
              if (val) { 
                  document.documentElement.classList.add('dark'); 
              } else { 
                  document.documentElement.classList.remove('dark'); 
              } 
              window.dispatchEvent(new CustomEvent('dark-mode-toggled', { detail: val }));
          })"
          :class="{ 'dark': darkMode }">
        <!-- Carbon Header -->
        <header class="c-header">
            <a href="{{ route('dashboard') }}" class="c-header__brand">
                <span class="c-header__brand-mark">&#9632;</span>
                InLife Inventory
            </a>
            <span class="c-header__spacer"></span>
            <div class="c-header__user">
                <!-- Dark Mode Toggle Button -->
                <button type="button" id="dark-mode-toggle" 
                        @click="darkMode = !darkMode"
                        onclick="if(!window.Alpine) { toggleThemeGlobal(); }"
                        style="background: none; border: none; color: #b3b3b3; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; font-size: 18px;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#b3b3b3'"
                        title="Toggle Tema Gelap/Terang">
                    <span class="theme-icon-moon">&#127769;</span>
                    <span class="theme-icon-sun">&#9728;&#65039;</span>
                </button>

                <!-- Notification Bell -->
                @auth
                <div style="position: relative; display: inline-block;" id="bell-dropdown-container">
                    <button type="button" onclick="toggleBellDropdown()" style="background: none; border: none; color: #b3b3b3; cursor: pointer; padding: 4px; display: flex; align-items: center; position: relative;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#b3b3b3'">
                        <span style="font-size: 18px;">&#128276;</span>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span style="position: absolute; top: -2px; right: -2px; display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; font-size: 10px; font-weight: 600; color: #fff; background-color: #ff0d00; border-radius: 50% !important; line-height: 1;" id="unread-count-badge">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Dropdown Panel -->
                    <div id="bell-dropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 320px; background: var(--color-canvas); border: 1px solid var(--color-hairline); box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 300; text-align: left;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-1);">
                            <span style="font-size: 12px; font-weight: 600; color: var(--color-ink); letter-spacing: 0.32px; text-transform: uppercase;">Notifikasi ({{ auth()->user()->unreadNotifications->count() }})</span>
                        </div>
                        <div style="max-height: 280px; overflow-y: auto;">
                            @if(auth()->user()->unreadNotifications->isEmpty())
                                <div style="padding: 24px; text-align: center; color: var(--color-ink-subtle); font-size: 13px;">Tidak ada notifikasi baru</div>
                            @else
                                @foreach(auth()->user()->unreadNotifications as $notification)
                                    <div style="padding: 12px 16px; border-bottom: 1px solid var(--color-hairline); font-size: 13px; color: var(--color-ink-muted); line-height: 1.4;">
                                        <p style="margin: 0 0 6px 0; font-weight: 400;">{{ $notification->data['message'] ?? 'Stok menipis' }}</p>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-size: 11px; color: var(--color-ink-subtle);">{{ $notification->created_at->diffForHumans() }}</span>
                                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="margin: 0;">
                                                @csrf
                                                <button type="submit" style="background: none; border: none; color: #ff0d00; font-size: 11px; cursor: pointer; padding: 0;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Tandai dibaca</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @endauth

                <span style="margin-left: 8px;">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0; display: inline;">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" style="margin-left: 8px;">
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
                <a href="{{ route('borrowings.index') }}"
                   class="c-sidebar__link {{ request()->routeIs('borrowings.*') ? 'active' : '' }}">
                    <span class="c-sidebar__icon">&#9645;</span>
                    Peminjaman
                </a>
            </div>
            @endrole

            @role('Admin')
            <div class="c-sidebar__section">
                <div class="c-sidebar__label">Manajemen</div>
                <a href="{{ route('users.index') }}"
                   class="c-sidebar__link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="c-sidebar__icon">&#128101;</span>
                    Kelola User
                    @php
                        $pendingUsersCount = \App\Models\User::where('status', 'pending')->count();
                    @endphp
                    @if($pendingUsersCount > 0)
                        <span style="display: inline-flex; align-items: center; justify-content: center; background: #ff0d00; color: #fff; font-size: 11px; font-weight: 600; padding: 2px 6px; margin-left: auto;" id="pending-users-badge">
                            {{ $pendingUsersCount }}
                        </span>
                    @endif
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
        <!-- Bell dropdown script -->
        <script>
            function toggleBellDropdown() {
                const dropdown = document.getElementById('bell-dropdown');
                if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }

            window.addEventListener('click', function(e) {
                const container = document.getElementById('bell-dropdown-container');
                const dropdown = document.getElementById('bell-dropdown');
                if (dropdown && container && !container.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        </script>
    </body>
</html>
