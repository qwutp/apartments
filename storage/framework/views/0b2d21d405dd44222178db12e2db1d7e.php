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

    <?php if(file_exists(public_path('hot'))): ?>
        <script type="module" src="http://localhost:3000/<?php echo app('Illuminate\Foundation\Vite')(); ?>/client"></script>
        <script type="module" src="http://localhost:3000/resources/js/app.js"></script>
    <?php else: ?>
        <script type="module" src="/build/assets/app.js"></script>
    <?php endif; ?>
</body>
</html><?php /**PATH C:\Users\пользователь\Downloads\Project\resources\views/app.blade.php ENDPATH**/ ?>