@extends('admin.layout.base')

@section('title', 'Vendre un Billet (Guichet)')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.tickets.sold') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Guichet : Vendre un Billet</h5>

            @if(session('flash_error'))
                <div class="alert alert-danger">{{ session('flash_error') }}</div>
            @endif

            <form action="{{ route('admin.tickets.sell.store') }}" method="POST">
                {{ csrf_field() }}
                
                <div class="form-group row">
                    <label for="listing_id" class="col-xs-2 col-form-label">Événement</label>
                    <div class="col-xs-10">
                        <select name="listing_id" id="listing_id" class="form-control" required onchange="loadPasses(this.value)">
                            <option value="">-- Sélectionnez l'événement --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} - {{ number_format($event->price, 0, ',', ' ') }} FCFA</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="pass_type_id" class="col-xs-2 col-form-label">Type de Pass</label>
                    <div class="col-xs-10">
                        <select name="pass_type_id" id="pass_type_id" class="form-control">
                            <option value="-1">Billet Standard (par défaut)</option>
                        </select>
                        <small class="form-text text-muted">Le prix par défaut sera appliqué si aucun pass spécifique n'est sélectionné.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="first_name" class="col-xs-2 col-form-label">Prénom du Client</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" name="first_name" id="first_name" placeholder="Ex: Jean (Optionnel)">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="last_name" class="col-xs-2 col-form-label">Nom du Client</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" name="last_name" id="last_name" placeholder="Ex: Dupont (Optionnel)">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="email" class="col-xs-2 col-form-label">Email du Client</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="email" name="email" id="email" placeholder="Ex: client@email.com (Optionnel)">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="phone" class="col-xs-2 col-form-label">WhatsApp du Client</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" name="phone" id="phone" placeholder="Ex: 0700000000" required>
                        <small class="form-text text-muted">Saisissez le numéro sans l'indicatif (+225 ajouté automatiquement si omis). Si le client n'a pas de compte, un compte sera créé avec les informations ci-dessus.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="payment_mode" class="col-xs-2 col-form-label">Mode de Paiement</label>
                    <div class="col-xs-10">
                        <select name="payment_mode" id="payment_mode" class="form-control" required>
                            <option value="ADMIN_CASH">Cash (Guichet)</option>
                            <option value="WALLET">Wallet (Si le client a des fonds)</option>
                        </select>
                        <small class="form-text text-muted">En mode Cash, le système prélève la commission sur le portefeuille du vendeur.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-xs-2 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary btn-block">Générer et Envoyer le Billet</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function loadPasses(listingId) {
    var passSelect = $('#pass_type_id');
    passSelect.empty();
    passSelect.append('<option value="-1">Billet Standard (par défaut)</option>');
    
    if(!listingId) return;
    
    $.ajax({
        url: '/admin/tickets/api/passes/' + listingId,
        type: 'GET',
        success: function(data) {
            if(data && data.length > 0) {
                $.each(data, function(index, pass) {
                    passSelect.append('<option value="' + pass.id + '">' + pass.pass_name + ' - ' + new Intl.NumberFormat('fr-FR').format(pass.price) + ' FCFA (' + pass.persons_per_pass + ' pers.)</option>');
                });
            }
        },
        error: function(err) {
            console.error("Erreur chargement pass:", err);
        }
    });
}
</script>
@endsection
