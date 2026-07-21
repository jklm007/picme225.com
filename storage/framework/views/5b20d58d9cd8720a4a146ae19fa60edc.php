<?php $__env->startSection('title', $listing->title); ?>

<?php $__env->startSection('styles'); ?>
<style>
.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

.pm-detail-page {
    font-family: 'Inter', system-ui, sans-serif;
    padding: 20px 16px;
    padding-top: calc(var(--header-h) + 20px);
    padding-bottom: calc(var(--nav-h) + 40px);
    min-height: 100vh;
    background: #f8fafc;
    color: #1e293b;
}

.pm-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--navy);
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    margin-bottom: 20px;
    background: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
}
.pm-back-btn:hover {
    transform: translateX(-4px);
    color: var(--gold);
}

.pm-product-card {
    background: #ffffff;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 24px;
}

.pm-gallery-main {
    width: 100%;
    height: 320px;
    background: #f1f5f9;
    position: relative;
    overflow: hidden;
}
.pm-gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pm-gallery-thumbs {
    display: flex;
    gap: 8px;
    padding: 12px;
    overflow-x: auto;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
}
.pm-gallery-thumbs img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}
.pm-gallery-thumbs img.active {
    border-color: var(--gold);
}

.pm-product-body {
    padding: 20px;
}

.pm-product-cat {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}

.pm-product-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--navy);
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.pm-product-price {
    font-size: 24px;
    font-weight: 800;
    color: var(--gold);
    margin-bottom: 20px;
}

.pm-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 20px 0;
}

.pm-section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--navy);
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pm-product-desc {
    font-size: 14px;
    line-height: 1.6;
    color: #475569;
    margin-bottom: 0;
}

.pm-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.pm-info-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
}
.pm-info-label {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 4px;
}
.pm-info-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
}

.pm-vcard-contact {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff9e6;
    padding: 16px;
    border-radius: 16px;
    border: 1px solid #ffeeba;
    margin-top: 20px;
}
.pm-vcard-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--navy);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}
.pm-vcard-details {
    flex: 1;
}
.pm-vcard-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
}
.pm-vcard-role {
    font-size: 11px;
    color: #64748b;
}

.pm-action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 24px;
}
.pm-btn-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: var(--gold);
    color: var(--navy);
    font-weight: 700;
    font-size: 14px;
    padding: 14px;
    border-radius: 12px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(201, 168, 76, 0.2);
    transition: all 0.2s;
}
.pm-btn-primary:hover {
    background: var(--gold-light);
    transform: translateY(-2px);
}
.pm-btn-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #25d366;
    color: white;
    font-weight: 700;
    font-size: 14px;
    padding: 14px;
    border-radius: 12px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
    transition: all 0.2s;
}
.pm-btn-secondary:hover {
    background: #20ba5a;
    transform: translateY(-2px);
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="pm-detail-page">
    <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-back-btn">
        <i class="fa fa-arrow-left"></i> Retour au Marché
    </a>

    <div class="pm-product-card">
        
        <div class="pm-gallery-main" style="position:relative; width:100%; height:350px;">
            <?php
                $allImages = $listing->images ?? [];
                $cover = $listing->cover_image ? img($listing->cover_image) : asset('images/default_product.png');
                $is_video = str_ends_with(strtolower($cover), '.mp4') || str_ends_with(strtolower($cover), '.mov') || str_ends_with(strtolower($cover), '.avi');
            ?>
            <img src="<?php echo e($cover); ?>" id="mainGalleryImage" alt="<?php echo e($listing->title); ?>" style="width:100%; height:100%; object-fit:cover; <?php echo e($is_video ? 'display:none;' : 'display:block;'); ?>">
            <video id="mainGalleryVideo" src="<?php echo e($is_video ? $cover : ''); ?>" controls style="width:100%; height:100%; object-fit:cover; <?php echo e($is_video ? 'display:block;' : 'display:none;'); ?>"></video>
        </div>

        <?php if(count($allImages) > 1): ?>
        <div class="pm-gallery-thumbs" style="display:flex; gap:8px; padding:10px; overflow-x:auto;">
            <?php $__currentLoopData = $allImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $resolvedImg = img($img);
                    $thumb_is_video = str_ends_with(strtolower($resolvedImg), '.mp4') || str_ends_with(strtolower($resolvedImg), '.mov') || str_ends_with(strtolower($resolvedImg), '.avi');
                ?>
                <?php if($thumb_is_video): ?>
                    <div class="pm-thumb-video-wrapper <?php echo e($idx === 0 ? 'active' : ''); ?>" onclick="switchGallery('<?php echo e($resolvedImg); ?>', this, true)" style="position:relative; width:60px; height:60px; flex-shrink:0; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer;">
                        <video src="<?php echo e($resolvedImg); ?>" style="width:100%; height:100%; object-fit:cover;" muted></video>
                        <i class="fa fa-play-circle" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); color:white; font-size:18px;"></i>
                    </div>
                <?php else: ?>
                    <img src="<?php echo e($resolvedImg); ?>" class="<?php echo e($idx === 0 ? 'active' : ''); ?>" onclick="switchGallery('<?php echo e($resolvedImg); ?>', this, false)" alt="Thumbnail" style="width:60px; height:60px; flex-shrink:0; border-radius:8px; object-fit:cover; border:2px solid #e2e8f0; cursor:pointer;">
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <div class="pm-product-body">
            <div class="pm-product-cat"><?php echo e($listing->category); ?></div>
            <h2 class="pm-product-title"><?php echo e($listing->title); ?></h2>
            <div class="pm-product-price"><?php echo e(number_format($listing->price, 0, ',', ' ')); ?> <?php echo e($listing->price_unit ?? 'FCFA'); ?></div>

            <div class="pm-divider"></div>

            <div class="pm-info-grid">
                <div class="pm-info-item">
                    <div class="pm-info-label">Localisation</div>
                    <div class="pm-info-value"><i class="fa fa-map-marker" style="color:var(--gold); margin-right:4px;"></i> <?php echo e($listing->location_city ?? 'Abidjan'); ?></div>
                </div>
                <div class="pm-info-item">
                    <div class="pm-info-label">État</div>
                    <div class="pm-info-value"><?php echo e(isset($listing->metadata['condition']) && $listing->metadata['condition'] === 'new' ? 'Neuf' : 'Excellent état'); ?></div>
                </div>
            </div>

            <div class="pm-divider"></div>

            <h3 class="pm-section-title">Description</h3>
            <p class="pm-product-desc"><?php echo e($listing->description); ?></p>

            <div class="pm-divider"></div>

            <h3 class="pm-section-title">Vendeur</h3>
            <div class="pm-vcard-contact">
                <div class="pm-vcard-avatar">
                    <?php echo e(strtoupper(substr($listing->user->first_name ?? 'V', 0, 1))); ?>

                </div>
                <div class="pm-vcard-details">
                    <div class="pm-vcard-name"><?php echo e($listing->user->first_name ?? 'Vendeur Pro'); ?> <?php echo e($listing->user->last_name ?? ''); ?></div>
                    <div class="pm-vcard-role">Membre vérifié Picme</div>
                </div>
            </div>

            <div class="pm-action-buttons">
                <a href="tel:<?php echo e($listing->owner_phone); ?>" class="pm-btn-primary">
                    <i class="fa fa-phone"></i> Appeler
                </a>
                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $listing->owner_phone)); ?>?text=Bonjour,%20je%20suis%20intéressé%20par%20votre%20annonce%20:%20<?php echo e(urlencode($listing->title)); ?>" class="pm-btn-secondary" target="_blank">
                    <i class="fa fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('user.include.bottom_nav', ['active' => 'store'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function switchGallery(src, el, isVideo) {
    const imgEl = document.getElementById('mainGalleryImage');
    const videoEl = document.getElementById('mainGalleryVideo');
    
    if (isVideo) {
        imgEl.style.display = 'none';
        videoEl.src = src;
        videoEl.style.display = 'block';
        videoEl.play();
    } else {
        videoEl.pause();
        videoEl.style.display = 'none';
        videoEl.src = '';
        imgEl.src = src;
        imgEl.style.display = 'block';
    }
    
    document.querySelectorAll('.pm-gallery-thumbs img, .pm-gallery-thumbs .pm-thumb-video-wrapper').forEach(function(thumb) {
        thumb.classList.remove('active');
    });
    el.classList.add('active');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/marketplace/detail.blade.php ENDPATH**/ ?>