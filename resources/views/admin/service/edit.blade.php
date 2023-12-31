@extends('admin.layout.base')

@section('title', 'Update Service Type ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.service.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">@lang('admin.service.Update_User')</h5>

            <form class="form-horizontal" action="{{route('admin.service.update', $service->id )}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="PATCH">
                 <div class="form-group row">
                     <div class="col-xs-10">
                     <input type="checkbox" name="ambulance" value="1" <?php if($service->ambulance == 1){?> checked <?php } ?>
                         > <label>Ambulance </label>
                     </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-xs-2 col-form-label">@lang('admin.service.Service_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->name }}" name="name" required id="name" placeholder="Service Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="provider_name" class="col-xs-2 col-form-label">@lang('admin.service.Provider_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->provider_name }}" name="provider_name" required id="provider_name" placeholder="Provider Name">
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="image" class="col-xs-2 col-form-label">@lang('admin.picture')</label>
                    <div class="col-xs-10">
                        @if(isset($service->image))
                        <img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{ $service->image }}">
                        @endif
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="calculator" class="col-xs-2 col-form-label">@lang('admin.service.Pricing_Logic')</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="calculator" name="calculator">
                            <option value="MIN" @if($service->calculator =='MIN') selected @endif>@lang('servicetypes.MIN')</option>
                            <option value="HOUR" @if($service->calculator =='HOUR') selected @endif>@lang('servicetypes.HOUR')</option>
                            <option value="DISTANCE" @if($service->calculator =='DISTANCE') selected @endif>@lang('servicetypes.DISTANCE')</option>
                            <option value="DISTANCEMIN" @if($service->calculator =='DISTANCEMIN') selected @endif>@lang('servicetypes.DISTANCEMIN')</option>
                            <option value="DISTANCEHOUR" @if($service->calculator =='DISTANCEHOUR') selected @endif>@lang('servicetypes.DISTANCEHOUR')</option>
                        </select>
                    </div>
                </div>
                
                 <!-- Set Hour Price -->
                 @if($service->calculator =='DISTANCEHOUR')
               
                  <div class="form-group row" >
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.hourly_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->hour }}" name="hour" required id="hourly_price" placeholder="Set Hour Price">
                    </div>
                </div>
                @else
                <div class="form-group row" >
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.hourly_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="" name="hourly_price" required id="hourly_price" placeholder="Set Hour Price (Only for DISTANCEHOUR)">
                    </div>
                </div>
                @endif
               

                <div class="form-group row">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.Base_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->fixed }}" name="fixed" required id="fixed" placeholder="Base Price">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="distance" class="col-xs-2 col-form-label">@lang('admin.service.Base_Distance') ({{ distance('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->distance }}" name="distance" required id="distance" placeholder="Base Distance">
                    </div>
                </div>

                  <!--rental amount -->
                <div class="form-group row">
                    <label for="distance" class="col-xs-2 col-form-label">Rental Amount</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->rental_amount }}" name="rental_amount" required id="rental" placeholder="Rental Amount">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="minute" class="col-xs-2 col-form-label">@lang('admin.service.unit_time') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->minute }}" name="minute" required id="minute" placeholder="Unit Time Pricing">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-2 col-form-label">@lang('admin.service.unit') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->price }}" name="price" required id="price" placeholder="Unit Distance Price">
                    </div>
                </div>

                 <div class="form-group row">
                    <label for="capacity" class="col-xs-2 col-form-label">@lang('admin.service.Seat_Capacity')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ $service->capacity }}" name="capacity" required id="capacity" placeholder="Seat Capacity">
                    </div>
                </div>

                <label style="font-weight: bold;">OUTSTATION</label>
                 <div class="form-group row">
                    <label for="outstation_price" class="col-xs-2 col-form-label">Price (based km)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->outstation_price }}" name="outstation_price" required id="outstation_price" placeholder="Outstation Price">
                    </div>
                </div>

                 <label style="font-weight: bold;">RENTAL</label>
              
                @for($i=0; $i<count($kmhours); $i++)
                <div class="form-group row">
                    <input type="hidden" name="km_hour_id[]" value="{{$kmhours[$i]->id}}">
                    <label for="ren_price" class="col-xs-2 col-form-label">{{$kmhours[$i]->kilometer}}kms- ({{$kmhours[$i]->hour}}hrs)</label>
                    <div class="col-xs-10">
                       <input class="form-control" type="text" value="{{count($kmhours_service) != 0 ? $kmhours_service[$i]->ren_price: ''}}" name="ren_price[]" required id="ren_price" placeholder="Rental Price">
                    </div>
                </div>
                @endfor

                
                <div class="form-group row">
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="{{route('admin.service.index')}}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                    </div>
                    <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">@lang('admin.service.Update_Service_Type')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
