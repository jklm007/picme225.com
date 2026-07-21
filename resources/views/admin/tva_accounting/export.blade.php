@extends('admin.layout.base')

@section('title', 'Export Rapport TVA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        Rapport TVA Détaillé - {{ Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
                    </h3>
                    <div class="card-tools">
                        <button onclick="window.print()" class="btn btn-sm btn-light">
                            <i class="fa fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- En-tête du Rapport -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>PICME225 - Plateforme de Transport</h4>
                            <p>
                                <strong>Période :</strong> {{ Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}<br>
                                <strong>Date d'édition :</strong> {{ Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                                <strong>Taux TVA :</strong> {{ Setting::get('dao_tva_percentage', 18) }}%
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h5>Récapitulatif</h5>
                            <table class="table table-sm table-bordered" style="width: auto; margin-left: auto;">
                                <tr>
                                    <th>Base Imposable</th>
                                    <td>{{ number_format($data->total_commission ?? 0) }} CFA</td>
                                </tr>
                                <tr>
                                    <th>TVA Collectée</th>
                                    <td><strong>{{ number_format($data->tva_collected ?? 0) }} CFA</strong></td>
                                </tr>
                                <tr>
                                    <th>Transactions</th>
                                    <td>{{ number_format($data->total_transactions ?? 0) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Détail des Transactions -->
                    <h5>Détail des Transactions</h5>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Chauffeur</th>
                                <th>Montant HT</th>
                                <th>Commission</th>
                                <th>TVA</th>
                                <th>Mode Paiement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $index => $transaction)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $transaction->request->user->first_name ?? 'N/A' }}</td>
                                <td>{{ $transaction->request->provider->first_name ?? 'N/A' }}</td>
                                <td>{{ number_format($transaction->total) }} CFA</td>
                                <td>{{ number_format($transaction->provider_commission) }} CFA</td>
                                <td>{{ number_format($transaction->tva_fee) }} CFA</td>
                                <td>
                                    @if($transaction->payment_mode == 'CARD')
                                        <span class="badge badge-success">Carte</span>
                                    @else
                                        <span class="badge badge-warning">Cash</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="4">TOTAL</td>
                                <td>{{ number_format($transactions->sum('total')) }} CFA</td>
                                <td>{{ number_format($transactions->sum('provider_commission')) }} CFA</td>
                                <td>{{ number_format($transactions->sum('tva_fee')) }} CFA</td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Signature -->
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <p>
                                <strong>Certifié conforme</strong><br>
                                Le {{ Carbon\Carbon::now()->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p>
                                <strong>Signature et Cachet</strong><br><br><br>
                                _______________________
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header .card-tools,
    .sidebar,
    .main-header,
    .main-footer {
        display: none !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
@endsection
