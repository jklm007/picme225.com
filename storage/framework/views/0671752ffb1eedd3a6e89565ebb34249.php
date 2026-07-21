

<?php $__env->startSection('content'); ?>
<style>
  /* Drive Page Brand Overrides */
  .banner-overlay { background: rgba(10,22,40,0.82) !important; }
  .banner-form {
    background: rgba(255,255,255,0.97) !important;
    border-top: 5px solid #C9A84C !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 45px rgba(0,0,0,0.25) !important;
  }
  .banner-form h5, .banner-form h5 i { color: #C9A84C !important; }
  .banner-form .right a:hover h5, .banner-form .right a:hover h5 i { color: #B89535 !important; text-decoration: none; }
  .banner-form .right a { text-decoration: none; }
  .banner-form .right h3 { color: #0A1628 !important; }
  .note-or a { color: #C9A84C !important; }
  .title-divider { background: linear-gradient(90deg, #C9A84C, #ecc94b) !important; }
  .content-more, .content-more i { color: #C9A84C !important; font-weight: 600 !important; }
  .content-more:hover, .content-more:hover i { color: #B89535 !important; }
  .content-more-btn {
    background: linear-gradient(135deg, #C9A84C, #B89535) !important;
    border-radius: 8px !important;
    color: #fff !important;
    font-weight: 700 !important;
    transition: all 0.2s !important;
  }
  .content-more-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(201,168,76,0.35) !important;
    color: #fff !important;
  }
  .find-city { background: #0A1628 !important; }
  .find-city h2, .find-city p { color: #fff !important; }
  .full-primary-btn.drive-btn {
    background: linear-gradient(135deg, #C9A84C, #B89535) !important;
    color: #fff !important;
    font-weight: 700 !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 14px 40px !important;
    font-size: 16px !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
  }
  .full-primary-btn.drive-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(201,168,76,0.35) !important;
  }

  /* Sections sombres : texte blanc garanti */
  .dark-section h2,
  .dark-section h3,
  .dark-section p,
  .darker-section h2,
  .darker-section h3,
  .darker-section p {
    color: #F8FAFC !important;
  }
  .dark-section .title-divider,
  .darker-section .title-divider {
    background: linear-gradient(90deg, #C9A84C, #ecc94b) !important;
    height: 3px !important;
    width: 50px !important;
    margin: 10px 0 16px 0 !important;
    display: block !important;
  }
</style>

<div class="banner row no-margin" style="background-image: url('<?php echo e(asset('asset/img/vip_chauffeur_abidjan.png')); ?>'); background-size: cover; background-position: center;">
    <div class="banner-overlay"></div>
    <div class="container">
        <div class="col-md-8">
            <h2 class="banner-head"><span class="strong"><?php echo app('translator')->get('home.work_puts_first'); ?></span><br><?php echo app('translator')->get('home.drive_when_you_want'); ?></h2>
        </div>
        <div class="col-md-4">
            <div class="banner-form">
                <div class="row no-margin fields">
                    <div class="left">
                    	<img src="<?php echo e(asset('asset/img/ride-form-icon.png')); ?>">
                    </div>
                    <div class="right">
                        <a href="<?php echo e(Setting::get('store_link_android_driver', 'https://play.google.com/')); ?>">
                            <h3>Télécharger l'app Driver</h3>
                            <h5>Google Play <i class="fa fa-download"></i></h5>
                        </a>
                    </div>
                </div>

                <div class="row no-margin fields">
                    <div class="left">
                    	<img src="<?php echo e(asset('asset/img/ride-form-icon.png')); ?>">
                    </div>
                    <div class="right">
                        <a href="<?php echo e(Setting::get('store_link_android_driver', 'https://play.google.com/')); ?>">
                            <h3>S'inscrire et Se connecter</h3>
                            <h5>Via l'application Driver <i class="fa fa-mobile"></i></h5>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row dark-section no-margin" style="background: #0A1628; color: #F8FAFC; padding: 60px 0;">
    <div class="container">
        
        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.set_schedule'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.set_schedule_content'); ?> <?php echo e(Setting::get('site_title', 'Tranxit')); ?> <?php echo app('translator')->get('home.set_schedule_content2'); ?></p>
        </div>

        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.more_everyturn'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.more_everyturn_content'); ?></p>
        </div>

        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.let_app_lead'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.let_app_lead_content'); ?></p>
        </div>

    </div>
</div>

<div class="row darker-section no-margin full-section" style="background: #0D1F3C; color: #F8FAFC; padding: 60px 0;">
    <div class="container">                
        <div class="col-md-6 content-block">
            <h3 style="color: #C9A84C;"><?php echo app('translator')->get('home.about_app'); ?></h3>
            <h2 style="color: #F8FAFC;"><?php echo app('translator')->get('home.about_app_heading'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.about_app_content'); ?></p>
            <a class="content-more-btn" href="#"><?php echo app('translator')->get('home.see_how_it_works'); ?> <i class="fa fa-chevron-right"></i></a>
        </div>
        <div class="col-md-6 full-img text-center" style="background-image: url(<?php echo e(asset('asset/img/driver-car.jpg')); ?>);"> 
            <!-- <img src="img/anywhere.png"> -->
        </div>
    </div>
</div>

<div class="row dark-section no-margin" style="background: #0A1628; color: #F8FAFC; padding: 60px 0;">
    <div class="container">
        
        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.rewards'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.reward_content'); ?></p>
        </div>

        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.requirement'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.requirement_content'); ?></p>
        </div>

        <div class="col-md-4 content-block small">
            <h2><?php echo app('translator')->get('home.safety'); ?></h2>
            <div class="title-divider"></div>
            <p><?php echo app('translator')->get('home.safe_content1'); ?> <?php echo e(Setting::get('site_title', 'Tranxit')); ?><?php echo app('translator')->get('home.safe_content2'); ?></p>
        </div>

    </div>
</div>
            
<div class="row find-city no-margin">
    <div class="container">
        <h2><?php echo app('translator')->get('home.start_making_money'); ?></h2>
        <p><?php echo app('translator')->get('home.start_making_money_heading'); ?></p>

        <button type="submit" class="full-primary-btn drive-btn"><?php echo app('translator')->get('home.start_drive_now'); ?></button>
    </div>
</div>

<div class="footer-city row no-margin" style="background-image: url(<?php echo e(asset('asset/img/vip_chauffeur_abidjan.png')); ?>); background-size: cover; background-position: center;"></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('user.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/drive.blade.php ENDPATH**/ ?>