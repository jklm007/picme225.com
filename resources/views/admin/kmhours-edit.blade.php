@extends('admin.layout.base')

@section('title', 'KM-Hours')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.kmhour.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.update_hour')</h5>

            <form class="form-horizontal" action="{{route('admin.kmhour.update',$KmHour->id)}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group col-xs-12">
					<label for="kilometer" class="col-xs-2 col-form-label">@lang('admin.km')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $KmHour->kilometer }}" name="kilometer" required id="kilometer" placeholder="kilometer">
					</div>
				</div>

				<div class="form-group col-xs-12">
					<label for="hour" class="col-xs-2 col-form-label">@lang('admin.hour')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $KmHour->hour }}" name="hour" required id="hour" placeholder="Hour">
					</div>
				</div>
			 

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.update_hour')</button>
						<a href="{{route('admin.kmhour.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection



