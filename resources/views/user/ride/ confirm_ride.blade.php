@extends('user.layout.base')

@section('title', 'Ride Confirmation ')

@section('styles')
<style type="text/css">
    .surge-block {
        background-color: black;
        width: 50px;
        height: 50px;
        border-radius: 25px;
        margin: 0 auto;
        padding: 10px;
        padding-top: 15px;
    }
    .surge-text {
        top: 11px;
        font-weight: bold;
        color: white;
    }
    .distance-duration {
        margin: 15px 0;
    }
</style>
@endsection

@section('content')
<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.ride.ride_now')</h4>
            </div>
        </div>
        @include('common.notify')
        <div class="row no-margin">
            <div class="col-md-6">
                <form action="{{url('create/ride')}}" method="POST" id="create_ride">
                {{ csrf_field() }}
                    <dl class="dl-horizontal left-right">
                        <dt>@lang('user.type')</dt>
                        <dd>{{$service->name}}</dd>
                        @if(isset($fare->rental_amount) && $fare->rental_amount !='')
                        <dt>@lang('user.package')</dt>
                        <dd>{{$fare->package}}</dd>
                        <dt>@lang('user.estimated_fare')</dt>
                        <dd>{{currency($fare->rental_amount)}}</dd>
                        @else
                        <dt>@lang('user.total_distance')</dt>
                        <dd>{{$fare->distance}} Kms</dd>
                        <dt>@lang('user.eta')</dt>
                        <dd>{{$fare->time}}</dd>
                        <dt>@lang('user.estimated_fare')@if($fare->round_trip == 1) (Round Trip) @endif</dt>
                        <dd>{{currency($fare->estimated_fare)}}</dd>
                        @endif
                        <hr>
                        <div class="distance-duration">
                            <p><strong>Distance:</strong> <span id="distance">{{$fare->distance}} km</span></p>
                            <p><strong>Duration:</strong> <span id="duration">{{$fare->time}}</span></p>
                        </div>
                        @if(Auth::user()->wallet_balance > 0)
                        <input type="checkbox" name="use_wallet" value="1"><span style="padding-left: 15px;">@lang('user.use_wallet_balance')</span>
                        <br>
                        <br>
                            <dt>@lang('user.available_wallet_balance')</dt>
                            <dd>{{currency(Auth::user()->wallet_balance)}}</dd>
                        @endif
                    </dl>

                    <input type="hidden" name="s_address" value="{{Request::get('s_address')}}">
                    <input type="hidden" name="d_address" value="{{Request::get('d_address')}}">
                    <input type="hidden" name="s_latitude" value="{{Request::get('s_latitude')}}">
                    <input type="hidden" name="s_longitude" value="{{Request::get('s_longitude')}}">
                    <input type="hidden" name="d_latitude" value="{{Request::get('d_latitude')}}">
                    <input type="hidden" name="d_longitude" value="{{Request::get('d_longitude')}}">
                    <input type="hidden" name="service_type" value="{{Request::get('service_type')}}">
                    <input type="hidden" name="distance" id="hidden_distance" value="{{$fare->distance}}">
                    <input type="hidden" name="duration" id="hidden_duration" value="{{$fare->time}}">
                    <input type="hidden" name="method" value="{{$fare->method}}">
                    <input type="hidden" name="package_id" value="{{Request::get('package_id')}}">
                    @if(isset($fare->round_trip))
                    <input type="hidden" name="round_trip" value="{{$fare->round_trip}}">
                    @endif
                    @if(Request::get('rental_hours') != '') 
                    <input type="hidden" name="rental_hours" value="{{Request::get('rental_hours')}}">
                    @endif
                    <p>@lang('user.payment_method')</p>
                    <select class="form-control" name="payment_mode" id="payment_mode" onchange="card(this.value);">
                      <option value="CASH">CASH</option>
                      @if(Setting::get('CARD') == 1)
                      @if($cards->count() > 0)
                        <option value="CARD">CARD</option>
                      @endif
                      @endif
                    </select>
                    <br>
                    @if(Setting::get('CARD') == 1)
                        @if($cards->count() > 0)
                        <select class="form-control" name="card_id" style="display: none;" id="card_id">
                          <option value="">Select Card</option>
                          @foreach($cards as $card)
                            <option value="{{$card->card_id}}">{{$card->brand}} **** **** **** {{$card->last_four}}</option>
                          @endforeach
                        </select>
                        @endif
                    @endif
                    @if($fare->surge == 1)
                        <span><em>Note : Due to High Demand the fare may vary!</em></span>
                        <div class="surge-block"><span class="surge-text">{{$fare->surge_value}}</span>
                        </div>
                    @endif
                    <button type="submit" class="half-primary-btn fare-btn">@lang('user.ride.ride_now')</button>
                    <button type="button" class="half-secondary-btn fare-btn" data-toggle="modal" data-target="#schedule_modal">Schedule Later</button>
                </form>
            </div>

            <div class="col-md-6">
                <div class="user-request-map">
                    <?php 
                    $map_icon = asset('asset/img/marker-start.png');
                    $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=roadmap&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$request->s_latitude.",".$request->s_longitude."&markers=icon:".$map_icon."%7C".$request->d_latitude.",".$request->d_longitude."&path=color:0x191919|weight:8|".$request->s_latitude.",".$request->s_longitude."|".$request->d_latitude.",".$request->d_longitude."&key=".Setting::get('map_key'); ?>
                    <div class="map-static" style="background-image: url({{$static_map}});">
                    </div>
                    <div class="from-to row no-margin">
                        <div class="from">
                            <h5>FROM</h5>
                            <p>{{$request->s_address}}</p>
                        </div>
                        @if($request->rental_hour =='')
                        <div class="to">
                            <h5>TO</h5>
                            <p>{{$request->d_address}}</p>
                        </div>
                        @endif
                    </div>
                </div> 
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Set Distance and Duration (Optional Dynamic Fetching with Google Maps)
</script>
@endsection

