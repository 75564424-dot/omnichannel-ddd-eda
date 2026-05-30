<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="xsrf-cookie" content="<?php echo e(config('session.xsrf_cookie', 'XSRF-TOKEN')); ?>">
        <meta name="app-name" content="<?php echo e(config('app.name')); ?>">
        <title><?php echo e(config('app.name')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
        <?php $__inertiaSsrResponse = app(\Inertia\Ssr\SsrState::class)->setPage($page)->dispatch();  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
    </head>
    <body class="bg-surface-container-low text-on-surface font-inter antialiased">
        <?php $__inertiaSsrResponse = app(\Inertia\Ssr\SsrState::class)->setPage($page)->dispatch();  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } else { ?><script data-page="app" type="application/json"><?php echo json_encode($page); ?></script><div id="app"></div><?php } ?>
    </body>
</html>
<?php /**PATH C:\Proyectos\omnichannel-ddd-eda\resources\views/app.blade.php ENDPATH**/ ?>