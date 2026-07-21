<?php if(count($errors) > 0): ?>
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(Session::has('flash_error')): ?>
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo e(Session::get('flash_error')); ?>

    </div>
<?php endif; ?>


<?php if(Session::has('flash_success')): ?>
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo e(Session::get('flash_success')); ?>

    </div>
<?php endif; ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/common/notify.blade.php ENDPATH**/ ?>