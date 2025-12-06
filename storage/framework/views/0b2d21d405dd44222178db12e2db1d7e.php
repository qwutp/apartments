<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetHome</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
    <div id="app">Загрузка Vite...</div>

    <?php
        $hotFile = public_path('hot');
        $manifestPath = public_path('build/manifest.json');
        $manifest = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
        $appEntry = $manifest['resources/js/app.js'] ?? null;
        $cssFiles = $appEntry['css'] ?? [];
        $jsFile = $appEntry['file'] ?? null;
    ?>

    <?php if(file_exists($hotFile)): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <?php elseif($jsFile): ?>
        <?php $__currentLoopData = $cssFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $css): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <link rel="stylesheet" href="<?php echo e(asset('build/'.$css)); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <script type="module" src="<?php echo e(asset('build/'.$jsFile)); ?>"></script>
    <?php else: ?>
        <script>
            console.error('Vite build files not found. Run "npm run build".');
        </script>
    <?php endif; ?>
</body>
</html><?php /**PATH C:\Users\пользователь\Downloads\Project\resources\views/app.blade.php ENDPATH**/ ?>