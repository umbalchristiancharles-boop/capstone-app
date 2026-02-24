<!-- filepath: c:\xampp\htdocs\capstone-app\resources\views\auth\admin-forgot-password.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Forgot Password</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf

            <h1 class="text-orange-700" style="margin-bottom: 1.5rem;">Admin Forgot Password</h1>

            @if (session('status'))
                <div class="mb-4 text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <label for="email">Admin Email</label>
            <input id="email" type="email" name="email" required autofocus value="{{ old('email') }}">

            @error('email')
                <p class="text-xs" style="color: #dc2626; margin-bottom: 0.5rem;">{{ $message }}</p>
            @enderror

            <button type="submit">
                Send Password Reset Link
            </button>

            <div class="mt-8 pt-6 border-t text-center">
                <a href="/admin-login">← Back to Admin Login</a>
            </div>
        </form>
    </div>
</body>
</html>