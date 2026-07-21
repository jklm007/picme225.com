@extends('user.layout.auth')

@section('title', 'Mot de passe oublié - ')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', sans-serif;
        background: #0A1628;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pm-login-wrapper { display: flex; width: 100%; min-height: 100vh; }

    /* LEFT */
    .pm-login-left {
        flex: 1;
        background: linear-gradient(135deg, #0A1628 0%, #0f2040 100%);
        display: flex; flex-direction: column; justify-content: center;
        padding: 60px; position: relative; overflow: hidden;
    }
    .pm-login-left::before {
        content: ''; position: absolute; top: -100px; left: -100px;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(201,168,76,0.15) 0%, transparent 70%);
        pointer-events: none;
    }
    .pm-login-left::after {
        content: ''; position: absolute; bottom: -80px; right: -80px;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .pm-back-btn {
        position: absolute; top: 30px; left: 30px;
        color: rgba(255,255,255,0.7); text-decoration: none;
        display: flex; align-items: center; gap: 8px;
        font-size: 14px; font-weight: 500; transition: all 0.3s ease;
        z-index: 10; background: rgba(255,255,255,0.05);
        padding: 8px 16px; border-radius: 30px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .pm-back-btn:hover { color: #C9A84C; background: rgba(201,168,76,0.1); border-color: rgba(201,168,76,0.3); transform: translateX(-3px); }
    .pm-login-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 60px; }
    .pm-login-logo img { height: 48px; filter: brightness(0) invert(1); }
    .pm-login-logo span { color: #C9A84C; font-size: 22px; font-weight: 700; letter-spacing: 1px; }
    .pm-login-tagline h1 { color: #fff; font-size: 38px; font-weight: 700; line-height: 1.2; margin-bottom: 20px; }
    .pm-login-tagline h1 span { color: #C9A84C; }
    .pm-login-tagline p { color: #888; font-size: 16px; line-height: 1.7; max-width: 380px; }

    /* RIGHT */
    .pm-login-right {
        width: 500px; background: #fff;
        display: flex; flex-direction: column; justify-content: center;
        padding: 40px 50px; position: relative; overflow-y: auto;
    }
    .pm-login-header { margin-bottom: 28px; }
    .pm-login-header h2 { color: #1A202C; font-size: 26px; font-weight: 700; margin-bottom: 6px; }
    .pm-login-header p { color: #666; font-size: 14px; }

    /* TOGGLE EMAIL/SMS */
    .pm-method-toggle { display: flex; gap: 0; margin-bottom: 24px; border: 1px solid #cbd5e0; border-radius: 10px; overflow: hidden; }
    .pm-method-btn {
        flex: 1; padding: 12px; font-family: 'Inter', sans-serif;
        font-size: 13px; font-weight: 600; cursor: pointer; border: none;
        background: #f7fafc; color: #666; transition: all 0.2s;
    }
    .pm-method-btn.active { background: #0A1628; color: #C9A84C; }
    .pm-method-btn i { margin-right: 6px; }

    /* FORM */
    .pm-form-group { margin-bottom: 18px; }
    .pm-form-row { display: flex; gap: 15px; }
    .pm-form-row .pm-form-group { flex: 1; }
    .pm-form-group label {
        display: block; color: #888; font-size: 12px; font-weight: 500;
        margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .pm-form-group input, .pm-form-group select {
        width: 100%; background: #fff; border: 1px solid #cbd5e0;
        border-radius: 10px; padding: 13px 16px; color: #1A202C;
        font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
    }
    .pm-form-group select { appearance: none; cursor: pointer; }
    .pm-form-group input:focus, .pm-form-group select:focus {
        border-color: #C9A84C; box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
    }
    .pm-form-group input::placeholder { color: #aaa; }
    .pm-btn-primary {
        width: 100%; background: linear-gradient(135deg, #C9A84C, #a88035);
        border: none; border-radius: 10px; padding: 14px; color: #fff;
        font-size: 15px; font-weight: 700; cursor: pointer;
        font-family: 'Inter', sans-serif; transition: all 0.2s; letter-spacing: 0.5px;
        display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px;
    }
    .pm-btn-primary:hover { background: linear-gradient(135deg, #d9b85c, #b89045); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(201,168,76,0.35); }
    .pm-btn-secondary {
        width: 100%; background: #edf2f7; border: 1px solid #cbd5e0; border-radius: 10px;
        padding: 14px; color: #4a5568; font-size: 15px; font-weight: 600; cursor: pointer;
        transition: all 0.2s; margin-top: 10px; font-family: 'Inter', sans-serif;
    }
    .pm-btn-secondary:hover { background: #e2e8f0; }

    /* STEPS */
    .pm-step { display: none; }
    .pm-step.active { display: block; }

    /* STATUS */
    .pm-alert { border-radius: 8px; font-size: 13px; padding: 10px 14px; margin-bottom: 18px; }
    .pm-alert-error { background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.25); color: #dc3545; }
    .pm-alert-success { background: rgba(46,204,113,0.08); border: 1px solid rgba(46,204,113,0.25); color: #27ae60; }
    .pm-alert-info { background: rgba(52,152,219,0.08); border: 1px solid rgba(52,152,219,0.25); color: #2980b9; }

    #sms-msg, #email-status { display: none; margin-bottom: 12px; }

    /* SEPARATOR */
    .pm-divider { border: none; border-top: 1px solid #edf2f7; margin: 20px 0; }
    .pm-login-footer { text-align: center; color: #555; font-size: 13px; margin-top: 20px; }
    .pm-login-footer a { color: #C9A84C; text-decoration: none; font-weight: 600; }
    .pm-login-footer a:hover { color: #e0b85c; }

    /* OTP INPUT */
    .pm-otp-input {
        width: 100%; background: #fff; border: 2px solid #C9A84C;
        border-radius: 10px; padding: 14px 16px; color: #1A202C;
        font-size: 22px; font-weight: 700; font-family: 'Inter', sans-serif;
        text-align: center; letter-spacing: 10px; outline: none; transition: all 0.2s;
    }
    .pm-otp-input:focus { box-shadow: 0 0 0 3px rgba(201,168,76,0.2); }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .pm-login-left { display: none; }
        .pm-login-right { width: 100%; padding: 40px 24px; }
    }
</style>

<div class="pm-login-wrapper">

    {{-- LEFT PANEL --}}
    <div class="pm-login-left">
        <a href="{{ url('/login') }}" class="pm-back-btn">
            <i class="fa fa-arrow-left"></i> Connexion
        </a>
        <div class="pm-login-logo">
            <img src="{{ Setting::get('site_logo', asset('logo-black.png')) }}" alt="PicMe225">
            <span>{{ Setting::get('site_title', 'PicMe225') }}</span>
        </div>
        <div class="pm-login-tagline">
            <h1>Réinitialisez votre <span>mot de passe</span></h1>
            <p>Pas de panique ! Récupérez votre accès en quelques secondes via Email ou SMS.</p>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="pm-login-right">
        <div class="pm-login-header">
            <h2>Mot de passe oublié</h2>
            <p>Choisissez votre méthode de récupération</p>
        </div>

        {{-- ERRORS --}}
        @if ($errors->any())
        <div class="pm-alert pm-alert-error">
            @foreach ($errors->all() as $error)
                <div><i class="fa fa-exclamation-circle"></i> {{ $error }}</div>
            @endforeach
        </div>
        @endif

        @if (session('status'))
        <div class="pm-alert pm-alert-success">
            <i class="fa fa-check-circle"></i> {{ session('status') }}
        </div>
        @endif

        @if (session('flash_success'))
        <div class="pm-alert pm-alert-success">
            <i class="fa fa-check-circle"></i> {{ session('flash_success') }}
        </div>
        @endif

        {{-- METHOD TOGGLE --}}
        <div class="pm-method-toggle">
            <button class="pm-method-btn active" id="btn-email-method" onclick="switchMethod('email')">
                <i class="fa fa-envelope"></i> Par Email
            </button>
            <button class="pm-method-btn" id="btn-sms-method" onclick="switchMethod('sms')">
                <i class="fa fa-mobile"></i> Par SMS
            </button>
        </div>

        {{-- ============================================================
             METHOD 1 : EMAIL
             ============================================================ --}}
        <div class="pm-step active" id="step-email">
            <form method="POST" action="{{ url('/password/email') }}">
                {{ csrf_field() }}
                <div class="pm-form-group">
                    <label>Adresse Email</label>
                    <input type="email" name="email" placeholder="votre@email.com" value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="pm-btn-primary">
                    <i class="fa fa-paper-plane"></i> Envoyer le lien de réinitialisation
                </button>
            </form>
        </div>

        {{-- ============================================================
             METHOD 2 : SMS (Firebase)
             ============================================================ --}}
        <div class="pm-step" id="step-sms">

            {{-- SUB STEP 1: Phone + OTP --}}
            <div class="pm-step active" id="sms-step-1">
                <div id="sms-msg"></div>

                <div class="pm-form-row">
                    <div class="pm-form-group" style="flex: 0.4;">
                        <label>Indicatif</label>
                        <select id="sms_country_code">
                            <option value="+225">+225 (CI)</option>
                            <option value="+33">+33 (FR)</option>
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+221">+221 (SN)</option>
                            <option value="+223">+223 (ML)</option>
                            <option value="+226">+226 (BF)</option>
                            <option value="+228">+228 (TG)</option>
                            <option value="+229">+229 (BJ)</option>
                            <option value="+233">+233 (GH)</option>
                            <option value="+234">+234 (NG)</option>
                            <option value="+237">+237 (CM)</option>
                        </select>
                    </div>
                    <div class="pm-form-group" style="flex: 1;">
                        <label>Numéro de téléphone</label>
                        <input type="tel" id="sms_phone" placeholder="0102030405">
                    </div>
                </div>

                <div id="recaptcha-container" style="margin: 12px 0;"></div>

                <button type="button" class="pm-btn-primary" id="btn-send-sms" onclick="sendSmsOtp()">
                    <i class="fa fa-mobile"></i> Envoyer le code SMS
                </button>
            </div>

            {{-- SUB STEP 2: OTP Input --}}
            <div class="pm-step" id="sms-step-2">
                <div class="pm-alert pm-alert-info" style="display:block; margin-bottom:16px;">
                    <i class="fa fa-info-circle"></i> Code OTP envoyé par SMS. Entrez-le ci-dessous.
                </div>
                <div class="pm-form-group">
                    <label>Code OTP (6 chiffres)</label>
                    <input type="text" class="pm-otp-input" id="sms_otp_code" maxlength="6" placeholder="••••••">
                </div>
                <button type="button" class="pm-btn-primary" onclick="verifySmsOtp()">
                    <i class="fa fa-check"></i> Vérifier le code
                </button>
                <button type="button" class="pm-btn-secondary" onclick="goBackSmsStep1()">
                    <i class="fa fa-redo"></i> Renvoyer le code
                </button>
            </div>

            {{-- SUB STEP 3: New Password --}}
            <div class="pm-step" id="sms-step-3">
                <div class="pm-alert pm-alert-success" style="display:block; margin-bottom:16px;">
                    <i class="fa fa-check-circle"></i> Numéro vérifié ! Choisissez un nouveau mot de passe.
                </div>
                <form method="POST" action="{{ url('/password/reset/otp') }}" id="reset-password-form">
                    {{ csrf_field() }}
                    <input type="hidden" name="phone_number" id="hidden_phone">
                    <input type="hidden" name="country_code" id="hidden_country">
                    <div class="pm-form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="••••••••" minlength="6" required>
                    </div>
                    <div class="pm-form-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" minlength="6" required>
                    </div>
                    <button type="submit" class="pm-btn-primary">
                        <i class="fa fa-lock"></i> Réinitialiser le mot de passe
                    </button>
                </form>
            </div>
        </div>

        <hr class="pm-divider">
        <div class="pm-login-footer">
            Retour à la <a href="{{ url('/login') }}">connexion</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- Firebase SDK --}}
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-auth.js"></script>

<script>
    /* ======================================================
       Firebase Config (projet picme-driver)
       ====================================================== */
    var firebaseConfig = {
        apiKey:            "AIzaSyAwsy0qo-17ONTGZcWz5ptvE9rsF0w_4X4",
        authDomain:        "picme-driver.firebaseapp.com",
        databaseURL:       "https://picme-driver.firebaseio.com",
        projectId:         "picme-driver",
        storageBucket:     "picme-driver.appspot.com",
        messagingSenderId: "753575568169",
        appId:             "1:753575568169:web:3f8bf626edf28b669e4d38",
        measurementId:     "G-HHETCDG6XL"
    };
    firebase.initializeApp(firebaseConfig);

    /* ======================================================
       Method Toggle
       ====================================================== */
    function switchMethod(method) {
        document.getElementById('step-email').classList.remove('active');
        document.getElementById('step-sms').classList.remove('active');
        document.getElementById('btn-email-method').classList.remove('active');
        document.getElementById('btn-sms-method').classList.remove('active');

        document.getElementById('step-' + method).classList.add('active');
        document.getElementById('btn-' + method + '-method').classList.add('active');

        if (method === 'sms' && !window.recaptchaRendered) {
            renderRecaptcha();
        }
    }

    /* ======================================================
       Sub-step navigation (SMS)
       ====================================================== */
    function showSmsStep(n) {
        ['sms-step-1','sms-step-2','sms-step-3'].forEach(function(id, i) {
            document.getElementById(id).classList.toggle('active', i + 1 === n);
        });
    }

    function goBackSmsStep1() {
        showSmsStep(1);
        if (!window.recaptchaRendered) renderRecaptcha();
    }

    /* ======================================================
       reCAPTCHA
       ====================================================== */
    window.recaptchaRendered = false;
    function renderRecaptcha() {
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'normal',
            'callback': function(response) { /* solved */ }
        });
        recaptchaVerifier.render().then(function() {
            window.recaptchaRendered = true;
        });
    }

    /* ======================================================
       SEND OTP
       ====================================================== */
    function showSmsMsg(msg, type) {
        var el = document.getElementById('sms-msg');
        el.className = 'pm-alert pm-alert-' + type;
        el.innerHTML = msg;
        el.style.display = 'block';
    }

    function sendSmsOtp() {
        var code  = document.getElementById('sms_country_code').value;
        var phone = document.getElementById('sms_phone').value.trim();
        var full  = code + phone;

        if (!/^\+\d{9,15}$/.test(full)) {
            showSmsMsg('<i class="fa fa-exclamation-circle"></i> Numéro invalide. Exemple : 0102030405', 'error');
            return;
        }

        document.getElementById('btn-send-sms').disabled = true;
        document.getElementById('btn-send-sms').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';

        firebase.auth().signInWithPhoneNumber(full, window.recaptchaVerifier)
            .then(function(confirmationResult) {
                window.confirmationResult = confirmationResult;
                document.getElementById('hidden_phone').value   = phone;
                document.getElementById('hidden_country').value = code;
                showSmsStep(2);
            })
            .catch(function(error) {
                document.getElementById('btn-send-sms').disabled = false;
                document.getElementById('btn-send-sms').innerHTML = '<i class="fa fa-mobile"></i> Envoyer le code SMS';
                showSmsMsg('<i class="fa fa-exclamation-circle"></i> Erreur : ' + error.message, 'error');
            });
    }

    /* ======================================================
       VERIFY OTP
       ====================================================== */
    function verifySmsOtp() {
        var code = document.getElementById('sms_otp_code').value.trim();
        if (code.length !== 6) {
            alert('Le code doit contenir 6 chiffres.');
            return;
        }

        window.confirmationResult.confirm(code)
            .then(function(result) {
                showSmsStep(3);
            })
            .catch(function(error) {
                var el = document.getElementById('sms-step-2').querySelector('.pm-alert-info');
                if (el) {
                    el.className = 'pm-alert pm-alert-error';
                    el.innerHTML = '<i class="fa fa-times-circle"></i> Code incorrect. Réessayez.';
                }
            });
    }
</script>
@endsection
