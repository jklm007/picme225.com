@extends('admin.layout.base')

@section('title', 'Gestion de la Trésorerie & Liquidité')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">

            <div class="row row-md">
                <div class="col-lg-6 col-md-6 col-xs-12">
                    <div class="box box-block bg-white tile tile-1 mb-2">
                        <div class="t-icon right"><i class="ti-money"></i></div>
                        <div class="t-content">
                            <h6 class="text-uppercase mb-1">Passif Virtuel (Total ECO/CFA)</h6>
                            <h1 class="mb-1 text-warning">{{ currency($totalVirtualEco) }}</h1>
                            <span>Somme due aux chauffeurs (Cashback + Commissions)</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-xs-12">
                    <div class="box box-block bg-white tile tile-1 mb-2">
                        <div class="t-icon right"><i class="ti-wallet"></i></div>
                        <div class="t-content">
                            <h6 class="text-uppercase mb-1">Réserve Physique (CFA)</h6>
                            <h1 class="mb-1 text-success">{{ currency($physicalCfaReserve) }}</h1>
                            <span>Liquidité réelle disponible en banque/MM</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-block bg-white">
                        <h5 class="mb-1">Ratio de Solvabilité</h5>
                        <div class="progress progress-md mb-1">
                            <div class="progress-bar @if($liquidityRatio >= 100) progress-bar-success @elseif($liquidityRatio >= 50) progress-bar-warning @else progress-bar-danger @endif"
                                role="progressbar" aria-valuenow="{{ $liquidityRatio }}" aria-valuemin="0"
                                aria-valuemax="100" style="width: {{ min($liquidityRatio, 100) }}%;">
                                {{ number_format($liquidityRatio, 2) }}%
                            </div>
                        </div>
                        <p class="text-muted">
                            Un ratio de 100% signifie que chaque ECO/CFA virtuel est couvert par 1 CFA réel.
                            <strong>Statut :</strong>
                            @if($liquidityRatio >= 100)
                                <span class="text-success">Totalement couvert</span>
                            @elseif($liquidityRatio >= 50)
                                <span class="text-warning">Couverture partielle (Attention)</span>
                            @else
                                <span class="text-danger">Risque d'insolvabilité ! Injectez des fonds.</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="box box-block bg-white">
                <h5 class="mb-1">Mise à jour de la Réserve Physique</h5>
                <form action="{{ route('admin.treasury.update') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label class="col-xs-12 col-form-label">Montant actuel en Banque / Mobile Money (CFA)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" name="physical_cfa_reserve"
                                value="{{ $physicalCfaReserve }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-10">
                            <button type="submit" class="btn btn-primary">Mettre à jour la réserve</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection