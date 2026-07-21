<?php $__env->startSection('content'); ?>
<div class="row no-margin" style="padding-top: 80px; padding-bottom: 60px; background:#0d1226; min-height:100vh;">
    <div class="container">
        <div class="row">
            <!-- Title removed per user request -->
        </div>

        <!-- Section de Filtrage -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-6 col-md-offset-3">
                <div class="search-box" style="background: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.05); display: flex; gap: 10px; border: 1px solid #edf2f7;">
                    <i class="fa fa-search" style="margin-top: 8px; color: #C9A84C; font-size: 14px;"></i>
                    <input type="text" id="vehicleSearch" placeholder="Rechercher par marque, modèle ou ville..." class="form-control" style="border: none; box-shadow: none; font-size: 0.95rem; padding: 4px 0;">
                </div>
            </div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <span style="color: #C9A84C; font-size: 14px; font-weight: 700; background: rgba(201,168,76,0.1); padding: 7px 18px; border-radius: 10px; display: inline-block; border: 1px solid rgba(201,168,76,0.2);">
                    🚗 <?php echo e($vehicles->total()); ?> véhicule(s) disponible(s)
                </span>
            </div>
        </div>

        <div class="row" id="vehicleGrid">
            <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-4 col-sm-6 vehicle-item" style="margin-bottom: 40px;" 
                     data-title="<?php echo e(strtolower($vehicle->title)); ?>" 
                     data-city="<?php echo e(strtolower($vehicle->location_city)); ?>"
                     data-brand="<?php echo e(strtolower($vehicle->brand)); ?>">
                    <div class="premium-card">
                        <div class="card-img-container">
                            <img src="<?php echo e(url($vehicle->cover_image)); ?>" alt="<?php echo e($vehicle->title); ?>">
                            <div class="price-badge"><?php echo e(number_format($vehicle->price)); ?> CFA <span>/ jour</span></div>
                        </div>
                        <div class="card-body-custom">
                            <div class="card-category"><?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?></div>
                            <h4 class="card-title-custom"><?php echo e($vehicle->title); ?></h4>
                            <div class="card-info">
                                <span><i class="fa fa-map-marker"></i> <?php echo e($vehicle->location_city); ?></span>
                                <?php if($vehicle->owner_name): ?>
                                    <span><i class="fa fa-user-circle"></i> <?php echo e($vehicle->owner_name); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer-custom">
                                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $vehicle->owner_phone ?: '2250101010101')); ?>?text=<?php echo e(urlencode('Bonjour ' . ($vehicle->owner_name ?: '') . ', je suis très intéressé par la location de votre véhicule : ' . $vehicle->title . ' (' . $vehicle->brand . ' ' . $vehicle->model . ') à ' . number_format($vehicle->price) . ' CFA/jour vu sur Picme.')); ?>" 
                                   target="_blank" 
                                   class="btn-whatsapp">
                                    <i class="fa fa-whatsapp"></i> Réserver maintenant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-md-12 text-center">
                    <p class="text-muted">Aucun véhicule disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-12 text-center">
                <?php echo e($vehicles->links()); ?>

            </div>
        </div>
    </div>
</div>

<style>
    .premium-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid #f0f0f0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .premium-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    .card-img-container {
        position: relative;
        height: 220px;
        overflow: hidden;
    }
    .card-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .premium-card:hover .card-img-container img {
        transform: scale(1.1);
    }
    .price-badge {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        color: #fff;
        padding: 8px 15px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .price-badge span {
        font-size: 0.8rem;
        font-weight: 400;
        opacity: 0.8;
    }
    .card-body-custom {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .card-category {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #C9A84C;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .card-title-custom {
        font-weight: 700;
        color: #333;
        margin: 0 0 15px 0;
        font-size: 1.3rem;
    }
    .card-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        color: #777;
        font-size: 0.95rem;
    }
    .card-info i {
        color: #C9A84C;
        margin-right: 5px;
    }
    .card-footer-custom {
        margin-top: auto;
    }
    .btn-whatsapp {
        display: block;
        width: 100%;
        background: linear-gradient(135deg, #2E7D32, #1B5E20);
        color: #fff !important;
        text-align: center;
        padding: 12px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 10px rgba(46, 125, 50, 0.3);
    }
    .btn-whatsapp:hover {
        background: linear-gradient(135deg, #1B5E20, #0d3d11);
        box-shadow: 0 6px 15px rgba(46, 125, 50, 0.4);
        transform: translateY(-1px);
    }
    #vehicleSearch:focus {
        outline: none;
    }
</style>

<?php $__env->startSection('scripts'); ?>
<script>
    $(document).ready(function(){
        $("#vehicleSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#vehicleGrid .vehicle-item").filter(function() {
                var match = $(this).data("title").indexOf(value) > -1 || 
                            $(this).data("city").indexOf(value) > -1 ||
                            $(this).data("brand").indexOf(value) > -1;
                $(this).toggle(match);
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/location.blade.php ENDPATH**/ ?>