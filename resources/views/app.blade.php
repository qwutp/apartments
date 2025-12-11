<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetHome</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(app()->environment('local'))
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' http://localhost:3000 http://127.0.0.1:3000 ws://localhost:3000 ws://127.0.0.1:3000; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:3000 http://127.0.0.1:3000 https://api-maps.yandex.ru https://yastatic.net https://yandex.st https://mc.yandex.ru https://core-renderer-tiles.maps.yandex.net; style-src 'self' 'unsafe-inline' http://localhost:3000 http://127.0.0.1:3000 https://fonts.googleapis.com; font-src 'self' http://localhost:3000 http://127.0.0.1:3000 https://fonts.gstatic.com; img-src 'self' data: https: http: http://localhost:3000 http://127.0.0.1:3000 https://oauth.yandex.ru; connect-src 'self' http://localhost:3000 http://127.0.0.1:3000 ws://localhost:3000 ws://127.0.0.1:3000 https://api-maps.yandex.ru https://oauth.yandex.ru https://mc.yandex.ru https://core-renderer-tiles.maps.yandex.net; frame-src 'self' http://localhost:3000 http://127.0.0.1:3000 https://oauth.yandex.ru;">
    @else
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://api-maps.yandex.ru https://yastatic.net https://yandex.st https://mc.yandex.ru https://core-renderer-tiles.maps.yandex.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https: http: https://oauth.yandex.ru; connect-src 'self' https://api-maps.yandex.ru https://oauth.yandex.ru https://mc.yandex.ru https://core-renderer-tiles.maps.yandex.net; frame-src 'self' https://oauth.yandex.ru;">
    @endif
</head>
<body>
    <div id="app">Загрузка Vite...</div>

    @if(file_exists(public_path('hot')))
        <script type="module" src="http://localhost:3000/@vite/client"></script>
        <script type="module" src="http://localhost:3000/resources/js/app.js"></script>
    @else
        <script type="module" src="/build/assets/app.js"></script>
    @endif
</body>
</html>