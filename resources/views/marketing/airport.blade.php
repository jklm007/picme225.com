@extends('user.layout.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600;700;800&display=swap');
*, *::before, *::after { box-sizing: border-box; }

/* ── WRAPPER ── */
.pm-airport {
    font-family: 'Inter', system-ui, sans-serif;
    background: #0d1226;
    min-height: 100vh;
    padding-top: 100px;
    padding-bottom: 80px;
}

/* ── HERO COMPACT ── */
.pm-air-hero-compact {
    background: linear-gradient(135deg, #0d1226 0%, #1a2a4a 60%, #0d1226 100%);
    padding: 14px 16px 10px;
    text-align: center;
    border-bottom: 1px solid rgba(201,168,76,0.15);
    position: relative;
    overflow: hidden;
}
.pm-air-hero-compact::before {
    content: '✈';
    position: absolute;
    right: -10px;
    top: -15px;
    font-size: 80px;
    opacity: 0.05;
    color: #C9A84C;
}
.pm-air-hero-compact h1 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 3px 0;
    line-height: 1.2;
}
.pm-air-hero-compact p {
    font-size: 12px;
    color: rgba(255,255,255,0.6);
    margin: 0;
}
.pm-air-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(201,168,76,0.15);
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 600;
    color: #C9A84C;
    margin-bottom: 8px;
}

/* ── BOOKING FORM CARD (MAIN FOCUS) ── */
.pm-book-card {
    background: #ffffff;
    border-radius: 20px;
    margin: 12px;
    padding: 18px 16px 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    position: relative;
    overflow: hidden;
}
.pm-book-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #25D366, #128C7E);
}

/* ── TRIP TYPE SWITCHER ── */
.pm-trip-switch {
    display: flex;
    background: #F0F4F8;
    border-radius: 10px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 14px;
}
.pm-trip-btn {
    flex: 1;
    padding: 9px 8px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: #718096;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    line-height: 1.2;
    text-align: center;
}
.pm-trip-btn.active {
    background: #25D366;
    color: #fff;
    box-shadow: 0 4px 12px rgba(37,211,102,0.35);
    transform: translateY(-1px);
}
.pm-trip-btn .t-icon { font-size: 14px; }

/* ── FORM FIELDS ── */
.pm-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.pm-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px; }
.pm-grid-1 { margin-bottom: 10px; }

.pm-fld { display: flex; flex-direction: column; gap: 3px; }
.pm-fld label {
    font-size: 10px;
    font-weight: 700;
    color: #718096;
    letter-spacing: .6px;
    text-transform: uppercase;
}
.pm-fld input,
.pm-fld select,
.pm-fld textarea {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 9px;
    padding: 9px 11px;
    color: #1a202c;
    font-size: 13px;
    font-weight: 500;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: 'Inter', sans-serif;
    width: 100%;
    height: 40px;
}
.pm-fld textarea { height: auto; min-height: 56px; resize: none; }
.pm-fld select { appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 10px center; background-size: 14px; padding-right: 28px; }
.pm-fld input:focus, .pm-fld select:focus, .pm-fld textarea:focus {
    border-color: #25D366;
    box-shadow: 0 0 0 3px rgba(37,211,102,0.12);
    background: #fff;
}
.pm-fld input::placeholder, .pm-fld textarea::placeholder { color: #b0bec5; font-weight: 400; }
.pm-fld select option { background: #fff; color: #1a202c; }

/* Error */
.pm-ferr { display: none; font-size: 10px; color: #e53e3e; font-weight: 600; }
.pm-fld.has-err input, .pm-fld.has-err select { border-color: #fc8181; background: #fff5f5; }
.pm-fld.has-err .pm-ferr { display: block; }

/* ── VEHICLE COMPACT ── */
.pm-veh-row {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.pm-veh-opt {
    flex: 1;
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 10px;
    padding: 8px 4px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.pm-veh-opt:hover { border-color: #25D366; }
.pm-veh-opt.selected {
    border-color: #25D366;
    background: #f0fff4;
}
.pm-veh-opt .v-ico { font-size: 20px; display: block; margin-bottom: 2px; }
.pm-veh-opt .v-name { font-size: 11px; font-weight: 700; color: #1a202c; display: block; }
.pm-veh-opt .v-sub { font-size: 10px; color: #718096; }

/* ── WHATSAPP BUTTON (MAIN CTA) ── */
.pm-wa-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 14px 20px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg, #25D366, #1DA851);
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    transition: all .25s ease;
    letter-spacing: .3px;
    box-shadow: 0 8px 24px rgba(37,211,102,0.35);
    margin-top: 4px;
    text-decoration: none;
}
.pm-wa-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(37,211,102,0.45);
    color: #fff;
    text-decoration: none;
}
.pm-wa-cta .wa-ico { font-size: 22px; flex-shrink: 0; }
.pm-wa-cta-sub { font-size: 11px; opacity: .85; font-weight: 400; display: block; text-align: left; }

.pm-book-note {
    text-align: center;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 10px;
}

/* ── SECTION HEADER ── */
.pm-section-hdr {
    padding: 24px 16px 12px;
    text-align: center;
}
.pm-section-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #C9A84C;
    margin-bottom: 6px;
}
.pm-section-hdr h2 {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

/* ── PRICING CARDS ── */
.pm-pricing-wrap { padding: 0 12px; }
.pm-price-card {
    background: #ffffff;
    border: 1.5px solid rgba(201,168,76,0.3);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 12px;
    transition: all .25s;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.pm-price-card:hover {
    transform: translateY(-3px);
    border-color: #C9A84C;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
.pm-price-card.featured {
    background: linear-gradient(135deg, #fffff0, #fef9e7);
    border-color: #C9A84C;
}
.pm-price-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}
.pm-price-name {
    font-size: 15px;
    font-weight: 700;
    color: #1a202c;
    font-family: 'Playfair Display', serif;
}
.pm-price-badge-star {
    font-size: 11px;
    font-weight: 700;
    color: #C9A84C;
    background: rgba(201,168,76,0.1);
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 20px;
    padding: 2px 8px;
}
.pm-price-amount {
    font-size: 24px;
    font-weight: 800;
    color: #C9A84C;
    font-family: 'Inter', sans-serif;
    line-height: 1;
}
.pm-price-unit { font-size: 12px; color: #718096; font-weight: 400; display: block; }
.pm-price-features { list-style: none; margin: 0; padding: 0; }
.pm-price-features li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #4a5568;
    padding: 5px 0;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}
.pm-price-features li:last-child { border-bottom: none; }
.pm-price-features li i { color: #C9A84C; width: 16px; text-align: center; flex-shrink: 0; }

/* ── FAQ ── */
.pm-faq-wrap { padding: 0 12px; }
.pm-faq-item {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    margin-bottom: 8px;
    overflow: hidden;
}
.pm-faq-q {
    padding: 14px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #e2e8f0;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}
.pm-faq-q::after { content: '﹢'; font-size: 18px; color: #C9A84C; transition: transform .2s; }
.pm-faq-item.open .pm-faq-q::after { content: '﹣'; }
.pm-faq-a {
    display: none;
    padding: 0 16px 14px;
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.6;
}
.pm-faq-item.open .pm-faq-a { display: block; }

/* ── REASSURANCE ── */
.pm-reassure {
    display: flex;
    gap: 8px;
    padding: 0 12px;
    flex-wrap: wrap;
    justify-content: center;
}
.pm-reassure-item {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    flex: 1;
    min-width: 80px;
}
.pm-reassure-item .r-ico { font-size: 20px; display: block; margin-bottom: 4px; }
.pm-reassure-item .r-label { font-size: 11px; font-weight: 600; color: #e2e8f0; line-height: 1.3; }

/* ── SECTION SPACER ── */
.pm-sep { height: 1px; background: rgba(255,255,255,0.06); margin: 16px 12px; }

/* ══ RESPONSIVE ══ */
@media (max-width: 767px) {
    .pm-grid-3 { grid-template-columns: 1fr 1fr; }
    .pm-grid-3 .pm-fld:last-child { grid-column: 1 / -1; }
}
@media (min-width: 768px) {
    .pm-airport { max-width: 520px; margin: 0 auto; }
    .pm-book-card { margin: 16px; padding: 28px 28px 24px; }
    .pm-air-hero-compact { padding: 20px 20px 16px; }
    .pm-air-hero-compact h1 { font-size: 26px; }
}
</style>

<div class="pm-airport">



    {{-- ══ FORMULAIRE PRINCIPAL (VISIBLE SANS SCROLL) ══ --}}
    <div class="pm-book-card">

        {{-- Sélecteur de trajet --}}
        <div class="pm-trip-switch">
            <button type="button" class="pm-trip-btn active" id="btn-depart" onclick="selectTrip('depart')">
                <span class="t-icon">🛫</span> Départ<br>aéroport
            </button>
            <button type="button" class="pm-trip-btn" id="btn-arrivee" onclick="selectTrip('arrivee')">
                <span class="t-icon">🛬</span> Arrivée<br>aéroport
            </button>
        </div>

        {{-- Prénom + Nom --}}
        <div class="pm-grid-2">
            <div class="pm-fld" id="fld-prenom">
                <label>@lang('home.form_first_name')</label>
                <input type="text" id="inp-prenom" placeholder="Jean">
                <span class="pm-ferr">Requis</span>
            </div>
            <div class="pm-fld" id="fld-nom">
                <label>@lang('home.form_last_name')</label>
                <input type="text" id="inp-nom" placeholder="Dupont">
                <span class="pm-ferr">Requis</span>
            </div>
        </div>

        {{-- WhatsApp + Passagers --}}
        <div class="pm-grid-2">
            <div class="pm-fld" id="fld-whatsapp">
                <label>@lang('home.form_whatsapp')</label>
                <input type="tel" id="inp-whatsapp" placeholder="+225 07 00 00 00">
                <span class="pm-ferr">Numéro requis</span>
            </div>
            <div class="pm-fld" id="fld-pax">
                <label>@lang('home.form_passengers')</label>
                <select id="inp-pax">
                    <option value="">Nb passagers</option>
                    <option value="1">1 passager</option>
                    <option value="2">2 passagers</option>
                    <option value="3">3 passagers</option>
                    <option value="4">4 passagers</option>
                    <option value="5-8">5 à 8 pass.</option>
                    <option value="9+">9+ (Groupe)</option>
                </select>
                <span class="pm-ferr">Requis</span>
            </div>
        </div>

        {{-- Date + Heure + @lang('home.form_flight_no') --}}
        <div class="pm-grid-3">
            <div class="pm-fld" id="fld-date">
                <label>@lang('home.form_date')</label>
                <input type="date" id="inp-date">
                <span class="pm-ferr">Requis</span>
            </div>
            <div class="pm-fld" id="fld-heure">
                <label>@lang('home.form_time')</label>
                <input type="time" id="inp-heure">
                <span class="pm-ferr">Requise</span>
            </div>
            <div class="pm-fld">
                <label>@lang('home.form_flight_no')</label>
                <input type="text" id="inp-vol" placeholder="AF 702">
            </div>
        </div>

        {{-- Adresse --}}
        <div class="pm-grid-1">
            <div class="pm-fld" id="fld-adresse">
                <label id="adresse-label">@lang('home.form_address')</label>
                <input type="text" id="inp-adresse" placeholder="Ex: Sofitel Ivoire, Cocody">
                <span class="pm-ferr">Adresse requise</span>
            </div>
        </div>

        {{-- Véhicule compact --}}
        <div class="pm-veh-row" id="vehicle-grid">
            <div class="pm-veh-opt selected" data-val="Standard" onclick="selectVehicle(this)">
                <span class="v-ico">🚗</span>
                <span class="v-name">@lang('home.standard_sedan')</span>
                <span class="v-sub"></span>
            </div>
            <div class="pm-veh-opt" data-val="VIP" onclick="selectVehicle(this)">
                <span class="v-ico">🚙</span>
                <span class="v-name">@lang('home.vip_suv')</span>
                <span class="v-sub"></span>
            </div>
            <div class="pm-veh-opt" data-val="Minibus" onclick="selectVehicle(this)">
                <span class="v-ico">🚌</span>
                <span class="v-name">@lang('home.minibus_9_15')</span>
                <span class="v-sub"></span>
            </div>
        </div>

        {{-- BOUTON WHATSAPP (CTA Principal) --}}
        <button type="button" class="pm-wa-cta" onclick="reserverWhatsApp()">
            <i class="fa fa-whatsapp wa-ico"></i>
            <span>@lang('home.book_via_whatsapp')</span>
        </button>

        <p class="pm-book-note"><i class="fa fa-lock" style="margin-right:4px;"></i> @lang('home.payment_on_board')</p>
    </div>

    {{-- ── RÉASSURANCE ── --}}
    <div class="pm-reassure" style="margin-top: 4px;">
        <div class="pm-reassure-item">
            <span class="r-ico">🕐</span>
            <span class="r-label">@lang('home.punctuality_guaranteed')</span>
        </div>
        <div class="pm-reassure-item">
            <span class="r-ico">🌡️</span>
            <span class="r-label">@lang('home.ac_wifi')</span>
        </div>
        <div class="pm-reassure-item">
            <span class="r-ico">🧳</span>
            <span class="r-label">@lang('home.luggage_handled')</span>
        </div>
        <div class="pm-reassure-item">
            <span class="r-ico">⭐</span>
            <span class="r-label">@lang('home.5_star_service')</span>
        </div>
    </div>

    <div class="pm-sep"></div>

    {{-- ══ TARIFS ══ --}}
    <div class="pm-section-hdr">
        <div class="pm-section-tag">@lang('home.our_rates')</div>
        <h2>@lang('home.transparent_formulas')</h2>
    </div>

    <div class="pm-pricing-wrap">
        <div class="pm-price-card">
            <div class="pm-price-head">
                <div>
                    <div class="pm-price-name">@lang('home.hotel_to_airport')</div>
                    <div class="pm-price-unit">@lang('home.departure_from_hotel')</div>
                </div>
                <div style="text-align:right;">
                    <div class="pm-price-amount">15 000</div>
                    <div class="pm-price-unit">FCFA</div>
                </div>
            </div>
            <ul class="pm-price-features">
                <li><i class="fa fa-clock-o"></i> @lang('home.privatization_1h30')</li>
                <li><i class="fa fa-map-marker"></i> @lang('home.brief_stops')</li>
                <li><i class="fa fa-snowflake-o"></i> @lang('home.ac_wifi_car')</li>
                <li><i class="fa fa-suitcase"></i> @lang('home.luggage_handling')</li>
            </ul>
        </div>

        <div class="pm-price-card">
            <div class="pm-price-head">
                <div>
                    <div class="pm-price-name">@lang('home.airport_to_hotel')</div>
                    <div class="pm-price-unit">@lang('home.arrival_at_airport')</div>
                </div>
                <div style="text-align:right;">
                    <div class="pm-price-amount">32 000</div>
                    <div class="pm-price-unit">FCFA</div>
                </div>
            </div>
            <ul class="pm-price-features">
                <li><i class="fa fa-plane"></i> @lang('home.vip_welcome')</li>
                <li><i class="fa fa-coffee"></i> @lang('home.free_waiting')</li>
                <li><i class="fa fa-glass"></i> @lang('home.fresh_water_premium')</li>
            </ul>
        </div>

        <div class="pm-price-card featured">
            <div class="pm-price-head">
                <div>
                    <div class="pm-price-name">@lang('home.round_trip')</div>
                    <div class="pm-price-unit">@lang('home.complete_formula')</div>
                </div>
                <div style="text-align:right;">
                    <div class="pm-price-amount">45 000</div>
                    <div class="pm-price-unit">FCFA</div>
                </div>
            </div>
            <ul class="pm-price-features">
                <li><i class="fa fa-check" style="color:#27ae60;"></i> @lang('home.all_premium_options')</li>
                <li><i class="fa fa-rocket" style="color:#C9A84C;"></i> @lang('home.business_choice')</li>
                <li><i class="fa fa-star" style="color:#C9A84C;"></i> @lang('home.save_2000')</li>
            </ul>
        </div>
    </div>

    <div class="pm-sep"></div>

    {{-- ══ FAQ ══ --}}
    <div class="pm-section-hdr">
        <div class="pm-section-tag">@lang('home.faq_title')</div>
        <h2>@lang('home.all_you_need_to_know')</h2>
    </div>

    <div class="pm-faq-wrap">
        <div class="pm-faq-item open">
            <div class="pm-faq-q" onclick="toggleFaq(this)">@lang('home.faq_q1')</div>
            <div class="pm-faq-a">Remplissez le formulaire ci-dessus et cliquez sur "@lang('home.book_via_whatsapp')". Notre équipe de conciergerie vous confirme votre chauffeur dans les 2 minutes.</div>
        </div>
        <div class="pm-faq-item">
            <div class="pm-faq-q" onclick="toggleFaq(this)">@lang('home.faq_q2')</div>
            <div class="pm-faq-a">Oui ! Pour les formules "@lang('home.airport_to_hotel')" et "Aller-Retour", l'attente est totalement gratuite en cas de retard de vol. Votre chauffeur surveille le statut de votre vol en temps réel.</div>
        </div>
        <div class="pm-faq-item">
            <div class="pm-faq-q" onclick="toggleFaq(this)">@lang('home.faq_q3')</div>
            <div class="pm-faq-a">@lang('home.faq_a3')</div>
        </div>
        <div class="pm-faq-item">
            <div class="pm-faq-q" onclick="toggleFaq(this)">@lang('home.faq_q4')</div>
            <div class="pm-faq-a">@lang('home.faq_a4')</div>
        </div>
    </div>

    <div class="pm-sep"></div>

    {{-- ══ CTA SECONDAIRE ══ --}}
    <div style="padding: 0 12px 16px; text-align: center;">
        <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 12px;">@lang('home.ready_to_book')</p>
        <button type="button" class="pm-wa-cta" onclick="scrollToForm()" style="display: inline-flex; width: auto; padding: 12px 24px;">
            <i class="fa fa-arrow-up wa-ico" style="font-size: 16px;"></i>
            <span>@lang('home.book_now_btn')</span>
        </button>
    </div>

</div>

@endsection

@section('scripts')
<script>
var tripType = 'depart';
var vehicleType = 'Standard';

function selectTrip(type) {
    tripType = type;
    var btnD = document.getElementById('btn-depart');
    var btnA = document.getElementById('btn-arrivee');
    if (type === 'depart') {
        btnD.classList.add('active');
        btnA.classList.remove('active');
        document.getElementById('adresse-label').textContent = '@lang('home.form_address')';
        document.getElementById('inp-adresse').placeholder = 'Ex: Sofitel Ivoire, Cocody';
    } else {
        btnA.classList.add('active');
        btnD.classList.remove('active');
        document.getElementById('adresse-label').textContent = "Destination après l'aéroport *";
        document.getElementById('inp-adresse').placeholder = 'Ex: Assinie, Zone 4, Plateau...';
    }
}

function selectVehicle(el) {
    document.querySelectorAll('.pm-veh-opt').forEach(function(v) { v.classList.remove('selected'); });
    el.classList.add('selected');
    vehicleType = el.getAttribute('data-val');
}

function validate() {
    var ok = true;
    var checks = [
        ['prenom', 'inp-prenom'],
        ['nom', 'inp-nom'],
        ['whatsapp', 'inp-whatsapp'],
        ['pax', 'inp-pax'],
        ['date', 'inp-date'],
        ['heure', 'inp-heure'],
        ['adresse', 'inp-adresse']
    ];
    checks.forEach(function(c) {
        var fld = document.getElementById('fld-' + c[0]);
        var inp = document.getElementById(c[1]);
        if (!fld || !inp) return;
        if (!inp.value.trim()) { fld.classList.add('has-err'); ok = false; }
        else { fld.classList.remove('has-err'); }
    });
    return ok;
}

function reserverWhatsApp() {
    if (!validate()) {
        var firstErr = document.querySelector('.has-err');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    var prenom  = document.getElementById('inp-prenom').value.trim();
    var nom     = document.getElementById('inp-nom').value.trim();
    var wa      = document.getElementById('inp-whatsapp').value.trim();
    var pax     = document.getElementById('inp-pax').value;
    var date    = document.getElementById('inp-date').value;
    var heure   = document.getElementById('inp-heure').value;
    var vol     = document.getElementById('inp-vol').value.trim();
    var adresse = document.getElementById('inp-adresse').value.trim();

    var direction = tripType === 'depart' ? '🛫 DÉPART VERS AÉROPORT' : '🛬 ARRIVÉE DEPUIS AÉROPORT';
    var d = new Date(date);
    var days   = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
    var months = ['jan','fév','mar','avr','mai','jun','juil','août','sep','oct','nov','déc'];
    var dateStr = days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();

    var msg =
        '✨ *RÉSERVATION PREMIUM — PicMe225*\n' +
        '━━━━━━━━━━━━━━━━━━━━━━\n' +
        direction + '\n' +
        '━━━━━━━━━━━━━━━━━━━━━━\n\n' +
        '👤 *Client :* ' + prenom + ' ' + nom + '\n' +
        '📱 *WhatsApp :* ' + wa + '\n' +
        '👥 *Passagers :* ' + pax + '\n\n' +
        '📅 *Date :* ' + dateStr + '\n' +
        '🕐 *Heure :* ' + heure + '\n' +
        (vol ? '✈️ *N° de vol :* ' + vol + '\n' : '') +
        '\n🚗 *Catégorie :* ' + vehicleType + '\n' +
        '📍 *' + (tripType === 'depart' ? 'Prise en charge' : 'Destination') + ' :* ' + adresse + '\n' +
        '\n━━━━━━━━━━━━━━━━━━━━━━\n' +
        '🔁 _Envoyé depuis PicMe225.site_';

    var waNum = '{{ Setting::get("whatsapp_airport", "2250700000000") }}'.replace(/[^0-9]/g, '');
    window.open('https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg), '_blank');
}

function scrollToForm() {
    document.querySelector('.pm-book-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleFaq(el) {
    el.closest('.pm-faq-item').classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('inp-date').min = today;

    document.querySelectorAll('.pm-fld input, .pm-fld select').forEach(function(el) {
        el.addEventListener('input', function() {
            var p = this.closest('.pm-fld');
            if (p) p.classList.remove('has-err');
        });
    });
});
</script>
@endsection
