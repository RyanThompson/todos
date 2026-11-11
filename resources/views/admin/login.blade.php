<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin login</title>
</head>
<body>
    <form method="post" action="/admin/login">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Sign in</button>
        @if ($errors->any())
            <p>{{ $errors->first() }}</p>
        @endif
    </form>
</body>
</html>