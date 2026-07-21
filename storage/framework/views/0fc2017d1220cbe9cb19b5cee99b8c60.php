<?php $__env->startSection('content'); ?>

<?php $login_user = asset('asset/img/login-user-bg.jpg'); ?>
<div class="full-page-bg" style="background-image: url(<?php echo e($login_user); ?>);">
<div class="log-overlay"></div>
    <div class="full-page-bg-inner">
        <div class="row no-margin">
            <div class="col-md-6 log-left">
                <span class="login-logo"><img src="<?php echo e(asset('asset/img/logo.png')); ?>"></span>
                <h2>Create your account and get moving in minutes</h2>
                <p>Welcome to <?php echo e(Setting::get('site_title', 'Tranxit')); ?>, the easiest way to get around at the tap of a button.</p>
            </div>
            <div class="col-md-6 log-right">
                <div class="login-box-outer">
                <div class="login-box row no-margin">
                    <div class="col-md-12">
                        <a class="log-blk-btn" href="<?php echo e(url('login')); ?>">ALREADY HAVE AN ACCOUNT?</a>
                        <h3>Reset Password</h3>
                    </div>
                     <?php if(session('status')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>
                    <form role="form" method="POST" action="<?php echo e(url('/password/reset')); ?>">
                        <?php echo e(csrf_field()); ?>

                        <input type="hidden" name="token" value="<?php echo e($token); ?>">

                        <div class="col-md-12">
                            <input type="email" class="form-control" name="email" placeholder="Email Address" value="<?php echo e(old('email')); ?>">

                            <?php if($errors->has('email')): ?>
                                <span class="help-block">
                                    <strong><?php echo e($errors->first('email')); ?></strong>
                                </span>
                            <?php endif; ?>                        
                        </div>
                        <div class="col-md-12">
                            <input type="password" class="form-control" name="password" placeholder="Password">

                            <?php if($errors->has('password')): ?>
                                <span class="help-block">
                                    <strong><?php echo e($errors->first('password')); ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <input type="password" placeholder="Re-type Password" class="form-control" name="password_confirmation">

                            <?php if($errors->has('password_confirmation')): ?>
                                <span class="help-block">
                                    <strong><?php echo e($errors->first('password_confirmation')); ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-12">
                            <button class="log-teal-btn" type="submit">RESET PASSWORD</button>
                        </div>
                    </form>     

                    <div class="col-md-12">
                        <p class="helper">Or <a href="<?php echo e(route('login')); ?>">Sign in</a> with your user account.</p>   
                    </div>

                </div>


                <div class="log-copy"><p class="no-margin"><?php echo e(Setting::get('site_copyright', '&copy; '.date('Y').' Appoets')); ?></p></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/user/auth/passwords/reset.blade.php ENDPATH**/ ?>