@extends('admin.layout.base')

@section('title', 'Détails de la campagne')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <!-- Header -->
        <div class="box box-block bg-white mb-2" style="border-radius: 8px;">
            <div class="pull-right">
                <a href="{{ route('admin.ad-campaign.index') }}" class="btn btn-default"><i class="fa fa-angle-left"></i> Retour</a>
                <a href="{{ route('admin.ad-campaign.edit', $campaign->id) }}" class="btn btn-primary"><i class="fa fa-pencil"></i> Modifier</a>
            </div>
            <h5 class="mb-1">Campagne : {{ $campaign->name }}</h5>
            <p class="text-muted" style="margin-bottom: 0;">
                <span class="label label-{{ $campaign->status == 'ACTIVE' ? 'success' : ($campaign->status == 'PAUSED' ? 'warning' : ($campaign->status == 'COMPLETED' ? 'info' : 'default')) }}">{{ $campaign->status }}</span>
                &nbsp;|&nbsp; Type: <strong>{{ $campaign->campaign_type }}</strong>
                &nbsp;|&nbsp; Dates: <strong>{{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'En continu' }}</strong>
            </p>
        </div>

        <!-- KPIs -->
        @php
            $totalImpressions = $campaign->performances->sum('impressions');
            $totalClicks = $campaign->performances->sum('clicks');
            $totalConversions = $campaign->performances->sum('conversions');
            $totalSpent = $campaign->total_spent ?: 0;
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
            $cpc = $totalClicks > 0 ? $totalSpent / $totalClicks : 0;
        @endphp
        <div class="row row-md mb-2">
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2" style="border-radius: 8px;">
                    <div class="t-icon right"><span class="bg-primary"></span><i class="ti-eye"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Impressions</h6>
                        <h1 class="mb-1">{{ number_format($totalImpressions, 0, ',', ' ') }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2" style="border-radius: 8px;">
                    <div class="t-icon right"><span class="bg-success"></span><i class="ti-hand-point-up"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Clics</h6>
                        <h1 class="mb-1">{{ number_format($totalClicks, 0, ',', ' ') }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2" style="border-radius: 8px;">
                    <div class="t-icon right"><span class="bg-warning"></span><i class="ti-target"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">CTR Moyen</h6>
                        <h1 class="mb-1">{{ number_format($ctr, 2) }}%</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2" style="border-radius: 8px;">
                    <div class="t-icon right"><span class="bg-danger"></span><i class="ti-wallet"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Dépensé</h6>
                        <h1 class="mb-1">{{ number_format($totalSpent, 0, ',', ' ') }} <small>FCFA</small></h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Details & Platforms -->
            <div class="col-md-8">
                <div class="box box-block bg-white" style="border-radius: 8px;">
                    <div class="pull-right">
                        <form action="{{ route('admin.ad-campaign.sync-performance', $campaign->id) }}" method="POST">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fa fa-refresh"></i> Actualiser</button>
                        </form>
                    </div>
                    <h5 class="mb-2"><i class="fa fa-bar-chart"></i> Performances par Plateforme</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Plateforme</th>
                                    <th>Statut API</th>
                                    <th>Impressions</th>
                                    <th>Clics</th>
                                    <th>Conversions</th>
                                    <th>Dépense</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaign->platforms as $platform)
                                @php
                                    $perf = $campaign->performances->where('ad_platform_id', $platform->id)->first();
                                @endphp
                                <tr>
                                    <td>
                                        @if($platform->platform == 'IN_APP') <i class="fa fa-mobile fa-lg text-primary"></i> App Interne
                                        @elseif($platform->platform == 'GOOGLE_ADS') <i class="fa fa-google fa-lg text-danger"></i> Google Ads
                                        @elseif($platform->platform == 'FACEBOOK_ADS') <i class="fa fa-facebook-official fa-lg text-info"></i> Facebook
                                        @elseif($platform->platform == 'TIKTOK_ADS') <i class="fa fa-music fa-lg text-dark"></i> TikTok
                                        @else {{ $platform->platform }} @endif
                                    </td>
                                    <td>
                                        <span class="label label-{{ $platform->status == 'ACTIVE' ? 'success' : ($platform->status == 'ERROR' ? 'danger' : 'default') }}">{{ $platform->status }}</span>
                                    </td>
                                    <td class="text-right">{{ $perf ? number_format($perf->impressions, 0, ',', ' ') : 0 }}</td>
                                    <td class="text-right">{{ $perf ? number_format($perf->clicks, 0, ',', ' ') : 0 }}</td>
                                    <td class="text-right">{{ $perf ? number_format($perf->conversions, 0, ',', ' ') : 0 }}</td>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($platform->spent, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Aucune plateforme configurée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="box box-block bg-white mt-2" style="border-radius: 8px;">
                    <h5 class="mb-2"><i class="fa fa-cogs"></i> Configuration & Ciblage</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Budget Total</strong> 
                                    <span class="badge badge-success badge-pill" style="font-size:14px;">{{ number_format($campaign->budget, 0, ',', ' ') }} FCFA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Budget Quotidien</strong> 
                                    <span>{{ $campaign->daily_budget ? number_format($campaign->daily_budget, 0, ',', ' ') . ' FCFA' : 'Non défini' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Annonceur</strong> 
                                    <span>{{ $campaign->user->first_name ?? 'N/A' }} {{ $campaign->user->last_name ?? '' }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Catégorie d'activité</strong> 
                                    <span>{{ $campaign->business_type ?: 'Non spécifié' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Audience ciblée</strong> 
                                    <span style="max-width:60%; text-align:right;">
                                        @if($campaign->target_audience && is_array($campaign->target_audience))
                                            {{ implode(', ', $campaign->target_audience) }}
                                        @else
                                            {{ $campaign->target_audience ?: 'Large / Automatique' }}
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Creative Preview -->
            <div class="col-md-4">
                <div class="box box-block bg-white text-center" style="border-radius: 8px;">
                    <h5 class="mb-2"><i class="fa fa-image"></i> Visuel de la campagne</h5>
                    <hr>
                    @if($campaign->contents->count() > 0)
                        @php $content = $campaign->contents->first(); @endphp
                        
                        <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px dashed #ddd; margin-bottom: 15px;">
                            @if($content->content_type == 'VIDEO' && $content->video_url)
                                <video width="100%" controls style="border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                    <source src="{{ img($content->video_url) }}" type="video/mp4">
                                    Votre navigateur ne supporte pas la vidéo.
                                </video>
                            @elseif($content->image_url)
                                <img src="{{ img($content->image_url) }}" alt="Visuel" style="max-width: 100%; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            @else
                                <div class="text-muted py-3"><i class="fa fa-picture-o fa-3x mb-1"></i><br>Aucun fichier média fourni.</div>
                            @endif
                        </div>
                        
                        @if($content->call_to_action)
                            <div class="mt-2 text-left">
                                <label class="text-muted"><i class="fa fa-link"></i> Lien Call-to-Action (CTA)</label>
                                <a href="{{ $content->call_to_action }}" target="_blank" class="btn btn-success btn-block" style="white-space: normal; word-wrap: break-word;">
                                    Ouvrir la page de destination <i class="fa fa-external-link"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-muted py-5"><i class="fa fa-film fa-4x mb-2"></i><br>Aucun contenu multimédia<br>associé à cette campagne.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

