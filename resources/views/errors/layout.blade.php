<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a; color: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 2rem;
        }
        .card {
            text-align: center; max-width: 420px;
        }
        .code {
            font-size: 7rem; font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        h1 { font-size: 1.5rem; font-weight: 600; margin: .75rem 0 .5rem; color: #f1f5f9; }
        p  { color: #94a3b8; font-size: .95rem; line-height: 1.6; }
        a  {
            display: inline-block; margin-top: 1.5rem;
            background: #6366f1; color: #fff; text-decoration: none;
            padding: .6rem 1.4rem; border-radius: .6rem; font-size: .875rem;
            transition: background .2s;
        }
        a:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a href="{{ url('/') }}">← Back home</a>
    </div>
</body>
</html>
