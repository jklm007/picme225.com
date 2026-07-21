

<?php $__env->startSection('content'); ?>
<div class="container text-center" style="padding-top: 15vh; padding-bottom: 15vh;">
    <img src="<?php echo e(asset('logo.png')); ?>" alt="PicMe Logo" style="max-width: 150px; margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: #333;">Vous êtes hors ligne</h1>
    <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px;">
        Vérifiez votre connexion internet pour continuer à utiliser PicMe.
    </p>
    <button onclick="window.location.reload()" class="btn" style="background-color: #C9A84C; color: white; padding: 10px 30px; font-weight: bold; border-radius: 30px;">
        Réessayer
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/offline.blade.php ENDPATH**/ ?>