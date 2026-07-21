<?php $__env->startSection('title', 'Profile '); ?>

<?php $__env->startSection('content'); ?>

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title"><?php echo app('translator')->get('user.profile.edit_information'); ?></h4>
            </div>
        </div>
            <?php echo $__env->make('common.notify', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="row no-margin edit-pro">
            <form action="<?php echo e(url('profile')); ?>" method="post" enctype="multipart/form-data">
            <?php echo e(csrf_field()); ?>

                <div class="col-md-12">
                    <label><?php echo app('translator')->get('user.profile.profile_picture'); ?></label>
                    <div class="profile-img-blk">
                        <div class="img_outer">
                            <img class="profile_preview" id="profile_image_preview" src="<?php echo e(img(Auth::user()->picture)); ?>" alt="your image"/>
                        </div>
                        <div class="fileUpload up-btn profile-up-btn">                   
                            <input type="file" id="profile_img_upload_btn" name="picture" class="upload" accept="image/x-png, image/jpeg"/>
                        </div>                             
                    </div> 
                </div>
                <div class="form-group col-md-6">
                    <label><?php echo app('translator')->get('user.profile.first_name'); ?></label>
                    <input type="text" class="form-control" name="first_name" required placeholder="<?php echo app('translator')->get('user.profile.first_name'); ?>" value="<?php echo e(Auth::user()->first_name); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label><?php echo app('translator')->get('user.profile.last_name'); ?></label>
                    <input type="text" class="form-control" name="last_name" required placeholder="<?php echo app('translator')->get('user.profile.last_name'); ?>" value="<?php echo e(Auth::user()->last_name); ?>">
                </div>

                <div class="form-group col-md-6">
                    <label><?php echo app('translator')->get('user.profile.email'); ?></label>
                    <input type="email" class="form-control" placeholder="<?php echo app('translator')->get('user.profile.email'); ?>" readonly value="<?php echo e(Auth::user()->email); ?>">
                </div>

                <div class="form-group col-md-6">
                    <label><?php echo app('translator')->get('user.profile.mobile'); ?></label>
                    <input type="text" class="form-control" name="mobile" required placeholder="<?php echo app('translator')->get('user.profile.mobile'); ?>" value="<?php echo e(Auth::user()->mobile); ?>">
                </div>

                <div class="form-group col-md-6">
                    <label><?php echo app('translator')->get('user.profile.language'); ?></label>
                    <select class="form-control" name="language">
                        <option <?php if(Auth::user()->language=='fr')  { echo 'selected=selected'; } ?>  value="fr">fr</option>
                        <option <?php if(Auth::user()->language=='en')  { echo 'selected=selected'; } ?>  value="en">en</option>
                    </select>
                </div>
              
                <div class="col-md-6 pull-right">
                    <button type="submit" class="form-sub-btn big"><?php echo app('translator')->get('user.profile.save'); ?></button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('user.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/account/edit_profile.blade.php ENDPATH**/ ?>