<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">

    {{-- CSRF token para sa axios / SPA --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard</title>

    {{-- Expose CSRF token sa window para sure --}}
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            authenticated: {{ auth()->check() ? 'true' : 'false' }},
            userId: {{ auth()->id() ?? 'null' }}
        };
    </script>

    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
