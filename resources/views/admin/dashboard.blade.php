@extends('admin.layout.base')

@section('title', 'Dashboard ')

@section('styles')
        <link rel="stylesheet" href="{{asset('main/vendor/jvectormap/jquery-jvectormap-2.0.3.css')}}">
@endsection

@section('content')

<div class="content-area py-1">
<div class="container-fluid">
    <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.Rides')</h6>
                                        <h1 class="mb-1">{{$rides->count()}}</h1>
                                        <span class="tag tag-danger mr-0-5">
                                            @if($rides->count() > 0)
                                                {{round(($cancel_rides / $rides->count()) * 100, 2)}}%
                                            @else
                                                0%
                                            @endif
                                        </span>
                                        <span class="text-muted font-90">% down from cancelled Request</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.Revenue')</h6>
                                        <h1 class="mb-1">{{currency($revenue)}}</h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>from {{$rides->count()}} Rides</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.service')</h6>
                                        <h1 class="mb-1">{{$service}}</h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
                                <div class="t-content">
                                        <h1 class="mb-1">{{$cancel_rides}}</h1>
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.total_rides')</h6>
                                        <i class="fa fa-caret-down text-danger mr-0-5"></i><span>for 
                                            @if($rides->count() > 0)
                                                {{round(($cancel_rides / $rides->count()) * 100, 2)}}%
                                            @else
                                                0%
                                            @endif
                                        Rides</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-shopping-cart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Annonces Marketplace</h6>
                                        <h1 class="mb-1">{{$marketplace_count}}</h1>
                                        <a href="{{ route('admin.marketplace-listings.index') }}" class="text-muted">Gérer les articles →</a>
                                </div>
                        </div>
                </div>
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-ticket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Billets Vendus (Total)</h6>
                                        <h1 class="mb-1">{{$tickets_sold}}</h1>
                                        <span class="text-muted">Volume d'activité événementielle</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-4 col-md-4 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-info"></span><i class="ti-money"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Commissions Marketplace</h6>
                                        <h1 class="mb-1">{{currency($marketplace_commission)}}</h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>Sur {{currency($marketplace_revenue)}} de CA</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-12 col-md-12 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-announcement"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Actualités Publiées</h6>
                                        <h1 class="mb-1">{{$news_count}}</h1>
                                        <a href="{{ route('admin.news.index') }}" class="text-muted">Gérer les flash infos →</a>
                                </div>
                        </div>
                </div>
        </div>

        <h5 class="mb-1">💰 Rentabilité Gateway P2P & Robot</h5>
        <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #3e70c9;">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-import"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Recharges P2P (Total)</h6>
                                        <h1 class="mb-1">{{currency($p2p_deposits)}}</h1>
                                        <span class="text-muted">Volume via Gateway</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #43b968;">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-gift"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Économies (vs 3.5%)</h6>
                                        <h1 class="mb-1 text-success">{{currency($p2p_savings)}}</h1>
                                        <span class="tag tag-success">Argent économisé</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #f59345;">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-export"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Commissions Retrait (2%)</h6>
                                        <h1 class="mb-1">{{currency($p2p_commissions)}}</h1>
                                        <span class="text-muted">Gagné sur les chauffeurs</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #f44236; background: linear-gradient(to right, #ffffff, #fff5f5);">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-money"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Bénéfice Net P2P</h6>
                                        <h1 class="mb-1 text-danger">{{currency($p2p_net_profit)}}</h1>
                                        <i class="fa fa-caret-up text-success mr-0-5"></i><span>Profit réel estimé</span>
                                </div>
                        </div>
                </div>
        </div>

        <h5 class="mb-1">📊 Quotas des APIs Cartographiques (Mois en cours)</h5>
        <div class="row row-md">
                <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #3e70c9;">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-map-alt"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Appels API Mapbox (Générateur de routes)</h6>
                                        <h1 class="mb-1">{{$mapbox_calls}} / {{$mapbox_limit}}</h1>
                                        <div class="progress progress-sm mb-0-5" style="height: 10px;">
                                            @php
                                                $mapboxPercent = min(100, ($mapbox_limit > 0 ? ($mapbox_calls / $mapbox_limit) * 100 : 0));
                                                $mapboxColor = $mapboxPercent > 90 ? '#f44236' : ($mapboxPercent > 70 ? '#f59345' : '#43b968');
                                            @endphp
                                            <div class="progress-bar" role="progressbar" style="width: {{$mapboxPercent}}%; background-color: {{ $mapboxColor }};"></div>
                                        </div>
                                        <span class="text-muted font-90">Bascule automatique vers OSRM à {{$mapbox_limit}} requêtes</span>
                                </div>
                        </div>
                </div>
                <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2" style="border-top: 4px solid #ea6b49;">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-google"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">Appels API Google Maps (Directions)</h6>
                                        <h1 class="mb-1">{{$google_calls}} / {{$google_limit}}</h1>
                                        <div class="progress progress-sm mb-0-5" style="height: 10px;">
                                            @php
                                                $googlePercent = min(100, ($google_limit > 0 ? ($google_calls / $google_limit) * 100 : 0));
                                                $googleColor = $googlePercent > 90 ? '#f44236' : ($googlePercent > 70 ? '#f59345' : '#43b968');
                                            @endphp
                                            <div class="progress-bar" role="progressbar" style="width: {{$googlePercent}}%; background-color: {{ $googleColor }};"></div>
                                        </div>
                                        <span class="text-muted font-90">Bascule automatique vers OSRM à {{$google_limit}} requêtes</span>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md">
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.cancel_count')</h6>
                                        <h1 class="mb-1">{{$user_cancelled}}</h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.provider_cancel_count')</h6>
                                        <h1 class="mb-1">{{$provider_cancelled}}</h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-rocket"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.fleets')</h6>
                                        <h1 class="mb-1">{{$fleet}}</h1>
                                </div>
                        </div>
                </div>
                <div class="col-lg-3 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                        <h6 class="text-uppercase mb-1">@lang('admin.dashboard.scheduled')</h6>
                                        <h1 class="mb-1">{{$scheduled_rides}}</h1>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row row-md mb-2">
                <div class="col-md-12">
                                <div class="box bg-white">
                                        <div class="box-block clearfix">
                                                <h5 class="float-xs-left">@lang('admin.dashboard.Recent_Rides')</h5>
                                                <div class="float-xs-right">
                                                        <button class="btn btn-link btn-sm text-muted" type="button"><i class="ti-close"></i></button>
                                                </div>
                                        </div>
                                        <table class="table mb-md-0">
                                                <tbody>
                                                @foreach($rides->take(10) as $index => $ride)
                                                        <tr>
                                                                <th scope="row">{{$index + 1}}</th>
                                                                <td>{{$ride->user->first_name}} {{$ride->user->last_name}}</td>
                                                                <td>
                                                                        @if($ride->status != "CANCELLED")
                                                                                <a class="text-primary" href="{{route('admin.requests.show',$ride->id)}}"><span class="underline">@lang('admin.dashboard.View_Ride_Details')</span></a>
                                                                        @else
                                                                                <span>@lang('admin.dashboard.No_Details_Found') </span>
                                                                        @endif
                                                                </td>
                                                                <td>
                                                                        <span class="text-muted">{{$ride->created_at->diffForHumans()}}</span>
                                                                </td>
                                                                <td>
                                                                        @if($ride->status == "COMPLETED")
                                                                                <span class="tag tag-success">{{$ride->status}}</span>
                                                                        @elseif($ride->status == "CANCELLED")
                                                                                <span class="tag tag-danger">{{$ride->status}}</span>
                                                                        @else
                                                                                <span class="tag tag-info">{{$ride->status}}</span>
                                                                        @endif
                                                                </td>
                                                        </tr>
                                                @endforeach
                                                </tbody>
                                        </table>
                                </div>
                        </div>

                </div>

        </div>
</div>
@endsection
