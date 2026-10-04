<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Branch Manager Dashboard - Chikin Tayo</title>
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            authenticated: {{ auth()->check() ? 'true' : 'false' }},
            userId: {{ auth()->id() ?? 'null' }}
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
