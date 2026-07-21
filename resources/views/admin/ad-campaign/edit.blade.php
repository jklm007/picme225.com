@extends('admin.layout.base')

@section('title', 'Modifier la campagne')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.ad-campaign.index') }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Modifier la campagne: {{ $campaign->name }}</h5>

            <form class="form-horizontal" action="{{ route('admin.ad-campaign.update', $campaign->id) }}" method="POST" role="form" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom de la campagne *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name', $campaign->name) }}" name="name" required id="name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="campaign_type" class="col-xs-12 col-form-label">Type de campagne *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="campaign_type" name="campaign_type" required>
                            <option value="BRAND_AWARENESS" {{ old('campaign_type', $campaign->campaign_type) == 'BRAND_AWARENESS' ? 'selected' : '' }}>Notoriété de marque</option>
                            <option value="LEAD_GENERATION" {{ old('campaign_type', $campaign->campaign_type) == 'LEAD_GENERATION' ? 'selected' : '' }}>Génération de leads</option>
                            <option value="SALES" {{ old('campaign_type', $campaign->campaign_type) == 'SALES' ? 'selected' : '' }}>Ventes</option>
                            <option value="TRAFFIC" {{ old('campaign_type', $campaign->campaign_type) == 'TRAFFIC' ? 'selected' : '' }}>Trafic</option>
                            <option value="ENGAGEMENT" {{ old('campaign_type', $campaign->campaign_type) == 'ENGAGEMENT' ? 'selected' : '' }}>Engagement</option>
                        </select>
                    </div>
                </div>

                @php
                    $selectedPlatforms = $campaign->platforms->pluck('platform')->toArray();
                @endphp
                <div class="form-group row">
                    <label class="col-xs-12 col-form-label">Réseaux de diffusion *</label>
                    <div class="col-xs-10">
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="IN_APP" {{ in_array('IN_APP', $selectedPlatforms) || empty($selectedPlatforms) ? 'checked' : '' }}> Application PicMe225 (Interne)
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="GOOGLE_ADS" {{ in_array('GOOGLE_ADS', $selectedPlatforms) ? 'checked' : '' }}> Google Ads
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="FACEBOOK_ADS" {{ in_array('FACEBOOK_ADS', $selectedPlatforms) ? 'checked' : '' }}> Facebook Ads
                            </label>
                        </div>
                        <div class="checkbox-inline" style="margin-right: 15px;">
                            <label>
                                <input type="checkbox" name="platforms[]" value="TIKTOK_ADS" {{ in_array('TIKTOK_ADS', $selectedPlatforms) ? 'checked' : '' }}> TikTok Ads
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="business_type" class="col-xs-12 col-form-label">Secteur d'activité (Optionnel)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('business_type', $campaign->business_type ?? '') }}" name="business_type" id="business_type" placeholder="Ex: Restaurant, VTC, Beauté...">
                    </div>
                </div>

                @php
                    $targetAudienceStr = '';
                    if (is_array($campaign->target_audience)) {
                        $targetAudienceStr = implode(', ', $campaign->target_audience);
                    } elseif (is_string($campaign->target_audience)) {
                        $targetAudienceStr = $campaign->target_audience;
                    }
                @endphp
                <div class="form-group row">
                    <label for="target_audience" class="col-xs-12 col-form-label">Audience cible (Mots-clés séparés par des virgules)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('target_audience', $targetAudienceStr) }}" name="target_audience" id="target_audience" placeholder="Ex: Jeunes, Abidjan, Etudiants">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="status" class="col-xs-12 col-form-label">Statut *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="status" name="status" required>
                            <option value="DRAFT" {{ old('status', $campaign->status) == 'DRAFT' ? 'selected' : '' }}>Brouillon</option>
                            <option value="ACTIVE" {{ old('status', $campaign->status) == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="PAUSED" {{ old('status', $campaign->status) == 'PAUSED' ? 'selected' : '' }}>En pause</option>
                            <option value="COMPLETED" {{ old('status', $campaign->status) == 'COMPLETED' ? 'selected' : '' }}>Terminée</option>
                            <option value="CANCELLED" {{ old('status', $campaign->status) == 'CANCELLED' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="budget" class="col-xs-12 col-form-label">Budget total (FCFA) *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('budget', $campaign->budget) }}" name="budget" required id="budget" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="daily_budget" class="col-xs-12 col-form-label">Budget quotidien (FCFA)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('daily_budget', $campaign->daily_budget) }}" name="daily_budget" id="daily_budget" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="start_date" class="col-xs-12 col-form-label">Date de début *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="date" value="{{ old('start_date', $campaign->start_date->format('Y-m-d')) }}" name="start_date" required id="start_date">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="end_date" class="col-xs-12 col-form-label">Date de fin</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="date" value="{{ old('end_date', $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '') }}" name="end_date" id="end_date">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-12 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $campaign->description) }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ad_slots" class="col-xs-12 col-form-label">Emplacement Publicitaire *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="ad_slots" name="ad_slots[]" multiple required>
                            <option value="">Sélectionner un emplacement</option>
                            @php
                                $selected_slots = old('ad_slots', $campaign->adSlots->pluck('id')->toArray());
                            @endphp
                            @foreach(\App\Models\AdSlot::all() as $slot)
                                <option value="{{ $slot->id }}" {{ in_array($slot->id, $selected_slots) ? 'selected' : '' }}>
                                    {{ $slot->name }} ({{ $slot->width }}x{{ $slot->height }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="media_file" class="col-xs-12 col-form-label">Image ou Vidéo (Media) (Optionnel)</label>
                    <div class="col-xs-10">
                        <input type="file" accept="image/*,video/*" name="media_file" class="dropify form-control-file" id="media_file">
                        <small class="text-muted">Laissez vide si vous ne souhaitez pas modifier le média existant.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="call_to_action" class="col-xs-12 col-form-label">Lien de redirection (Call to Action)</label>
                    <div class="col-xs-10">
                        @php
                            $content = $campaign->contents->first();
                            $default_cta = $content ? $content->call_to_action : '';
                        @endphp
                        <input class="form-control" type="url" value="{{ old('call_to_action', $default_cta) }}" name="call_to_action" id="call_to_action" placeholder="https://example.com">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.ad-campaign.index') }}" class="btn btn-danger btn-block">Annuler</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

