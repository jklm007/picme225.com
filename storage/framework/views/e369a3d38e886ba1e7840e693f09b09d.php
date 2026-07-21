<?php $__env->startSection('title', 'Mon Profil'); ?>
<?php $__env->startSection('header-sub', 'Paramètres de votre compte'); ?>
<?php $bottomNavActive = 'profil'; ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* ── PROFILE PAGE ── */
    .profile-page {
        background: #F0F2F5;
        min-height: calc(100vh - 64px);
    }

    /* ── HERO SECTION ── */
    .profile-hero {
        background: linear-gradient(160deg, #0D1B2A 0%, #1a3050 60%, #0f2540 100%);
        padding: 28px 20px 60px 20px;
        text-align: center;
        position: relative;
    }
    .profile-avatar-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 14px;
    }
    .profile-avatar {
        width: 96px; height: 96px;
        border-radius: 50%;
        border: 4px solid #C9A84C;
        box-shadow: 0 8px 32px rgba(201,168,76,0.4);
        background-size: cover;
        background-position: center;
        background-color: #1a3050;
        display: flex; align-items: center; justify-content: center;
        font-size: 36px; font-weight: 800; color: #C9A84C;
        margin: 0 auto;
    }
    .profile-avatar-edit {
        position: absolute;
        bottom: 2px; right: 2px;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #C9A84C;
        border: 2px solid #0D1B2A;
        display: flex; align-items: center; justify-content: center;
        color: #0D1B2A;
        font-size: 11px;
        cursor: pointer;
    }
    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: #fff;
        margin-bottom: 4px;
    }
    .profile-email {
        font-size: 13px;
        color: rgba(255,255,255,0.55);
        margin-bottom: 14px;
    }
    .profile-verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(46,204,113,0.15);
        border: 1px solid rgba(46,204,113,0.3);
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #2ecc71;
        margin-bottom: 18px;
    }
    .profile-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        background: linear-gradient(135deg, #C9A84C, #E2C06E);
        color: #0D1B2A;
        border-radius: 24px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(201,168,76,0.4);
        transition: transform 0.2s;
    }
    .profile-edit-btn:hover { color: #0D1B2A; text-decoration: none; transform: scale(1.03); }

    /* ── STATS STRIP ── */
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 18px;
        padding: 16px 0;
        margin-top: 20px;
    }
    .profile-stat-item {
        text-align: center;
        border-right: 1px solid rgba(255,255,255,0.08);
    }
    .profile-stat-item:last-child { border-right: none; }
    .profile-stat-num {
        font-size: 18px;
        font-weight: 800;
        color: #C9A84C;
    }
    .profile-stat-lbl {
        font-size: 10px;
        color: rgba(255,255,255,0.45);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* ── CARDS ── */
    .profile-cards {
        padding: 20px 16px;
        position: relative;
        z-index: 2;
        margin-top: -28px;
    }

    .profile-section-label {
        font-size: 11px;
        font-weight: 700;
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 4px 8px 4px;
    }

    .profile-card {
        background: #fff;
        border-radius: 18px;
        margin-bottom: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .profile-card-title {
        padding: 16px 18px 12px 18px;
        font-size: 14px;
        font-weight: 700;
        color: #1C2E4A;
        border-bottom: 1px solid #F5F7FA;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .profile-card-title .card-title-icon {
        width: 32px; height: 32px;
        border-radius: 10px;
        background: rgba(201,168,76,0.12);
        display: flex; align-items: center; justify-content: center;
        color: #C9A84C;
        font-size: 14px;
    }

    .profile-row {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        border-bottom: 1px solid #F8FAFC;
        gap: 14px;
    }
    .profile-row:last-child { border-bottom: none; }
    .profile-row-icon {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #F0F4F8;
        display: flex; align-items: center; justify-content: center;
        color: #475569;
        font-size: 14px;
        flex-shrink: 0;
    }
    .profile-row-body { flex: 1; }
    .profile-row-label { font-size: 11px; color: #94A3B8; margin-bottom: 2px; }
    .profile-row-val { font-size: 14px; font-weight: 600; color: #1C2E4A; }
    .profile-row-action {
        color: #94A3B8;
        font-size: 14px;
        flex-shrink: 0;
    }
    a.profile-row { text-decoration: none; cursor: pointer; transition: background 0.15s; }
    a.profile-row:hover { background: #F8FAFC; }

    /* Wallet row special */
    .profile-wallet-val {
        font-size: 18px;
        font-weight: 800;
        color: #C9A84C;
    }
    .profile-eco-val {
        font-size: 18px;
        font-weight: 800;
        color: #2ecc71;
    }

    /* ── LOGOUT BUTTON ── */
    .profile-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px;
        background: #fff;
        border-radius: 18px;
        color: #e74c3c;
        font-size: 15px;
        font-weight: 700;
        border: 2px solid rgba(231,76,60,0.15);
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 6px;
    }
    .profile-logout-btn:hover { background: #fff5f5; border-color: #e74c3c; }

    /* ── VERSION ── */
    .profile-version {
        text-align: center;
        padding: 16px;
        color: #CBD5E0;
        font-size: 12px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="profile-page">

    
    <div class="profile-hero">
        <div class="profile-avatar-wrap">
            <?php if(Auth::user()->picture): ?>
                <div class="profile-avatar" style="background-image:url('<?php echo e(img(Auth::user()->picture)); ?>')"></div>
            <?php else: ?>
                <div class="profile-avatar"><?php echo e(strtoupper(substr(Auth::user()->first_name,0,1))); ?></div>
            <?php endif; ?>
            <a href="<?php echo e(url('edit/profile')); ?>" class="profile-avatar-edit"><i class="fa fa-pencil"></i></a>
        </div>

        <div class="profile-name"><?php echo e(Auth::user()->first_name); ?> <?php echo e(Auth::user()->last_name); ?></div>
        <div class="profile-email"><?php echo e(Auth::user()->email); ?></div>
        <div class="profile-verified-badge"><i class="fa fa-check-circle"></i> Compte Actif</div>

        <div>
            <a href="<?php echo e(url('edit/profile')); ?>" class="profile-edit-btn">
                <i class="fa fa-pencil"></i> Modifier le profil
            </a>
        </div>

        <div class="profile-stats">
            <div class="profile-stat-item">
                <div class="profile-stat-num"><?php echo e(currency(Auth::user()->wallet_balance)); ?></div>
                <div class="profile-stat-lbl">Wallet</div>
            </div>
            <div class="profile-stat-item">
                <div class="profile-stat-num"><?php echo e(Auth::user()->eco_token_balance ?? '0'); ?></div>
                <div class="profile-stat-lbl">ECO Tokens</div>
            </div>
            <div class="profile-stat-item">
                <div class="profile-stat-num">–</div>
                <div class="profile-stat-lbl">Trajets</div>
            </div>
        </div>
    </div>

    
    <div class="profile-cards">

        <?php echo $__env->make('common.notify', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        
        <div class="profile-section-label" style="margin-top:8px;">Informations personnelles</div>
        <div class="profile-card">
            <div class="profile-card-title">
                <div class="card-title-icon"><i class="fa fa-user"></i></div>
                Mon Profil
            </div>
            <div class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-id-card-o"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Prénom</div>
                    <div class="profile-row-val"><?php echo e(Auth::user()->first_name); ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-id-card-o"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Nom</div>
                    <div class="profile-row-val"><?php echo e(Auth::user()->last_name); ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-envelope-o"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Email</div>
                    <div class="profile-row-val"><?php echo e(Auth::user()->email); ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-phone"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Téléphone</div>
                    <div class="profile-row-val"><?php echo e(Auth::user()->mobile ?? '–'); ?></div>
                </div>
            </div>
        </div>

        
        <div class="profile-section-label">Paiement</div>
        <div class="profile-card">
            <div class="profile-card-title">
                <div class="card-title-icon"><i class="fa fa-money"></i></div>
                Portefeuille & Paiement
            </div>
            <a href="<?php echo e(url('wallet')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-google-wallet"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Solde Wallet</div>
                    <div class="profile-wallet-val"><?php echo e(currency(Auth::user()->wallet_balance)); ?></div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo e(url('wallet')); ?>" class="profile-row">
                <div class="profile-row-icon" style="color:#2ecc71;"><i class="fa fa-leaf"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">ECO Tokens</div>
                    <div class="profile-eco-val"><?php echo e(Auth::user()->eco_token_balance ?? '0'); ?></div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo e(url('payment')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-credit-card"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Moyens de paiement</div>
                    <div class="profile-row-val">Gérer mes cartes</div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
        </div>

        
        <div class="profile-section-label">Historique</div>
        <div class="profile-card">
            <div class="profile-card-title">
                <div class="card-title-icon"><i class="fa fa-history"></i></div>
                Historique
            </div>
            <a href="<?php echo e(url('trips')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-car"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Mes trajets</div>
                    <div class="profile-row-val">Voir l'historique</div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo e(url('upcoming/trips')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-calendar"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Trajets planifiés</div>
                    <div class="profile-row-val">Mes réservations</div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
        </div>

        
        <div class="profile-section-label">Paramètres</div>
        <div class="profile-card">
            <div class="profile-card-title">
                <div class="card-title-icon"><i class="fa fa-cog"></i></div>
                Paramètres
            </div>
            <div class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-globe"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Langue</div>
                    <div class="profile-row-val"><?php echo e(Auth::user()->language == 'fr' ? '🇫🇷 Français' : '🇬🇧 English'); ?></div>
                </div>
            </div>
            <a href="<?php echo e(url('promotions')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-tag"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Promotions</div>
                    <div class="profile-row-val">Mes codes promo</div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
        </div>

        
        <div class="profile-section-label">Sécurité</div>
        <div class="profile-card">
            <div class="profile-card-title">
                <div class="card-title-icon"><i class="fa fa-shield"></i></div>
                Sécurité
            </div>
            <a href="<?php echo e(url('change/password')); ?>" class="profile-row">
                <div class="profile-row-icon"><i class="fa fa-lock"></i></div>
                <div class="profile-row-body">
                    <div class="profile-row-label">Mot de passe</div>
                    <div class="profile-row-val">Modifier le mot de passe</div>
                </div>
                <div class="profile-row-action"><i class="fa fa-chevron-right"></i></div>
            </a>
        </div>

        
        <form action="<?php echo e(url('/logout')); ?>" method="POST" id="profile-logout-form">
            <?php echo e(csrf_field()); ?>

        </form>
        <button class="profile-logout-btn" onclick="document.getElementById('profile-logout-form').submit()">
            <i class="fa fa-sign-out"></i>
            Se déconnecter
        </button>

        <div class="profile-version">PicMe225 PWA • Version 2.0</div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('user.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/account/profile.blade.php ENDPATH**/ ?>