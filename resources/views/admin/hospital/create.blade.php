@extends('admin.layout.base')

@section('title', 'Add Promocode ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.hospital.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.hospital.add_hospital')</h5>

            <form class="form-horizontal" action="{{route('admin.hospital.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="hospital_address" class="col-xs-2 col-form-label">@lang('admin.hospital.hospital')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('hospital_address') }}" name="hospital_address" required id="pac-input" placeholder="Hospital name">
					</div>
				</div>
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.hospital.add_hospital')</button>
						<a href="{{route('admin.document.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
@section('scripts')
<script>
    var input = document.getElementById('pac-input');
    var s_latitude = document.getElementById('latitude');
    var s_longitude = document.getElementById('longitude');

    function initMap() {

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function(event) {
          
             var place = autocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                    // window.alert("Autocomplete's returned place contains no geometry");
                    return;
            }
            s_latitude.value = place.geometry.location.lat();
            s_longitude.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                    query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    s_latitude.value = results[0].geometry.location.lat();
                    s_longitude.value = results[0].geometry.location.lng();
                }
            });
        }
        });

    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key') }}&libraries=places&callback=initMap" async defer></script>
@endsection
