@extends('admin.layout.base')

@section('title', 'Campagnes Publicitaires')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif 
            <h5 class="mb-1">Campagnes Publicitaires</h5>
            <a href="{{ route('admin.ad-campaign.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right">
                <i class="fa fa-plus"></i> Créer une campagne
            </a>
            <button type="button" class="btn btn-info pull-right" data-toggle="modal" data-target="#pricingModal">
                <i class="fa fa-cog"></i> Tarification Interne
            </button>
            <table class="table table-striped table-bordered dataTable" id="table-campaigns">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Budget</th>
                        <th>Dépensé</th>
                        <th>Plateformes</th>
                        <th>Dates</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($campaigns as $campaign)
                    <tr>
                        <td>{{ $campaign->id }}</td>
                        <td>{{ $campaign->name }}</td>
                        <td>{{ $campaign->user->first_name ?? 'N/A' }} {{ $campaign->user->last_name ?? '' }}</td>
                        <td>
                            @if($campaign->campaign_type == 'BRAND_AWARENESS')
                                <span class="label label-info">Notoriété</span>
                            @elseif($campaign->campaign_type == 'LEAD_GENERATION')
                                <span class="label label-primary">Génération de leads</span>
                            @elseif($campaign->campaign_type == 'SALES')
                                <span class="label label-success">Ventes</span>
                            @elseif($campaign->campaign_type == 'TRAFFIC')
                                <span class="label label-warning">Trafic</span>
                            @else
                                <span class="label label-default">Engagement</span>
                            @endif
                        </td>
                        <td>
                            @if($campaign->status == 'ACTIVE')
                                <span class="label label-success">Active</span>
                            @elseif($campaign->status == 'PAUSED')
                                <span class="label label-warning">En pause</span>
                            @elseif($campaign->status == 'COMPLETED')
                                <span class="label label-info">Terminée</span>
                            @elseif($campaign->status == 'CANCELLED')
                                <span class="label label-danger">Annulée</span>
                            @else
                                <span class="label label-default">Brouillon</span>
                            @endif
                        </td>
                        <td>{{ number_format($campaign->budget, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($campaign->total_spent, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @foreach($campaign->platforms as $platform)
                                @if($platform->platform == 'IN_APP')
                                    <span class="label label-primary" title="{{ $platform->status }}"><i class="fa fa-mobile"></i> App Interne</span>
                                @elseif($platform->platform == 'GOOGLE_ADS')
                                    <span class="label label-danger" title="{{ $platform->status }}"><i class="fa fa-google"></i> Google</span>
                                @elseif($platform->platform == 'FACEBOOK_ADS')
                                    <span class="label label-info" title="{{ $platform->status }}"><i class="fa fa-facebook"></i> Facebook</span>
                                @elseif($platform->platform == 'TIKTOK_ADS')
                                    <span class="label label-default" style="background-color: #000; color:#fff;" title="{{ $platform->status }}">TikTok</span>
                                @else
                                    <span class="label label-default">{{ $platform->platform }}</span>
                                @endif
                                
                                @if($platform->status == 'ERROR')
                                    <i class="fa fa-exclamation-triangle text-danger" title="Erreur API"></i>
                                @endif
                                <br/>
                            @endforeach
                        </td>
                        <td>
                            {{ $campaign->start_date->format('d/m/Y') }}
                            @if($campaign->end_date)
                                - {{ $campaign->end_date->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.ad-campaign.destroy', $campaign->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.ad-campaign.show', $campaign->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Voir
                                </a>
                                <a href="{{ route('admin.ad-campaign.edit', $campaign->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-pencil"></i> Modifier
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?')">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tarification Interne -->
<div class="modal fade" id="pricingModal" tabindex="-1" role="dialog" aria-labelledby="pricingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.ad-campaign.settings') }}" method="POST">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="pricingModalLabel"><i class="fa fa-money"></i> Paramètres de Tarification (App Interne)</h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2">Ces paramètres définissent la facturation des publicités diffusées au sein de l'application.</p>
                    
                    <div class="form-group">
                        <label for="ad_in_app_cpc">Coût Par Clic (CPC) - <em>Pour campagnes au trafic/ventes</em></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="ad_in_app_cpc" name="ad_in_app_cpc" value="{{ Setting::get('ad_in_app_cpc', 50) }}" required min="0">
                            <span class="input-group-addon">FCFA</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ad_in_app_cpm">Coût Pour Mille vues (CPM) - <em>Pour campagnes de notoriété</em></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="ad_in_app_cpm" name="ad_in_app_cpm" value="{{ Setting::get('ad_in_app_cpm', 1000) }}" required min="0">
                            <span class="input-group-addon">FCFA</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les tarifs</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

