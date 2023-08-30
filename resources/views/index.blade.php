@extends('user.layout.app')

@section('content')
<div class="banner row no-margin" style="background-image: url('{{ asset('asset/img/banner-bg.jpg') }}');">
    <div class="banner-overlay"></div>
    <div class="container">
        <div class="col-md-8">
            <h2 class="banner-head"><span class="strong">@lang('home.get_there')</span><br>@lang('home.your_day')</h2>
        </div>
        <div class="col-md-4">
            <div class="banner-form">
                <div class="row no-margin fields">
                    <div class="left">
                        <img src="{{ asset('asset/img/ride-form-icon.png') }}">
                    </div>
                    <div class="right">
                        <a href="{{url('login')}}">
                            <h3>@lang('home.sign_up_ride_sm')</h3>
                            <h5>@lang('home.sign_up')<i class="fa fa-chevron-right"></i></h5>
                        </a>
                    </div>
                </div>
                <div class="row no-margin fields">
                    <div class="left">
                        <img src="{{ asset('asset/img/ride-form-icon.png') }}">
                    </div>
                    <div class="right">
                        <a href="{{ url('/provider/register') }}">
                            <h3>@lang('home.sign_up_drive_sm')</h3>
                            <h5>@lang('home.sign_up') <i class="fa fa-chevron-right"></i></h5>
                        </a>
                    </div>
                </div>
                <p class="note-or">Or <a href="{{ url('/provider/login') }}">@lang('home.sign_in')</a>@lang('home.rider_account') </p>
            </div>
        </div>
    </div>
</div>

<div class="row white-section no-margin">
    <div class="container">
        <div class="col-md-6 img-block text-center"> 
            <img src="{{ asset('asset/img/tap.png') }}">
        </div>
        <div class="col-md-6 content-block">
            <h2>@lang('home.tap_app_get_ride')</h2>
            <div class="title-divider"></div>
            <p>{{ Setting::get('site_title', 'Tranxit')  }} @lang('home.tap_app_content')</p>
            <a class="content-more" href="#">@lang('home.more_reasons') <i class="fa fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<div class="row gray-section no-margin">
    <div class="container">                
        <div class="col-md-6 content-block">
            <h2>@lang('home.ready_any')</h2>
            <div class="title-divider"></div>
            <p> @lang('home.ready_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.ready_content2') </p>
            <a class="content-more" href="#">@lang('home.more_reasons')<i class="fa fa-chevron-right"></i></a>
        </div>
        <div class="col-md-6 img-block text-center"> 
            <img src="{{ asset('asset/img/anywhere.png') }}">
        </div>
    </div>
</div>

<div class="row white-section no-margin">
    <div class="container">
        <div class="col-md-6 img-block text-center"> 
            <img src="{{ asset('asset/img/low-cost.png') }}">
        </div>
        <div class="col-md-6 content-block">
            <h2>@lang('home.low_cost')</h2>
            <div class="title-divider"></div>
            <p> @lang('home.low_cost_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.low_cost_content2') </p>
            <a class="content-more" href="#">@lang('home.more_reasons') <i class="fa fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<div class="row gray-section no-margin full-section">
    <div class="container">                
        <div class="col-md-6 content-block">
            <h3>@lang('home.behind_wheel')</h3>
            <h2>@lang('home.wheel_heading')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.wheel_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.wheel_content2')</p>
            <a class="content-more-btn" href="#">@lang('home.why_drive') {{ Setting::get('site_title', 'Tranxit')  }} <i class="fa fa-chevron-right"></i></a>
        </div>
        <div class="col-md-6 full-img text-center" style="background-image: url({{ asset('asset/img/behind-the-wheel.jpg') }});"> 
            <!-- <img src="img/anywhere.png"> -->
        </div>
    </div>
</div>

<div class="row white-section no-margin">
    <div class="container">
        <div class="col-md-6 img-block text-center"> 
            <img src="{{ asset('asset/img/low-cost.png') }}">
        </div>
        <div class="col-md-6 content-block">
            <h2>@lang('home.helping_city')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.help_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.help_content2')</p>
            <a class="content-more" href="#">@lang('home.our_local_impact') <i class="fa fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<div class="row gray-section no-margin">
    <div class="container">
        <div class="col-md-6 content-block">
            <h2>@lang('home.safety')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.safety_content1') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.safety_content2')</p>
            <a class="content-more" href="#">@lang('home.how_keep_you_safe')<i class="fa fa-chevron-right"></i></a>
        </div>
        <div class="col-md-6 img-block text-center"> 
            <img src="{{ asset('asset/img/seat-belt.jpg') }}">
        </div>
    </div>
</div>

<div class="row find-city no-margin">
    <div class="container">
        <h2>{{ Setting::get('site_title','Tranxit') }} @lang('home.in_chennai')</h2>
        <form>
            <div class="input-group find-form">
                <input type="text" class="form-control" placeholder="@lang('home.search')" >
                <span class="input-group-addon">
                    <button type="submit">
                        <i class="fa fa-arrow-right"></i>
                    </button>  
                </span>
            </div>
        </form>
    </div>
</div>

<div class="footer-city row no-margin" style="background-image: url({{ asset('asset/img/footer-city.png') }});"></div>
@endsection