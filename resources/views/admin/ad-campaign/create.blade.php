@extends('admin.layout.base')

@section('title', 'Créer une campagne publicitaire')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.ad-campaign.index') }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Créer une nouvelle campagne publicitaire</h5>

            <form class="form-horizontal" action="{{ route('admin.ad-campaign.store') }}" method="POST" role="form" enctype="multipart/form-data">
                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="user_id" class="col-xs-12 col-form-label">Client *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">Sélectionner un client</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom de la campagne *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Ex: Campagne été 2024">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="campaign_type" class="col-xs-12 col-form-label">Type de campagne *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="campaign_type" name="campaign_type" required>
                            <option value="BRAND_AWARENESS" {{ old('campaign_type') == 'BRAND_AWARENESS' ? 'selected' : '' }}>Notoriété de marque</option>
                            <option value="LEAD_GENERATION" {{ old('campaign_type') == 'LEAD_GENERATION' ? 'selected' : '' }}>Génération de leads</option>
                            <option value="SALES" {{ old('campaign_type') == 'SALES' ? 'selected' : '' }}>Ventes</option>
                            <option value="TRAFFIC" {{ old('campaign_type') == 'TRAFFIC' ? 'selected' : '' }}>Trafic</option>
                            <option value="ENGAGEMENT" {{ old('campaign_type') == 'ENGAGEMENT' ? 'selected' : '' }}>Engagement</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-xs-12 col-form-label">Réseaux de diffusion *</label>
                    <div class="col-xs-10">
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="IN_APP" checked> Application PicMe225 (Interne)
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="GOOGLE_ADS"> Google Ads
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="FACEBOOK_ADS"> Facebook Ads
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="TIKTOK_ADS"> TikTok Ads
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="business_type" class="col-xs-12 col-form-label">Secteur d'activité (Optionnel)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('business_type') }}" name="business_type" id="business_type" placeholder="Ex: Restaurant, VTC, Beauté...">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="target_audience" class="col-xs-12 col-form-label">Audience cible (Mots-clés séparés par des virgules)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('target_audience') }}" name="target_audience" id="target_audience" placeholder="Ex: Jeunes, Abidjan, Etudiants">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="budget" class="col-xs-12 col-form-label">Budget total (FCFA) *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('budget') }}" name="budget" required id="budget" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="daily_budget" class="col-xs-12 col-form-label">Budget quotidien (FCFA)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('daily_budget') }}" name="daily_budget" id="daily_budget" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="start_date" class="col-xs-12 col-form-label">Date de début *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="date" value="{{ old('start_date') }}" name="start_date" required id="start_date">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="end_date" class="col-xs-12 col-form-label">Date de fin</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="date" value="{{ old('end_date') }}" name="end_date" id="end_date">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-12 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ad_slots" class="col-xs-12 col-form-label">Emplacement Publicitaire *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="ad_slots" name="ad_slots[]" multiple required>
                            <option value="">Sélectionner un emplacement</option>
                            @foreach(\App\Models\AdSlot::all() as $slot)
                                <option value="{{ $slot->id }}" {{ (is_array(old('ad_slots')) && in_array($slot->id, old('ad_slots'))) == $slot->id ? 'selected' : '' }}>
                                    {{ $slot->name }} ({{ $slot->width }}x{{ $slot->height }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="media_file" class="col-xs-12 col-form-label">Image ou Vidéo (Media) *</label>
                    <div class="col-xs-10">
                        <input type="file" accept="image/*,video/*" name="media_file" class="dropify form-control-file" id="media_file" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="call_to_action" class="col-xs-12 col-form-label">Lien de redirection (Call to Action)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="url" value="{{ old('call_to_action') }}" name="call_to_action" id="call_to_action" placeholder="https://example.com">
                    </div>
                </div>


                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.ad-campaign.index') }}" class="btn btn-danger btn-block">Annuler</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Créer la campagne</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

