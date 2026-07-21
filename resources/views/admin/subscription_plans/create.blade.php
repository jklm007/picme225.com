@extends('admin.layout.base')

@section('title', 'Créer un Plan d\'Abonnement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-plus"></i> Nouveau Plan d'Abonnement
                    </h3>
                </div>
                <form action="{{ route('admin.subscription-plans.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du Plan <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" 
                                           placeholder="Ex: PLATINUM" required>
                                    <small class="text-muted">Sera converti en majuscules</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prix Mensuel (CFA) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" 
                                           placeholder="25000" min="0" step="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Commission <span class="text-danger">*</span></label>
                                    <select name="commission_type" class="form-control" id="commission_type" required>
                                        <option value="percentage">Pourcentage (%)</option>
                                        <option value="fixed">Montant Fixe (CFA)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valeur Commission <span class="text-danger">*</span></label>
                                    <input type="number" name="commission_value" class="form-control" 
                                           placeholder="50" min="0" step="0.01" required id="commission_value">
                                    <small class="text-muted" id="commission_hint">
                                        Ex: 5 pour 5% ou 50 pour 50 CFA
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Priorité de Dispatch (0-100) <span class="text-danger">*</span></label>
                                    <input type="number" name="priority" class="form-control" 
                                           placeholder="90" min="0" max="100" required>
                                    <small class="text-muted">Plus élevé = Priorité maximale</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Max Catégories <span class="text-danger">*</span></label>
                                    <input type="number" name="max_categories" class="form-control" 
                                           value="1" min="1" step="1" required>
                                    <small class="text-muted">Nombre de services activables</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bonus Staking (%)</label>
                                    <input type="number" name="staking_bonus_percentage" class="form-control" 
                                           placeholder="5" min="0" max="100" step="0.1">
                                    <small class="text-muted">Optionnel</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Assurance DAO Incluse <span class="text-danger">*</span></label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" 
                                       id="insurance_included" name="insurance_included" value="1" checked>
                                <label class="custom-control-label" for="insurance_included">
                                    Inclure l'assurance mutuelle DAO
                                </label>
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
                                    <tr>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                        </td>
                                        <td>
                                            <select name="service_commissions[{{ $service->id }}][type]" class="form-control form-control-sm">
                                                <option value="percentage">Pourcentage (%)</option>
                                                <option value="fixed">Fixe (CFA)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="service_commissions[{{ $service->id }}][value]" 
                                                   class="form-control form-control-sm" 
                                                   step="0.01" min="0" placeholder="Utiliser déf. du plan">
                                        </td>
                                        <td>
                                            <small class="text-info">Si vide, la commission générale sera au plan de base.</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Conseils</h5>
                            <ul class="mb-0">
                                <li><strong>Commission Fixe :</strong> Idéal pour les plans premium (ex: GOLD = 50 CFA)</li>
                                <li><strong>Commission % :</strong> Standard pour la plupart des plans</li>
                                <li><strong>Priorité :</strong> GOLD=100, PRO=80, ECO=60, STANDARD=40, NONE=20</li>
                                <li><strong>Assurance :</strong> Recommandé pour tous les plans payants</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Créer le Plan
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
    const input = document.getElementById('commission_value');
    
    if (this.value === 'fixed') {
        hint.textContent = 'Ex: 50 pour 50 CFA par course';
        input.placeholder = '50';
    } else {
        hint.textContent = 'Ex: 5 pour 5%';
        input.placeholder = '5';
    }
});
</script>
@endsection
