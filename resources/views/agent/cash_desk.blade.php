@extends('agent.layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8">
        <h3 class="mb-4 text-center fw-bold">Vente à la Porte (CASH)</h3>
        
        @if(!$event)
            <div class="alert alert-warning text-center">Aucun événement récent trouvé.</div>
        @elseif(count($passes) == 0)
            <div class="alert alert-warning text-center">Aucun type de pass configuré pour l'événement : <strong>{{ $event->title }}</strong>.</div>
        @else
            <div class="card bg-dark text-white shadow-lg border-secondary">
                <div class="card-body p-4">
                    <h5 class="card-title text-center mb-4">Événement : <span class="text-primary">{{ $event->title }}</span></h5>
                    
                    <form id="saleForm">
                        <input type="hidden" id="eventId" value="{{ $event->id }}">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Type de Pass</label>
                            <div class="d-grid gap-2">
                                @foreach($passes as $pass)
                                    <input type="radio" class="btn-check" name="pass_type" id="pass_{{ $pass->id }}" value="{{ $pass->id }}" autocomplete="off" required>
                                    <label class="btn btn-outline-primary text-start p-3 d-flex justify-content-between align-items-center" for="pass_{{ $pass->id }}">
                                        <div>
                                            <strong>{{ $pass->name }}</strong><br>
                                            <small class="text-white-50">Accès : {{ $pass->persons_per_pass ?: 1 }} personne(s)</small>
                                        </div>
                                        <span class="badge bg-primary fs-5">{{ $pass->price }} FCFA</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom du client (Optionnel)</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="customerName" placeholder="Ex: Jean Dupont">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Téléphone (Optionnel)</label>
                            <input type="tel" class="form-control bg-dark text-white border-secondary" id="customerPhone" placeholder="Ex: 0707070707">
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg py-3 fw-bold" id="btnSubmit">
                            ENCAISSER ET VALIDER
                        </button>
                    </form>
                </div>
            </div>

            <!-- Result Modal -->
            <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <div class="modal-body text-center p-5">
                            <h1 class="text-success mb-3" style="font-size: 4rem;">✅</h1>
                            <h3 class="fw-bold mb-3">Vente Réussie</h3>
                            <p class="fs-5 text-muted">Le paiement a été enregistré et l'accès est accordé.</p>
                            <button type="button" class="btn btn-primary btn-lg mt-3 w-100 fw-bold" data-bs-dismiss="modal" onclick="$('#saleForm')[0].reset()">NOUVELLE VENTE</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('#saleForm').submit(function(e) {
        e.preventDefault();
        
        let passId = $('input[name="pass_type"]:checked').val();
        if (!passId) {
            alert('Veuillez sélectionner un pass.');
            return;
        }

        let btn = $('#btnSubmit');
        let originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...').prop('disabled', true);

        $.ajax({
            url: '{{ route("agent.processSale") }}',
            type: 'POST',
            data: {
                event_id: $('#eventId').val(),
                pass_type_id: passId,
                customer_name: $('#customerName').val(),
                customer_phone: $('#customerPhone').val()
            },
            success: function(response) {
                btn.html(originalText).prop('disabled', false);
                var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                resultModal.show();
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                let msg = 'Erreur lors de la vente.';
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                alert(msg);
            }
        });
    });
</script>
@endsection
