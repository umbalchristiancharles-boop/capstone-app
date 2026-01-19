<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Reset Password</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" action="{{ route('admin.password.update') }}"
          class="bg-white p-6 rounded shadow w-full max-w-md">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <h1 class="text-xl font-semibold mb-4">Reset Admin Password</h1>

        <label class="block text-sm font-medium mb-1" for="email">Admin Email</label>
        <input id="email" type="email" name="email" required
               class="w-full border rounded px-3 py-2 mb-3"
               value="{{ $email ?? old('email') }}">

        <label class="block text-sm font-medium mb-1" for="password">New Password</label>
        <input id="password" type="password" name="password" required
               class="w-full border rounded px-3 py-2 mb-3">

        <label class="block text-sm font-medium mb-1" for="password_confirmation">
            Confirm Password
        </label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
               class="w-full border rounded px-3 py-2 mb-4">

        @error('email')
            <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
        @enderror
        @error('password')
            <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 rounded hover:bg-indigo-700">
            Reset Password
        </button>
    </form>
</body>
</html>
