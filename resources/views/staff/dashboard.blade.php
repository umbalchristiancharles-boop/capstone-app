<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Dashboard - Chikin Tayo</title>
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            authenticated: {{ auth()->check() ? 'true' : 'false' }},
            userId: {{ auth()->id() ?? 'null' }}
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #__chikin_msg_btn { z-index: 10000 !important; }
        #__chikin_msg_modal { z-index: 10001 !important; }
    </style>
</head>
<body>
    @include('partials.messaging_button')
    <div id="app"></div>
</body>
</html>
