<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { color-scheme: light; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #fff7ed, #f8fafc 50%, #fff1f2);
            color: #1f2937;
        }
        .card {
            max-width: 640px;
            padding: 3rem 2.5rem;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            text-align: center;
        }
        .tag {
            display: inline-block;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.7rem;
        }
        h1 { font-size: clamp(2rem, 4vw, 3.2rem); margin: 1rem 0 0.75rem; }
        p { margin: 0; color: #4b5563; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="card">
        <div class="tag">Maintenance</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
