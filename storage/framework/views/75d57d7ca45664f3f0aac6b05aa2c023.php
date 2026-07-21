<?php $__env->startSection('content'); ?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600;700;800&display=swap');
*, *::before, *::after { box-sizing: border-box; }

.pm-airport {
    font-family: 'Inter', system-ui, sans-serif;
    background: #0d1226;
    color: #e2e8f0;
    min-height: 100vh;
}

.pm-air-hero {
    min-height: 80px;
}

.pm-air-body {
    padding: 40px 0 80px;
    position: relative;
    z-index: 10;
}

/* Les cartes deviennent blanches et se détachent joliment de l'image de fond */
.pm-form-card {
    background: #ffffff;
    border: 2px solid #C9A84C;
    border-radius: 24px;
    padding: 48px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}
.pm-form-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #C9A84C, #ecc94b);
    border-radius: 24px 24px 0 0;
}
.pm-form-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 12px;
}
.pm-form-card > p {
    color: #4a5568;
    font-size: 15px;
    margin-bottom: 36px;
}

/* Trip type switcher */
.pm-trip-type {
    display: flex;
    background: rgba(0,0,0,0.03);
    border-radius: 12px;
    padding: 6px;
    margin-bottom: 36px;
    border: 1px solid rgba(0,0,0,0.05);
}
.pm-trip-btn {
    flex: 1;
    padding: 14px 16px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #718096;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all .3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.pm-trip-btn.active {
    background: #ffffff;
    color: #1a202c;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Form fields */
.pm-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
.pm-form-row.full { grid-template-columns: 1fr; }
.pm-form-row.three { grid-template-columns: 1fr 1fr 1fr; }

.pm-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.pm-field label {
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.pm-field input,
.pm-field select,
.pm-field textarea {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    color: #2d3748;
    font-size: 15px;
    outline: none;
    transition: all .2s;
    font-family: 'Inter', sans-serif;
    width: 100%;
}
.pm-field input::placeholder,
.pm-field textarea::placeholder { color: #a0aec0; }
.pm-field input:focus,
.pm-field select:focus,
.pm-field textarea:focus {
    border-color: #b7791f;
    box-shadow: 0 0 0 3px rgba(183, 121, 31, 0.15);
}
.pm-field select option { background: #fff; color: #2d3748; }
.pm-field textarea { resize: vertical; min-height: 90px; }

.pm-vehicle-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}
.pm-vehicle-option {
    background: #ffffff;
    border: 2px solid #edf2f7;
    border-radius: 16px;
    padding: 20px 12px;
    text-align: center;
    cursor: pointer;
    transition: all .3s;
    position: relative;
}
.pm-vehicle-option:hover { 
    border-color: #cbd5e0; 
    transform: translateY(-2px);
}
.pm-vehicle-option.selected {
    border-color: #C9A84C;
    background: #fffff0;
}
.pm-vehicle-option.selected::after {
    content: '✓';
    position: absolute;
    top: 10px; right: 12px;
    font-size: 14px;
    font-weight: 800;
    color: #C9A84C;
}
.pm-vehicle-option .emoji { font-size: 32px; margin-bottom: 12px; display: block; }
.pm-vehicle-option strong { display: block; font-size: 14px; font-weight: 700; color: #1a202c; }
.pm-vehicle-option span { font-size: 12px; color: #718096; }

/* Submit button */
.pm-wa-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    width: 100%;
    padding: 20px 32px;
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, #2E7D32, #1B5E20);
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all .3s ease;
    letter-spacing: .5px;
    margin-top: 12px;
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.25);
}
.pm-wa-btn:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 15px 25px rgba(46, 125, 50, 0.35); 
}
.pm-wa-btn i { font-size: 26px; }
.pm-wa-btn-sub { font-size: 13px; opacity: .85; font-weight: 400; display:block; text-align:left;}

.pm-form-note {
    text-align: center;
    font-size: 13px;
    color: #4a5568;
    margin-top: 20px;
}

/* ─── INFO CARDS (Pricing) ───────────────────────────────── */
.pm-info-card {
    background: #ffffff;
    border: 2px solid #C9A84C;
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 24px;
    transition: all .3s;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.pm-info-card:hover { 
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    border-color: #ecc94b;
}
.pm-info-card h4 {
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #1a202c;
    font-family: 'Playfair Display', serif;
}
.pm-info-card h4 .price { 
    font-size: 22px; 
    color: #C9A84C; 
    font-weight: 800; 
    font-family: 'Inter', sans-serif;
}
.pm-feature-list { margin-top: 20px; font-size: 14px; color: #4a5568; }
.pm-feature-list div { display: flex; align-items: center; margin-bottom: 12px; }
.pm-feature-list div i { margin-right: 12px; width: 18px; text-align: center; color: #C9A84C; }

/* ─── ERROR ─────────────────────────────────────────────── */
.pm-field-error { display: none; font-size: 12px; color: #e53e3e; margin-top: 6px; font-weight: 500;}
.pm-field.has-error input,
.pm-field.has-error select { border-color: #fc8181; background: #fff5f5; }
.pm-field.has-error .pm-field-error { display: block; }

@media (max-width: 768px) {
    .pm-form-row { grid-template-columns: 1fr; gap: 16px; }
    .pm-form-row.three { grid-template-columns: 1fr; }
    .pm-vehicle-grid { grid-template-columns: 1fr; }
    .pm-form-card { padding: 32px 24px; }
}
</style>

<div class="pm-airport">

    <!-- Espace vide pour laisser respirer l'image -->
    <section class="pm-air-hero"></section>

    <!-- ═══ FORM + INFO ═══ -->
    <section class="pm-air-body">
        <div class="container">
            <div class="row">
                
                <!-- ── Info column (Pricing) ── -->
                <div class="col-md-5">
                    <div style="padding-right: 20px; margin-bottom: 40px;">
                        <div style="font-size:14px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#C9A84C;margin-bottom:24px;">Nos Formules À partir de</div>

                        <div class="pm-info-card">
                            <h4>Hôtel ➔ Aéroport <span class="price">15 000 FCFA</span></h4>
                            <div class="pm-feature-list">
                                <div><i class="fa fa-clock-o"></i> Privatisation 1h30 (Marge généreuse)</div>
                                <div><i class="fa fa-map-marker"></i> Arrêts brefs en route autorisés</div>
                                <div><i class="fa fa-snowflake-o"></i> Voiture ultra-climatisée & Wi-Fi à bord</div>
                                <div><i class="fa fa-suitcase"></i> Prise en charge de vos bagages</div>
                            </div>
                        </div>

                        <div class="pm-info-card">
                            <h4>Aéroport ➔ Hôtel <span class="price">32 000 FCFA</span></h4>
                            <div class="pm-feature-list">
                                <div><i class="fa fa-plane"></i> Accueil personnalisé avec Pancarte VIP</div>
                                <div><i class="fa fa-coffee"></i> <strong>Attente gratuite</strong> en cas de retard de vol</div>
                                <div><i class="fa fa-glass"></i> Bouteilles d'eau fraîche & Confort Premium</div>
                            </div>
                        </div>

                        <div class="pm-info-card" style="background: #fffff0; border: 2px solid #C9A84C;">
                            <h4>Aller-Retour <i class="fa fa-star" style="color:#ecc94b;margin-left:8px;font-size:18px;"></i> <span class="price">45 000 FCFA</span></h4>
                            <div class="pm-feature-list">
                                <div><i class="fa fa-check" style="color:#38a169;"></i> Inclut absolument toutes les options Premium</div>
                                <div><i class="fa fa-rocket" style="color:#ecc94b;"></i> Le choix privilégié de notre clientèle d'affaires</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Form column ── -->
                <div class="col-md-7">
                    <div class="pm-form-card">
                        <h2>Réserver votre chauffeur</h2>
                        <p>Remplissez les détails de votre course. Notre équipe de conciergerie vous répondra sur WhatsApp en 2 minutes.</p>

                        <!-- Trip direction -->
                        <div class="pm-trip-type">
                            <button type="button" class="pm-trip-btn active" id="btn-depart" onclick="selectTripType('depart')">
                                🛫 Départ vers l'aéroport
                            </button>
                            <button type="button" class="pm-trip-btn" id="btn-arrivee" onclick="selectTripType('arrivee')">
                                🛬 Arrivée depuis l'aéroport
                            </button>
                        </div>

                        <!-- Name & WhatsApp -->
                        <div class="pm-form-row">
                            <div class="pm-field" id="field-prenom">
                                <label>Prénom *</label>
                                <input type="text" id="inp-prenom" placeholder="Ex: Jean" />
                                <span class="pm-field-error">Ce champ est requis.</span>
                            </div>
                            <div class="pm-field" id="field-nom">
                                <label>Nom *</label>
                                <input type="text" id="inp-nom" placeholder="Ex: Dupont" />
                                <span class="pm-field-error">Ce champ est requis.</span>
                            </div>
                        </div>

                        <div class="pm-form-row">
                            <div class="pm-field" id="field-whatsapp">
                                <label>Numéro WhatsApp *</label>
                                <input type="tel" id="inp-whatsapp" placeholder="Ex: +225 07 00 00 00 00" />
                                <span class="pm-field-error">Numéro valide requis.</span>
                            </div>
                            <div class="pm-field" id="field-pax">
                                <label>Nombre de passagers *</label>
                                <select id="inp-pax">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="1">1 passager</option>
                                    <option value="2">2 passagers</option>
                                    <option value="3">3 passagers</option>
                                    <option value="4">4 passagers</option>
                                    <option value="5-8">5 à 8 passagers</option>
                                    <option value="9+">9+ passagers (Groupe)</option>
                                </select>
                                <span class="pm-field-error">Veuillez sélectionner.</span>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="pm-form-row three">
                            <div class="pm-field" id="field-date">
                                <label>Date du vol *</label>
                                <input type="date" id="inp-date" />
                                <span class="pm-field-error">Date requise.</span>
                            </div>
                            <div class="pm-field" id="field-heure">
                                <label>Heure de vol *</label>
                                <input type="time" id="inp-heure" />
                                <span class="pm-field-error">Heure requise.</span>
                            </div>
                            <div class="pm-field">
                                <label>N° de vol (optionnel)</label>
                                <input type="text" id="inp-vol" placeholder="Ex: AF 702" />
                            </div>
                        </div>

                        <!-- Pickup / Zone -->
                        <div class="pm-form-row full">
                            <div class="pm-field" id="field-adresse">
                                <label id="adresse-label">Adresse de prise en charge *</label>
                                <input type="text" id="inp-adresse" placeholder="Ex: Cocody Riviera 3, Carrefour la Vie" />
                                <span class="pm-field-error">Veuillez indiquer votre adresse.</span>
                            </div>
                        </div>

                        <!-- Vehicle choice -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:13px;font-weight:600;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Catégorie de véhicule *</label>
                        </div>
                        <div class="pm-vehicle-grid" id="vehicle-grid">
                            <div class="pm-vehicle-option selected" data-val="Standard" onclick="selectVehicle(this)">
                                <span class="emoji">🚗</span>
                                <strong>Standard</strong>
                                <span>Berline élégante</span>
                            </div>
                            <div class="pm-vehicle-option" data-val="VIP" onclick="selectVehicle(this)">
                                <span class="emoji">🚙</span>
                                <strong>VIP</strong>
                                <span>SUV Premium</span>
                            </div>
                            <div class="pm-vehicle-option" data-val="Minibus" onclick="selectVehicle(this)">
                                <span class="emoji">🚌</span>
                                <strong>Minibus</strong>
                                <span>Spacieux (9-15)</span>
                            </div>
                        </div>

                        <!-- Luggage / Notes -->
                        <div class="pm-form-row full">
                            <div class="pm-field">
                                <label>Informations complémentaires / Bagages</label>
                                <textarea id="inp-notes" placeholder="Précisez le nombre de bagages ou toute exigence particulière..."></textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="button" class="pm-wa-btn" onclick="reserverWhatsApp()">
                            <i class="fa fa-whatsapp"></i>
                            <div style="text-align:left;">
                                <div style="line-height:1.2;">Réserver via WhatsApp</div>
                                <div class="pm-wa-btn-sub">Notre équipe vous répond sous 2 minutes</div>
                            </div>
                        </button>
                        <p class="pm-form-note"><i class="fa fa-lock" style="margin-right:6px;"></i> Données sécurisées. Paiement à bord ou en ligne selon vos préférences.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
var tripType = 'depart';
var vehicleType = 'Standard';

function selectTripType(type) {
    tripType = type;
    document.getElementById('btn-depart').classList.toggle('active', type === 'depart');
    document.getElementById('btn-arrivee').classList.toggle('active', type === 'arrivee');

    var label = document.getElementById('adresse-label');
    var inp = document.getElementById('inp-adresse');
    if (type === 'depart') {
        label.textContent = 'Adresse de prise en charge *';
        inp.placeholder = 'Ex: Sofitel Ivoire, Cocody';
    } else {
        label.textContent = 'Destination après l\'aéroport *';
        inp.placeholder = 'Ex: Assinie, Zone 4, Plateau...';
    }
}

function selectVehicle(el) {
    document.querySelectorAll('.pm-vehicle-option').forEach(function(v) {
        v.classList.remove('selected');
    });
    el.classList.add('selected');
    vehicleType = el.getAttribute('data-val');
}

function validate() {
    var ok = true;
    var fields = [
        ['prenom', 'inp-prenom'],
        ['nom', 'inp-nom'],
        ['whatsapp', 'inp-whatsapp'],
        ['pax', 'inp-pax'],
        ['date', 'inp-date'],
        ['heure', 'inp-heure'],
        ['adresse', 'inp-adresse']
    ];

    var today = new Date().toISOString().split('T')[0];
    var dateEl = document.getElementById('inp-date');
    if (!dateEl.min) dateEl.min = today;

    fields.forEach(function(f) {
        var fieldDiv = document.getElementById('field-' + f[0]);
        var input = document.getElementById(f[1]);
        if (!input) return;
        if (!fieldDiv) return;

        var val = input.value.trim();
        if (!val) {
            fieldDiv.classList.add('has-error');
            ok = false;
        } else {
            fieldDiv.classList.remove('has-error');
        }
    });
    return ok;
}

function reserverWhatsApp() {
    if (!validate()) {
        document.querySelector('.has-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    var prenom = document.getElementById('inp-prenom').value.trim();
    var nom = document.getElementById('inp-nom').value.trim();
    var whatsapp = document.getElementById('inp-whatsapp').value.trim();
    var pax = document.getElementById('inp-pax').value;
    var date = document.getElementById('inp-date').value;
    var heure = document.getElementById('inp-heure').value;
    var vol = document.getElementById('inp-vol').value.trim();
    var adresse = document.getElementById('inp-adresse').value.trim();
    var notes = document.getElementById('inp-notes').value.trim();

    var direction = tripType === 'depart' ? '🛫 DÉPART VERS AÉROPORT' : '🛬 ARRIVÉE DEPUIS AÉROPORT';

    var d = new Date(date);
    var days = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    var months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    var dateStr = days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();

    var msg = '✨ *RÉSERVATION PREMIUM — PicMe225*\n\n' +
        '━━━━━━━━━━━━━━━━━━━━━━\n' +
        direction + '\n' +
        '━━━━━━━━━━━━━━━━━━━━━━\n\n' +
        '👤 *Client :* ' + prenom + ' ' + nom + '\n' +
        '📱 *WhatsApp :* ' + whatsapp + '\n' +
        '👥 *Passagers :* ' + pax + '\n\n' +
        '📅 *Date du vol :* ' + dateStr + '\n' +
        '🕐 *Heure :* ' + heure + '\n' +
        (vol ? '✈️ *N° de vol :* ' + vol + '\n' : '') +
        '\n🚗 *Catégorie :* ' + vehicleType + '\n' +
        '📍 *' + (tripType === 'depart' ? 'Prise en charge' : 'Destination') + ' :* ' + adresse + '\n' +
        (notes ? '\n📝 *Exigences :* ' + notes + '\n' : '') +
        '\n━━━━━━━━━━━━━━━━━━━━━━\n' +
        '🔁 _Envoyé depuis le portail VIP_';

    var waNumber = '<?php echo e(Setting::get("whatsapp_airport", "2250700000000")); ?>';
    waNumber = waNumber.replace(/[^0-9]/g, '');

    var url = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
    window.open(url, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('inp-date').min = today;
});

document.querySelectorAll('.pm-field input, .pm-field select, .pm-field textarea').forEach(function(el) {
    el.addEventListener('input', function() {
        var parent = this.closest('.pm-field');
        if (parent) parent.classList.remove('has-error');
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/marketing/airport.blade.php ENDPATH**/ ?>