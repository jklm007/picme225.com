@extends('admin.layout.base')

@section('title', 'Modifier Ligne Régionale')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.regional-routes.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

			<h5 style="margin-bottom: 2em;">Modifier la ligne régionale</h5>

            <form class="form-horizontal" action="{{ route('admin.regional-routes.update', $route->id) }}" method="POST" enctype="multipart/form-data" role="form">
            	{{ csrf_field() }}
            	<input type="hidden" name="_method" value="PATCH">
				
				<div class="form-group row">
					<label for="departure_city" class="col-xs-2 col-form-label">Ville de départ</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $route->departure_city }}" name="departure_city" required>
					</div>
				</div>

				<div class="form-group row">
					<label for="destination_city" class="col-xs-2 col-form-label">Ville de destination</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $route->destination_city }}" name="destination_city" required>
					</div>
				</div>

				<div class="form-group row">
					<label for="distance_km" class="col-xs-2 col-form-label">Distance (km)</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" step="0.1" value="{{ $route->distance_km }}" name="distance_km" required>
					</div>
				</div>

				<div class="form-group row">
					<label for="is_active" class="col-xs-2 col-form-label">Actif ?</label>
					<div class="col-xs-10">
						<label class="switch">
                            <input type="checkbox" name="is_active" value="1" @if($route->is_active) checked @endif>
                            <span class="slider round"></span>
                        </label>
					</div>
				</div>

				<div class="form-group row">
					<label class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Mettre à jour</button>
						<a href="{{ route('admin.regional-routes.index') }}" class="btn btn-default">Annuler</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
