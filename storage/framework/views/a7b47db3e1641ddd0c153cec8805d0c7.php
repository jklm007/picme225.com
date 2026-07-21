<header>
    <nav class="navbar navbar-fixed-top">
      <div class="container">
        <div class="col-md-12">
            <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>             

              <a class="navbar-brand" href="<?php echo e(url('dashboard')); ?>"><img src="<?php echo e(Setting::get('site_logo')); ?>"></a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">            
                <ul class="nav navbar-nav navbar-right">                  
                  <li class="menu-drop">
                      <div class="dropdown">
                          <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?php echo e(Auth::user()->first_name); ?> <?php echo e(Auth::user()->last_name); ?>

                          <span class="caret"></span></button>
                          <ul class="dropdown-menu">
                            <li><a href="<?php echo e(url('trips')); ?>"><?php echo app('translator')->get('user.my_trips'); ?></a></li>
                            <li><a href="<?php echo e(url('profile')); ?>"><?php echo app('translator')->get('user.profile.profile'); ?></a></li>
                            <li><a href="<?php echo e(url('change/password')); ?>"><?php echo app('translator')->get('user.profile.change_password'); ?></a></li>
                            <li><a href="<?php echo e(url('/logout')); ?>"
                                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();"><?php echo app('translator')->get('user.profile.logout'); ?></a></li>
                                    <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" style="display: none;">
                                        <?php echo e(csrf_field()); ?>

                                    </form>
                          </ul>
                        </div>
                  </li>
                </ul>
            </div>
        </div>
      </div>
    </nav>
</header><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/include/header.blade.php ENDPATH**/ ?>