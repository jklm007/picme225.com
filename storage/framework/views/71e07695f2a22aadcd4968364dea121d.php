<?php $__env->startSection('content'); ?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
*, *::before, *::after { box-sizing: border-box; }

.pm-mkt {
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--bg-color, #0d1226);
    color: var(--text-color, #e2e8f0);
    min-height: 100vh;
    transition: background 0.3s, color 0.3s;
}

/* Light Mode Variables */
:root {
    --bg-color: #0d1226;
    --bg-header: #0d1226;
    --bg-card: #ffffff;
    --text-color: #e2e8f0;
    --text-dark: #1a202c;
    --border-color: rgba(255,255,255,0.08);
    --border-card: #edf2f7;
    --header-text: #ffffff;
}

[data-theme="light"] {
    --bg-color: #f8fafc;
    --bg-header: #ffffff;
    --bg-card: #ffffff;
    --text-color: #4a5568;
    --text-dark: #1a202c;
    --border-color: #e2e8f0;
    --border-card: #e2e8f0;
    --header-text: #1a202c;
}

[data-theme="dark"] {
    --bg-color: #0d1226;
    --bg-header: #0d1226;
    --bg-card: #1a202c;
    --text-color: #e2e8f0;
    --text-dark: #ffffff;
    --border-color: rgba(255,255,255,0.08);
    --border-card: rgba(255,255,255,0.08);
    --header-text: #ffffff;
}

/* ─── HEADER ─────────────────────────────────────────── */
.pm-mkt-header {
    background: var(--bg-header);
    padding: 80px 0 40px;
    border-bottom: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
}
.pm-mkt-header::before {
    content: '';
    position: absolute;
    width: 600px; height: 300px;
    background: radial-gradient(ellipse, rgba(255,75,124,.05) 0%, transparent 70%);
    top: -50px; right: -100px;
    pointer-events: none;
}
.pm-mkt-header h1 {
    font-size: clamp(30px, 5vw, 52px);
    font-weight: 900;
    letter-spacing: -1px;
    margin-bottom: 12px;
    color: var(--header-text);
}
.pm-mkt-header h1 span {
    background: linear-gradient(135deg, #C9A84C, #ecc94b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.pm-mkt-header p { color: #a0aec0; font-size: 17px; margin-bottom: 0; }

/* ─── FILTERS BAR ─────────────────────────────────────── */
.pm-mkt-filters {
    background: var(--bg-header);
    backdrop-filter: blur(12px);
    padding: 20px 0;
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 60px;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
}
.pm-search-box {
    position: relative;
    margin-bottom: 0;
}
.pm-search-box input {
    width: 100%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 12px 16px 12px 44px;
    color: #ffffff;
    font-size: 15px;
    outline: none;
    transition: all .2s;
}
.pm-search-box input::placeholder { color: #718096; }
.pm-search-box input:focus { 
    background: rgba(255,255,255,0.12);
    border-color: rgba(201,168,76,.5); 
    box-shadow: 0 0 0 3px rgba(201,168,76,.1);
}
.pm-search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
}
.pm-cat-chips {
    display: flex;
    gap: 10px;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-top: 16px;
    -webkit-overflow-scrolling: touch;
}
.pm-cat-chips::-webkit-scrollbar {
    height: 4px;
}
.pm-cat-chips::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
}
[data-theme="light"] .pm-cat-chips::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
}
.pm-cat-chip {
    padding: 7px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.07);
    color: #a0aec0;
    user-select: none;
    white-space: nowrap;
    flex-shrink: 0;
}
.pm-cat-chip:hover,
.pm-cat-chip.active {
    background: rgba(201,168,76,.15);
    border-color: #C9A84C;
    color: #C9A84C;
    box-shadow: 0 0 12px rgba(201,168,76,0.3);
}
.pm-cat-chip.active-all {
    background: rgba(255,255,255,.1);
    border-color: rgba(255,255,255,.4);
    color: #ffffff;
    box-shadow: 0 0 12px rgba(255,255,255,0.2);
}

/* ─── BODY ────────────────────────────────────────────── */
.pm-mkt-body {
    padding: 28px 0 80px; }
.pm-mkt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}
.pm-listing-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 24px;
    overflow: hidden;
    transition: all .4s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
}
.pm-listing-card:hover {
    transform: translateY(-8px);
    border-color: #C9A84C;
    box-shadow: 0 24px 48px rgba(0,0,0,.3), 0 0 20px rgba(201,168,76,.15);
}
.pm-listing-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f1f3f8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    position: relative;
}
.pm-listing-img img { width: 100%; height: 100%; object-fit: cover; }
.pm-listing-cat {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(255,255,255,.9);
    backdrop-filter: blur(10px);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #C9A84C;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.pm-listing-body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.pm-listing-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pm-listing-desc {
    font-size: 13px;
    color: #718096;
    line-height: 1.6;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.pm-listing-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #edf2f7;
    padding-top: 14px;
    margin-top: auto;
}
.pm-listing-price {
    font-size: 20px;
    font-weight: 800;
    color: #C9A84C !important;
}
.pm-listing-price small { font-size: 12px; font-weight: 400; color: #a0aec0; }
.pm-listing-location {
    font-size: 12px;
    color: #718096;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* CTA button */
.pm-cta-btn {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
    border: none;
    background: linear-gradient(135deg, #0A1628, #102040);
    color: #C9A84C;
    transition: all .2s;
    letter-spacing: .3px;
}
.pm-cta-btn:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(10,22,40,.3); }

/* ─── APP MODAL ──────────────────────────────────────── */
.pm-app-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(13,18,38,.6);
    backdrop-filter: blur(8px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.pm-app-modal.show { display: flex; }
.pm-modal-box {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 24px;
    padding: 40px;
    max-width: 440px;
    width: 90%;
    text-align: center;
    position: relative;
    box-shadow: 0 40px 80px rgba(0,0,0,.15);
    animation: pm-modal-in .3s ease;
    color: #1a202c;
}
@keyframes pm-modal-in {
    from { opacity: 0; transform: scale(.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.pm-modal-close {
    position: absolute;
    top: 16px; right: 20px;
    background: none;
    border: none;
    color: #a0aec0;
    font-size: 22px;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}
.pm-modal-close:hover { color: #1a202c; }
.pm-modal-box .pm-qr-box {
    width: 160px; height: 160px;
    background: #fff;
    border-radius: 16px;
    padding: 10px;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(255,75,124,.15);
}
.pm-modal-box .pm-qr-box img { width: 100%; height: 100%; }
.pm-modal-box h3 { font-size: 22px; font-weight: 800; margin-bottom: 10px; color: #0d1226; }
.pm-modal-box p { font-size: 14px; color: #4a5568; margin-bottom: 24px; line-height: 1.6; }
.pm-modal-dl-btns { display: flex; flex-direction: column; gap: 12px; }
.pm-modal-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s;
}
.pm-modal-btn-play {
    background: linear-gradient(135deg, #34a853, #1a8e3e);
    color: #fff;
    box-shadow: 0 4px 12px rgba(52,168,83,.2);
}
.pm-modal-btn-apk {
    background: #f8f9fc;
    color: #4a5568;
    border: 1px solid #edf2f7;
}
.pm-modal-btn:hover { text-decoration: none; opacity: .9; transform: translateY(-2px); }
.pm-modal-btn i { font-size: 20px; }
.pm-modal-btn-apk:hover { background: #edf2f7; color: #1a202c; }

/* ─── EMPTY STATE ─────────────────────────────────────── */
.pm-empty {
    text-align: center;
    padding: 80px 20px;
    color: #718096;
}
.pm-empty i { font-size: 64px; margin-bottom: 16px; display: block; color: #C9A84C; opacity: 0.5; }
.pm-empty h4 { font-size: 20px; color: #e2e8f0; margin-bottom: 8px; font-weight: 700; }
.pm-empty p { font-size: 14px; color: #718096; }

/* Skeleton loading */
.pm-skeleton {
    background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
    background-size: 200% 100%;
    animation: pm-shimmer 1.8s ease-in-out infinite;
    border-radius: 8px;
}
@keyframes pm-shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }

.pm-results-count { 
    color: #C9A84C !important; 
    font-size: 15px !important; 
    margin: 20px 0 24px 0 !important; 
    font-weight: 800 !important; 
    background: rgba(201,168,76,0.15) !important; 
    padding: 8px 20px !important; 
    border-radius: 12px !important; 
    display: inline-block !important; 
    border: 1px solid #C9A84C !important; 
    visibility: visible !important;
    opacity: 1 !important;
}
</style>

<!-- ═══ APP MODAL ═══ -->
<div class="pm-app-modal" id="pm-app-modal">
    <div class="pm-modal-box">
        <button class="pm-modal-close" onclick="document.getElementById('pm-app-modal').classList.remove('show')">✕</button>
        <div class="pm-qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?php echo e(urlencode(Setting::get('store_link_android','https://play.google.com'))); ?>" alt="QR Code App" />
        </div>
        <h3>Commander via l'application</h3>
        <p>Pour finaliser votre achat en toute sécurité (avec séquestre financier garanti), téléchargez l'application PicMe225 sur votre smartphone Android.</p>
        <div class="pm-modal-dl-btns">
            <a href="<?php echo e(Setting::get('store_link_android','https://play.google.com/store/apps')); ?>" target="_blank" class="pm-modal-btn pm-modal-btn-play" id="btn-modal-play">
                <i class="fa fa-android"></i> Google Play Store
            </a>
            <a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')" class="pm-modal-btn pm-modal-btn-apk" id="btn-modal-apk">
                <i class="fa fa-download"></i> Installer l'Application
            </a>
        </div>
    </div>
</div>

    <!-- ═══ FILTERS ═══ -->
    <section class="pm-mkt-filters">
        <div class="container">
            <form action="<?php echo e(route('marketplace.public')); ?>" method="GET" id="pm-filter-form">
                <!-- Search and Toggle Button -->
                <div style="display:flex; gap:10px; align-items:center;">
                    <div class="pm-search-box" style="flex: 1; min-width: 0;">
                        <i class="fa fa-search"></i>
                        <input type="text" name="search" id="pm-search-input" value="<?php echo e(request('search')); ?>" placeholder="Rechercher un article, un véhicule..." />
                    </div>
                    <button type="button" onclick="document.getElementById('pm-advanced-filters').style.display = document.getElementById('pm-advanced-filters').style.display === 'none' ? 'flex' : 'none';" style="padding: 12px 16px; background: rgba(255,255,255,0.08); color: var(--text-color); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; cursor: pointer; white-space: nowrap; font-weight: 600;">
                        <i class="fa fa-sliders"></i> Filtres
                    </button>
                </div>
                
                <!-- Advanced Filters -->
                <div id="pm-advanced-filters" style="display: <?php echo e(request('city') || request('min_price') || request('max_price') || (request('sort') && request('sort') != 'newest') ? 'flex' : 'none'); ?>; flex-wrap:wrap; gap:10px; align-items:center; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                    <input type="text" name="city" value="<?php echo e(request('city')); ?>" placeholder="Ville" style="flex: 1 1 120px; padding: 12px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); outline: none;">
                    
                    <input type="number" name="min_price" value="<?php echo e(request('min_price')); ?>" placeholder="Prix min" style="flex: 1 1 100px; padding: 12px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); outline: none;">
                    <input type="number" name="max_price" value="<?php echo e(request('max_price')); ?>" placeholder="Prix max" style="flex: 1 1 100px; padding: 12px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); outline: none;">
                    
                    <select name="sort" style="flex: 1 1 150px; padding: 12px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); outline: none;" onchange="document.getElementById('pm-filter-form').submit();">
                        <option value="newest" <?php echo e(request('sort') == 'newest' ? 'selected' : ''); ?>>Plus récentes</option>
                        <option value="price_asc" <?php echo e(request('sort') == 'price_asc' ? 'selected' : ''); ?>>Prix croissant</option>
                        <option value="price_desc" <?php echo e(request('sort') == 'price_desc' ? 'selected' : ''); ?>>Prix décroissant</option>
                    </select>

                    <button type="submit" style="padding: 12px 24px; background: #C9A84C; color: white; font-weight: bold; border: none; border-radius: 12px; cursor: pointer; flex: 1 1 100%;">Appliquer les filtres</button>
                </div>
            </form>
            
            <div class="pm-cat-chips">
                <div class="pm-cat-chip main-cat <?php echo e(!request('category') ? 'active-all' : ''); ?>" data-cat="">Tout afficher</div>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isActiveMain = false;
                        if (request('category') == $c->name) {
                            $isActiveMain = true;
                        } elseif (request('category')) {
                            // Check if an active subcategory belongs to this parent
                            $activeSub = $c->children->firstWhere('name', request('category'));
                            if ($activeSub) $isActiveMain = true;
                        }
                    ?>
                    <div class="pm-cat-chip main-cat <?php echo e($isActiveMain ? 'active' : ''); ?>" data-cat="<?php echo e($c->name); ?>">
                        <?php echo e($c->label ?? $c->name); ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php
                $subcategoriesToShow = collect();
                if (request('category')) {
                    $activeCategoryObj = \App\Models\MarketplaceCategory::where('name', request('category'))->first();
                    if ($activeCategoryObj) {
                        if ($activeCategoryObj->parent_id == null) {
                            $subcategoriesToShow = $activeCategoryObj->children;
                        } else {
                            $subcategoriesToShow = \App\Models\MarketplaceCategory::where('parent_id', $activeCategoryObj->parent_id)->orderBy('order_index')->get();
                        }
                    }
                }
            ?>
            
            <?php if($subcategoriesToShow->count() > 0): ?>
                <div class="pm-cat-chips" id="pm-subcat-container" style="margin-top:10px;">
                    <?php $__currentLoopData = $subcategoriesToShow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="pm-cat-chip pm-sub-chip <?php echo e(request('category') == $sub->name ? 'active' : ''); ?>" data-cat="<?php echo e($sub->name); ?>">
                            <?php echo e($sub->label ?? $sub->name); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <!-- ═══ APP DOWNLOAD MODAL ═══ -->
    <div class="pm-app-modal" id="pm-app-modal">
        <div class="pm-modal-box">
            <button class="pm-modal-close" onclick="document.getElementById('pm-app-modal').classList.remove('show')">✕</button>
            <div class="pm-qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?php echo e(urlencode(Setting::get('store_link_android','https://play.google.com'))); ?>" alt="QR Code App" />
            </div>
            <h3>Commander via l'application</h3>
            <p>Pour finaliser votre achat en toute sécurité (avec séquestre financier garanti), téléchargez l'application PicMe225 sur votre smartphone Android.</p>
            <div class="pm-modal-dl-btns">
                <a href="<?php echo e(Setting::get('store_link_android','https://play.google.com/store/apps')); ?>" target="_blank" class="pm-modal-btn pm-modal-btn-play" id="btn-modal-play">
                    <i class="fa fa-android"></i> Google Play Store
                </a>
                <a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')" class="pm-modal-btn pm-modal-btn-apk" id="btn-modal-apk">
                    <i class="fa fa-download"></i> Installer l'Application
                </a>
            </div>
        </div>
    </div>

    <!-- ═══ GRID ═══ -->
    <section class="pm-mkt-body">
        <div class="container">
            <p class="pm-results-count" id="pm-count">Chargement des annonces…</p>
            <div class="pm-mkt-grid" id="pm-grid">
                <!-- Skeleton loaders will be replaced by JS -->
            </div>
            <div style="margin-top: 40px; display: flex; justify-content: center;">
                <?php echo e($listings->appends(request()->query())->links()); ?>

            </div>
        </div>
    </section>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function() {
    var allListings = [];
    var currentCat = '';
    var currentSubCat = '';
    var searchQuery = '';

    // ─── Format price ───────────────────────────────────────
    function formatPrice(price, unit) {
        var num = parseFloat(price);
        var formatted = num.toLocaleString('fr-FR') + ' FCFA';
        if (unit === 'day') formatted += ' <small>/jour</small>';
        else if (unit === 'month') formatted += ' <small>/mois</small>';
        return formatted;
    }

    // ─── Category emoji ────────────────────────────────────
    function catEmoji(cat) {
        if (!cat) return '📦';
        cat = cat.toUpperCase();
        if (cat.includes('VEHICL') || cat.includes('AUTO')) return '🚗';
        if (cat.includes('REAL_ESTATE') || cat.includes('IMMO')) return '🏠';
        if (cat.includes('TICKET') || cat.includes('EVENT') || cat.includes('BILLET')) return '🎫';
        if (cat.includes('SERVICE')) return '🛠️';
        return '📦';
    }

    // ─── Render listings ────────────────────────────────────
    function renderListings(listings) {
        var grid = document.getElementById('pm-grid');
        var countEl = document.getElementById('pm-count');

        if (!listings.length) {
            grid.innerHTML = '<div class="pm-empty" style="grid-column:1/-1;"><i class="fa fa-search"></i><h4>Aucune annonce trouvée</h4><p>Essayez une autre catégorie ou modifiez votre recherche.</p></div>';
            countEl.textContent = '0 annonce trouvée';
            return;
        }

        countEl.textContent = '<?php echo e($listings->total()); ?>' + ' annonce' + ('<?php echo e($listings->total()); ?>' > 1 ? 's' : '') + ' trouvée' + ('<?php echo e($listings->total()); ?>' > 1 ? 's' : '');

        grid.innerHTML = listings.map(function(item) {
            var price = formatPrice(item.price, item.price_unit);
            var img = item.media_url
                ? '<img src="' + item.media_url + '" alt="' + (item.title || '') + '" loading="lazy" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML=\'<span style=font-size:48px>' + catEmoji(item.category) + '</span>\'">'
                : '<span style="font-size:48px;">' + catEmoji(item.category) + '</span>';
            var city = (item.location_city && item.location_city.toLowerCase() !== 'non spécifié') ? item.location_city : 'Abidjan';
            var photosBadge = item.images_count > 1
                ? '<span style="position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);color:#fff;border-radius:16px;padding:4px 10px;font-size:11px;font-weight:600;"><i class="fa fa-camera"></i> ' + item.images_count + '</span>'
                : '';
            
            var searchBadge = item.type === 'SEARCH'
                ? '<span style="position:absolute;top:12px;right:12px;background:#FF4B7C;color:#fff;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;letter-spacing:.5px;"><i class="fa fa-search"></i> RECHERCHE</span>'
                : '';

            var detailUrl = '/marketplace/' + item.id;
            var whatsappMsg = encodeURIComponent('Bonjour, je suis intéressé(e) par cette annonce : ' + (item.title || '') + ' - https://picme225.site/marketplace/' + item.id);
            var whatsappUrl = 'https://wa.me/2250759747444?text=' + whatsappMsg;

            return '<div class="pm-listing-card" style="cursor:pointer;text-decoration:none;" onclick="window.location=\'' + detailUrl + '\'">' +
                '<div class="pm-listing-img">' +
                    img +
                    '<span class="pm-listing-cat">' + (item.category || 'Article') + '</span>' +
                    searchBadge +
                    photosBadge +
                '</div>' +
                '<div class="pm-listing-body">' +
                    '<div class="pm-listing-title">' + (item.title || 'Sans titre') + '</div>' +
                    '<div class="pm-listing-desc">' + (item.description || '') + '</div>' +
                    '<div class="pm-listing-footer">' +
                        '<div class="pm-listing-price">' + price + '</div>' +
                        '<div class="pm-listing-location"><i class="fa fa-map-marker"></i>' + city + '</div>' +
                    '</div>' +
                    '<div style="display:flex;gap:8px;margin-top:10px;">' +
                        '<button class="pm-cta-btn" style="flex:1;" onclick="event.stopPropagation();window.location=\'' + detailUrl + '\'">🔍 Voir le détail</button>' +
                        '<a href="' + whatsappUrl + '" class="pm-cta-btn" style="flex:1;background:linear-gradient(135deg,#25D366,#128C7E);color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;" onclick="event.stopPropagation();" target="_blank"><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'currentColor\'><path d=\'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z\'/><path d=\'M12 0C5.374 0 0 5.373 0 12c0 2.117.554 4.103 1.523 5.83L.057 23.999l6.305-1.654A11.937 11.937 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12 0zm0 21.818a9.818 9.818 0 01-5.006-1.376l-.358-.214-3.741.981.999-3.648-.233-.374A9.817 9.817 0 012.182 12C2.182 6.578 6.578 2.182 12 2.182S21.818 6.578 21.818 12 17.422 21.818 12 21.818z\'/></svg> Vendeur</a>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    // ─── Category chips (Backend Filtering) ─────────────────
    document.querySelectorAll('.pm-cat-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            var catName = this.getAttribute('data-cat');
            var form = document.getElementById('pm-filter-form');
            var catInput = document.getElementById('pm-category-input');
            
            if(!catInput) {
                catInput = document.createElement('input');
                catInput.type = 'hidden';
                catInput.name = 'category';
                catInput.id = 'pm-category-input';
                form.appendChild(catInput);
            }
            
            catInput.value = catName;
            form.submit();
        });
    });

    // ─── Close modal on backdrop ────────────────────────────
    document.getElementById('pm-app-modal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });

    // ─── Theme Toggle ───────────────────────────────────────
    var themeToggle = document.getElementById('theme-toggle');
    var currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            var theme = document.documentElement.getAttribute('data-theme');
            var newTheme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

    // ─── Load listings from server-side data (no auth required) ─────────────────
    try {
        <?php
            $safeListings = $listings->getCollection()->map(function($l) {
                $imgs = is_array($l->images) ? $l->images : [];
                $count = count($imgs) + ($l->cover_image ? 1 : 0);
                return [
                    'id'            => $l->id,
                    'title'         => $l->title,
                    'description'   => $l->description,
                    'price'         => $l->price,
                    'price_unit'    => $l->price_unit,
                    'category'      => $l->category,
                    'type'          => $l->type,
                    'location_city' => $l->location_city,
                    'media_url'     => $l->media_url,
                    'images_count'  => $count,
                    'status'        => $l->status,
                ];
            })->values();
        ?>
        var serverListings = <?php echo json_encode($safeListings, 15, 512) ?>;
        allListings = serverListings;
        renderListings(allListings);
    } catch(e) {
        document.getElementById('pm-grid').innerHTML = '<div class="pm-empty" style="grid-column:1/-1;"><i class="fa fa-exclamation-circle" style="color:#FF4B7C;"></i><h4>Impossible de charger les annonces</h4><p>Vérifiez votre connexion ou réessayez plus tard.</p></div>';
        document.getElementById('pm-count').textContent = '';
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/marketplace/index.blade.php ENDPATH**/ ?>