<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventaris') }} — {{ $title ?? 'Authentication' }}</title>

        <!-- IBM Plex Sans — Carbon's typeface -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;600&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body style="font-family: 'IBM Plex Sans', 'Helvetica Neue', Arial, sans-serif; background-color: var(--color-surface-1); color: var(--color-ink); margin: 0;">

        {{-- Carbon utility bar --}}
        <div style="background-color: var(--color-surface-1); border-bottom: 1px solid var(--color-hairline); height: 32px; display: flex; align-items: center; padding: 0 24px;">
            <span style="font-size: 12px; color: var(--color-ink-muted); letter-spacing: 0.32px;">InLife Inventory Management</span>
        </div>

        {{-- Carbon top nav --}}
        <nav style="background-color: var(--color-canvas); border-bottom: 1px solid var(--color-hairline); height: 48px; display: flex; align-items: center; padding: 0 24px;">
            <a href="/" style="font-size: 14px; font-weight: 600; color: var(--color-ink); letter-spacing: 0.16px; text-decoration: none;">
                <span style="color: #ff0d00;">&#9632;</span>&nbsp; InLife
            </a>
        </nav>

        {{-- Auth card shell --}}
        <div style="min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 48px 16px;">
            <div style="width: 100%; max-width: 400px; background-color: var(--color-canvas); border: 1px solid var(--color-hairline); padding: 48px;">
                {{ $slot }}
            </div>
        </div>

    </body>
</html>
