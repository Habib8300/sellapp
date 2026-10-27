<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>প্রবেশাধিকার নেই | DCU Jersey House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #0f172a; font-family: "Hind Siliguri", sans-serif; }
        main { width: min(90vw, 34rem); padding: 2rem; border-radius: 1rem; background: white; box-shadow: 0 10px 30px #0f172a14; }
        h1 { margin-top: 0; }
        button, a { display: inline-block; margin: .5rem .5rem 0 0; padding: .7rem 1rem; border-radius: .5rem; font: inherit; }
        button { border: 0; background: #b45309; color: white; cursor: pointer; }
        a { color: #92400e; }
        form { display: inline; }
    </style>
</head>
<body>
<main>
    <h1>এই পেজে প্রবেশের অনুমতি নেই</h1>
    <p>আপনার বর্তমান অ্যাকাউন্টে অ্যাডমিন প্যানেলের অনুমতি নেই। অ্যাডমিন অ্যাকাউন্ট দিয়ে ঢুকতে সাইন আউট করে আবার লগ ইন করুন।</p>
    @auth
        <form method="POST" action="{{ route('admin.switch-account') }}">
            @csrf
            <button type="submit">সাইন আউট করে অ্যাডমিন লগ ইন</button>
        </form>
    @endauth
    <a href="/">স্টোরে ফিরে যান</a>
</main>
</body>
</html>
