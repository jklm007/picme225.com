@extends('provider.layout.app')
@section('body-class', 'light-theme')

@section('content')
<style>
    /* PREMIUM 2026 PROFILE STYLES */
    .premium-profile-wrapper {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
        min-height: 100vh;
        padding-bottom: 100px;
    }
    
    /* Cover & Avatar Header */
    .premium-cover {
        height: 220px;
        background: linear-gradient(135deg, rgba(46,204,113,0.1) 0%, rgba(39,174,96,0.05) 100%);
        position: relative;
        overflow: hidden;
    }
    .premium-cover::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(46,204,113,0.15) 0%, transparent 60%);
        animation: rotateBg 20s linear infinite;
    }
    @keyframes rotateBg {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .premium-header-content {
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        top: -60px;
        padding: 0 24px;
        display: flex;
        align-items: flex-end;
        gap: 24px;
        z-index: 10;
    }

    .premium-avatar-container {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        padding: 6px;
        background: #ffffff;
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        position: relative;
        flex-shrink: 0;
    }
    .premium-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #eee;
    }
    .status-badge {
        position: absolute;
        bottom: 10px;
        right: 0px;
        background: #2ecc71;
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(46,204,113,0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .premium-user-info {
        padding-bottom: 20px;
    }
    .premium-user-info h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 800;
        color: #1a1a1a;
        letter-spacing: -0.5px;
    }
    .premium-user-info p {
        margin: 4px 0 0 0;
        color: #777;
        font-size: 15px;
        font-weight: 500;
    }

    /* Main Form Area */
    .premium-form-container {
        max-width: 900px;
        margin: -20px auto 0;
        padding: 0 24px;
        position: relative;
        z-index: 10;
    }
    .premium-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(0,0,0,0.04);
        padding: 40px;
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.02);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .premium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 48px rgba(0,0,0,0.06);
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i {
        color: #2ecc71;
        font-size: 20px;
    }

    /* Premium Inputs */
    .premium-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .premium-grid { grid-template-columns: 1fr; }
        .premium-header-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
            top: -70px;
        }
        .premium-user-info { padding-bottom: 0; margin-top: 10px; }
        .status-badge { right: 10px; bottom: 0px; }
    }
    .full-width {
        grid-column: 1 / -1;
    }

    .input-group-premium {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .input-group-premium label {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .input-premium {
        width: 100%;
        background: #f8f9fc;
        border: 1.5px solid transparent;
        border-radius: 14px;
        padding: 14px 18px;
        font-size: 15px;
        font-weight: 500;
        color: #333;
        transition: all 0.3s ease;
    }
    .input-premium:focus {
        outline: none;
        background: #ffffff;
        border-color: #2ecc71;
        box-shadow: 0 0 0 4px rgba(46,204,113,0.15);
    }
    .input-premium::placeholder {
        color: #aaa;
    }
    
    select.input-premium {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
    }

    /* File Input Styling */
    .file-upload-wrapper {
        position: relative;
        background: #f8f9fc;
        border: 1.5px dashed #d0d4d9;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .file-upload-wrapper:hover {
        border-color: #2ecc71;
        background: rgba(46,204,113,0.02);
    }
    .file-upload-wrapper input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
    }
    .file-upload-icon {
        font-size: 24px;
        color: #2ecc71;
        margin-bottom: 8px;
    }
    .file-upload-text {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }
    
    /* Submit Button */
    .btn-premium {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
        border: none;
        border-radius: 14px;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(46,204,113,0.3);
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
    .btn-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(46,204,113,0.4);
    }
    .btn-premium:active {
        transform: translateY(0);
    }

    /* Select2 Overrides */
    .select2-container--default .select2-selection--single {
        background: #f8f9fc !important;
        border: 1.5px solid transparent !important;
        border-radius: 14px !important;
        height: 50px !important;
        display: flex;
        align-items: center;
        padding: 0 8px;
        transition: all 0.3s;
    }
    .select2-container--open .select2-selection--single {
        background: #ffffff !important;
        border-color: #2ecc71 !important;
        box-shadow: 0 0 0 4px rgba(46,204,113,0.15) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #333 !important;
        font-weight: 500;
    }
    .select2-dropdown {
        border: none !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        overflow: hidden;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #2ecc71 !important;
    }
</style>

<div class="premium-profile-wrapper">
    <!-- Dynamic Cover -->
    <div class="premium-cover"></div>

    @php
        $user = Auth::guard('provider')->user();
        $avatar = $user->avatar;
        if ($avatar) {
            if (strpos($avatar, 'lorempixel.com') !== false) {
                $avatar_url = asset('asset/img/provider.jpg');
            } elseif (strpos($avatar, 'http') === 0) {
                $avatar_url = $avatar;
            } else {
                $avatar_url = \Storage::disk('s3')->url( $avatar);
            }
        } else {
            $avatar_url = asset('asset/img/provider.jpg');
        }
    @endphp

    <!-- Header Avatar & Info -->
    <div class="premium-header-content">
        <div class="premium-avatar-container">
            <img src="{{ $avatar_url }}" alt="Provider Avatar" class="premium-avatar">
            <div class="status-badge">{{ $user->status }}</div>
        </div>
        <div class="premium-user-info">
            <h1>{{ $user->first_name }} {{ $user->last_name }}</h1>
            <p><i class="fa fa-envelope-o"></i> {{ $user->email ?? 'Non renseigné' }}</p>
        </div>
    </div>

    <!-- Main Form Content -->
    <div class="premium-form-container">
        <form action="{{ route('provider.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Section 1: Personal Info -->
            <div class="premium-card">
                <div class="section-title"><i class="fa fa-user-o"></i> Informations Personnelles</div>
                
                <div class="premium-grid">
                    <div class="input-group-premium">
                        <label>Prénom</label>
                        <input type="text" class="input-premium" name="first_name" value="{{ $user->first_name }}" required>
                    </div>
                    <div class="input-group-premium">
                        <label>Nom</label>
                        <input type="text" class="input-premium" name="last_name" value="{{ $user->last_name }}" required>
                    </div>
                    
                    <div class="input-group-premium">
                        <label>Téléphone</label>
                        <input type="text" class="input-premium" name="mobile" value="{{ $user->mobile }}" required>
                    </div>
                    <div class="input-group-premium">
                        <label>Langue</label>
                        <select class="input-premium" name="language" required>
                            <option value="en" {{ $user->language == 'en' ? 'selected' : '' }}>English</option>
                            <option value="fr" {{ $user->language == 'fr' ? 'selected' : '' }}>Français</option>
                        </select>
                    </div>

                    <div class="input-group-premium full-width">
                        <label>Photo de profil</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="avatar" accept="image/*">
                            <i class="fa fa-cloud-upload file-upload-icon"></i>
                            <div class="file-upload-text">Cliquez pour remplacer votre photo (JPEG, PNG)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Address -->
            <div class="premium-card">
                <div class="section-title"><i class="fa fa-map-marker"></i> Adresse Complète</div>
                
                <div class="premium-grid">
                    <div class="input-group-premium full-width">
                        <label>Adresse Principale</label>
                        <input type="text" class="input-premium" name="address" value="{{ $user->profile ? $user->profile->address : '' }}" placeholder="Rue, Quartier">
                    </div>
                    <div class="input-group-premium full-width">
                        <label>Complément (Optionnel)</label>
                        <input type="text" class="input-premium" name="address_secondary" value="{{ $user->profile ? $user->profile->address_secondary : '' }}" placeholder="Bâtiment, Étage, Appartement">
                    </div>

                    <div class="input-group-premium">
                        <label>Ville</label>
                        <input type="text" class="input-premium" name="city" value="{{ $user->profile ? $user->profile->city : '' }}" placeholder="Ex: Abidjan">
                    </div>
                    <div class="input-group-premium">
                        <label>Code Postal</label>
                        <input type="text" class="input-premium" name="postal_code" value="{{ $user->profile ? $user->profile->postal_code : '' }}">
                    </div>

                    <div class="input-group-premium full-width">
                        <label>Pays</label>
                        <select class="input-premium select2-country" name="country" required>
                            <option value="">Sélectionnez un pays</option>
                            <option value="CI" selected>Côte d'Ivoire</option>
                            <option value="FR">France</option>
                            <option value="US">United States</option>
                            <!-- Plus de pays -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Service & Vehicle -->
            <div class="premium-card">
                <div class="section-title"><i class="fa fa-car"></i> Informations du Véhicule & Service</div>
                
                <div class="premium-grid">
                    <div class="input-group-premium">
                        <label>Type de Service</label>
                        <select class="input-premium" name="service_type" required>
                            <option value="">Sélectionnez un service</option>
                            @foreach(get_all_service_types() as $type)
                                <option value="{{ $type->id }}" {{ $user->service && $user->service->service_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group-premium">
                        <label>Modèle du Véhicule</label>
                        <input type="text" class="input-premium" name="service_model" value="{{ $user->service ? $user->service->service_model : '' }}" placeholder="Ex: Toyota Corolla">
                    </div>

                    <div class="input-group-premium">
                        <label>Numéro de Plaque</label>
                        <input type="text" class="input-premium" name="service_number" value="{{ $user->service ? $user->service->service_number : '' }}" placeholder="Ex: 1234 AB 01">
                    </div>
                    
                    <div class="input-group-premium">
                        <label>Hôpital Assigné</label>
                        <select class="input-premium" name="hospital_id" id="hospital">
                            <option value="0">Aucun / Indépendant</option>
                            @foreach(get_all_hospitals() as $type)
                                <option value="{{ $type->id }}" {{ $user->service && $user->service->hospital_id == $type->id ? 'selected' : '' }}>{{ $type->hospital_address }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group-premium full-width">
                        <label>Document Hôpital</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="document_url" accept="application/pdf, image/*">
                            <i class="fa fa-file-text-o file-upload-icon"></i>
                            <div class="file-upload-text">Cliquez pour importer (PDF, JPG)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <button type="submit" class="btn-premium">
                <i class="fa fa-check-circle"></i> Enregistrer les Modifications
            </button>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof jQuery !== 'undefined'){
            $('.select2-country').select2({
                placeholder: "Sélectionnez un pays",
                allowClear: true,
                width: '100%'
            });
            
            // Script to show file name on selection
            $('input[type="file"]').on('change', function(e){
                if(e.target.files.length > 0) {
                    var fileName = e.target.files[0].name;
                    $(this).siblings('.file-upload-text').text('Sélectionné : ' + fileName);
                }
            });
        }
    });
</script>
@endsection
