@extends('admin.layout.base')

@section('title', 'Gestion des APKs ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5>Mise à jour des Applications Mobiles (APK)</h5>
            <p class="font-12 text-muted mb-3">Téléversez manuellement les dernières versions des applications, ou utilisez les versions par défaut générées par le système.</p>

            <form class="form-horizontal" action="{{ route('admin.settings.apks.store') }}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}

                <!-- APK UTILISATEUR -->
                <div class="form-group row">
                    <label for="user_apk" class="col-xs-12 col-form-label">Application Utilisateur (Passager/Client)</label>
                    <div class="col-xs-8">
                        <input type="file" accept=".apk" name="user_apk" class="dropify form-control-file" id="user_apk" aria-describedby="fileHelp">
                        <small id="fileHelp" class="form-text text-muted">Format attendu : .apk (Max 100 Mo)</small>
                        <div class="mt-2">
                            <strong>Fichier actuellement servi :</strong> 
                            <span class="text-primary">{{ Setting::get('user_apk_path', 'user_apk_default.apk') }}</span>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        @if(Setting::get('user_apk_path') === 'user_apk_custom.apk')
                        <a href="{{ route('admin.settings.apks.reset', ['type' => 'user']) }}" class="btn btn-warning btn-sm mt-2">
                            Réinitialiser (Utiliser le fichier par défaut)
                        </a>
                        @else
                        <span class="badge badge-success mt-2">Fichier par défaut actif</span>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- APK CHAUFFEUR -->
                <div class="form-group row">
                    <label for="driver_apk" class="col-xs-12 col-form-label">Application Chauffeur (Optionnel)</label>
                    <div class="col-xs-8">
                        <input type="file" accept=".apk" name="driver_apk" class="dropify form-control-file" id="driver_apk" aria-describedby="fileHelp2">
                        <small id="fileHelp2" class="form-text text-muted">Format attendu : .apk (Max 100 Mo)</small>
                        <div class="mt-2">
                            <strong>Fichier actuellement servi :</strong> 
                            <span class="text-primary">{{ Setting::get('driver_apk_path', 'driver_apk_default.apk') }}</span>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        @if(Setting::get('driver_apk_path') === 'driver_apk_custom.apk')
                        <a href="{{ route('admin.settings.apks.reset', ['type' => 'driver']) }}" class="btn btn-warning btn-sm mt-2">
                            Réinitialiser (Utiliser le fichier par défaut)
                        </a>
                        @else
                        <span class="badge badge-success mt-2">Fichier par défaut actif</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-12 col-md-3 offset-md-9">
                        <button type="submit" class="btn btn-primary btn-block">Enregistrer les APKs</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
