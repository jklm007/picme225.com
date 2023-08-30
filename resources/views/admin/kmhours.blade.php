@extends('admin.layout.base')

@section('title', 'KM-Hours')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">

			<h5 style="margin-bottom: 2em;">@lang('admin.add_hour')</h5>

            <form class="form-horizontal" action="{{route('admin.kmhour.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<div class="col-xs-12">
				<div class="form-group col-xs-6">
					<label for="kilometer" class="col-xs-2 col-form-label">@lang('admin.km')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('kilometer') }}" name="kilometer" required id="kilometer" placeholder="kilometer">
					</div>
				</div>

				<div class="form-group col-xs-6">
					<label for="hour" class="col-xs-2 col-form-label">@lang('admin.hour')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('hour') }}" name="hour" required id="hour" placeholder="Hour">
					</div>
				</div>
			   <div class="clearfix"></div>
			</div>

				<div class="form-group row">
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.add_hour')</button>
					</div>
				</div>
			</form>
		</div>
		<div class="box box-block bg-white">
			 <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.km') </th>
                            <th>@lang('admin.hour')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($kmhours as $index => $kmhour)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$kmhour->kilometer}}</td>
                            <td>{{$kmhour->hour}}</td>
                            <th><form action="{{ route('admin.kmhour.destroy', $kmhour->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    @if( Setting::get('demo_mode') == 0)
                                    <a href="{{ route('admin.kmhour.edit', $kmhour->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                    @endif
                                </form></th>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.km') </th>
                            <th>@lang('admin.hour')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                    </tfoot>
                </table>
		</div>
    </div>
</div>

@endsection



