@extends('admin.layout.base')

@section('title', 'Suivi des Plannings VTC Actifs')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1"><i class="fa fa-calendar-check-o"></i> Plannings VTC (Trajets Récurrents)</h5>
            <p class="text-muted">Affiche la liste de tous les plannings de transport générés dynamiquement via OSRM.</p>

            <form class="form-inline mb-2" action="{{ route('admin.transport-schedules.index') }}" method="GET">
                <div class="form-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Chercher un passager...">
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a href="{{ route('admin.transport-schedules.index') }}" class="btn btn-default">Réinitialiser</a>
            </form>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Passager</th>
                        <th>Service</th>
                        <th>Trajet & Distance</th>
                        <th>Horaires</th>
                        <th>Jours</th>
                        <th>Prix Mensuel</th>
                        <th>Expire le</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr>
                        <td>
                            @if($schedule->user)
                                <strong>{{ $schedule->user->first_name }} {{ $schedule->user->last_name }}</strong><br>
                                <small>{{ $schedule->user->mobile }}</small>
                            @else
                                <span class="text-danger">Utilisateur supprimé</span>
                            @endif
                        </td>
                        <td>{{ $schedule->serviceType->name ?? 'N/A' }}</td>
                        <td>
                            <small>
                                <strong>De:</strong> {{ Str::limit($schedule->s_address, 30) }}<br>
                                <strong>À:</strong> {{ Str::limit($schedule->d_address, 30) }}
                            </small><br>
                            <span class="badge badge-info">{{ $schedule->distance_km ?? '?' }} km</span>
                        </td>
                        <td>
                            <span class="badge badge-success"><i class="fa fa-arrow-right"></i> {{ substr($schedule->pickup_time, 0, 5) }}</span>
                            @if($schedule->return_time)
                                <br><span class="badge badge-warning mt-1"><i class="fa fa-arrow-left"></i> {{ substr($schedule->return_time, 0, 5) }}</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                @foreach($schedule->active_days ?? [] as $day)
                                    <span class="badge badge-default">{{ $day }}</span>
                                @endforeach
                            </small>
                        </td>
                        <td><strong>{{ number_format($schedule->monthly_price) }} CFA</strong></td>
                        <td>
                            {{ $schedule->expires_at ? \Carbon\Carbon::parse($schedule->expires_at)->format('d/m/Y') : 'N/A' }}
                            @if($schedule->expires_at && \Carbon\Carbon::parse($schedule->expires_at)->isPast())
                                <span class="text-danger">(Expiré)</span>
                            @endif
                        </td>
                        <td>
                            @if($schedule->status == 'ACTIVE')
                                <span class="badge badge-success">Actif</span>
                            @elseif($schedule->status == 'EXPIRED')
                                <span class="badge badge-danger">Expiré</span>
                            @else
                                <span class="badge badge-secondary">{{ $schedule->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Aucun planning actif trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $schedules->appends(['search' => request('search')])->links() }}
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_active'] }}</h3>
                        <p>Plannings Actifs (Valides)</p>
                    </div>
                    <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['expiring_in_3_days'] }}</h3>
                        <p>Expirent d'ici 3 jours</p>
                    </div>
                    <div class="icon"><i class="fa fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_monthly_revenue']) }} CFA</h3>
                        <p>Revenus Mensuels (Plannings Actifs)</p>
                    </div>
                    <div class="icon"><i class="fa fa-money"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
