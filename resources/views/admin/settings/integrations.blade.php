@extends('admin.layout.base')

@section('title', 'Intégrations & APIs')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5>Configurations Centralisées (Cloud, WhatsApp, Sociaux, Ads)</h5>
            <p class="font-12 text-muted mb-3">Les modifications effectuées ici sont appliquées instantanément sur les serveurs sans redémarrage.</p>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form class="form-horizontal" action="{{ route('admin.settings.integrations.store') }}" method="POST" role="form">
                {{ csrf_field() }}

                <!-- CLOUDFLARE R2 SECTION -->
                <h4 class="mb-2"><i class="fa fa-cloud text-info"></i> Cloudflare R2 (Stockage Fichiers)</h4>
                <div class="form-group row">
                    <label for="r2_access_key" class="col-xs-12 col-md-3 col-form-label">Access Key ID</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('r2_access_key', '') }}" name="r2_access_key" id="r2_access_key" placeholder="Ex: e6b48...">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="r2_secret_key" class="col-xs-12 col-md-3 col-form-label">Secret Access Key</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('r2_secret_key', '') }}" name="r2_secret_key" id="r2_secret_key" placeholder="Secret...">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="r2_endpoint" class="col-xs-12 col-md-3 col-form-label">S3 Endpoint</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('r2_endpoint', '') }}" name="r2_endpoint" id="r2_endpoint" placeholder="https://<account_id>.r2.cloudflarestorage.com">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="r2_bucket" class="col-xs-12 col-md-3 col-form-label">Nom du Bucket</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('r2_bucket', 'picme225-bucket') }}" name="r2_bucket" id="r2_bucket" placeholder="picme225-bucket">
                    </div>
                </div>

                <hr>

                <!-- WHATSAPP API SECTION -->
                <h4 class="mb-2 mt-4"><i class="fa fa-whatsapp text-success"></i> WhatsApp API (Evolution)</h4>
                <div class="form-group row">
                    <label for="evolution_api_url" class="col-xs-12 col-md-3 col-form-label">API URL</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('evolution_api_url', 'http://evolution-api-service:8080') }}" name="evolution_api_url" id="evolution_api_url">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="evolution_api_key" class="col-xs-12 col-md-3 col-form-label">API Key (Global)</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('evolution_api_key', '') }}" name="evolution_api_key" id="evolution_api_key">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="evolution_instance" class="col-xs-12 col-md-3 col-form-label">Nom Instance Principale</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('evolution_instance', 'picme_whatsapp') }}" name="evolution_instance" id="evolution_instance">
                    </div>
                </div>

                <hr>

                <!-- FACEBOOK SECTION -->
                <h4 class="mb-2 mt-4"><i class="fa fa-facebook-square text-primary"></i> Facebook Page API</h4>
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

                <!-- TIKTOK SECTION -->
                <h4 class="mb-2 mt-4"><i class="fa fa-music text-dark"></i> TikTok API</h4>
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

                <hr>

                <!-- GOOGLE ADS SECTION -->
                <h4 class="mb-2 mt-4"><i class="fa fa-google text-danger"></i> Google Ads API</h4>
                <div class="form-group row">
                    <label for="google_ads_customer_id" class="col-xs-12 col-md-3 col-form-label">Client/Customer ID</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('google_ads_customer_id', '') }}" name="google_ads_customer_id" id="google_ads_customer_id" placeholder="Ex: 123-456-7890">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="google_ads_developer_token" class="col-xs-12 col-md-3 col-form-label">Developer Token</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('google_ads_developer_token', '') }}" name="google_ads_developer_token" id="google_ads_developer_token" placeholder="Developer Token">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="google_ads_client_id" class="col-xs-12 col-md-3 col-form-label">OAuth Client ID</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="text" value="{{ Setting::get('google_ads_client_id', '') }}" name="google_ads_client_id" id="google_ads_client_id" placeholder="OAuth Client ID">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="google_ads_client_secret" class="col-xs-12 col-md-3 col-form-label">OAuth Client Secret</label>
                    <div class="col-xs-12 col-md-9">
                        <input class="form-control" type="password" value="{{ Setting::get('google_ads_client_secret', '') }}" name="google_ads_client_secret" id="google_ads_client_secret" placeholder="OAuth Client Secret">
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
