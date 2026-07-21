<?php if(!defined('BOTTOM_NAV_RENDERED')): ?>
<?php
    define('BOTTOM_NAV_RENDERED', true);
    $active = $active ?? '';
?>
<style>
    /* ── PREMIUM BOTTOM NAVIGATION ── */
    .pm-bottom-nav {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 50000 !important;
        height: 70px !important;
        background: rgba(255,255,255,0.97) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        display: flex !important;
        justify-content: space-around !important;
        align-items: center !important;
        border-radius: 24px 24px 0 0 !important;
        box-shadow: 0 -4px 30px rgba(0,0,0,0.10) !important;
        border-top: 1px solid rgba(0,0,0,0.04) !important;
        padding: 0 6px !important;
        padding-bottom: env(safe-area-inset-bottom, 0px) !important;
    }
    .pm-nav-links {
        display: flex !important;
        width: 100% !important;
        justify-content: space-around !important;
        align-items: center !important;
        height: 100% !important;
    }
    .pm-nav-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        color: #94A3B8 !important;
        width: 62px !important;
        position: relative !important;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1) !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        cursor: pointer !important;
    }
    
    .pm-nav-icon-wrap {
        width: 32px !important;
        height: 32px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1) !important;
        color: #94A3B8 !important;
        margin-top: 0 !important;
        background: transparent !important;
        border: 3px solid transparent !important;
        box-shadow: none !important;
    }
    .pm-nav-icon-wrap i {
        font-size: 20px !important;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1) !important;
    }
    .pm-nav-label {
        margin-top: 2px !important;
        font-size: 10px !important;
        font-weight: 600 !important;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1) !important;
    }

    /* Active State: Elevated / Floating */
    .pm-nav-item.active .pm-nav-icon-wrap {
        width: 52px !important;
        height: 52px !important;
        background: linear-gradient(135deg, #C9A84C, #E2C06E) !important;
        box-shadow: 0 6px 20px rgba(201,168,76,0.55) !important;
        border-color: rgba(255,255,255,0.9) !important;
        margin-top: -26px !important;
        color: #0D1B2A !important;
    }
    .pm-nav-item.active .pm-nav-icon-wrap i {
        font-size: 22px !important;
        color: #0D1B2A !important;
    }
    .pm-nav-item.active .pm-nav-label {
        color: #C9A84C !important;
        font-weight: 700 !important;
    }
    
    .pm-nav-item:active { transform: scale(0.93) !important; }

    body {
        padding-bottom: calc(70px + env(safe-area-inset-bottom, 0px)) !important;
    }
</style>

<?php
    $currentCatName = 'VTC';
    $currentCatIcon = 'fa-taxi';
    if (request()->has('cat')) {
        $catId = request()->get('cat');
        // We can query the categories list using a fast database lookup or from view variables
        $matched = \App\Models\Service::find($catId);
        if ($matched) {
            $currentCatName = $matched->name;
            $nameLower = strtolower($matched->name);
            if (strpos($nameLower, 'livraison') !== false || strpos($nameLower, 'delivery') !== false) {
                $currentCatIcon = 'fa-truck';
            } elseif (strpos($nameLower, 'partage') !== false || strpos($nameLower, 'pool') !== false || strpos($nameLower, 'share') !== false) {
                $currentCatIcon = 'fa-users';
            } elseif (strpos($nameLower, 'location') !== false || strpos($nameLower, 'rental') !== false) {
                $currentCatIcon = 'fa-key';
            } elseif (strpos($nameLower, 'voyage') !== false || strpos($nameLower, 'outstation') !== false) {
                $currentCatIcon = 'fa-road';
            }
        }
    }
?>

<nav class="pm-bottom-nav" id="pm-global-bottom-nav">
    <div class="pm-nav-links">
        <a href="<?php echo e(url('home')); ?>" class="pm-nav-item <?php echo e($active === 'home' ? 'active' : ''); ?>">
            <div class="pm-nav-icon-wrap"><i class="fa fa-home"></i></div>
            <span class="pm-nav-label">Accueil</span>
        </a>

        <a href="<?php echo e(url('trips')); ?>" class="pm-nav-item <?php echo e($active === 'trips' ? 'active' : ''); ?>">
            <div class="pm-nav-icon-wrap"><i class="fa fa-history"></i></div>
            <span class="pm-nav-label">Trajets</span>
        </a>

        <a href="<?php echo e(url('dashboard')); ?>" class="pm-nav-item <?php echo e($active === 'vtc' ? 'active' : ''); ?>">
            <div class="pm-nav-icon-wrap"><i class="fa <?php echo e($active === 'vtc' ? $currentCatIcon : 'fa-taxi'); ?>"></i></div>
            <span class="pm-nav-label"><?php echo e($active === 'vtc' ? $currentCatName : 'VTC'); ?></span>
        </a>

        <a href="<?php echo e(route('user.marketplace.explore')); ?>" class="pm-nav-item <?php echo e($active === 'store' ? 'active' : ''); ?>">
            <div class="pm-nav-icon-wrap"><i class="fa fa-shopping-cart"></i></div>
            <span class="pm-nav-label">Marché</span>
        </a>

        <a href="<?php echo e(url('profile')); ?>" class="pm-nav-item <?php echo e($active === 'profil' ? 'active' : ''); ?>">
            <div class="pm-nav-icon-wrap"><i class="fa fa-user"></i></div>
            <span class="pm-nav-label">Profil</span>
        </a>
    </div>
</nav>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/include/bottom_nav.blade.php ENDPATH**/ ?>