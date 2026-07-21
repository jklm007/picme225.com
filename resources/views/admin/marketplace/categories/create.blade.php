@extends('admin.layout.base')
@section('title', 'Ajouter une Catégorie')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.marketplace-categories.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Ajouter une Nouvelle Catégorie</h5>
            <form class="form-horizontal" action="{{ route('admin.marketplace-categories.store') }}" method="POST">
                {{ csrf_field() }}
                <div class="form-group row">
                    <label for="parent_id" class="col-xs-2 col-form-label">Catégorie Parente (Optionnel)</label>
                    <div class="col-xs-10">
                        <select name="parent_id" class="form-control">
                            <option value="">-- Aucune (Catégorie Principale) --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->label }} ({{ $parent->name }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Laissez vide pour créer une catégorie principale.</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-xs-2 col-form-label">Nom (Valeur Backend)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="ex: Vente de Véhicule">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="label" class="col-xs-2 col-form-label">Label (Affiché Mobile)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('label') }}" name="label" required id="label" placeholder="ex: Véhicules">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="icon" class="col-xs-2 col-form-label">Icône (Optionnel)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('icon') }}" name="icon" id="icon" placeholder="ex: ic_car">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="order_index" class="col-xs-2 col-form-label">Ordre d'affichage</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('order_index', 0) }}" name="order_index" required id="order_index">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-xs-10 col-xs-offset-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
