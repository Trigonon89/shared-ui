<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ config('app.name', 'Trigonon') }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Figtree, ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            padding: 1.5rem;
        }
        .card {
            max-width: 32rem;
            width: 100%;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .code {
            font-size: .875rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #0d6efd;
            margin: 0 0 .5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 .75rem;
        }
        p.message {
            color: #4b5563;
            line-height: 1.5;
            margin: 0 0 1.75rem;
        }
        .actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: .625rem 1.25rem;
            border-radius: .5rem;
            font-weight: 500;
            font-size: .9375rem;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: #0d6efd;
            color: #fff;
        }
        .btn-primary:hover { background: #0b5ed7; }
        .btn-secondary {
            background: #fff;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-secondary:hover { background: #f3f4f6; }
        .ref {
            margin-top: 1.75rem;
            font-size: .8125rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="code">Error {{ $code }}</p>
        <h1>{{ $heading }}</h1>
        <p class="message">{{ $message }}</p>
        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="var t = window.top; if (t.document.referrer) { t.history.back(); } else { t.location.href = '/'; }">
                &larr; Go back
            </button>
            <a href="/" target="_top" class="btn btn-primary">Home</a>
        </div>
        @if ($errorId ?? null)
            <p class="ref">Reference #{{ $errorId }}</p>
        @endif
    </div>
</body>
</html>
