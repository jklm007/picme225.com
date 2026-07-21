@extends('user.layout.auth')

@section('content')

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
        width: 500px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 40px 50px;
        position: relative;
        overflow-y: auto;
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

    /* ─── Form ─── */
    .pm-form-group {
        margin-bottom: 18px;
    }

    .pm-form-row {
        display: flex;
        gap: 15px;
    }

    .pm-form-row .pm-form-group {
        flex: 1;
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

    .pm-form-group input, .pm-form-group select {
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

    .pm-form-group select {
        appearance: none;
        cursor: pointer;
    }

    .pm-form-group input:focus, .pm-form-group select:focus {
        border-color: #C9A84C;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
    }

    .pm-form-group input::placeholder { color: #444; }

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
        margin-top: 10px;
    }

    .pm-btn-login:hover {
        background: linear-gradient(135deg, #d9b85c, #b89045);
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(201,168,76,0.35);
    }

    .pm-btn-verify {
        width: 100%;
        background: #e2e8f0;
        border: 1px solid #cbd5e0;
        border-radius: 10px;
        padding: 14px;
        color: #4a5568;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 15px;
    }

    .pm-btn-verify:hover {
        background: #cbd5e0;
        border-color: #a0aec0;
    }

    .pm-login-footer {
        text-align: center;
        color: #555;
        font-size: 13px;
        margin-top: 25px;
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

    .pm-success-msg {
        color: #4CAF50;
        font-size: 13px;
        margin-top: 8px;
        display: none;
    }

    #second_step {
        display: none;
    }

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
        <a href="{{ url('/') }}" class="pm-back-btn">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
        <div class="pm-login-logo">
            <img src="{{ Setting::get('site_logo', asset('logo-black.png')) }}" alt="PicMe225">
            <span>{{ Setting::get('site_title', 'PicMe225') }}</span>
        </div>

        <div class="pm-login-tagline">
            <h1>Créez votre compte <span>PicMe225</span></h1>
            <p>Rejoignez la plateforme de mobilité numéro 1 en Côte d'Ivoire. Rapide, sécurisé et pensé pour vous.</p>
        </div>

        <div class="pm-login-features">
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-car"></i></div>
                <span>Commandez des courses instantanément</span>
            </div>
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-shopping-bag"></i></div>
                <span>Achetez et louez sur notre Marketplace</span>
            </div>
            <div class="pm-feature-item">
                <div class="pm-feature-icon"><i class="fa fa-shield"></i></div>
                <span>Paiement Mobile Money sécurisé</span>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="pm-login-right">
        <div class="pm-login-header">
            <h2>Inscription Passager</h2>
            <p>Vérifiez votre numéro pour commencer</p>
        </div>

        <!-- ERRORS -->
        @if ($errors->any())
            <div class="pm-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- FORM -->
        <form role="form" method="POST" action="{{ url('/register') }}">
            {{ csrf_field() }}

            <!-- STEP 1: PHONE VERIFICATION -->
            <div id="first_step">
                <div class="pm-form-row">
                    <div class="pm-form-group" style="flex: 0.4;">
                        <label>Indicatif</label>
                        <select name="country_code" id="country_code">
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
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="0102030405" required autofocus>
                    </div>
                </div>

                <div id="recaptcha-container" style="margin-top: 10px;"></div>
                <div id="mobile_verfication" class="pm-success-msg"></div>

                <button type="button" class="pm-btn-verify" id="btn-verify" onclick="onSignInSubmit();">
                    Vérifier le numéro
                </button>
            </div>

            <!-- STEP 2: USER DETAILS -->
            <div id="second_step">
                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label>Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Jean" required>
                    </div>
                    <div class="pm-form-group">
                        <label>Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Kouadio" required>
                    </div>
                </div>

                <div class="pm-form-group">
                    <label>Adresse E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="jean.kouadio@email.com" required>
                </div>

                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="pm-form-group">
                        <label>Confirmer</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="pm-form-group">
                    <label>Sexe</label>
                    <select name="gender" required>
                        <option value="MALE">Homme</option>
                        <option value="FEMALE">Femme</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <button type="submit" class="pm-btn-login">
                    <i class="fa fa-check-circle"></i> Créer mon compte
                </button>
            </div>
        </form>

        <div class="pm-login-footer">
            Déjà un compte ?
            <a href="{{ url('/login') }}">Se connecter</a><br><br>
            Vous souhaitez devenir chauffeur ? <a href="{{ url('/drive') }}">En savoir plus</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-auth.js"></script>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
  var firebaseConfig = {
    apiKey: "AIzaSyAwsy0qo-17ONTGZcWz5ptvE9rsF0w_4X4",
    authDomain: "picme-driver.firebaseapp.com",
    databaseURL: "https://picme-driver.firebaseio.com",
    projectId: "picme-driver",
    storageBucket: "picme-driver.appspot.com",
    messagingSenderId: "753575568169",
    appId: "1:753575568169:web:3f8bf626edf28b669e4d38",
    measurementId: "G-HHETCDG6XL"
  };
  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);

  window.onload = function() {
    renderReCaptcha();
  };

  function renderReCaptcha() {
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
      'size': 'normal',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
      }
    });
    recaptchaVerifier.render();
  }

  function validatePhoneNumber(phoneNumber) {
    const regex = /^\+\d{1,3}\d{8,14}$/; // Format international
    return regex.test(phoneNumber);
  }

  function onSignInSubmit() {
    var countryCode = document.getElementById('country_code').value;
    var phoneNumber = document.getElementById('phone_number').value;
    var fullPhoneNumber = countryCode + phoneNumber;

    if (!validatePhoneNumber(fullPhoneNumber)) {
        alert("Veuillez entrer un numéro de téléphone valide.");
        return;
    }

    var appVerifier = window.recaptchaVerifier;

    firebase.auth().signInWithPhoneNumber(fullPhoneNumber, appVerifier)
      .then(function(confirmationResult) {
        window.confirmationResult = confirmationResult;
        var code = window.prompt("Entrez le code de vérification reçu par SMS :");
        return confirmationResult.confirm(code);
      })
      .then(function(result) {
        var msg = document.getElementById('mobile_verfication');
        msg.innerHTML = '<i class="fa fa-check"></i> Numéro vérifié avec succès !';
        msg.style.display = 'block';
        
        document.getElementById('phone_number').setAttribute('readonly', true);
        document.getElementById('country_code').setAttribute('disabled', true);
        document.getElementById('btn-verify').style.display = 'none';
        document.getElementById('recaptcha-container').style.display = 'none';
        
        document.getElementById('second_step').style.display = 'block';
        
        // Ensure disabled field value is still submitted
        var hiddenCountry = document.createElement("input");
        hiddenCountry.setAttribute("type", "hidden");
        hiddenCountry.setAttribute("name", "country_code");
        hiddenCountry.setAttribute("value", countryCode);
        document.forms[0].appendChild(hiddenCountry);
      })
      .catch(function(error) {
        console.error('Error during signInWithPhoneNumber:', error);
        var msg = document.getElementById('mobile_verfication');
        msg.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Erreur : ' + error.message;
        msg.style.display = 'block';
        msg.style.color = '#f17b84';
      });
  }
</script>
@endsection
