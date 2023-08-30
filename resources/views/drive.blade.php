@extends('user.layout.app')

@section('content')
<div class="banner row no-margin" style="background-image: url('{{ asset('asset/img/banner-bg.jpg') }}');">
    <div class="banner-overlay"></div>
    <div class="container">
        <div class="col-md-8">
            <h2 class="banner-head"><span class="strong">@lang('home.work_puts_first')</span><br>@lang('home.drive_when_you_want')</h2>
        </div>
        <div class="col-md-4">
            <div class="banner-form">
                <div class="row no-margin fields">
                    <div class="left">
                    	<img src="{{asset('asset/img/ride-form-icon.png')}}">
                    </div>
                    <div class="right">
                        <a href="{{url('provider/register')}}">
                            <h3>@lang('home.sign_up_drive_sm')</h3>
                            <h5>@lang('home.sign_up') <i class="fa fa-chevron-right"></i></h5>
                        </a>
                    </div>
                </div>

                <div class="row no-margin fields">
                    <div class="left">
                    	<img src="{{asset('asset/img/ride-form-icon.png')}}">
                    </div>
                    <div class="right">
                        <a href="{{url('provider/login')}}">
                            <h3>@lang('home.sign_up_drive_sm')</h3>
                            <h5>@lang('home.sign_in_up') <i class="fa fa-chevron-right"></i></h5>
                        </a>
                    </div>
                </div>

                <p class="note-or">Or <a href="{{ url('login') }}">@lang('home.sign_in')</a> @lang('home.rider_account')</p>
            </div>
        </div>
    </div>
</div>

<div class="row white-section no-margin">
    <div class="container">
        
        <div class="col-md-4 content-block small">
            <h2>@lang('home.set_schedule')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.set_schedule_content') {{ Setting::get('site_title', 'Tranxit') }} @lang('home.set_schedule_content2')</p>
        </div>

        <div class="col-md-4 content-block small">
            <h2>@lang('home.more_everyturn')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.more_everyturn_content')</p>
        </div>

        <div class="col-md-4 content-block small">
            <h2>@lang('home.let_app_lead')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.let_app_lead_content')</p>
        </div>

    </div>
</div>

<div class="row gray-section no-margin full-section">
    <div class="container">                
        <div class="col-md-6 content-block">
            <h3>@lang('home.about_app')</h3>
            <h2>@lang('home.about_app_heading')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.about_app_content')</p>
            <a class="content-more-btn" href="#">@lang('home.see_how_it_works') <i class="fa fa-chevron-right"></i></a>
        </div>
        <div class="col-md-6 full-img text-center" style="background-image: url({{ asset('asset/img/driver-car.jpg') }});"> 
            <!-- <img src="img/anywhere.png"> -->
        </div>
    </div>
</div>

<div class="row white-section no-margin">
    <div class="container">
        
        <div class="col-md-4 content-block small">
            <h2>@lang('home.rewards')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.reward_content')</p>
        </div>

        <div class="col-md-4 content-block small">
            <h2>@lang('home.requirement')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.requirement_content')</p>
        </div>

        <div class="col-md-4 content-block small">
            <h2>@lang('home.safety')</h2>
            <div class="title-divider"></div>
            <p>@lang('home.safe_content1') {{ Setting::get('site_title', 'Tranxit') }}@lang('home.safe_content2')</p>
        </div>

    </div>
</div>
            
<div class="row find-city no-margin">
    <div class="container">
        <h2>@lang('home.start_making_money')</h2>
        <p>@lang('home.start_making_money_heading')</p>

        <button type="submit" class="full-primary-btn drive-btn">@lang('home.start_drive_now')</button>
    </div>
</div>

<div class="footer-city row no-margin" style="background-image: url({{ asset('asset/img/footer-city.png') }});"></div>
@endsection