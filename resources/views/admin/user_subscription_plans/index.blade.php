@extends('admin.layout.base')

@section('title', 'Gestion des Abonnements Utilisateurs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-users"></i> Abonnements Passagers
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-subscription-plans.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Nouveau Plan Utilisateur
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Période</th>
                                <th>Prix</th>
                                <th>Commission</th>
                                <th>Priorité</th>
                                <th>Assurance</th>
                                <th>Passagers Actifs</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                            <tr>
                                <td><strong>{{ $plan->name }}</strong></td>
                                <td>{{ $plan->period }}</td>
                                <td><strong>{{ number_format($plan->price) }} CFA</strong></td>
                                <td>
                                    @if($plan->commission_type == 'fixed')
                                        <span class="badge badge-success">{{ number_format($plan->commission_value) }} CFA fixe</span>
                                    @else
                                        <span class="badge badge-info">{{ $plan->commission_value }}%</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: {{ $plan->priority }}%">
                                            {{ $plan->priority }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($plan->insurance_included)
                                        <span class="badge badge-success">✅ Incluse</span>
                                    @else
                                        <span class="badge badge-danger">❌ Non</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $plan->active_subscribers ?? 0 }} passager(s)
                                    </span>
                                </td>
                                <td>
                                    @if($plan->status)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.user-subscription-plans.edit', $plan->id) }}" 
                                           class="btn btn-sm btn-info" title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.user-subscription-plans.toggle-status', $plan->id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                    title="{{ $plan->status ? 'Désactiver' : 'Activer' }}">
                                                <i class="fa fa-{{ $plan->status ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.user-subscription-plans.destroy', $plan->id) }}" 
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce plan ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques Globales -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $plans->count() }}</h3>
                            <p>Plans Disponibles</p>
                        </div>
                        <div class="icon"><i class="fa fa-list"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $plans->where('status', 1)->count() }}</h3>
                            <p>Plans Actifs</p>
                        </div>
                        <div class="icon"><i class="fa fa-check"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $plans->sum('active_subscribers') }}</h3>
                            <p>Passagers Abonnés Totaux</p>
                        </div>
                        <div class="icon"><i class="fa fa-users"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
