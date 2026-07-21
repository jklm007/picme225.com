@extends('admin.layout.base')

@section('title', 'Nouveau Plan Utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Créer un Nouveau Plan Utilisateur</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.user-subscription-plans.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Nom du Plan</label>
                                <input type="text" class="form-control" name="name" id="name" required placeholder="ex: WORK PASS">
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label for="price">Prix (CFA)</label>
                                <input type="number" class="form-control" name="price" id="price" required min="0">
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="period">Période</label>
                                <select name="period" id="period" class="form-control" required>
                                    <option value="DAILY">Journalier</option>
                                    <option value="WEEKLY">Hebdomadaire</option>
                                    <option value="MONTHLY" selected>Mensuel</option>
                                    <option value="YEARLY">Annuel</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label for="priority">Priorité (0-1000)</label>
                                <input type="number" class="form-control" name="priority" id="priority" value="100" required>
                                <small class="text-muted">Plus le nombre est élevé, plus le plan est mis en avant.</small>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="commission_type">Type de Commission</label>
                                <select name="commission_type" id="commission_type" class="form-control" required>
                                    <option value="percentage">Pourcentage (%)</option>
                                    <option value="fixed">Montant Fixe (CFA)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label for="commission_value">Valeur de la Commission</label>
                                <input type="number" step="0.01" class="form-control" name="commission_value" id="commission_value" required min="0">
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="max_categories">Catégories Maximum</label>
                                <input type="number" class="form-control" name="max_categories" id="max_categories" value="1" required min="1">
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="insurance_included">Assurance Incluse ?</label>
                                <select name="insurance_included" id="insurance_included" class="form-control" required>
                                    <option value="0">Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Enregistrer le Plan</button>
                            <a href="{{ route('admin.user-subscription-plans.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
