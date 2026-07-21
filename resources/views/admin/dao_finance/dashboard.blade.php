@extends('admin.layout.base')

@section('title', 'Finances DAO - Répartition')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-chart-pie"></i> Dashboard Financier DAO
                        </h3>
                        <div class="card-tools">
                            <form method="GET" class="form-inline">
                                <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette Semaine</option>
                                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce Mois</option>
                                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Cette Année</option>
                                    <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Tout</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Statistiques Globales -->
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ number_format($financialData->total_trips ?? 0) }}</h3>
                                        <p>Courses Totales</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-car"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ number_format($financialData->total_revenue ?? 0) }} <small>CFA</small></h3>
                                        <p>Revenu Total</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ number_format($financialData->total_commission ?? 0) }} <small>CFA</small>
                                        </h3>
                                        <p>Commission Totale</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-percentage"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3>{{ number_format($financialData->total_treasury ?? 0) }} <small>CFA</small></h3>
                                        <p>Trésorerie DAO</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-vault"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- Graphique de Répartition -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Répartition de la Commission</h3>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="distributionChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Détails de Répartition</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Bénéficiaire</th>
                                                    <th>Pourcentage</th>
                                                    <th>Montant (CFA)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><i class="fa fa-university text-danger"></i> TVA (État)</td>
                                                    <td>{{ $currentConfig['tva'] }}%</td>
                                                    <td>{{ number_format($financialData->total_tva ?? 0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fa fa-shield-alt text-primary"></i> Assurance DAO</td>
                                                    <td>{{ $currentConfig['insurance'] }}%</td>
                                                    <td>{{ number_format($financialData->total_insurance ?? 0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fa fa-users text-info"></i> Syndicat</td>
                                                    <td>{{ $currentConfig['syndicate'] }}%</td>
                                                    <td>{{ number_format($financialData->total_syndicate ?? 0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fa fa-handshake text-success"></i> Coopérative</td>
                                                    <td>{{ $currentConfig['cooperative'] }}%</td>
                                                    <td>{{ number_format($financialData->total_cooperative ?? 0) }}</td>
                                                </tr>
                                                <tr class="font-weight-bold">
                                                    <td><i class="fa fa-vault text-warning"></i> Trésorerie DAO</td>
                                                    <td>{{ 100 - array_sum($currentConfig) }}%</td>
                                                    <td>{{ number_format($financialData->total_treasury ?? 0) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Commission par Niveau d'Abonnement -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Commission par Niveau d'Abonnement</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Niveau</th>
                                                    <th>Nombre de Courses</th>
                                                    <th>Commission Totale</th>
                                                    <th>Commission Moyenne</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($commissionByLevel as $level)
                                                    <tr>
                                                        <td>
                                                            @if($level->subscription_level == 'gold')
                                                                <span class="badge badge-warning">🏆 GOLD</span>
                                                            @elseif($level->subscription_level == 'pro')
                                                                <span class="badge badge-primary">⭐ PRO</span>
                                                            @elseif($level->subscription_level == 'eco')
                                                                <span class="badge badge-success">🌿 ECO/CFA</span>
                                                            @elseif($level->subscription_level == 'standard')
                                                                <span class="badge badge-info">📦 STANDARD</span>
                                                            @else
                                                                <span class="badge badge-secondary">❌ NONE</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($level->trip_count) }}</td>
                                                        <td>{{ number_format($level->total_commission) }} CFA</td>
                                                        <td>{{ number_format($level->avg_commission, 2) }} CFA</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration des Pourcentages -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">⚙️ Configuration de Répartition</h3>
                                    </div>
                                    <form method="POST" action="{{ url('admin/dao-finance/update-distribution') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>TVA (%)</label>
                                                        <input type="number" step="0.01" name="dao_tva_percentage"
                                                            class="form-control" value="{{ $currentConfig['tva'] }}"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Assurance (%)</label>
                                                        <input type="number" step="0.01" name="dao_insurance_percentage"
                                                            class="form-control" value="{{ $currentConfig['insurance'] }}"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Syndicat (%)</label>
                                                        <input type="number" step="0.01" name="dao_syndicate_percentage"
                                                            class="form-control" value="{{ $currentConfig['syndicate'] }}"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Coopérative (%)</label>
                                                        <input type="number" step="0.01" name="dao_cooperative_percentage"
                                                            class="form-control" value="{{ $currentConfig['cooperative'] }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i>
                                                Tous les pourcentages sont calculés sur la <strong>commission</strong>, pas
                                                sur le montant total de la course.
                                                Le reste va automatiquement à la Trésorerie DAO.
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Enregistrer la Configuration
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Plans d'Abonnement -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Plans d'Abonnement Actifs</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Plan</th>
                                                    <th>Prix Mensuel</th>
                                                    <th>Type Commission</th>
                                                    <th>Valeur Commission</th>
                                                    <th>Priorité</th>
                                                    <th>Chauffeurs Actifs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subscriptionPlans as $plan)
                                                    <tr>
                                                        <td><strong>{{ $plan->name }}</strong></td>
                                                        <td>{{ number_format($plan->price) }} CFA</td>
                                                        <td>
                                                            @if($plan->commission_type == 'fixed')
                                                                <span class="badge badge-success">Fixe</span>
                                                            @else
                                                                <span class="badge badge-info">Pourcentage</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($plan->commission_type == 'fixed')
                                                                {{ number_format($plan->commission_value) }} CFA
                                                            @else
                                                                {{ $plan->commission_value }}%
                                                            @endif
                                                        </td>
                                                        <td>{{ $plan->priority }}</td>
                                                        <td>
                                                            @php
                                                                $count = $subscriptionStats->where('subscription_level', strtolower($plan->name))->first();
                                                            @endphp
                                                            {{ $count ? $count->count : 0 }}
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
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            // Graphique de répartition
            var ctx = document.getElementById('distributionChart').getContext('2d');
            var distributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['TVA', 'Assurance', 'Syndicat', 'Coopérative', 'Trésorerie DAO'],
                    datasets: [{
                        data: [
                        {{ $financialData->total_tva ?? 0 }},
                        {{ $financialData->total_insurance ?? 0 }},
                        {{ $financialData->total_syndicate ?? 0 }},
                        {{ $financialData->total_cooperative ?? 0 }},
                            {{ $financialData->total_treasury ?? 0 }}
                        ],
                        backgroundColor: [
                            '#dc3545',
                            '#007bff',
                            '#17a2b8',
                            '#28a745',
                            '#ffc107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        </script>
    @endsection
@endsection