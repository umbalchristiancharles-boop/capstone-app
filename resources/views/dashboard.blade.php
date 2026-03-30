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
    <style>
        #__chikin_msg_btn {
            z-index: 10000 !important;
            position: fixed !important;
            right: 18px !important;
            bottom: 18px !important;
        }
        #__chikin_msg_modal {
            z-index: 10001 !important;
        }
    </style>
</head>
<body>
    {{-- Messaging button placed BEFORE Vue app so it won't be covered --}}
    @include('partials.messaging_button')
    
    <div id="app"></div>
</body>
</html>
