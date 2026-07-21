@extends('agent.layout')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-8 text-center">
        <h2 class="mb-3 fw-bold">Bonjour, {{ $user->first_name }} !</h2>
        <p class="text-muted mb-5 fs-5">Bienvenue sur votre terminal de contrôle. Que souhaitez-vous faire ?</p>

        <div class="d-grid gap-4 mt-4">
            <a href="{{ route('agent.scanner') }}" class="btn btn-success btn-lg py-4 fs-3 fw-bold shadow-lg rounded-4 d-flex flex-column align-items-center justify-content-center">
                <span style="font-size: 3rem; margin-bottom: 10px;">📷</span>
                SCANNER UN BILLET
            </a>

            <a href="{{ route('agent.cashdesk') }}" class="btn btn-primary btn-lg py-4 fs-3 fw-bold shadow-lg rounded-4 d-flex flex-column align-items-center justify-content-center">
                <span style="font-size: 3rem; margin-bottom: 10px;">💰</span>
                VENDRE À LA PORTE
            </a>
        </div>
    </div>
</div>
@endsection
