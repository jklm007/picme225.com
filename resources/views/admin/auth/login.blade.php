@extends('admin.layout.auth')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #0d1226;
    font-family: 'Inter', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Animated background ── */
.pm-login-bg {
    position: fixed;
    inset: 0;
    background: #0d1226;
    overflow: hidden;
    z-index: 0;
}
.pm-login-bg::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(ellipse, rgba(201,168,76,0.12) 0%, transparent 70%);
    top: -150px; right: -100px;
    pointer-events: none;
}
.pm-login-bg::after {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(ellipse, rgba(10,60,120,0.2) 0%, transparent 70%);
    bottom: -100px; left: -100px;
    pointer-events: none;
}

/* ── Wrapper ── */
.pm-login-wrapper {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 480px;
    padding: 20px 16px;
    margin: 0 auto;
}

/* ── Logo ── */
.pm-login-logo {
    text-align: center;
    margin-bottom: 32px;
}
.pm-login-logo img {
    height: 64px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(201,168,76,0.3));
}
.pm-login-logo .pm-brand-name {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    margin-top: 12px;
    letter-spacing: 0.5px;
}
.pm-login-logo .pm-brand-sub {
    display: block;
    font-size: 12px;
    color: #C9A84C;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 4px;
}

/* ── Card ── */
.pm-login-card {
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 40px 80px rgba(0,0,0,0.4);
}
.pm-login-card::before {
    content: '';
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #C9A84C, #ecc94b, #C9A84C);
    background-size: 200% 100%;
    animation: pm-gold-shine 3s linear infinite;
}
@keyframes pm-gold-shine {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* ── Tabs ── */
.pm-tabs {
    display: flex;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    overflow-x: auto;
    scrollbar-width: none;
}
.pm-tabs::-webkit-scrollbar { display: none; }
.pm-tab {
    flex: 1;
    min-width: 90px;
    padding: 16px 12px;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    color: #718096;
    cursor: pointer;
    border: none;
    background: transparent;
    border-bottom: 2px solid transparent;
    transition: all .25s;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.pm-tab:hover { color: #a0aec0; }
.pm-tab.active {
    color: #C9A84C;
    border-bottom-color: #C9A84C;
}
.pm-tab .pm-tab-icon {
    display: block;
    font-size: 18px;
    margin-bottom: 4px;
}

/* ── Tab content ── */
.pm-tab-content { padding: 36px 32px 32px; }
.pm-tab-pane { display: none; }
.pm-tab-pane.active { display: block; }

/* ── Form title ── */
.pm-form-title {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 6px;
    font-family: 'Playfair Display', serif;
}
.pm-form-subtitle {
    font-size: 13px;
    color: #718096;
    margin-bottom: 28px;
}

/* ── Fields ── */
.pm-field {
    margin-bottom: 20px;
}
.pm-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #a0aec0;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.pm-field-wrap {
    position: relative;
}
.pm-field-wrap i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #4a5568;
    font-size: 15px;
    pointer-events: none;
    transition: color .2s;
}
.pm-field input {
    width: 100%;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 14px 16px 14px 44px;
    color: #ffffff;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    outline: none;
    transition: all .2s;
}
.pm-field input::placeholder { color: #4a5568; }
.pm-field input:focus {
    background: rgba(255,255,255,0.09);
    border-color: rgba(201,168,76,0.5);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
}
.pm-field input:focus + i,
.pm-field-wrap:focus-within i { color: #C9A84C; }
.help-block.text-danger {
    display: block;
    font-size: 12px;
    color: #fc8181;
    margin-top: 6px;
    padding-left: 4px;
}

/* ── Submit button ── */
.pm-submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px 24px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #C9A84C, #b8943e);
    color: #0d1226;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: all .3s ease;
    letter-spacing: 0.5px;
    margin-top: 8px;
    box-shadow: 0 8px 20px rgba(201,168,76,0.25);
}
.pm-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(201,168,76,0.4);
    background: linear-gradient(135deg, #d4b355, #C9A84C);
}
.pm-submit-btn:active { transform: translateY(0); }

/* ── Footer link ── */
.pm-forgot {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
}
.pm-forgot a {
    color: #718096;
    text-decoration: none;
    transition: color .2s;
}
.pm-forgot a:hover { color: #C9A84C; }

/* ── Bottom badge ── */
.pm-login-footer {
    text-align: center;
    margin-top: 28px;
    font-size: 12px;
    color: #4a5568;
}
.pm-login-footer span { color: #C9A84C; }

/* ── Flash messages ── */
.pm-flash {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pm-flash-error {
    background: rgba(252,129,129,0.1);
    border: 1px solid rgba(252,129,129,0.3);
    color: #fc8181;
}
.pm-flash-success {
    background: rgba(72,187,120,0.1);
    border: 1px solid rgba(72,187,120,0.3);
    color: #68d391;
}

@media (max-width: 480px) {
    .pm-tab-content { padding: 28px 20px 24px; }
    .pm-tab { padding: 14px 8px; font-size: 11px; min-width: 70px; }
}
</style>

<!-- Animated background -->
<div class="pm-login-bg"></div>

<div class="pm-login-wrapper">
    <!-- Logo -->
    <div class="pm-login-logo">
        @if(Setting::get('site_logo'))
            <img src="{{ img(Setting::get('site_logo')) }}" alt="{{ Setting::get('site_title', 'PicMe225') }}">
        @else
            <img src="{{ asset('logo.png') }}" alt="PicMe225">
        @endif
        <span class="pm-brand-name">{{ Setting::get('site_title', 'PicMe225') }}</span>
        <span class="pm-brand-sub">Portail Administration</span>
    </div>

    <!-- Card -->
    <div class="pm-login-card">

        <!-- Tabs -->
        <div class="pm-tabs" id="pm-tabs">
            <button class="pm-tab @if(!$errors->has('login_type')) active @endif"
                    data-pane="admin" onclick="switchTab(this, 'admin')">
                <span class="pm-tab-icon">🛡️</span>Admin
            </button>
            <button class="pm-tab @if($errors->has('login_type') && $errors->first('login_type') == 'dispatcher') active @endif"
                    data-pane="dispatcher" onclick="switchTab(this, 'dispatcher')">
                <span class="pm-tab-icon">📡</span>Dispatcher
            </button>
            <button class="pm-tab @if($errors->has('login_type') && $errors->first('login_type') == 'fleet') active @endif"
                    data-pane="fleet" onclick="switchTab(this, 'fleet')">
                <span class="pm-tab-icon">🚗</span>Flotte
            </button>
            <button class="pm-tab @if($errors->has('login_type') && $errors->first('login_type') == 'account') active @endif"
                    data-pane="account" onclick="switchTab(this, 'account')">
                <span class="pm-tab-icon">💰</span>Compte
            </button>
        </div>

        <div class="pm-tab-content">

            <!-- Flash messages -->
            @if(session('flash_success'))
                <div class="pm-flash pm-flash-success">
                    <i class="fa fa-check-circle"></i> {{ session('flash_success') }}
                </div>
            @endif
            @if(session('flash_error') || $errors->has('login'))
                <div class="pm-flash pm-flash-error">
                    <i class="fa fa-exclamation-circle"></i>
                    {{ session('flash_error') ?? $errors->first('login') ?? 'Identifiants incorrects.' }}
                </div>
            @endif

            <!-- ── ADMIN ── -->
            <div class="pm-tab-pane @if(!$errors->has('login_type')) active @endif" id="pane-admin">
                <div class="pm-form-title">Connexion Admin</div>
                <div class="pm-form-subtitle">Accès réservé à l'administrateur principal</div>
                <form method="POST" action="{{ url('/admin/login') }}">
                    {{ csrf_field() }}
                    <div class="pm-field">
                        <label>Adresse e-mail</label>
                        <div class="pm-field-wrap">
                            <input type="email" name="email" placeholder="admin@picme225.site" required autocomplete="email">
                            <i class="fa fa-envelope"></i>
                        </div>
                        @if($errors->has('email'))
                            <span class="help-block text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="pm-field">
                        <label>Mot de passe</label>
                        <div class="pm-field-wrap">
                            <input type="password" name="password" placeholder="••••••••••" required autocomplete="current-password">
                            <i class="fa fa-lock"></i>
                        </div>
                        @if($errors->has('password'))
                            <span class="help-block text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="pm-submit-btn">
                        <i class="fa fa-sign-in"></i> Se connecter
                    </button>
                </form>
                <div class="pm-forgot">
                    <a href="{{ url('/admin/password/reset') }}">Mot de passe oublié ?</a>
                </div>
            </div>

            <!-- ── DISPATCHER ── -->
            <div class="pm-tab-pane @if($errors->has('login_type') && $errors->first('login_type') == 'dispatcher') active @endif" id="pane-dispatcher">
                <div class="pm-form-title">Connexion Dispatcher</div>
                <div class="pm-form-subtitle">Gestion des répartitions et affectations</div>
                <form method="POST" action="{{ url('/dispatcher/login') }}">
                    {{ csrf_field() }}
                    <div class="pm-field">
                        <label>Adresse e-mail</label>
                        <div class="pm-field-wrap">
                            <input type="email" name="email" placeholder="dispatcher@picme225.site" required autocomplete="email">
                            <i class="fa fa-envelope"></i>
                        </div>
                    </div>
                    <div class="pm-field">
                        <label>Mot de passe</label>
                        <div class="pm-field-wrap">
                            <input type="password" name="password" placeholder="••••••••••" required autocomplete="current-password">
                            <i class="fa fa-lock"></i>
                        </div>
                    </div>
                    <button type="submit" class="pm-submit-btn">
                        <i class="fa fa-sign-in"></i> Se connecter
                    </button>
                </form>
            </div>

            <!-- ── FLEET ── -->
            <div class="pm-tab-pane @if($errors->has('login_type') && $errors->first('login_type') == 'fleet') active @endif" id="pane-fleet">
                <div class="pm-form-title">Connexion Flotte</div>
                <div class="pm-form-subtitle">Gestion de la flotte de véhicules</div>
                <form method="POST" action="{{ url('/fleet/login') }}">
                    {{ csrf_field() }}
                    <div class="pm-field">
                        <label>Adresse e-mail</label>
                        <div class="pm-field-wrap">
                            <input type="email" name="email" placeholder="fleet@picme225.site" required autocomplete="email">
                            <i class="fa fa-envelope"></i>
                        </div>
                    </div>
                    <div class="pm-field">
                        <label>Mot de passe</label>
                        <div class="pm-field-wrap">
                            <input type="password" name="password" placeholder="••••••••••" required autocomplete="current-password">
                            <i class="fa fa-lock"></i>
                        </div>
                    </div>
                    <button type="submit" class="pm-submit-btn">
                        <i class="fa fa-sign-in"></i> Se connecter
                    </button>
                </form>
            </div>

            <!-- ── ACCOUNT ── -->
            <div class="pm-tab-pane @if($errors->has('login_type') && $errors->first('login_type') == 'account') active @endif" id="pane-account">
                <div class="pm-form-title">Connexion Comptabilité</div>
                <div class="pm-form-subtitle">Gestion des comptes financiers et paiements</div>
                <form method="POST" action="{{ url('/account/login') }}">
                    {{ csrf_field() }}
                    <div class="pm-field">
                        <label>Adresse e-mail</label>
                        <div class="pm-field-wrap">
                            <input type="email" name="email" placeholder="compte@picme225.site" required autocomplete="email">
                            <i class="fa fa-envelope"></i>
                        </div>
                    </div>
                    <div class="pm-field">
                        <label>Mot de passe</label>
                        <div class="pm-field-wrap">
                            <input type="password" name="password" placeholder="••••••••••" required autocomplete="current-password">
                            <i class="fa fa-lock"></i>
                        </div>
                    </div>
                    <button type="submit" class="pm-submit-btn">
                        <i class="fa fa-sign-in"></i> Se connecter
                    </button>
                </form>
            </div>

        </div><!-- /.pm-tab-content -->
    </div><!-- /.pm-login-card -->

    <div class="pm-login-footer">
        <i class="fa fa-lock" style="margin-right:5px;"></i>
        Connexion sécurisée &mdash; <span>PicMe225</span> &copy; {{ date('Y') }}
    </div>
</div>

<script>
function switchTab(btn, pane) {
    // Update tab buttons
    document.querySelectorAll('.pm-tab').forEach(function(t) {
        t.classList.remove('active');
    });
    btn.classList.add('active');

    // Update panes
    document.querySelectorAll('.pm-tab-pane').forEach(function(p) {
        p.classList.remove('active');
    });
    var target = document.getElementById('pane-' + pane);
    if (target) target.classList.add('active');
}
</script>
@endsection
