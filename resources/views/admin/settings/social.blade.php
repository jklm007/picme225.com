@extends('admin.layout.base')

@section('title', 'Configuration Réseaux Sociaux (API) ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5>Configuration des API Facebook & TikTok</h5>
            <p class="font-12 text-muted mb-3">Saisissez les identifiants et jetons d'accès (Access Tokens) générés sur Meta for Developers et TikTok for Developers pour permettre la publication automatique des annonces sur vos pages.</p>

            <form class="form-horizontal" action="{{ route('admin.settings.social.store') }}" method="POST" role="form">
                {{ csrf_field() }}

                <h4 class="mb-2"><i class="fa fa-facebook-square text-primary"></i> Facebook Page API</h4>
                <div class="form-group row">
                    <label for="facebook_page_id" class="col-xs-12 col-md-3 col-form-label">Page ID</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('facebook_page_id', '') }}" name="facebook_page_id" id="facebook_page_id" placeholder="Ex: 123456789012345">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="facebook_access_token" class="col-xs-12 col-md-3 col-form-label">Access Token (Long Lived)</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('facebook_access_token', '') }}" name="facebook_access_token" id="facebook_access_token" placeholder="EAA...">
                    </div>
                </div>

                <hr>

                <h4 class="mb-2 mt-4"><i class="fa fa-music text-dark"></i> TikTok API (Direct Post)</h4>
                
                <div class="form-group row">
                    <label for="tiktok_client_key" class="col-xs-12 col-md-3 col-form-label">Client Key</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('tiktok_client_key', '') }}" name="tiktok_client_key" id="tiktok_client_key" placeholder="Client Key TikTok">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="tiktok_client_secret" class="col-xs-12 col-md-3 col-form-label">Client Secret</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('tiktok_client_secret', '') }}" name="tiktok_client_secret" id="tiktok_client_secret" placeholder="Client Secret">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="tiktok_access_token" class="col-xs-12 col-md-3 col-form-label">Access Token</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('tiktok_access_token', '') }}" name="tiktok_access_token" id="tiktok_access_token" placeholder="Access Token">
                    </div>
                </div>

                <div class="form-group row mt-4">
                    <div class="col-xs-12 col-md-3 offset-md-9">
                        <button type="submit" class="btn btn-primary btn-block">Enregistrer les Configurations</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
