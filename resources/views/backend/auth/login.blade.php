{{--
    Admin sign-in screen.

    Credentials are checked in Backend\AuthController against the users table —
    nothing about them lives in this file.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login &middot; Bfinz</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #eef2f7;
            color: #1c2536;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border: 1px solid #dbe3ed;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(28, 37, 54, .08);
        }

        h1 { margin: 0 0 24px; font-size: 22px; font-weight: 600; }

        label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 11px 12px;
            margin-bottom: 18px;
            font-size: 15px;
            color: #1c2536;
            border: 1px solid #dbe3ed;
            border-radius: 8px;
            outline: none;
        }

        input:focus { border-color: #2f5bea; box-shadow: 0 0 0 3px rgba(47, 91, 234, .15); }

        button {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background: #2f5bea;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover { background: #2547c4; }

        .alert {
            padding: 11px 14px;
            margin-bottom: 20px;
            font-size: 14px;
            border-radius: 8px;
            color: #8a2b33;
            background: #fdecee;
            border: 1px solid #f6c9ce;
        }

        .alert--ok { color: #1d6b45; background: #e8f6ee; border-color: #bfe4ce; }
    </style>
</head>
<body>

    <main class="card">

        <h1>Admin Login</h1>

        {{-- Flash from a successful sign-out. --}}
        @if (session('success'))
            <div class="alert alert--ok" role="status">{{ session('success') }}</div>
        @endif

        {{-- Wrong credentials, a deactivated account or too many attempts all
             arrive here as a validation error. --}}
        @if ($errors->any())
            <div class="alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('backend.auth.authenticate') }}">
            @csrf

            <label for="email">Username</label>
            <input type="text" id="email" name="email" value="{{ old('email') }}"
                   autocomplete="username" autofocus required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" required>

            <button type="submit">Login</button>
        </form>

    </main>

</body>
</html>
