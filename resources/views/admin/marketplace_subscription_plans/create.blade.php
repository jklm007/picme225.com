@extends('admin.layout.base')

@section('title', 'Nouveau Plan Marketplace')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.marketplace-subscription-plans.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

            <h5 style="margin-bottom: 2em;"><i class="fa fa-plus"></i> Ajouter un Plan Marketplace</h5>

            <form class="form-horizontal" action="{{ route('admin.marketplace-subscription-plans.store') }}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}

                <div class="form-group row">
                    <label for="name" class="col-xs-2 col-form-label">Nom du Plan</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Ex: STARTER, PRO, BUSINESS">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-2 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="3" placeholder="Description du plan (avantages, limites...)">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-2 col-form-label">Prix (CFA)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('price', 0) }}" name="price" required id="price" min="0" step="1">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="period" class="col-xs-2 col-form-label">Période</label>
                    <div class="col-xs-10">
                        <select class="form-control" name="period" required id="period">
                            <option value="MONTHLY" {{ old('period') == 'MONTHLY' ? 'selected' : '' }}>Mensuel</option>
                            <option value="YEARLY" {{ old('period') == 'YEARLY' ? 'selected' : '' }}>Annuel</option>
                            <option value="WEEKLY" {{ old('period') == 'WEEKLY' ? 'selected' : '' }}>Hebdomadaire</option>
                            <option value="DAILY" {{ old('period') == 'DAILY' ? 'selected' : '' }}>Journalier</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="commission_type" class="col-xs-2 col-form-label">Type Commission Ventes</label>
                    <div class="col-xs-10">
                        <select class="form-control" name="commission_type" required id="commission_type">
                            <option value="percentage" {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                            <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>Fixe (CFA)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="commission_value" class="col-xs-2 col-form-label">Valeur Commission</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('commission_value', 0) }}" name="commission_value" required id="commission_value" min="0" step="0.01">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="max_categories" class="col-xs-2 col-form-label">Catégories Max</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('max_categories', 1) }}" name="max_categories" required id="max_categories" min="1" step="1" placeholder="Nombre max de catégories où publier">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="priority" class="col-xs-2 col-form-label">Priorité (Mise en avant)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('priority', 0) }}" name="priority" required id="priority" min="0" max="10000" placeholder="Ex: 0, 100, 500">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="insurance_included" class="col-xs-2 col-form-label">Assurance Vendeur</label>
                    <div class="col-xs-10">
                        <select class="form-control" name="insurance_included" required id="insurance_included">
                            <option value="0" {{ old('insurance_included') == '0' ? 'selected' : '' }}>Non incluse</option>
                            <option value="1" {{ old('insurance_included') == '1' ? 'selected' : '' }}>Incluse</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="zipcode" class="col-xs-2 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Créer le Plan Marketplace</button>
                        <a href="{{route('admin.marketplace-subscription-plans.index')}}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
