<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <title>GoodFeet - {{ __('msg.maintenance_title') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="ff_secondary" style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f7f7f7;">
    <div style="max-width:600px; text-align:center; padding:24px; background:#fff; border-radius:16px; box-shadow:0 10px 40px rgba(0,0,0,0.06);">
        <h1 style="font-size:28px; margin-bottom:12px;">{{ __('msg.maintenance_title') }}</h1>
        <p style="font-size:16px; color:#555; margin-bottom:8px;">
            {{ __('msg.maintenance_message') }}
        </p>
    </div>
</body>
</html>
