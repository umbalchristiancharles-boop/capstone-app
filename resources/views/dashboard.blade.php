<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    {{-- CSRF token para sa axios / SPA --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard</title>

    {{-- Expose CSRF token sa window para sure --}}
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
        };
    </script>

    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
