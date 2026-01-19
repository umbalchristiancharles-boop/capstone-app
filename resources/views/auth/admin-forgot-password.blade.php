<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Forgot Password</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

          class="bg-white p-6 rounded shadow w-full max-w-md">
        @csrf

        <h1 class="text-xl font-semibold mb-4">Admin Forgot Password</h1>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <label class="block text-sm font-medium mb-1" for="email">Admin Email</label>
        <input id="email" type="email" name="email" required autofocus
               class="w-full border rounded px-3 py-2 mb-3 @error('email') border-red-500 @enderror"
               value="{{ old('email') }}">

        @error('email')
            <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 rounded hover:bg-indigo-700">
            Send Password Reset Link
        </button>

        {{-- FIXED ROUTE --}}
        <a href="/admin-login" class="block text-center text-sm text-gray-500 mt-3">
            ← Back to Admin Login
        </a>
    </form>
</body>
</html>
