<?php $__env->startSection('title', 'Dashboard '); ?>

<?php $__env->startSection('styles'); ?>
        <link rel="stylesheet" href="<?php echo e(asset('main/vendor/jvectormap/jquery-jvectormap-2.0.3.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="content-area py-1">
<div class="container-fluid">
    <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.Rides'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($rides->count()); ?></h1>
                                        <span class="tag tag-danger mr-0-5">
                                            <?php if($rides->count() > 0): ?>
                                                <?php echo e(round(($cancel_rides / $rides->count()) * 100, 2)); ?>%
                                            <?php else: ?>
                                                0%
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-muted font-90">% down from cancelled Request</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.Revenue'); ?></h6>
                                        <h1 class="mb-1"><?php echo e(currency($revenue)); ?></h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>from <?php echo e($rides->count()); ?> Rides</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.service'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($service); ?></h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
                                <div class="t-content">
                                        <h1 class="mb-1"><?php echo e($cancel_rides); ?></h1>
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.total_rides'); ?></h6>
                                        <i class="fa fa-caret-down text-danger mr-0-5"></i><span>for 
                                            <?php if($rides->count() > 0): ?>
                                                <?php echo e(round(($cancel_rides / $rides->count()) * 100, 2)); ?>%
                                            <?php else: ?>
                                                0%
                                            <?php endif; ?>
                                        Rides</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-shopping-cart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Annonces Marketplace</h6>
                                        <h1 class="mb-1"><?php echo e($marketplace_count); ?></h1>
                                        <a href="<?php echo e(route('admin.marketplace-listings.index')); ?>" class="text-muted">Gérer les articles →</a>
                                </div>
                        </div>
                </div>
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-ticket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Billets Vendus (Total)</h6>
                                        <h1 class="mb-1"><?php echo e($tickets_sold); ?></h1>
                                        <span class="text-muted">Volume d'activité événementielle</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-info"></span><i class="ti-money"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Commissions Marketplace</h6>
                                        <h1 class="mb-1"><?php echo e(currency($marketplace_commission)); ?></h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>Sur <?php echo e(currency($marketplace_revenue)); ?> de CA</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-12 col-md-12 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-announcement"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Actualités Publiées</h6>
                                        <h1 class="mb-1"><?php echo e($news_count); ?></h1>
                                        <a href="<?php echo e(route('admin.news.index')); ?>" class="text-muted">Gérer les flash infos →</a>
                                </div>
                        </div>
                </div>
        </div>

        <h5 class="mb-1">💰 Rentabilité Gateway P2P & Robot</h5>
        <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #3e70c9;">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-import"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Recharges P2P (Total)</h6>
                                        <h1 class="mb-1"><?php echo e(currency($p2p_deposits)); ?></h1>
                                        <span class="text-muted">Volume via Gateway</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #43b968;">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-gift"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Économies (vs 3.5%)</h6>
                                        <h1 class="mb-1 text-success"><?php echo e(currency($p2p_savings)); ?></h1>
                                        <span class="tag tag-success">Argent économisé</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #f59345;">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-export"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Commissions Retrait (2%)</h6>
                                        <h1 class="mb-1"><?php echo e(currency($p2p_commissions)); ?></h1>
                                        <span class="text-muted">Gagné sur les chauffeurs</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #f44236; background: linear-gradient(to right, #ffffff, #fff5f5);">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-money"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Bénéfice Net P2P</h6>
                                        <h1 class="mb-1 text-danger"><?php echo e(currency($p2p_net_profit)); ?></h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>Profit réel estimé</span>
                                </div>
                        </div>
                </div>
        </div>

        <h5 class="mb-1">📊 Quotas des APIs Cartographiques (Mois en cours)</h5>
        <div class="row row-md">
                <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #3e70c9;">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-map-alt"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Appels API Mapbox (Générateur de routes)</h6>
                                        <h1 class="mb-1"><?php echo e($mapbox_calls); ?> / <?php echo e($mapbox_limit); ?></h1>
                                        <div class="progress progress-sm mb-0-5" style="height: 10px;">
                                            <?php
                                                $mapboxPercent = min(100, ($mapbox_limit > 0 ? ($mapbox_calls / $mapbox_limit) * 100 : 0));
                                                $mapboxColor = $mapboxPercent > 90 ? '#f44236' : ($mapboxPercent > 70 ? '#f59345' : '#43b968');
                                            ?>
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo e($mapboxPercent); ?>%; background-color: <?php echo e($mapboxColor); ?>;"></div>
                                        </div>
                                        <span class="text-muted font-90">Bascule automatique vers OSRM à <?php echo e($mapbox_limit); ?> requêtes</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #ea6b49;">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-google"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Appels API Google Maps (Directions)</h6>
                                        <h1 class="mb-1"><?php echo e($google_calls); ?> / <?php echo e($google_limit); ?></h1>
                                        <div class="progress progress-sm mb-0-5" style="height: 10px;">
                                            <?php
                                                $googlePercent = min(100, ($google_limit > 0 ? ($google_calls / $google_limit) * 100 : 0));
                                                $googleColor = $googlePercent > 90 ? '#f44236' : ($googlePercent > 70 ? '#f59345' : '#43b968');
                                            ?>
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo e($googlePercent); ?>%; background-color: <?php echo e($googleColor); ?>;"></div>
                                        </div>
                                        <span class="text-muted font-90">Bascule automatique vers OSRM à <?php echo e($google_limit); ?> requêtes</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.cancel_count'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($user_cancelled); ?></h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.provider_cancel_count'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($provider_cancelled); ?></h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-rocket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.fleets'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($fleet); ?></h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1"><?php echo app('translator')->get('admin.dashboard.scheduled'); ?></h6>
                                        <h1 class="mb-1"><?php echo e($scheduled_rides); ?></h1>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md mb-2">
                <div class="col-md-12">
                                <div class="box bg-white">
                                        <div class="box-block clearfix">
                                                <h5 class="float-xs-left"><?php echo app('translator')->get('admin.dashboard.Recent_Rides'); ?></h5>
                                                <div class="float-xs-right">
                                                        <button class="btn btn-link btn-sm text-muted" type="button"><i class="ti-close"></i></button>
                                                </div>
                                        </div>
                                        <table class="table mb-md-0">
                                                <tbody>
                                                <?php $__currentLoopData = $rides->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ride): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                                <th scope="row"><?php echo e($index + 1); ?></th>
                                                                <td><?php echo e($ride->user->first_name); ?> <?php echo e($ride->user->last_name); ?></td>
                                                                <td>
                                                                        <?php if($ride->status != "CANCELLED"): ?>
                                                                                <a class="text-primary" href="<?php echo e(route('admin.requests.show',$ride->id)); ?>"><span class="underline"><?php echo app('translator')->get('admin.dashboard.View_Ride_Details'); ?></span></a>
                                                                        <?php else: ?>
                                                                                <span><?php echo app('translator')->get('admin.dashboard.No_Details_Found'); ?> </span>
                                                                        <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                        <span class="text-muted"><?php echo e($ride->created_at->diffForHumans()); ?></span>
                                                                </td>
                                                                <td>
                                                                        <?php if($ride->status == "COMPLETED"): ?>
                                                                                <span class="tag tag-success"><?php echo e($ride->status); ?></span>
                                                                        <?php elseif($ride->status == "CANCELLED"): ?>
                                                                                <span class="tag tag-danger"><?php echo e($ride->status); ?></span>
                                                                        <?php else: ?>
                                                                                <span class="tag tag-info"><?php echo e($ride->status); ?></span>
                                                                        <?php endif; ?>
                                                                </td>
                                                        </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                        </table>
                                </div>
                        </div>

                </div>

        </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>