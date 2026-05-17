<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Welcome' }} | My App</title>
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0; padding: 0;
            background-color: var(--light); color: var(--dark);
            text-align: center;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        header { padding: 20px 0; font-weight: 700; font-size: 1.25rem; }
        .hero { padding: 60px 20px; }
        h1 { font-size: 2.75rem; margin-bottom: 16px; letter-spacing: -0.025em; }
        .subtitle { font-size: 1.2rem; color: #475569; margin-bottom: 32px; }
        .cta-btn {
            background-color: var(--primary); color: white;
            padding: 14px 28px; font-weight: 600; text-decoration: none;
            border-radius: 6px; display: inline-block; transition: opacity 0.2s;
        }
        .cta-btn:hover { opacity: 0.9; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-top: 60px; text-align: left; }
        .card { background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <header>🚀 SoftwareCo</header>

    <!-- Page Content injected here -->
    {{ $slot }}

</body>
</html>
