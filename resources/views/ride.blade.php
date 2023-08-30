@extends('user.layout.app')

@section('content')
    <div class="banner row no-margin" style="background-image: url('{{ asset('asset/img/banner-bg.jpg') }}');">
        <div class="banner-overlay"></div>
        <div class="container">
            <div class="col-md-8">
                <h2 class="banner-head"><span class="strong">@lang('home.ride_want')</span><br>@lang('home.ride_best_way')</h2>
            </div>
            <div class="col-md-4">
                <div class="banner-form">
                    <div class="row no-margin fields">
                        <div class="left">
                            <img src="{{asset('asset/img/ride-form-icon.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('login')}}">
                                <h3>@lang('home.ride_with') {{Setting::get('site_title','Tranxit')}}</h3>
                                <h5>@lang('home.sign_in_up') <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>
                    <div class="row no-margin fields">
                        <div class="left">
                            <img src="{{asset('asset/img/ride-form-icon.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('register')}}">
                                <h3>@lang('home.sign_up_ride_sm')</h3>
                                <h5>@lang('home.sign_up') <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>

                    <p class="note-or">Or <a href="{{url('provider/login')}}">@lang('home.sign_in')</a> @lang('home.with_driver_acc')</p>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="row white-section no-margin">
        <div class="container">
            
            <div class="col-md-4 content-block small">
                <h2>@lang('home.tap_app_get_ride')</h2>
                <div class="title-divider"></div>
                <p>{{ Setting::get('site_title', 'Tranxit')  }} @lang('home.tap_app_content')</p>
            </div>

            <div class="col-md-4 content-block small">
                <h2>@lang('home.choose_to_pay')</h2>
                <div class="title-divider"></div>
                <p>@lang('home.choose_pay_content1') {{ Setting::get('site_title', 'Tranxit') }}@lang('home.low_cost_content2')</p>
            </div>

            <div class="col-md-4 content-block small">
                <h2>@lang('home.you_rate')</h2>
                <div class="title-divider"></div>
                <p>@lang('home.you_rate_content')</p>
            </div>


        </div>
    </div>

    <div class="row gray-section no-margin">
        <div class="container">                
            <h2 class="sub-head"><span class="strong">@lang('home.ride_for_price')</span><br>@lang('home.any_occasion')</h2>

            <div class="car-tab">
                <ul class="nav nav-tabs">
                  <li class="active"><a data-toggle="tab" href="#economy">@lang('home.economy')</a></li>
                  <li><a data-toggle="tab" href="#premium">@lang('home.premium')</a></li>
                  <li><a data-toggle="tab" href="#accessibility">@lang('home.accessibility')</a></li>
                  <li><a data-toggle="tab" href="#carpool">@lang('home.carpool')</a></li>
                </ul>

                <div class="tab-content">
                  <div id="economy" class="tab-pane fade in active">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide1.png')}}">
                    </div>
                  </div>
                  <div id="premium" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide2.png')}}">
                    </div>
                  </div>
                  <div id="accessibility" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide3.png')}}">
                    </div>
                  </div>

                  <div id="carpool" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide4.png')}}">
                    </div>
                  </div>


                </div>
            </div>
        </div>
    </div>


    <div class="row white-section no-margin">
        <div class="container">
            
            <div class="col-md-6 content-block">
                <h2 class="two-title"><span class="light">@lang('home.pricing')</span><br><span class="strong">@lang('home.get_estimate')</span></h2>
                <div class="title-divider"></div>
                  <form action="{{url('ride')}}" method="GET" onkeypress="return disableEnterKey(event);">
                <?php if(isset($_GET['s_address']) && isset($_GET['d_address'])){
                  $s_address = $_GET['s_address'];
                  $d_address = $_GET['d_address'];
                }else{
                  $s_address = '';
                  $d_address = ''; 
                } ?>
               
            <div class="input-group fare-form">
                <input type="text" class="form-control"  placeholder="@lang('home.pickup_location')" id="origin-input" name="s_address" value="{{$s_address}}">                               
            </div>

            <div class="input-group fare-form no-border-right">
                <input type="text" class="form-control"  placeholder="@lang('home.drop_location')" id="destination-input" name="d_address" value="{{$d_address}}">
                <span class="input-group-addon">
                    <button type="submit">
                        <i class="fa fa-arrow-right"></i>
                    </button>  
                </span>
            </div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="des_latitude" id="des-latitude">
            <input type="hidden" name="des_longitude" id="des-longitude">
           
            <div class="fare-detail">
             @if(isset($service_name))
              @for($i=0;$i<count($service_name);$i++)
                <div class=fare-radio>
                    <input type="radio" name="fare" id="bmw" checked="checked">
                    <label for="bmw">
                        <div class="fade-radio-inner">
                            <div class="name">{{$service_name[$i]}}</div>
                            <div class="rate">${{$total[$i]}}</div>
                        </div>
                    </label>
                 </div>
                @endfor
                 @endif
               
               
            </div>

            <button type="submit" class="full-primary-btn fare-btn">@lang('home.ride_now')</button>

            </form>
            </div>

            <div class="col-md-6 map-right">
                <div class="map-responsive">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d386950.6511603643!2d-73.70231446529533!3d40.738882125234106!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNueva+York!5e0!3m2!1ses-419!2sus!4v1445032011908" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
                </div>                            
            </div>
            
        </div>
    </div>          

    <div class="row gray-section no-margin">
        <div class="container">                
            <div class="col-md-6 content-block">
                <h2>@lang('home.safety')</h2>
                <div class="title-divider"></div>
                <p>@lang('home.safety_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.safety_content2')</p>
                <a class="content-more" href="#">@lang('home.how_keep_you_safe') <i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="col-md-6 img-block text-center"> 
                <img src="{{asset('asset/img/seat-belt.jpg')}}">
            </div>
        </div>
    </div>


    <div class="row find-city no-margin">
        <div class="container">
            <h2>{{Setting::get('site_title','Tranxit')}} @lang('home.is_city')</h2>
            <form>
                <div class="input-group find-form">
                    <input type="text" class="form-control"  placeholder="@lang('home.search')" >
                    <span class="input-group-addon">
                        <button type="submit">
                            <i class="fa fa-arrow-right"></i>
                        </button>  
                    </span>
                </div>
            </form>
        </div>
    </div>
    <script>
    var input = document.getElementById('origin-input');
    var s_latitude = document.getElementById('latitude');
    var s_longitude = document.getElementById('longitude');
    var input_val = document.getElementById('destination-input');
    var d_latitude = document.getElementById('des-latitude');
    var d_longitude = document.getElementById('des-longitude');

    function initMap() {

        var autocomplete = new google.maps.places.Autocomplete(input);
        var autocomplete1 = new google.maps.places.Autocomplete(input_val);
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
         autocomplete1.addListener('place_changed', function(event) {
          
             var place = autocomplete1.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                    // window.alert("Autocomplete's returned place contains no geometry");
                    return;
            }
            d_latitude.value = place.geometry.location.lat();
            d_longitude.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                    query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    d_latitude.value = results[0].geometry.location.lat();
                    d_longitude.value = results[0].geometry.location.lng();
                }
            });
        }
        });

    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key') }}&libraries=places&callback=initMap" async defer></script>

    <?php $footer = asset('asset/img/footer-city.png'); ?>
    <div class="footer-city row no-margin" style="background-image: url({{$footer}});"></div>
@endsection
