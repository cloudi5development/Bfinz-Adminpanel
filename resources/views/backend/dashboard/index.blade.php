{{-- Admin dashboard. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard &middot; Bfinz</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            padding: 24px;
            background: #ffffff;
            color: #1c2536;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        h1 { margin: 0; font-size: 36px; font-weight: 600; text-align: center; }

        button {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background: #d64550;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover { background: #b93842; }
    </style>
</head>
<body>

    <h1>Welcome to dashboard</h1>

    <form method="POST" action="{{ route('backend.auth.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

</body>
</html>
