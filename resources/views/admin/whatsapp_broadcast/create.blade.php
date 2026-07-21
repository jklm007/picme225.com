@extends('admin.layout.base')

@section('title', 'WhatsApp AI Broadcast')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 style="margin-bottom: 2em;">
                <i class="fa fa-whatsapp text-success"></i> WhatsApp Broadcast & AI Assistant
            </h5>

            @if(session('flash_success'))
                <div class="alert alert-success">{{ session('flash_success') }}</div>
            @endif

            <form class="form-horizontal" action="{{ route('admin.whatsapp.broadcast.send') }}" method="POST" enctype="multipart/form-data" id="broadcast_form">
                {{ csrf_field() }}

                <!-- Target Selection -->
                <div class="form-group row">
                    <label class="col-xs-12 col-md-2 col-form-label">Envoyer à :</label>
                    <div class="col-xs-12 col-md-10">
                        <select class="form-control" name="target" required>
                            <option value="GROUPS">Groupes WhatsApp uniquement</option>
                            <option value="USERS">Utilisateurs uniquement</option>
                            <option value="PROVIDERS">Fournisseurs uniquement</option>
                            <option value="ALL">Utilisateurs & Fournisseurs</option>
                            <option value="ALL_WITH_GROUPS">Groupes + Utilisateurs + Fournisseurs</option>
                        </select>
                    </div>
                </div>

                <!-- AI Prompt Section -->
                <div class="form-group row bg-light p-2" style="background-color: #f8f9fa; border-radius: 5px; margin: 10px 0;">
                    <label class="col-xs-12 col-form-label">
                        <strong><i class="fa fa-magic"></i> Générateur de message IA (Groq Llama 3)</strong>
                    </label>
                    <div class="col-xs-12 col-md-8">
                        <input type="text" id="ai_prompt" class="form-control" placeholder="Ex: Rédige un message marketing pour annoncer que notre site beta est en ligne...">
                        <small class="text-muted">Décrivez le message que vous voulez envoyer. Le ton sera Marketing/Vente.</small>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <button type="button" class="btn btn-info btn-block" id="btn_generate_ai">
                            Générer avec l'IA
                        </button>
                    </div>
                </div>

                <!-- Message Textarea -->
                <div class="form-group row">
                    <label for="message" class="col-xs-12 col-md-2 col-form-label">Message WhatsApp</label>
                    <div class="col-xs-12 col-md-10">
                        <textarea class="form-control" rows="8" name="message" id="message" required placeholder="Saisissez ou générez votre message ici..."></textarea>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="form-group row">
                    <label for="image" class="col-xs-12 col-md-2 col-form-label">Image (Optionnel)</label>
                    <div class="col-xs-12 col-md-10">
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">
                        <small id="fileHelp" class="form-text text-muted">Ajoutez une image promotionnelle (JPEG, PNG).</small>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-group row">
                    <div class="col-xs-12 col-md-10 offset-md-2">
                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Êtes-vous sûr de vouloir envoyer ce message à la cible sélectionnée ?');">
                            <i class="fa fa-paper-plane"></i> Envoyer le Broadcast
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('#btn_generate_ai').click(function() {
        let prompt = $('#ai_prompt').val();
        if (!prompt) {
            alert('Veuillez entrer une description pour l\'IA.');
            return;
        }

        let btn = $(this);
        let originalText = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i> Génération...');
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.whatsapp.broadcast.generate') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                prompt: prompt
            },
            success: function(response) {
                if(response.success) {
                    $('#message').val(response.message);
                } else {
                    alert('Erreur IA: ' + response.error);
                }
            },
            error: function() {
                alert('Erreur de connexion au serveur IA.');
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
</script>
@endsection
