@extends('user.layout.base')

@section('title', 'Publier une Annonce – PicMe225 Marketplace')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --navy:#0D1B2A; --navy-2:#162436; --navy-3:#1e3048;
    --gold:#C9A84C; --gold-light:#E2C06E; --gold-pale:rgba(201,168,76,0.12);
    --white:#fff; --gray-50:#f9fafc; --gray-100:#f0f2f7; --gray-200:#e4e7ef;
    --gray-400:#adb5c9; --gray-500:#7a8bad; --success:#27ae60; --danger:#e74c3c;
}
header,.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
body,html{margin:0;padding:0;background:var(--gray-50);font-family:'Inter',sans-serif;color:var(--navy)}
.pm-mk-header{
    background:linear-gradient(135deg,var(--lime, #22C55E),var(--lime-dark, #15803D));
    padding:16px 16px 12px; position:sticky; top:0; z-index:50;
    display:flex;align-items:center;gap:12px;
    box-shadow:0 2px 16px rgba(0,0,0,0.1);
}
.pm-mk-header h1{font-size:17px;font-weight:800;color:#ffffff;margin:0;flex:1;text-shadow: 0 1px 2px rgba(0,0,0,0.1);}
.pm-mk-back{color:#ffffff;font-size:20px;text-decoration:none;line-height:1}
.pm-mk-body{padding:14px 14px 170px !important;max-width:540px;margin:0 auto}
.pm-form-card{background:var(--white);border-radius:16px;padding:16px;margin-bottom:14px;box-shadow:0 2px 12px rgba(13,27,42,0.06)}
.pm-form-card h2{font-size:13px;font-weight:700;color:var(--navy);margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid var(--gold-pale);display:flex;align-items:center;gap:8px}
.pm-form-card h2 i{color:var(--gold)}
.pm-field{margin-bottom:12px}
.pm-field label{display:block;font-size:12px;font-weight:600;color:var(--navy-2);margin-bottom:5px}
.pm-field input,.pm-field textarea,.pm-field select{
    width:100%;padding:11px 14px;border:1.5px solid var(--gray-200);border-radius:10px;
    font-size:13px;color:var(--navy);background:var(--gray-50);outline:none;
    transition:all 0.2s;box-sizing:border-box;font-family:'Inter',sans-serif;
}
.pm-field input:focus,.pm-field textarea:focus,.pm-field select:focus{
    border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px var(--gold-pale);
}
.pm-field textarea{resize:vertical;min-height:90px}
.pm-condition-row{display:flex;gap:10px}
.pm-condition-btn{
    flex:1;padding:10px;border:2px solid var(--gray-200);border-radius:10px;
    text-align:center;cursor:pointer;font-weight:600;font-size:12px;color:var(--gray-500);
    transition:all 0.2s;
}
.pm-condition-btn.active{border-color:var(--gold);background:var(--gold-pale);color:var(--navy)}
.pm-condition-btn i{display:block;font-size:22px;margin-bottom:4px}
.pm-photo-upload{
    border:2px dashed var(--gray-200);border-radius:12px;padding:20px;
    text-align:center;cursor:pointer;transition:all 0.2s;background:var(--gray-50);
}
.pm-photo-upload:hover,.pm-photo-upload.dragover{border-color:var(--gold);background:var(--gold-pale)}
.pm-photo-upload i{font-size:28px;color:var(--gold);display:block;margin-bottom:8px}
.pm-photo-upload p{margin:0;font-size:12px;color:var(--gray-500)}
.pm-preview-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px}
.pm-preview-thumb{
    aspect-ratio:1;border-radius:10px;overflow:hidden;position:relative;
    background:var(--gray-100);
}
.pm-preview-thumb img{width:100%;height:100%;object-fit:cover}
.pm-preview-thumb .pm-rm{
    position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;
    background:rgba(231,76,60,0.9);color:white;border:none;cursor:pointer;
    font-size:10px;display:flex;align-items:center;justify-content:center;
}
.pm-info-banner{
    background:linear-gradient(135deg,rgba(201,168,76,0.12),rgba(201,168,76,0.06));
    border:1.5px solid rgba(201,168,76,0.3);border-radius:12px;
    padding:12px 14px;display:flex;align-items:flex-start;gap:10px;margin-bottom:14px;
}
.pm-info-banner i{color:var(--gold);font-size:18px;flex-shrink:0;margin-top:1px}
.pm-info-banner p{margin:0;font-size:12px;color:var(--navy-2);line-height:1.5}
.pm-info-banner strong{color:var(--navy)}
.pm-submit-btn{
    width:100%;padding:15px;background:linear-gradient(135deg,var(--gold),var(--gold-light));
    color:var(--navy);border:none;border-radius:14px;font-size:15px;font-weight:800;
    cursor:pointer;transition:all 0.2s;box-shadow:0 4px 18px rgba(201,168,76,0.4);
    display:flex;align-items:center;justify-content:center;gap:8px;
}
.pm-submit-btn:active{transform:scale(0.98)}
.pm-submit-footer{
    position:fixed;bottom:70px !important;left:0;right:0;padding:12px 16px;
    background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
    border-top:1px solid var(--gray-100);
    box-shadow:0 -4px 16px rgba(13,27,42,0.08);
    z-index:9999;
}
.pm-row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:400px){.pm-row2{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<div class="page-content">
{{-- Header --}}
<div class="pm-mk-header">
    <a href="{{ url('marketplace') }}" class="pm-mk-back"><i class="fa fa-arrow-left"></i></a>
    <h1>Publier une annonce</h1>
</div>

<div class="pm-mk-body">

    {{-- Info banner --}}
    <div class="pm-info-banner">
        <i class="fa fa-info-circle"></i>
        <p><strong>Validation requise :</strong> Votre annonce sera vérifiée par notre équipe avant d'être visible sur la Marketplace. Cela prend généralement moins de 24h.</p>
    </div>

    @if($errors->any())
    <div style="background:rgba(231,76,60,0.1);border:1.5px solid var(--danger);border-radius:10px;padding:10px 14px;margin-bottom:12px;">
        <ul style="margin:0;padding-left:16px;font-size:13px;color:var(--danger)">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('user.marketplace.store') }}" method="POST" enctype="multipart/form-data" id="listing-form">
        @csrf

        {{-- Informations générales --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-tag"></i> Informations générales</h2>
            <div class="pm-field">
                <label>Titre de l'annonce *</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: iPhone 14 Pro – 128Go – Comme Neuf" required maxlength="255">
            </div>
            <div class="pm-row2">
                <div class="pm-field">
                    <label>Catégorie *</label>
                    <select name="category" id="cat-select" required>
                        <option value="">-- Catégorie --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @foreach($cat->children as $child)
                                <option value="{{ $child->name }}" style="padding-left:16px" {{ old('category') == $child->name ? 'selected' : '' }}>
                                    &nbsp;&nbsp;↳ {{ $child->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="pm-field">
                    <label>Sous-catégorie</label>
                    <input type="text" name="sub_category" value="{{ old('sub_category') }}" placeholder="Ex: Smartphones">
                </div>
            </div>
            
            {{-- Dynamic Fields Container --}}
            <div id="dynamic-fields-container"></div>
            <div class="pm-field">
                <label>Description détaillée *</label>
                <textarea name="description" rows="4" placeholder="Décrivez votre article en détail : état, caractéristiques, raison de la vente..." required maxlength="5000">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Prix --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-money"></i> Prix</h2>
            <div class="pm-row2">
                <div class="pm-field">
                    <label>Prix *</label>
                    <input type="number" name="price" value="{{ old('price') }}" placeholder="0" min="0" required>
                </div>
                <div class="pm-field">
                    <label>Unité</label>
                    <select name="price_unit">
                        <option value="FCFA" {{ old('price_unit') == 'FCFA' ? 'selected' : '' }}>FCFA</option>
                        <option value="/mois" {{ old('price_unit') == '/mois' ? 'selected' : '' }}>FCFA / mois</option>
                        <option value="/jour" {{ old('price_unit') == '/jour' ? 'selected' : '' }}>FCFA / jour</option>
                        <option value="/nuit" {{ old('price_unit') == '/nuit' ? 'selected' : '' }}>FCFA / nuit</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- État --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-star"></i> État de l'article</h2>
            <input type="hidden" name="condition" id="condition-input" value="{{ old('condition', 'used') }}">
            <div class="pm-condition-row">
                <div class="pm-condition-btn {{ old('condition', 'used') === 'new' ? 'active' : '' }}" onclick="setCondition('new',this)">
                    <i class="fa fa-gift"></i> Neuf
                </div>
                <div class="pm-condition-btn {{ old('condition', 'used') === 'used' ? 'active' : '' }}" onclick="setCondition('used',this)">
                    <i class="fa fa-recycle"></i> Occasion
                </div>
            </div>
        </div>

        {{-- Contact & Localisation --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-map-marker"></i> Contact & Localisation</h2>
            <div class="pm-row2">
                <div class="pm-field">
                    <label>Téléphone / WhatsApp *</label>
                    <input type="tel" name="phone" value="{{ old('phone', Auth::user()->mobile) }}" placeholder="+225 07 00 00 00 00" required>
                </div>
                <div class="pm-field">
                    <label>Ville / Commune</label>
                    <input type="text" name="location_city" value="{{ old('location_city') }}" placeholder="Ex: Cocody, Abidjan">
                </div>
            </div>
        </div>

        {{-- Fichier Numérique (Caché par défaut) --}}
        <div class="pm-form-card" id="digital-upload-zone" style="display:none; border:2px solid var(--gold); background:var(--gold-pale);">
            <h2><i class="fa fa-file-archive-o"></i> Produit Numérique</h2>
            <p style="font-size:12px;color:var(--navy-2);margin-bottom:12px;">Veuillez uploader le fichier qui sera livré à l'acheteur après le paiement (PDF, ZIP, MP4, etc.).</p>
            <div class="pm-field">
                <input type="file" name="digital_file" id="digital_file" style="background:#fff;">
            </div>
        </div>
        
        {{-- Billetterie et Agents --}}
        <div class="pm-form-card" id="tickets-zone" style="display:none;">
            <h2><i class="fa fa-ticket"></i> Configuration Billetterie & Agents</h2>
            <div class="pm-field">
                <label class="text-warning">Agents Assignés *</label>
                <select name="assigned_agents[]" id="assigned_agents" multiple style="height: 100px;" title="Maintenez CTRL pour en sélectionner plusieurs">
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->first_name }} {{ $agent->last_name }} ({{ $agent->email }})</option>
                    @endforeach
                </select>
                <small class="text-muted" style="display:block;margin-top:5px;">Sélectionnez les agents autorisés à scanner pour cet événement. (Maintenez CTRL ou CMD appuyé pour sélectionner plusieurs)</small>
            </div>
            
            <div id="passes_container" style="margin-top: 15px;">
                <div class="pass-row" style="border: 1px solid var(--gray-200); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <div class="pm-row2">
                        <div class="pm-field">
                            <label>Nom du Pass *</label>
                            <input type="text" name="passes[0][name]" placeholder="Ex: Entrée standard">
                        </div>
                        <div class="pm-field">
                            <label>Prix</label>
                            <input type="number" name="passes[0][price]" placeholder="Prix">
                        </div>
                    </div>
                    <div class="pm-row2">
                        <div class="pm-field">
                            <label>Quantité</label>
                            <input type="number" name="passes[0][quantity]" placeholder="Stock (Optionnel)">
                        </div>
                        <div class="pm-field">
                            <label>Pers./Pass</label>
                            <input type="number" name="passes[0][persons_per_pass]" value="1" min="1">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="pm-condition-btn" style="width:100%; border: 1px dashed var(--gold); color: var(--gold);" onclick="addPass()">+ Ajouter un autre pass</button>
        </div>

        {{-- Photos --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-camera"></i> Photos (max 6)</h2>
            <div class="pm-photo-upload" id="photo-zone" onclick="document.getElementById('photo-input').click()">
                <i class="fa fa-cloud-upload"></i>
                <p>Cliquez ou glissez vos photos ici</p>
                <p style="font-size:10px;margin-top:4px;color:var(--gold)">JPG, PNG, WEBP — max 5 Mo par photo</p>
            </div>
            <input type="file" name="photos[]" id="photo-input" multiple accept="image/*" style="display:none" onchange="handlePhotos(this)">
            <div class="pm-preview-grid" id="photo-previews"></div>
        </div>

        {{-- Informations complémentaires --}}
        <div class="pm-form-card">
            <h2><i class="fa fa-info-circle"></i> Infos complémentaires</h2>
            <div class="pm-field">
                <label>Autres détails (optionnel)</label>
                <textarea name="extra_info" rows="3" placeholder="Accessoires inclus, garantie, négociable, etc.">{{ old('extra_info') }}</textarea>
            </div>
        </div>

    </form>
</div>

{{-- Sticky footer submit --}}
<div class="pm-submit-footer">
    <button type="submit" form="listing-form" class="pm-submit-btn" id="submit-btn">
        <i class="fa fa-check-circle"></i> Soumettre pour validation
    </button>
</div>
@include('user.include.bottom_nav', ['active' => 'store'])
</div>
@endsection

@section('scripts')
<script>
function setCondition(val, el) {
    document.getElementById('condition-input').value = val;
    document.querySelectorAll('.pm-condition-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
}

// ─── DYNAMIC FIELDS LOGIC ─────────────────────────────────
const dynamicConfig = {
    VEHICLES: [
        {name: 'brand', label: 'Marque', type: 'text', placeholder: 'Ex: Toyota'},
        {name: 'model', label: 'Modèle', type: 'text', placeholder: 'Ex: RAV4'},
        {name: 'year', label: 'Année', type: 'number', placeholder: 'Ex: 2020'},
        {name: 'color', label: 'Couleur', type: 'text', placeholder: 'Ex: Gris'},
        {name: 'plate_number', label: 'Immatriculation', type: 'text', placeholder: 'Ex: 1234 AB 01'},
    ],
    REAL_ESTATE: [
        {name: 'rooms', label: 'Nombre de pièces', type: 'number', placeholder: 'Ex: 4'},
        {name: 'bathrooms', label: 'Salles de bain', type: 'number', placeholder: 'Ex: 2'},
    ]
};

function renderDynamicFields(catValue) {
    const container = document.getElementById('dynamic-fields-container');
    container.innerHTML = '';
    if (!catValue) return;
    
    let key = '';
    const catUpper = catValue.toUpperCase();
    if (catUpper.includes('VEHICL') || catUpper.includes('AUTO') || catUpper.includes('VOITURE')) key = 'VEHICLES';
    else if (catUpper.includes('REAL_ESTATE') || catUpper.includes('IMMO') || catUpper.includes('MAISON')) key = 'REAL_ESTATE';
    
    const fields = dynamicConfig[key];
    if (fields) {
        let html = '<div class="pm-row2" style="margin-top:12px; padding-top:12px; border-top:1px solid var(--gray-100);">';
        fields.forEach(f => {
            html += `<div class="pm-field">
                <label>${f.label}</label>
                <input type="${f.type}" name="${f.name}" placeholder="${f.placeholder}">
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }
    
    // Digital Upload logic
    const isDigital = catUpper.includes('DIGITAL') || catUpper.includes('NUMÉRIQUE');
    const digitalZone = document.getElementById('digital-upload-zone');
    if(isDigital) {
        digitalZone.style.display = 'block';
    } else {
        digitalZone.style.display = 'none';
    }
    
    // Tickets logic
    const ticketsZone = document.getElementById('tickets-zone');
    if (catUpper === 'TICKETS' || catUpper === 'TRAVEL' || catUpper.includes('BILLET') || catUpper.includes('EVENEMENT')) {
        ticketsZone.style.display = 'block';
    } else {
        ticketsZone.style.display = 'none';
    }
}

let passCount = 1;
function addPass() {
    const container = document.getElementById('passes_container');
    const div = document.createElement('div');
    div.className = 'pass-row';
    div.style = 'border: 1px solid var(--gray-200); padding: 10px; border-radius: 8px; margin-bottom: 10px;';
    div.innerHTML = `
        <div class="pm-row2">
            <div class="pm-field">
                <label>Nom du Pass *</label>
                <input type="text" name="passes[${passCount}][name]" placeholder="Ex: Entrée standard">
            </div>
            <div class="pm-field">
                <label>Prix</label>
                <input type="number" name="passes[${passCount}][price]" placeholder="Prix">
            </div>
        </div>
        <div class="pm-row2">
            <div class="pm-field">
                <label>Quantité</label>
                <input type="number" name="passes[${passCount}][quantity]" placeholder="Stock (Optionnel)">
            </div>
            <div class="pm-field">
                <label>Pers./Pass</label>
                <input type="number" name="passes[${passCount}][persons_per_pass]" value="1" min="1">
            </div>
        </div>
        <button type="button" class="pm-condition-btn" style="width:100%; border: 1px solid var(--danger); color: var(--danger); padding: 5px; margin-top: 5px;" onclick="this.parentElement.remove()">- Supprimer</button>
    `;
    container.appendChild(div);
    passCount++;
}

document.getElementById('cat-select').addEventListener('change', function(e) {
    renderDynamicFields(e.target.value);
});
renderDynamicFields(document.getElementById('cat-select').value);

var selectedFiles = [];

function handlePhotos(input) {
    var newFiles = Array.from(input.files);
    selectedFiles = selectedFiles.concat(newFiles).slice(0, 6);
    renderPreviews();
}

function renderPreviews() {
    var grid = document.getElementById('photo-previews');
    grid.innerHTML = '';
    selectedFiles.forEach(function(file, idx) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.className = 'pm-preview-thumb';
            div.innerHTML = '<img src="' + e.target.result + '">' +
                '<button type="button" class="pm-rm" onclick="removePhoto(' + idx + ')"><i class="fa fa-times"></i></button>';
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removePhoto(idx) {
    selectedFiles.splice(idx, 1);
    renderPreviews();
}

// Drag & drop
var zone = document.getElementById('photo-zone');
zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
zone.addEventListener('drop', function(e) {
    e.preventDefault();
    zone.classList.remove('dragover');
    selectedFiles = selectedFiles.concat(Array.from(e.dataTransfer.files)).slice(0, 6);
    renderPreviews();
});

// Submit — rebuild file input
document.getElementById('listing-form').addEventListener('submit', function(e) {
    var btn = document.getElementById('submit-btn');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi en cours...';
    btn.disabled = true;
});
</script>
@endsection
