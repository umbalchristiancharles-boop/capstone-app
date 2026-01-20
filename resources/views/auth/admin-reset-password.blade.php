<!-- filepath: c:\xampp\htdocs\capstone-app\resources\views\auth\admin-reset-password.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Reset Password</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <h1 class="text-orange-700" style="margin-bottom: 1.5rem;">Reset Admin Password</h1>

            <label for="email">Admin Email</label>
            <input id="email" type="email" name="email" required value="{{ $email ?? old('email') }}">

            <label for="password">New Password</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            @error('email')
                <p class="text-xs" style="color: #dc2626; margin-bottom: 0.5rem;">{{ $message }}</p>
            @enderror
            @error('password')
                <p class="text-xs" style="color: #dc2626; margin-bottom: 0.5rem;">{{ $message }}</p>
            @enderror

            <button type="submit">
                Reset Password
            </button>
        </form>
    </div>
</body>
</html>