<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Navigation Drawer</title>
    <style>
        /* Style de base pour le tiroir */
        .dash-left {
            transition: transform 0.3s ease-in-out;
        }

        /* Style pour les petits écrans */
        @media (max-width: 768px) {
            .dash-left {
                position: fixed;
                top: 0;
                left: -250px;
                /* Cache le tiroir par défaut */
                width: 250px;
                height: 100%;
                background-color: #fff;
                z-index: 1000;
                overflow-y: auto;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            }

            .dash-left.open {
                transform: translateX(250px);
                /* Affiche le tiroir */
            }

            /* Bouton pour ouvrir/fermer le tiroir */
            .menu-toggle {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1001;
                background-color: #333;
                color: #fff;
                border: none;
                padding: 10px;
                cursor: pointer;
            }

            /* Overlay pour fermer le tiroir */
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .overlay.active {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Bouton pour ouvrir/fermer le tiroir -->
    <button class="menu-toggle" onclick="toggleDrawer()">☰</button>

    <!-- Overlay pour fermer le tiroir en cliquant à l'extérieur -->
    <div class="overlay" onclick="toggleDrawer()"></div>

    <!-- Votre navigation existante -->
    <div class="col-md-3">
        <div class="dash-left">
            <div class="user-img">
                <?php $profile_image = img(Auth::user()->picture); ?>
                <div class="pro-img" style="background-image: url(<?php echo e($profile_image); ?>);"></div>
                <h4><?php echo e(Auth::user()->first_name); ?> <?php echo e(Auth::user()->last_name); ?></h4>
            </div>
            <div class="side-menu">
                <ul>
                    <li class="<?php echo e(Request::is('dashboard') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('dashboard')); ?>"><i class="fa fa-tachometer"></i> <?php echo app('translator')->get('user.dashboard'); ?></a>
                    </li>

                    <!-- New Features -->
                    <li class="<?php echo e(Request::is('eco-wallet') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('wallet')); ?>"><i class="fa fa-leaf" style="color: #2ecc71;"></i> ECO/CFA Wallet
                            <span class="badge"
                                style="background: #2ecc71;"><?php echo e(Auth::user()->eco_token_balance ?? '0'); ?></span></a>
                    </li>
                    <li class="<?php echo e(Request::is('dao-governance') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('dao/proposals')); ?>"><i class="fa fa-university"></i> DAO Governance</a>
                    </li>
                    <li class="<?php echo e(Request::is('my-tickets') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('trips')); ?>"><i class="fa fa-qrcode"></i> My QR Tickets</a>
                    </li>

                    <li class="<?php echo e(Request::is('trips') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('trips')); ?>"><i class="fa fa-history"></i> <?php echo app('translator')->get('user.my_trips'); ?></a>
                    </li>
                    <li class="<?php echo e(Request::is('upcoming/trips') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('upcoming/trips')); ?>"><i class="fa fa-calendar"></i>
                            <?php echo app('translator')->get('user.upcoming_trips'); ?></a>
                    </li>

                    <li class="dropdown">
                        <a class="dropdown-toggle"><i class="fa fa-cog"></i> <?php echo app('translator')->get('user.profile.settings'); ?></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo e(url('profile')); ?>"><?php echo app('translator')->get('user.profile.profile'); ?></a></li>
                            <li><a href="<?php echo e(url('change/password')); ?>"><?php echo app('translator')->get('user.profile.change_password'); ?></a></li>
                        </ul>
                    </li>

                    <li class="<?php echo e(Request::is('payment') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('/payment')); ?>"><i class="fa fa-credit-card"></i> <?php echo app('translator')->get('user.payment'); ?></a>
                    </li>
                    <li class="<?php echo e(Request::is('promotions') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('/promotions')); ?>"><i class="fa fa-tag"></i> <?php echo app('translator')->get('user.promotion'); ?></a>
                    </li>
                    <li class="<?php echo e(Request::is('wallet') ? 'active' : ''); ?>">
                        <a href="<?php echo e(url('/wallet')); ?>"><i class="fa fa-google-wallet"></i> <?php echo app('translator')->get('user.my_wallet'); ?> <span
                                class="pull-right"><?php echo e(currency(Auth::user()->wallet_balance)); ?></span></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/logout')); ?>" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i>
                            <?php echo app('translator')->get('user.profile.logout'); ?></a>
                    </li>
                    <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" style="display: none;">
                        <?php echo e(csrf_field()); ?>

                    </form>
                </ul>
            </div>
        </div>
    </div>

    <style>
        /* Modern Sidebar Styles */
        .dash-left {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .user-img {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .pro-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            margin: 0 auto 10px;
            background-size: cover;
            background-position: center;
        }

        .side-menu ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .side-menu ul li a {
            display: block;
            padding: 15px 20px;
            color: #555;
            border-bottom: 1px solid #f5f5f5;
            transition: all 0.2s;
            text-decoration: none;
            font-weight: 500;
        }

        .side-menu ul li a i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            color: #764ba2;
        }

        .side-menu ul li:hover a,
        .side-menu ul li.active a {
            background: #f8f9fa;
            color: #764ba2;
            border-left: 4px solid #764ba2;
        }

        /* Mobile Drawer Overrides */
        @media (max-width: 768px) {
            .dash-left {
                position: fixed;
                top: 0;
                left: -280px;
                width: 280px;
                height: 100vh;
                z-index: 9999;
                border-radius: 0;
                overflow-y: auto;
            }

            .dash-left.open {
                left: 0;
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.2);
            }

            .menu-toggle {
                background: white;
                color: #333;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                border: none;
            }
        }
    </style>

    <script>
        function toggleDrawer() {
            const dashLeft = document.querySelector('.dash-left');
            const overlay = document.querySelector('.overlay');
            dashLeft.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // Fermer le tiroir en cliquant sur l'overlay
        document.querySelector('.overlay').addEventListener('click', toggleDrawer);
    </script>
</body>

</html><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/include/nav.blade.php ENDPATH**/ ?>