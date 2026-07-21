@extends('admin.layout.base')

@section('title', 'Modifier le Plan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-edit"></i> Modifier le Plan: {{ $plan->name }}
                    </h3>
                </div>
                <form action="{{ route('admin.subscription-plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du Plan <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" 
                                           value="{{ $plan->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prix Mensuel (CFA) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" 
                                           value="{{ $plan->price }}" min="0" step="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Commission <span class="text-danger">*</span></label>
                                    <select name="commission_type" class="form-control" id="commission_type" required>
                                        <option value="percentage" {{ $plan->commission_type == 'percentage' ? 'selected' : '' }}>
                                            Pourcentage (%)
                                        </option>
                                        <option value="fixed" {{ $plan->commission_type == 'fixed' ? 'selected' : '' }}>
                                            Montant Fixe (CFA)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valeur Commission <span class="text-danger">*</span></label>
                                    <input type="number" name="commission_value" class="form-control" 
                                           value="{{ $plan->commission_value }}" min="0" step="0.01" required id="commission_value">
                                    <small class="text-muted" id="commission_hint">
                                        {{ $plan->commission_type == 'fixed' ? 'Montant en CFA' : 'Pourcentage' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Priorité de Dispatch (0-100) <span class="text-danger">*</span></label>
                                    <input type="number" name="priority" class="form-control" 
                                           value="{{ $plan->priority }}" min="0" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Max Catégories <span class="text-danger">*</span></label>
                                    <input type="number" name="max_categories" class="form-control" 
                                           value="{{ $plan->max_categories ?? 1 }}" min="1" step="1" required>
                                    <small class="text-muted">Nombre de services activables</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bonus Staking (%)</label>
                                    <input type="number" name="staking_bonus_percentage" class="form-control" 
                                           value="{{ $plan->staking_bonus_percentage }}" min="0" max="100" step="0.1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assurance DAO Incluse <span class="text-danger">*</span></label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="insurance_included" name="insurance_included" value="1" 
                                               {{ $plan->insurance_included ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="insurance_included">
                                            Inclure l'assurance mutuelle DAO
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Statut <span class="text-danger">*</span></label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                                id="status" name="status" value="1" 
                                                {{ $plan->status ? 'checked' : '' }}>
                                         <label class="custom-control-label" for="status">
                                             Plan actif
                                         </label>
                                     </div>
                                 </div>
                             </div>
                         </div>

                        <hr>
                        <h4 class="mb-3"><i class="fa fa-percent"></i> Commissions Spécifiques par Catégorie</h4>
                        <p class="text-muted">Définissez des commissions spéciales qui prévaudront sur la commission générale du plan pour ces catégories spécifiques.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Catégorie de Service</th>
                                        <th>Type de Commission</th>
                                        <th>Valeur</th>
                                        <th>Détails</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($services as $service)
                                    @php
                                        $comm = $planCommissions->get($service->id);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                        </td>
                                        <td>
                                            <select name="service_commissions[{{ $service->id }}][type]" class="form-control form-control-sm">
                                                <option value="percentage" {{ ($comm && $comm->commission_type == 'percentage') ? 'selected' : '' }}>Pourcentage (%)</option>
                                                <option value="fixed" {{ ($comm && $comm->commission_type == 'fixed') ? 'selected' : '' }}>Fixe (CFA)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="service_commissions[{{ $service->id }}][value]" 
                                                   class="form-control form-control-sm" 
                                                   step="0.01" min="0" 
                                                   value="{{ $comm ? $comm->commission_value : '' }}"
                                                   placeholder="Par déf: {{ $plan->commission_value }}">
                                        </td>
                                        <td>
                                            <small class="text-info">Si vide, la commission générale ({{ $plan->commission_value }}{{ $plan->commission_type == 'percentage' ? '%' : ' CFA' }}) sera appliquée.</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fa fa-exclamation-triangle"></i> Attention</h5>
                            <p class="mb-0">
                                Ce plan a actuellement <strong>{{ $plan->active_subscribers ?? 0 }} abonné(s) actif(s)</strong>.
                                Les modifications affecteront les nouveaux abonnements uniquement.
                            </p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Enregistrer les Modifications
                        </button>
                        <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('commission_type').addEventListener('change', function() {
    const hint = document.getElementById('commission_hint');
    
    if (this.value === 'fixed') {
        hint.textContent = 'Montant en CFA';
    } else {
        hint.textContent = 'Pourcentage';
    }
});
</script>
@endsection
