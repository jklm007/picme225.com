@extends('admin.layout.base')
@section('title', 'Connexion WhatsApp')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">
                Connexion WhatsApp IA
            </h5>
            <p class="mb-2">Scannez ce QR Code avec le téléphone dédié pour connecter le numéro WhatsApp de PicMe.</p>

            <div class="text-center mt-4">
                @if(isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    <img src="{{ $qrCode }}" alt="QR Code WhatsApp" style="max-width: 300px; border: 1px solid #ccc; padding: 10px; border-radius: 10px;">
                    <p class="mt-2 text-muted">Ce QR Code expire dans quelques secondes.</p>
                @endif
            </div>

            <div class="mt-4">
                <h6>Instructions :</h6>
                <ol>
                    <li>Ouvrez <strong>WhatsApp</strong> sur le téléphone dédié</li>
                    <li>Allez dans <strong>Paramètres</strong> > <strong>Appareils connectés</strong></li>
                    <li>Appuyez sur <strong>Connecter un appareil</strong></li>
                    <li>Scannez le QR Code ci-dessus</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
