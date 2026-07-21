@extends('admin.layout.base')

@section('title', 'Comptabilité TVA & Prévisions')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec sélecteur de période -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fa fa-file-invoice"></i> Comptabilité TVA & Prévisions
                    </h3>
                    <div class="card-tools">
                        <form method="GET" class="form-inline">
                            <select name="month" class="form-control form-control-sm mr-2">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="form-control form-control-sm mr-2">
                                @for($y = Carbon\Carbon::now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-sm btn-light">
                                <i class="fa fa-search"></i> Afficher
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerte Échéance -->
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <h5><i class="fa fa-exclamation-triangle"></i> Prochaine Échéance de Déclaration</h5>
                <p class="mb-0">
                    <strong>Date limite :</strong> {{ $nextDeadline['date'] }} 
                    ({{ $nextDeadline['days_remaining'] }} jours restants)
                    <br>
                    <strong>Période concernée :</strong> {{ $nextDeadline['period'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- Statistiques du Mois Actuel -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($currentMonthData->tva_collected ?? 0) }} <small>CFA</small></h3>
                    <p>TVA Collectée</p>
                </div>
                <div class="icon"><i class="fa fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($currentMonthData->total_transactions ?? 0) }}</h3>
                    <p>Transactions</p>
                </div>
                <div class="icon"><i class="fa fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($currentMonthData->effective_rate ?? 0, 2) }}%</h3>
                    <p>Taux Effectif</p>
                </div>
                <div class="icon"><i class="fa fa-percentage"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($currentMonthData->total_commission ?? 0) }} <small>CFA</small></h3>
                    <p>Base Imposable</p>
                </div>
                <div class="icon"><i class="fa fa-calculator"></i></div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <!-- Historique 12 mois -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historique TVA (12 derniers mois)</h3>
                </div>
                <div class="card-body">
                    <canvas id="historicalChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition Annuelle par Trimestre -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TVA par Trimestre {{ $year }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="quarterlyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Prévisions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-chart-line"></i> Prévisions TVA (3 prochains mois)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="forecastChart" height="80"></canvas>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th>Prévision</th>
                                        <th>Tendance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($forecasts as $forecast)
                                    <tr>
                                        <td><strong>{{ $forecast['month'] }}</strong></td>
                                        <td>{{ number_format($forecast['forecast_base']) }} CFA</td>
                                        <td>
                                            @if($forecast['growth_rate'] > 0)
                                                <span class="badge badge-success">
                                                    <i class="fa fa-arrow-up"></i> {{ $forecast['growth_rate'] }}%
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fa fa-arrow-down"></i> {{ abs($forecast['growth_rate']) }}%
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fa fa-info-circle"></i> 
                                    Prévisions basées sur la moyenne mobile et la tendance des 6 derniers mois.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails Mensuels -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Détails du Mois Sélectionné</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Revenu Total</th>
                            <td>{{ number_format($currentMonthData->total_revenue ?? 0) }} CFA</td>
                        </tr>
                        <tr>
                            <th>Commission Totale (Base)</th>
                            <td>{{ number_format($currentMonthData->total_commission ?? 0) }} CFA</td>
                        </tr>
                        <tr>
                            <th>TVA Collectée ({{ $tvaRate }}%)</th>
                            <td><strong>{{ number_format($currentMonthData->tva_collected ?? 0) }} CFA</strong></td>
                        </tr>
                        <tr>
                            <th>TVA Paiements en Ligne</th>
                            <td>{{ number_format($currentMonthData->tva_paid_online ?? 0) }} CFA</td>
                        </tr>
                        <tr>
                            <th>TVA Paiements Cash</th>
                            <td>{{ number_format($currentMonthData->tva_cash ?? 0) }} CFA</td>
                        </tr>
                        <tr>
                            <th>Nombre de Transactions</th>
                            <td>{{ number_format($currentMonthData->total_transactions ?? 0) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Récapitulatif Annuel {{ $year }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>TVA Totale Année</th>
                            <td><strong>{{ number_format($yearlyData->total_tva ?? 0) }} CFA</strong></td>
                        </tr>
                        <tr>
                            <th>Transactions Année</th>
                            <td>{{ number_format($yearlyData->total_transactions ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>Commission Totale Année</th>
                            <td>{{ number_format($yearlyData->total_commission ?? 0) }} CFA</td>
                        </tr>
                    </table>
                    <hr>
                    <h5>Par Trimestre :</h5>
                    <table class="table table-sm">
                        @foreach($yearlyData->quarters as $quarter => $amount)
                        <tr>
                            <th>{{ $quarter }}</th>
                            <td>{{ number_format($amount) }} CFA</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ url('admin/tva-accounting/export?year='.$year.'&month='.$month) }}" 
                       class="btn btn-primary" target="_blank">
                        <i class="fa fa-file-excel"></i> Exporter Rapport Détaillé
                    </a>
                    <a href="{{ url('admin/tva-accounting/pdf?year='.$year.'&month='.$month) }}" 
                       class="btn btn-danger">
                        <i class="fa fa-file-pdf"></i> Générer PDF
                    </a>
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="fa fa-print"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Graphique Historique
var ctxHistorical = document.getElementById('historicalChart').getContext('2d');
var historicalChart = new Chart(ctxHistorical, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($historicalData, 'month')) !!},
        datasets: [{
            label: 'TVA Collectée (CFA)',
            data: {!! json_encode(array_column($historicalData, 'tva_collected')) !!},
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Graphique Trimestriel
var ctxQuarterly = document.getElementById('quarterlyChart').getContext('2d');
var quarterlyChart = new Chart(ctxQuarterly, {
    type: 'doughnut',
    data: {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [{
            data: [
                {{ $yearlyData->quarters['Q1'] ?? 0 }},
                {{ $yearlyData->quarters['Q2'] ?? 0 }},
                {{ $yearlyData->quarters['Q3'] ?? 0 }},
                {{ $yearlyData->quarters['Q4'] ?? 0 }}
            ],
            backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Graphique Prévisions
var ctxForecast = document.getElementById('forecastChart').getContext('2d');
var forecastChart = new Chart(ctxForecast, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($forecasts, 'month')) !!},
        datasets: [
            {
                label: 'Scénario Pessimiste',
                data: {!! json_encode(array_column($forecasts, 'forecast_pessimistic')) !!},
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: '#dc3545',
                borderWidth: 1
            },
            {
                label: 'Prévision Base',
                data: {!! json_encode(array_column($forecasts, 'forecast_base')) !!},
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: '#28a745',
                borderWidth: 2
            },
            {
                label: 'Scénario Optimiste',
                data: {!! json_encode(array_column($forecasts, 'forecast_optimistic')) !!},
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: '#007bff',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endsection
@endsection
