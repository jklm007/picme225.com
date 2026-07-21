# Snippets Dashboard Dispatcher (Blade/JS)

Ces snippets sont à intégrer dans les vues Blade existantes du Dispatcher Panel (`resources/views/dispatcher/`).

## 1. Modal d'Assignation Hybride

Ajoutez ce modal dans votre layout principal ou la page de gestion des courses.

```html
<!-- Modal Assignation -->
<div class="modal fade" id="assignDriverModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assigner un Chauffeur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="assign_request_id">
        
        <!-- Option 1: Broadcast -->
        <div class="card mb-3">
          <div class="card-body text-center">
            <h5>Assignation Automatique (Broadcast)</h5>
            <p>Envoyer la demande à tous les chauffeurs dans un rayon de 10km.</p>
            <button class="btn btn-primary btn-lg btn-block" onclick="broadcastDrivers()">
              <i class="fa fa-bullhorn"></i> LANCER BROADCAST
            </button>
          </div>
        </div>

        <hr>

        <!-- Option 2: Manuel -->
        <div class="card">
            <div class="card-body">
                <h5>Assignation Manuelle</h5>
                <div class="form-group">
                    <label>Rechercher un chauffeur</label>
                    <select class="form-control" id="manual_provider_select">
                        <!-- Rempli via AJAX -->
                    </select>
                </div>
                <button class="btn btn-success btn-block" onclick="assignManual()">
                    Assigner ce chauffeur
                </button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

## 2. Scripts JavaScript (jQuery)

Ajoutez ces fonctions dans votre fichier JS principal ou dans une balise `<script>` au bas de la page.

```javascript
// Ouvrir le modal
function openAssignModal(requestId) {
    $('#assign_request_id').val(requestId);
    $('#assignDriverModal').modal('show');
    loadAvailableProviders(); // Fonction existante pour charger les providers
}

// 1. Broadcast
function broadcastDrivers() {
    var requestId = $('#assign_request_id').val();
    
    $.ajax({
        url: '/api/dispatcher/broadcast-drivers',
        type: 'POST',
        data: {
            request_id: requestId,
            radius: 10
        },
        headers: {
            'Authorization': 'Bearer ' + YOUR_API_TOKEN // Assurez-vous d'avoir le token
        },
        success: function(response) {
            toastr.success('Broadcast envoyé à ' + response.data.count + ' chauffeurs !');
            $('#assignDriverModal').modal('hide');
            // Rafraîchir la liste des courses
        },
        error: function(err) {
            toastr.error('Erreur: ' + err.responseJSON.error);
        }
    });
}

// 2. Assignation Manuelle
function assignManual() {
    var requestId = $('#assign_request_id').val();
    var providerId = $('#manual_provider_select').val();

    if(!providerId) {
        alert('Veuillez sélectionner un chauffeur');
        return;
    }

    $.ajax({
        url: '/api/dispatcher/assign-driver',
        type: 'POST',
        data: {
            request_id: requestId,
            provider_id: providerId
        },
        headers: {
            'Authorization': 'Bearer ' + YOUR_API_TOKEN
        },
        success: function(response) {
            toastr.success('Chauffeur assigné avec succès !');
            $('#assignDriverModal').modal('hide');
            // Rafraîchir la liste
        },
        error: function(err) {
            toastr.error('Erreur: ' + err.responseJSON.error);
        }
    });
}
```

## 3. Bouton "Forcer Validation" (Page Détails Course)

Si un passager a un problème de téléphone, le dispatcher peut valider manuellement.

```html
<div class="card border-danger">
    <div class="card-header bg-danger text-white">Zone Danger</div>
    <div class="card-body">
        <p>Le passager ne peut pas présenter son QR Code ?</p>
        <div class="input-group">
            <input type="text" class="form-control" id="force_ticket_token" placeholder="Entrer le Token du Ticket">
            <div class="input-group-append">
                <button class="btn btn-warning" onclick="forceValidation()">Forcer Validation</button>
            </div>
        </div>
    </div>
</div>

<script>
function forceValidation() {
    var token = $('#force_ticket_token').val();
    if(!token) return;

    if(!confirm("Êtes-vous sûr de vouloir forcer la validation ? Cette action sera logguée.")) return;

    $.ajax({
        url: '/api/dispatcher/force-validation',
        type: 'POST',
        data: { ticket_token: token, reason: 'Dispatcher Manual Force' },
        headers: { 'Authorization': 'Bearer ' + YOUR_API_TOKEN },
        success: function(res) {
            toastr.success('Ticket validé manuellement.');
            location.reload();
        }
    });
}
</script>
```
