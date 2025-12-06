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
</head>
<body>
    <div id="app">Загрузка Vite...</div>

    @php
        $hotFile = public_path('hot');
        $manifestPath = public_path('build/manifest.json');
        $manifest = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
        $appEntry = $manifest['resources/js/app.js'] ?? null;
        $cssFiles = $appEntry['css'] ?? [];
        $jsFile = $appEntry['file'] ?? null;
    @endphp

    @if(file_exists($hotFile))
        @vite(['resources/js/app.js'])
    @elseif($jsFile)
        @foreach($cssFiles as $css)
            <link rel="stylesheet" href="{{ asset('build/'.$css) }}">
        @endforeach
        <script type="module" src="{{ asset('build/'.$jsFile) }}"></script>
    @else
        <script>
            console.error('Vite build files not found. Run "npm run build".');
        </script>
    @endif
</body>
</html>