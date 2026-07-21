<?php $__env->startSection('title', 'Accueil – PicMe225'); ?>

<?php $__env->startSection('styles'); ?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --navy:       #0D1B2A;
    --navy-2:     #162436;
    --navy-3:     #1e3048;
    --gold:       #C9A84C;
    --gold-light: #E2C06E;
    --gold-pale:  rgba(201,168,76,0.12);
    --gold-glow:  rgba(201,168,76,0.3);
    --lime:       #22C55E;
    --lime-dark:  #15803D;
    --lime-glow:  rgba(34, 197, 94, 0.4);
    --white:      #ffffff;
    --gray-50:    #f9fafc;
    --gray-100:   #f0f2f7;
    --gray-200:   #e4e7ef;
    --gray-300:   #cbd2e0;
    --gray-400:   #adb5c9;
    --gray-500:   #7a8bad;
    --radius:     24px;
    --radius-sm:  12px;
    --shadow:     0 8px 24px rgba(13,27,42,0.06);
}

body {
    background-color: var(--gray-50);
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
}

/* Reset layout */
header, .dash-left, .footer-content, .menu-toggle, .overlay, .row.footer {
    display: none !important;
}

.pm-app-container {
    padding-bottom: 90px; /* space for bottom nav */
}

/* HEADER SECTION */
.pm-home-header {
    background: linear-gradient(135deg, var(--lime) 0%, var(--lime-dark) 100%);
    padding: 40px 20px 60px 20px;
    color: var(--navy);
    border-bottom-left-radius: 32px;
    border-bottom-right-radius: 32px;
    position: relative;
    box-shadow: 0 4px 20px var(--lime-glow);
}

.pm-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.pm-user-greeting {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pm-user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gold);
    box-shadow: 0 0 10px var(--gold-glow);
}

.pm-greeting-text h1 {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 2px 0;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.pm-greeting-text p {
    font-size: 13px;
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-weight: 500;
}

.pm-header-actions {
    display: flex;
    gap: 12px;
}

.pm-action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffff;
    text-decoration: none;
    font-size: 16px;
    transition: background 0.3s;
    border: 1px solid rgba(255,255,255,0.3);
}

.pm-action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #ffffff;
}

/* SEARCH FIELD */
.pm-search-container {
    margin-top: -30px;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}

.pm-search-box {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow);
    text-decoration: none;
    color: var(--navy);
    transition: transform 0.3s;
}

.pm-search-box:active {
    transform: scale(0.98);
}

.pm-search-icon {
    width: 24px;
    height: 24px;
    color: var(--gold);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
}

.pm-search-text {
    flex: 1;
}

.pm-search-text h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
}

.pm-search-text p {
    margin: 0;
    font-size: 13px;
    color: var(--gray-500);
}

/* SECTION GLOBALS */
.pm-section {
    padding: 24px 20px 0;
}

.pm-section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 16px;
}

.pm-section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
    margin: 0;
}

.pm-section-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--gold);
    text-decoration: none;
}

/* CATEGORIES */
.pm-categories-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 12px;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
}

.pm-categories-scroll::-webkit-scrollbar {
    display: none;
}

.pm-category-card {
    flex: 0 0 auto;
    width: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    scroll-snap-align: start;
}

.pm-category-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: var(--white);
    box-shadow: 0 4px 12px rgba(13,27,42,0.04);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.3s;
}

.pm-category-icon-wrap img {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.pm-category-card:active .pm-category-icon-wrap {
    transform: scale(0.95);
    background: var(--gold-pale);
}

.pm-category-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--navy-2);
    text-align: center;
}

/* MARKETPLACE RECENT */
.pm-products-scroll {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 16px;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
}

.pm-products-scroll::-webkit-scrollbar {
    display: none;
}

.pm-product-card {
    flex: 0 0 160px;
    background: var(--white);
    border-radius: var(--radius-sm);
    overflow: hidden;
    box-shadow: var(--shadow);
    text-decoration: none;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
}

.pm-product-img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    background: var(--gray-100);
}

.pm-product-info {
    padding: 12px;
}

.pm-product-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pm-product-price {
    font-size: 14px;
    font-weight: 700;
    color: var(--gold);
    margin: 0;
}

/* ADS / BANNERS */
.pm-ads-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 0 20px 16px 20px;
    margin: 0 -20px;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
}

.pm-ads-scroll::-webkit-scrollbar {
    display: none;
}

.pm-ad-banner {
    flex: 0 0 75vw;
    height: 120px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    scroll-snap-align: center;
    position: relative;
    box-shadow: 0 4px 10px var(--lime-glow);
    display: flex;
    align-items: center;
    padding: 16px;
    text-decoration: none;
    background: linear-gradient(135deg, var(--lime), var(--lime-dark));
    color: var(--navy);
}

.pm-ad-bg-img {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.15;
    z-index: 1;
}

.pm-ad-content {
    position: relative;
    z-index: 2;
    max-width: 80%;
}

.pm-ad-tag {
    background: var(--navy);
    color: var(--white);
    font-size: 9px;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.pm-ad-title {
    font-size: 15px;
    font-weight: 800;
    margin: 0 0 4px 0;
    color: var(--navy);
}

.pm-ad-desc {
    font-size: 11px;
    margin: 0;
    opacity: 0.8;
    color: var(--navy);
}

.pm-feed-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    padding-bottom: 20px;
}
@media (min-width: 576px) {
    .pm-feed-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
.pm-feed-card {
    background: #ffffff;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    border: 1px solid #f1f5f9;
    transition: transform 0.2s, box-shadow 0.2s;
}
.pm-feed-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}
.pm-feed-img-wrap {
    height: 180px;
    background: #f1f5f9;
    position: relative;
}
.pm-feed-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pm-feed-badge-sponsored {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--gold);
    color: var(--navy);
    font-size: 10px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 6px;
    text-transform: uppercase;
}
.pm-feed-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.pm-feed-cat {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 4px;
}
.pm-feed-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
    margin: 0 0 6px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pm-feed-price {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    margin-top: auto;
    margin-bottom: 6px;
}
.pm-feed-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: #64748b;
}
.pm-feed-btn {
    display: block;
    text-align: center;
    background: var(--navy);
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 10px;
    transition: background 0.2s;
}
.pm-feed-btn:hover {
    background: var(--gold);
    color: var(--navy);
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="pm-app-container">
    
    
    <div class="pm-home-header" style="padding-top: 100px;">
        <div class="pm-header-top" style="margin-bottom: 0;">
            <div class="pm-user-greeting">
                <div class="pm-greeting-text">
                    <h1>Bonjour, <?php echo e(strtok($user->first_name, ' ')); ?> 👋</h1>
                    <p>Prêt pour un nouveau trajet ?</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="pm-search-container">
        <a href="<?php echo e(url('dashboard')); ?>" class="pm-search-box">
            <div class="pm-search-icon">
                <i class="fa fa-map-marker"></i>
            </div>
            <div class="pm-search-text">
                <h3>Où allons-nous ?</h3>
                <p>Réservez votre VTC maintenant</p>
            </div>
            <div class="pm-search-icon" style="color:var(--gray-300)">
                <i class="fa fa-chevron-right"></i>
            </div>
        </a>
    </div>

    
    <div class="pm-section">
        <div class="pm-section-header">
            <h2 class="pm-section-title">Nos Services</h2>
        </div>
        <div class="pm-categories-scroll">
            
            <a href="<?php echo e(url('dashboard')); ?>" class="pm-category-card">
                <div class="pm-category-icon-wrap">
                    <img src="<?php echo e(asset('images/default_category.png')); ?>" alt="VTC" onerror="this.src='<?php echo e(asset('asset/logo.png')); ?>'">
                </div>
                <span class="pm-category-label">VTC</span>
            </a>
            
            
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(strtolower($cat->name) !== 'vtc'): ?>
            <a href="<?php echo e(url('dashboard')); ?>" class="pm-category-card">
                <div class="pm-category-icon-wrap">
                    <img src="<?php echo e($cat->image_url ?? asset('images/default_category.png')); ?>" alt="<?php echo e($cat->name); ?>" onerror="this.src='<?php echo e(asset('asset/logo.png')); ?>'">
                </div>
                <span class="pm-category-label"><?php echo e($cat->name); ?></span>
            </a>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            
            <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-category-card">
                <div class="pm-category-icon-wrap" style="background:var(--gold-pale)">
                    <i class="fa fa-shopping-cart" style="font-size:24px; color:var(--gold)"></i>
                </div>
                <span class="pm-category-label">Marché</span>
            </a>
        </div>
    </div>

    
    <div class="pm-section">
        <div class="pm-section-header">
            <h2 class="pm-section-title">Offres & Découvertes</h2>
        </div>
        <div class="pm-ads-scroll">
            <?php if(isset($ads) && $ads->count() > 0): ?>
                <?php $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $content = $ad->contents->first(); 
                        $imgUrl = $content ? $content->image_url : null;
                        $cta = $content ? $content->call_to_action : '#';
                        // Logic to handle internal routing properly for dynamic ads
                        if (is_numeric($cta)) {
                            $targetUrl = url('/user/store/product/' . $cta);
                        } elseif ($cta && strpos($cta, 'http') === false && $cta !== '#') {
                            $targetUrl = url($cta);
                        } else {
                            $targetUrl = route('user.marketplace.explore'); // Fallback to store
                        }
                    ?>
                    <a href="<?php echo e($targetUrl); ?>" class="pm-ad-banner">
                        <?php if($imgUrl): ?>
                            <img src="<?php echo e($imgUrl); ?>" class="pm-ad-bg-img" alt="Ad">
                        <?php endif; ?>
                        <div class="pm-ad-content">
                            <span class="pm-ad-tag">Sponsorisé</span>
                            <h3 class="pm-ad-title"><?php echo e($content->title ?? $ad->name); ?></h3>
                            <p class="pm-ad-desc"><?php echo e($content->headline ?? $ad->description ?? ''); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            
            <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-ad-banner">
                <div class="pm-ad-content">
                    <span class="pm-ad-tag">Nouveau</span>
                    <h3 class="pm-ad-title">PicMe225 Rewards</h3>
                    <p class="pm-ad-desc">Gagnez des ECO Tokens à chaque trajet.</p>
                </div>
            </a>
            <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-ad-banner">
                <div class="pm-ad-content">
                    <span class="pm-ad-tag">Marché</span>
                    <h3 class="pm-ad-title">Découvrez le Store</h3>
                    <p class="pm-ad-desc">Achetez et vendez facilement dans l'app.</p>
                </div>
            </a>
        </div>
    </div>

    <?php if(isset($recentProducts) && $recentProducts->count() > 0): ?>
    <div class="pm-section">
        <div class="pm-section-header">
            <h2 class="pm-section-title">Récemment sur le Marché</h2>
            <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-section-link">Voir tout</a>
        </div>
        <div class="pm-products-scroll">
            <?php $__currentLoopData = $recentProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('user.marketplace.detail', $prod->id)); ?>" class="pm-product-card">
                <img src="<?php echo e($prod->media_url); ?>" class="pm-product-img" onerror="this.src='<?php echo e(asset('asset/logo.png')); ?>'" alt="<?php echo e($prod->title); ?>">
                <div class="pm-product-info">
                    <h4 class="pm-product-title"><?php echo e($prod->title); ?></h4>
                    <p class="pm-product-price"><?php echo e(currency($prod->price)); ?></p>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="pm-section" style="padding-bottom: 80px;">
        <div class="pm-section-header">
            <h2 class="pm-section-title">Découvrez nos annonces</h2>
        </div>
        
        <div class="pm-feed-grid" id="pm-feed-container">
            <!-- Dynamically loaded listings -->
        </div>
        
        <div class="pm-feed-loader" id="pm-feed-loader" style="text-align: center; padding: 20px; display: none;">
            <i class="fa fa-spinner fa-spin" style="font-size: 24px; color: var(--gold);"></i>
        </div>
    </div>

    
    <?php echo $__env->make('user.include.bottom_nav', ['active' => 'home'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    
    <?php echo $__env->make('user.include.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    var page = 1;
    var loading = false;
    var hasMore = true;

    function loadFeed() {
        if (loading || !hasMore) return;
        loading = true;
        document.getElementById('pm-feed-loader').style.display = 'block';

        fetch('<?php echo e(route("user.marketplace.feed")); ?>?page=' + page)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var container = document.getElementById('pm-feed-container');
                var items = data.data || [];
                
                if (items.length === 0) {
                    hasMore = false;
                    if (page === 1) {
                        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--gray-500); padding: 40px 0;">Aucune annonce disponible.</div>';
                    }
                    document.getElementById('pm-feed-loader').style.display = 'none';
                    loading = false;
                    return;
                }

                items.forEach(function(item) {
                    var card = document.createElement('a');
                    card.className = 'pm-feed-card';
                    card.href = item.detail_url;

                    var imgWrap = document.createElement('div');
                    imgWrap.className = 'pm-feed-img-wrap';

                    var img = document.createElement('img');
                    img.className = 'pm-feed-img';
                    img.src = item.cover_image;
                    img.alt = item.title;
                    img.loading = 'lazy';
                    imgWrap.appendChild(img);

                    if (item.is_sponsored) {
                        var badge = document.createElement('span');
                        badge.className = 'pm-feed-badge-sponsored';
                        badge.textContent = 'Sponsorisé';
                        imgWrap.appendChild(badge);
                    }

                    card.appendChild(imgWrap);

                    var body = document.createElement('div');
                    body.className = 'pm-feed-body';

                    var cat = document.createElement('div');
                    cat.className = 'pm-feed-cat';
                    cat.textContent = item.category;
                    body.appendChild(cat);

                    var title = document.createElement('h3');
                    title.className = 'pm-feed-title';
                    title.textContent = item.title;
                    body.appendChild(title);

                    var price = document.createElement('div');
                    price.className = 'pm-feed-price';
                    price.textContent = item.price + ' ' + item.price_unit;
                    body.appendChild(price);

                    var meta = document.createElement('div');
                    meta.className = 'pm-feed-meta';
                    meta.innerHTML = '<span><i class="fa fa-map-marker" style="color:var(--gold); margin-right:4px;"></i>' + item.location_city + '</span>';
                    body.appendChild(meta);

                    card.appendChild(body);
                    container.appendChild(card);
                });

                page++;
                loading = false;
                document.getElementById('pm-feed-loader').style.display = 'none';
            })
            .catch(function() {
                loading = false;
                document.getElementById('pm-feed-loader').style.display = 'none';
            });
    }

    // Scroll listener
    window.addEventListener('scroll', function() {
        if ((window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 100) {
            loadFeed();
        }
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadFeed();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.user_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/home.blade.php ENDPATH**/ ?>