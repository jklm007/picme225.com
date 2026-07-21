@extends('agent.layout')

@section('styles')
<style>
    #reader {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        border: 2px solid #333;
        border-radius: 10px;
        overflow: hidden;
        background: #000;
    }
    .result-box {
        display: none;
        padding: 30px;
        border-radius: 15px;
        margin-top: 20px;
        text-align: center;
    }
    .result-success {
        background-color: #198754;
        color: white;
    }
    .result-error {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 text-center">
        <h3 class="mb-4 fw-bold">Scanner de Billets</h3>
        
        <div id="reader" class="shadow"></div>

        <div id="resultBox" class="result-box shadow-lg">
            <h1 id="resultIcon" style="font-size: 4rem;">✅</h1>
            <h2 id="resultTitle" class="fw-bold mb-2 mt-3"></h2>
            <p id="resultText" class="fs-4 mb-0"></p>
            <p id="resultUser" class="fs-6 mt-3 opacity-75"></p>
            <button class="btn btn-light btn-lg mt-4 w-100 fw-bold shadow-sm" onclick="resumeScanner()">SUIVANT</button>
        </div>

        <div class="mt-5 p-4 rounded" id="manualEntryBox" style="background: #1e1e1e;">
            <p class="text-muted fw-bold mb-2">Ou saisir le code manuellement :</p>
            <div class="input-group input-group-lg">
                <input type="text" id="manualCode" class="form-control" placeholder="PKM-..." aria-label="Code du billet">
                <button class="btn btn-primary fw-bold" type="button" onclick="submitManualCode()">Valider</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrcodeScanner;
    let isProcessing = false;

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;
        
        if (html5QrcodeScanner) {
            html5QrcodeScanner.pause(true);
        }

        processCode(decodedText);
    }

    function processCode(code) {
        $.ajax({
            url: '{{ route("agent.processScan") }}',
            type: 'POST',
            data: {
                qr_code: code
            },
            success: function(response) {
                showResult(true, 'ACCÈS AUTORISÉ', response.message, response.customer);
            },
            error: function(xhr) {
                let msg = 'Erreur de validation.';
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                showResult(false, 'REFUSÉ', msg, null);
            }
        });
    }

    function showResult(isSuccess, title, subtitle, customer) {
        $('#reader').slideUp();
        $('#manualEntryBox').slideUp();
        let box = $('#resultBox');
        
        box.removeClass('result-success result-error');
        if (isSuccess) {
            box.addClass('result-success');
            $('#resultIcon').text('✅');
        } else {
            box.addClass('result-error');
            $('#resultIcon').text('❌');
        }
        
        $('#resultTitle').text(title);
        $('#resultText').text(subtitle || '');
        if (customer) {
            $('#resultUser').text('Client : ' + customer);
        } else {
            $('#resultUser').text('');
        }
        
        box.slideDown();
    }

    function resumeScanner() {
        $('#resultBox').slideUp(function() {
            $('#reader').slideDown();
            $('#manualEntryBox').slideDown();
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume();
            }
            $('#manualCode').val('');
            isProcessing = false;
        });
    }

    function submitManualCode() {
        let code = $('#manualCode').val();
        if (code) {
            isProcessing = true;
            if (html5QrcodeScanner) html5QrcodeScanner.pause(true);
            processCode(code);
        }
    }

    $(document).ready(function() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 },
            false
        );
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>
@endsection
