<?php $__env->startSection('content'); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--color-brand-primary, #0A1628);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pm-login-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }

    /* ─── Left panel ─── */
    .pm-login-left {
        flex: 1;
        background: linear-gradient(135deg, #0A1628 0%, #0f2040 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px;
        position: relative;
        overflow: hidden;
    }

    .pm-back-btn {
        position: absolute;
        top: 30px;
        left: 30px;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        z-index: 10;
        background: rgba(255, 255, 255, 0.05);
        padding: 8px 16px;
        border-radius: 30px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .pm-back-btn:hover {
        color: #C9A84C;
        background: rgba(201, 168, 76, 0.1);
        border-color: rgba(201, 168, 76, 0.3);
        transform: translateX(-3px);
    }

    .pm-login-left::before {
        content: '';
        position: absolute;
        top: -100px;
        left: -100px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(201,168,76,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .pm-login-left::after {
        content: '';
        position: absolute;
        bottom: -80px;
        right: -80px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
        pointer-events: none;
    }

    .pm-login-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 60px;
    }

    .pm-login-logo img {
        height: 48px;
        filter: brightness(0) invert(1);
    }

    .pm-login-logo span {
        color: #C9A84C;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .pm-login-tagline h1 {
        color: #ffffff;
        font-size: 40px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .pm-login-tagline h1 span { color: #C9A84C; }

    .pm-login-tagline p {
        color: #888;
        font-size: 16px;
        line-height: 1.7;
        max-width: 380px;
    }

    .pm-login-features {
        margin-top: 50px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .pm-feature-item {
        display: flex;
        align-items: center;
        gap: 14px;
        color: #aaa;
        font-size: 14px;
    }

    .pm-feature-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(201,168,76,0.12);
        border: 1px solid rgba(201,168,76,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #C9A84C;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* ─── Right panel ─── */
    .pm-login-right {
        width: 480px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px 50px;
        position: relative;
    }

    .pm-login-header {
        margin-bottom: 32px;
    }

    .pm-login-header h2 {
        color: #1A202C;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .pm-login-header p {
        color: #666;
        font-size: 14px;
    }

    /* ─── Role tabs ─── */
    .pm-role-tabs {
        display: flex;
        background: #F8FAFC;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 4px;
        margin-bottom: 28px;
        gap: 4px;
    }

    .pm-role-tab {
        flex: 1;
        padding: 11px;
        border: none;
        border-radius: 9px;
        background: transparent;
        color: #666;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: 'Inter', sans-serif;
    }

    .pm-role-tab.active {
        background: #C9A84C;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(201,168,76,0.3);
    }

    .pm-role-tab:hover:not(.active) {
        background: #e2e8f0;
        color: #4a5568;
    }

    /* ─── Form ─── */
    .pm-form-group {
        margin-bottom: 18px;
    }

    .pm-form-group label {
        display: block;
        color: #888;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pm-form-group input {
        width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e0;
        border-radius: 10px;
        padding: 13px 16px;
        color: #1A202C;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s;
        outline: none;
    }

    .pm-form-group input:focus {
        border-color: #C9A84C;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
    }

    .pm-form-group input::placeholder { color: #444; }

    .pm-form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .pm-remember {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 13px;
        cursor: pointer;
    }

    .pm-remember input[type=checkbox] {
        width: 16px;
        height: 16px;
        accent-color: #C9A84C;
        cursor: pointer;
    }

    .pm-forgot {
        color: #C9A84C;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .pm-forgot:hover { color: #e0b85c; }

    .pm-btn-login {
        width: 100%;
        background: linear-gradient(135deg, #C9A84C, #a88035);
        border: none;
        border-radius: 10px;
        padding: 14px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .pm-btn-login:hover {
        background: linear-gradient(135deg, #d9b85c, #b89045);
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(201,168,76,0.35);
    }

    .pm-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
        color: #333;
        font-size: 12px;
    }

    .pm-divider::before,
    .pm-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .pm-social-btns {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
    }

    .pm-social-btn {
        flex: 1;
        background: #ffffff;
        border: 1px solid #cbd5e0;
        border-radius: 10px;
        padding: 12px;
        color: #4a5568;
        font-size: 13px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .pm-social-btn:hover {
        background: #f8fafc;
        color: #1a202c;
        border-color: #a0aec0;
    }

    .pm-login-footer {
        text-align: center;
        color: #555;
        font-size: 13px;
        margin-top: 20px;
    }

    .pm-login-footer a {
        color: #C9A84C;
        text-decoration: none;
        font-weight: 600;
    }

    .pm-login-footer a:hover { color: #e0b85c; }

    .pm-error {
        background: rgba(220,53,69,0.1);
        border: 1px solid rgba(220,53,69,0.3);
        border-radius: 8px;
        color: #f17b84;
        font-size: 13px;
        padding: 10px 14px;
        margin-bottom: 18px;
    }

    .pm-driver-note {
        display: none;
        background: rgba(201,168,76,0.08);
        border: 1px solid rgba(201,168,76,0.2);
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 20px;
        color: #C9A84C;
        font-size: 13px;
        line-height: 1.5;
    }

    .pm-driver-note i { margin-right: 6px; }

    /* ─── Responsive ─── */
    @media (max-width: 900px) {
        .pm-login-left { display: none; }
        .pm-login-right {
            width: 100%;
            padding: 40px 24px;
        }
    }
</style>

<div class="pm-login-wrapper">

    <!-- Left visual panel -->
    <div class="pm-login-left">
        <a href="<?php echo e(url('/')); ?>" class="pm-back-btn">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
        <div class="pm-login-logo">
            <img src="<?php echo e(Setting::get('site_logo', asset('logo-black.png'))); ?>" alt="PicMe225">
            <span><?php echo e(Setting::get('site_title', 'PicMe225')); ?></span>
        </div>

        <div class="pm-login-tagline">
            <h1>Bienvenue sur <span>PicMe225</span></h1>
            <p>La plateforme de mobilité et de services numéro 1 en Côte d'Ivoire. Commandez un taxi, un Woro ou gérez votre activité de chauffeur.</p>
        </div>

        <div class="pm-login-features">
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-car"></i></div>
                <span>Taxi, Woro, Transport interurbain & Aéroport</span>
            </div>
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-shopping-bag"></i></div>
                <span>Marketplace & Location de véhicules</span>
            </div>
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-shield"></i></div>
                <span>Paiement Mobile Money sécurisé</span>
            </div>
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-map-marker"></i></div>
                <span>Géolocalisation en temps réel</span>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="pm-login-right">
        <div class="pm-login-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace personnel</p>
        </div>

        <!-- ROLE TABS -->
        <div class="pm-role-tabs">
            <button class="pm-role-tab active" id="tab-user" onclick="switchRole('user')">
                <i class="fa fa-user"></i> Passager
            </button>
            <button class="pm-role-tab" id="tab-driver" onclick="switchRole('driver')">
                <i class="fa fa-car"></i> Chauffeur
            </button>
        </div>

        <!-- DRIVER NOTE -->
        <div class="pm-driver-note" id="driver-note">
            <i class="fa fa-info-circle"></i>
            Connectez-vous avec votre compte chauffeur. Votre tableau de bord vous donnera accès à vos courses, revenus et statistiques.
        </div>

        <!-- ERRORS -->
        <?php if($errors->any()): ?>
            <div class="pm-error">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form id="pm-login-form" method="POST" action="/login">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="role" id="role-field" value="user">

            <div class="pm-form-group">
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: #1a202c;">E-mail ou Numéro de téléphone</label>
                <input id="email" type="text" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                       placeholder="votre@email.com ou +22501020304"
                       style="width: 100%; padding: 14px 16px; border-radius: 10px; border: 1px solid #cbd5e0; background: #ffffff; color: #1a202c; font-size: 15px; outline: none; transition: border-color 0.2s;">
            </div>

            <div class="pm-form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="pm-form-options">
                <label class="pm-remember">
                    <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                    Se souvenir de moi
                </label>
                <a href="#" class="pm-forgot" id="forgot-link">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="pm-btn-login">
                <i class="fa fa-sign-in"></i>
                <span id="btn-login-text">Se connecter</span>
            </button>
        </form>

        <?php if(Setting::get('social_login', 0) == 1): ?>
        <div class="pm-divider">ou continuer avec</div>
        <div class="pm-social-btns">
            <a href="<?php echo e(url('/auth/facebook')); ?>" class="pm-social-btn">
                <i class="fa fa-facebook"></i> Facebook
            </a>
            <a href="<?php echo e(url('/auth/google')); ?>" class="pm-social-btn">
                <i class="fa fa-google"></i> Google
            </a>
        </div>
        <?php endif; ?>

        <div class="pm-login-footer" id="register-link">
            Pas encore de compte ?
            <a href="<?php echo e(url('/register')); ?>">Créer un compte Passager</a>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function switchRole(role) {
        const form      = document.getElementById('pm-login-form');
        const roleField = document.getElementById('role-field');
        const tabUser   = document.getElementById('tab-user');
        const tabDriver = document.getElementById('tab-driver');
        const note      = document.getElementById('driver-note');
        const btnText   = document.getElementById('btn-login-text');
        const forgotLink = document.getElementById('forgot-link');
        const registerLink = document.getElementById('register-link');

        if (role === 'driver') {
            tabUser.classList.remove('active');
            tabDriver.classList.add('active');
            roleField.value = 'driver';
            form.action = '/provider/login';
            note.style.display = 'block';
            btnText.textContent = 'Se connecter (Chauffeur)';
            forgotLink.href = '<?php echo e(url("/provider/password/reset")); ?>';
            registerLink.innerHTML = 'Vous souhaitez devenir chauffeur ? <a href="<?php echo e(url("/provider/register")); ?>">S\'inscrire comme chauffeur</a>';
        } else {
            tabDriver.classList.remove('active');
            tabUser.classList.add('active');
            roleField.value = 'user';
            form.action = '/login';
            note.style.display = 'none';
            btnText.textContent = 'Se connecter';
            forgotLink.href = '<?php echo e(url("/password/reset")); ?>';
            registerLink.innerHTML = 'Pas encore de compte ? <a href="<?php echo e(url("/register")); ?>">Créer un compte Passager</a>';
        }
    }

    // Auto-detect role from URL query param ?role=driver
    const params = new URLSearchParams(window.location.search);
    if (params.get('role') === 'driver') {
        switchRole('driver');
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('user.layout.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/auth/login.blade.php ENDPATH**/ ?>