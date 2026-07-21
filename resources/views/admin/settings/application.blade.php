@extends('admin.layout.base')

@section('title', 'Site Settings ')

@section('content')

	<div class="content-area py-1">
		<div class="container-fluid">
			<div class="box box-block bg-white">
				<h5>@lang('admin.setting.Site_Settings')</h5>

				<form class="form-horizontal" action="{{ route('admin.settings.store') }}" method="POST"
					enctype="multipart/form-data" role="form">
					{{csrf_field()}}

					<div class="form-group row">
						<label for="site_title" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Name')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('site_title', 'Tranxit')  }}"
								name="site_title" required id="site_title" placeholder="Site Name">
						</div>
					</div>

					<div class="form-group row">
						<label for="site_logo" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Logo')</label>
						<div class="col-xs-10">
							@if(Setting::get('site_logo') != '')
								<img style="height: 90px; margin-bottom: 15px;"
									src="{{ img(Setting::get('site_logo', asset('logo-black.png'))) }}">
							@endif
							<input type="file" accept="image/*" name="site_logo" class="dropify form-control-file"
								id="site_logo" aria-describedby="fileHelp">
						</div>
					</div>


					<div class="form-group row">
						<label for="site_icon" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Icon')</label>
						<div class="col-xs-10">
							@if(Setting::get('site_icon') != '')
								<img style="height: 90px; margin-bottom: 15px;" src="{{ img(Setting::get('site_icon')) }}">
							@endif
							<input type="file" accept="image/*" name="site_icon" class="dropify form-control-file"
								id="site_icon" aria-describedby="fileHelp">
						</div>
					</div>

					<div class="form-group row">
						<label for="tax_percentage"
							class="col-xs-2 col-form-label">@lang('admin.setting.Copyright_Content')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text"
								value="{{ Setting::get('site_copyright', '&copy; ' . date('Y') . ' Appoets') }}"
								name="site_copyright" id="site_copyright" placeholder="Site Copyright">
						</div>
					</div>

					<div class="form-group row">
						<label for="store_link_android"
							class="col-xs-2 col-form-label">@lang('admin.setting.Playstore_link')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('store_link_android', '')  }}"
								name="store_link_android" id="store_link_android" placeholder="Playstore link">
						</div>
					</div>

					<div class="form-group row">
						<label for="store_link_ios"
							class="col-xs-2 col-form-label">@lang('admin.setting.Appstore_Link')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('store_link_ios', '')  }}"
								name="store_link_ios" id="store_link_ios" placeholder="Appstore link">
						</div>
					</div>

					<div class="form-group row">
						<label for="provider_select_timeout"
							class="col-xs-2 col-form-label">@lang('admin.setting.Provider_Accept_Timeout')</label>
						<div class="col-xs-10">
							<input class="form-control" type="number"
								value="{{ Setting::get('provider_select_timeout', '60')  }}" name="provider_select_timeout"
								required id="provider_select_timeout" placeholder="Provider Timout">
						</div>
					</div>

					<div class="form-group row">
						<label for="provider_search_radius"
							class="col-xs-2 col-form-label">@lang('admin.setting.Provider_Search_Radius')</label>
						<div class="col-xs-10">
							<input class="form-control" type="number"
								value="{{ Setting::get('provider_search_radius', '100')  }}" name="provider_search_radius"
								required id="provider_search_radius" placeholder="Provider Search Radius">
						</div>
					</div>

					<div class="form-group row">
						<label for="sos_number" class="col-xs-2 col-form-label">@lang('admin.setting.SOS_Number')</label>
						<div class="col-xs-10">
							<input class="form-control" type="number" value="{{ Setting::get('sos_number', '911')  }}"
								name="sos_number" required id="sos_number" placeholder="SOS Number">
						</div>
					</div>

					<div class="form-group row">
						<label for="stripe_secret_key" class="col-xs-2 col-form-label"> Manual Assigning </label>
						<div class="col-xs-10">
							<div class="float-xs-left mr-1"><input @if(Setting::get('manual_request') == 1) checked @endif
									name="manual_request" type="checkbox" class="js-switch" data-color="#43b968"></div>
						</div>
					</div>

					<div class="form-group row">
						<label for="broadcast_request" class="col-xs-2 col-form-label"> BroadCast Assigning </label>
						<div class="col-xs-10">
							<div class="float-xs-left mr-1"><input @if(Setting::get('broadcast_request') == 1) checked @endif
									name="broadcast_request" type="checkbox" class="js-switch" data-color="#43b968"></div>
						</div>
					</div>

					<div class="form-group row">
						<label for="track_distance" class="col-xs-2 col-form-label"> Track Live Travel Distance </label>
						<div class="col-xs-10">
							<div class="float-xs-left mr-1"><input @if(Setting::get('track_distance') == 1) checked @endif
									name="track_distance" type="checkbox" class="js-switch" data-color="#43b968"></div>
						</div>
					</div>

					<div class="form-group row">
						<label for="contact_number"
							class="col-xs-2 col-form-label">@lang('admin.setting.Contact_Number')</label>
						<div class="col-xs-10">
							<input class="form-control" type="number" value="{{ Setting::get('contact_number', '911')  }}"
								name="contact_number" required id="contact_number" placeholder="Contact Number">
						</div>
					</div>

					<div class="form-group row">
						<label for="contact_email"
							class="col-xs-2 col-form-label">@lang('admin.setting.Contact_Email')</label>
						<div class="col-xs-10">
							<input class="form-control" type="email" value="{{ Setting::get('contact_email', '')  }}"
								name="contact_email" required id="contact_email" placeholder="Contact Email">
						</div>
					</div>

					<div class="form-group row">
						<label for="social_login"
							class="col-xs-2 col-form-label">@lang('admin.setting.Social_Login')</label>
						<div class="col-xs-10">
							<select class="form-control" id="social_login" name="social_login">
								<option value="1" @if(Setting::get('social_login', 0) == 1) selected @endif>
									@lang('admin.Enable')</option>
								<option value="0" @if(Setting::get('social_login', 0) == 0) selected @endif>
									@lang('admin.Disable')</option>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="map_key" class="col-xs-2 col-form-label">@lang('admin.setting.map_key')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('map_key')  }}" name="map_key"
								required id="map_key" placeholder="@lang('admin.setting.map_key')">
						</div>
					</div>

					<div class="form-group row">
						<label for="eco_currency_name" class="col-xs-2 col-form-label">Monnaie ECO</label>
						<div class="col-xs-10">
							<select class="form-control" id="eco_currency_name" name="eco_currency_name">
								<option value="ECO" @if(Setting::get('eco_currency_name', 'ECO') == 'ECO') selected @endif>ECO
								</option>
								<option value="ECO/CFA" @if(Setting::get('eco_currency_name', 'ECO') == 'ECO/CFA') selected
								@endif>ECO/CFA</option>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="fb_app_version"
							class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_version')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('fb_app_version')  }}"
								name="fb_app_version" required id="fb_app_version"
								placeholder="@lang('admin.setting.fb_app_version')">
						</div>
					</div>

					<div class="form-group row">
						<label for="fb_app_id" class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_id')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('fb_app_id')  }}"
								name="fb_app_id" required id="fb_app_id" placeholder="@lang('admin.setting.fb_app_id')">
						</div>
					</div>

					<div class="form-group row">
						<label for="fb_app_secret"
							class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_secret')</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('fb_app_secret')  }}"
								name="fb_app_secret" required id="fb_app_secret"
								placeholder="@lang('admin.setting.fb_app_secret')">
						</div>
					</div>

					<div class="form-group row">
						<label for="cinetpay_site_id"
							class="col-xs-2 col-form-label">CinetPay Site ID</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('cinetpay_site_id', env('CINETPAY_SITE_ID'))  }}"
								name="cinetpay_site_id" id="cinetpay_site_id"
								placeholder="Ex: 586548">
						</div>
					</div>

					<div class="form-group row">
						<label for="cinetpay_api_key"
							class="col-xs-2 col-form-label">CinetPay API Key</label>
						<div class="col-xs-10">
							<input class="form-control" type="text" value="{{ Setting::get('cinetpay_api_key', env('CINETPAY_API_KEY'))  }}"
								name="cinetpay_api_key" id="cinetpay_api_key"
								placeholder="Ex: 1234567890abcdef">
						</div>
					</div>

					<div class="form-group row">
						<label for="payment_gateway"
							class="col-xs-2 col-form-label">Passerelle de Paiement par défaut</label>
						<div class="col-xs-10">
							<select class="form-control" id="payment_gateway" name="payment_gateway">
								<option value="MANUAL" @if(Setting::get('payment_gateway', env('PAYMENT_GATEWAY', 'MANUAL')) == 'MANUAL') selected @endif>Système Manuel (Soft Launch)</option>
								<option value="CINETPAY" @if(Setting::get('payment_gateway', env('PAYMENT_GATEWAY', 'MANUAL')) == 'CINETPAY') selected @endif>CinetPay (Production)</option>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="zipcode" class="col-xs-2 col-form-label"></label>
						<div class="col-xs-10">
							<button type="submit"
								class="btn btn-primary">@lang('admin.setting.Update_Site_Settings')</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection