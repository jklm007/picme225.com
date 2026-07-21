@extends('admin.layout.base')

@section('title', 'Ride Variant & DAO Settings ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
			<h5>Ride Variant & DAO Settings</h5>

            <form class="form-horizontal" action="{{ route('admin.settings.variants.store') }}" method="POST" role="form">
            	{{csrf_field()}}

                <div class="card card-block">
                    <h6 class="card-title">DAO Config</h6>
                    <div class="form-group row">
                        <label for="dao_quorum" class="col-xs-3 col-form-label">DAO Quorum (Votes)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" value="{{ Setting::get('dao_quorum', '5')  }}" name="dao_quorum" required id="dao_quorum" placeholder="Quorum to execute proposal">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="dao_voting_period_days" class="col-xs-3 col-form-label">Voting Period (Days)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" value="{{ Setting::get('dao_voting_period_days', '7')  }}" name="dao_voting_period_days" required id="dao_voting_period_days">
                        </div>
                    </div>
                </div>

                <div class="card card-block">
                    <h6 class="card-title">Detour Constraints (Dynamic Ride)</h6>
                    <div class="form-group row">
                        <label for="detour_max_distance_km" class="col-xs-3 col-form-label">Max Detour Distance (km)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="0.1" value="{{ Setting::get('detour_max_distance_km', '10')  }}" name="detour_max_distance_km" required id="detour_max_distance_km">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="detour_max_time_mins" class="col-xs-3 col-form-label">Max Detour Time (mins)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" value="{{ Setting::get('detour_max_time_mins', '30')  }}" name="detour_max_time_mins" required id="detour_max_time_mins">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="detour_max_percentage" class="col-xs-3 col-form-label">Max Detour Percentage (%)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="1" value="{{ Setting::get('detour_max_percentage', '50')  }}" name="detour_max_percentage" required id="detour_max_percentage">
                        </div>
                    </div>
                </div>

                <div class="card card-block">
                    <h6 class="card-title">Pricing Variants</h6>
                    <div class="form-group row">
                        <label for="prive_variant_multiplier" class="col-xs-3 col-form-label">Private Multiplier (x1.5 etc)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="0.1" value="{{ Setting::get('prive_variant_multiplier', '1.5')  }}" name="prive_variant_multiplier" required id="prive_variant_multiplier">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="arret_variant_discount" class="col-xs-3 col-form-label">Remise Arrêt PDP vs Privé (%)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="1" value="{{ Setting::get('arret_variant_discount', '20')  }}" name="arret_variant_discount" required id="arret_variant_discount">
                            <small class="text-muted">Réduction appliquée sur le prix Privé équivalent en mode Arrêt sans segment configuré. Ex: 20 = 20% moins cher que le Privé.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="arret_min_fare" class="col-xs-3 col-form-label">Tarif Minimum Arrêt PDP (FCFA)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="1" value="{{ Setting::get('arret_min_fare', '300')  }}" name="arret_min_fare" required id="arret_min_fare">
                            <small class="text-muted">Prix plancher garanti au chauffeur pour tout trajet Arrêt PDP. Protège contre les courses trop courtes.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="partage_min_fare" class="col-xs-3 col-form-label">Tarif Minimum Partagé / passager (FCFA)</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="1" value="{{ Setting::get('partage_min_fare', '500')  }}" name="partage_min_fare" required id="partage_min_fare">
                            <small class="text-muted">Prix plancher par passager pour le mode Partagé. Garantit la rentabilité du chauffeur même si 1 seul passager monte.</small>
                        </div>
                    </div>
                </div>

                <div class="card card-block">
                    <h6 class="card-title">Delivery Settings</h6>
                    <div class="form-group row">
                        <label for="delivery_stop_fee" class="col-xs-3 col-form-label">Fee per extra stop</label>
                        <div class="col-xs-9">
                            <input class="form-control" type="number" step="0.1" value="{{ Setting::get('delivery_stop_fee', '100')  }}" name="delivery_stop_fee" required id="delivery_stop_fee">
                        </div>
                    </div>
                </div>

				<div class="form-group row">
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Settings</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>
@endsection
