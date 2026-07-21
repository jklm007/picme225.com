@extends('admin.layout.base')

@section('title', 'Approbation des Recharges Manuelles')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">

        <div class="box box-block bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>
                    <i class="fa fa-check-circle text-success"></i>
                    Validation des Recharges Manuelles
                    @if($total > 0)
                        <span class="badge badge-danger ml-2">{{ $total }} en attente</span>
                    @endif
                </h5>
                <div>
                    <span class="badge badge-info">Mode : {{ \Setting::get('payment_gateway', env('PAYMENT_GATEWAY', 'MANUAL')) }}</span>
                </div>
            </div>

            {{-- Alertes flash --}}
            @if(session('flash_success'))
                <div class="alert alert-success">{{ session('flash_success') }}</div>
            @endif
            @if(session('flash_error'))
                <div class="alert alert-danger">{{ session('flash_error') }}</div>
            @endif

            @if($pending->isEmpty())
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> Aucune recharge en attente de validation. ✅
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Compte</th>
                                <th>Nom</th>
                                <th>Montant (CFA)</th>
                                <th>Méthode</th>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $item)
                                @php
                                    $isUser     = $item['account_type'] === 'USER';
                                    $name       = $isUser
                                        ? optional($item['user'] ?? null)['first_name'] . ' ' . optional($item['user'] ?? null)['last_name']
                                        : optional($item['provider'] ?? null)['first_name'] . ' ' . optional($item['provider'] ?? null)['last_name'];
                                    $method     = $item['via'] ?? $item['transaction_desc'] ?? '—';
                                    $ref        = $item['transaction_desc'] ?? $item['transaction_id'] ?? '—';
                                    $amount     = $item['amount'];
                                    $created    = $item['created_at'];
                                    $id         = $item['id'];
                                @endphp
                                <tr>
                                    <td><small class="text-muted">{{ $id }}</small></td>
                                    <td>
                                        @if($isUser)
                                            <span class="badge badge-primary">Passager</span>
                                        @else
                                            <span class="badge badge-warning text-dark">Prestataire</span>
                                        @endif
                                    </td>
                                    <td>{{ $name ?? '—' }}</td>
                                    <td><strong>{{ number_format($amount, 0, ',', ' ') }} CFA</strong></td>
                                    <td>{{ $method }}</td>
                                    <td><code>{{ Str::limit($ref, 30) }}</code></td>
                                    <td>{{ \Carbon\Carbon::parse($created)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($isUser)
                                            <form action="{{ route('admin.wallet-approvals.user.approve', $id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Confirmer la validation de {{ number_format($amount, 0, \',\', \' \') }} CFA ?')">
                                                    <i class="fa fa-check"></i> Valider
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.wallet-approvals.user.reject', $id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Rejeter cette recharge ?')">
                                                    <i class="fa fa-times"></i> Rejeter
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.wallet-approvals.provider.approve', $id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Confirmer la validation de {{ number_format($amount, 0, \',\', \' \') }} CFA ?')">
                                                    <i class="fa fa-check"></i> Valider
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.wallet-approvals.provider.reject', $id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Rejeter cette recharge ?')">
                                                    <i class="fa fa-times"></i> Rejeter
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fa fa-info-circle"></i>
                    <strong>Procédure :</strong> Vérifiez la preuve de paiement envoyée par l'utilisateur (capture Wave/Orange/MTN) 
                    avant de cliquer sur <strong>Valider</strong>. Le montant sera crédité immédiatement et de manière irréversible.
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
