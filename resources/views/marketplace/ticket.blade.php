<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Ticket - {{ $listing->title }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F1F5F9; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .ticket-card { background: white; width: 100%; max-width: 380px; border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 1px solid #E2E8F0; }
        .header { background: #1E293B; color: white; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
        .content { padding: 30px; text-align: center; position: relative; }
        .qr-wrapper { background: #FFFFFF; border: 1px solid #F1F5F9; padding: 15px; border-radius: 20px; display: inline-block; margin-bottom: 25px; }
        .qr-code { width: 220px; height: 220px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left; margin-bottom: 25px; padding: 0 10px; }
        .info-item label { display: block; font-size: 11px; color: #94A3B8; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
        .info-item span { display: block; font-size: 15px; color: #1E293B; font-weight: 600; }
        .divider { height: 1px; background: #E2E8F0; margin: 20px 0; position: relative; border-bottom: 2px dashed #CBD5E1; }
        .divider::before, .divider::after { content: ''; position: absolute; top: -10px; width: 20px; height: 20px; background: #F1F5F9; border-radius: 50%; }
        .divider::before { left: -40px; }
        .divider::after { right: -40px; }
        .footer { padding: 20px; background: #F8FAFC; color: #64748B; font-size: 12px; text-align: center; border-top: 1px solid #E2E8F0; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 12px; text-transform: uppercase; margin-top: 10px; }
        .status-paid { background: #DCFCE7; color: #15803d; }
        .status-used { background: #F1F5F9; color: #64748B; }
    </style>
</head>
<body>

    <div class="ticket-card">
        <div class="header">
            <h1>{{ $listing->title }}</h1>
            <p>{{ $pass->name ?? 'Entrée Standard' }}</p>
        </div>

        <div class="content">
            <div class="qr-wrapper">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($ticket->qr_code) }}" 
                     alt="QR Code" class="qr-code">
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label>Billet N°</label>
                    <span>#{{ $ticket->id }}</span>
                </div>
                <div class="info-item">
                    <label>Prix</label>
                    <span>{{ number_format($ticket->total_price, 0) }} FCFA</span>
                </div>
                <div class="info-item" style="grid-column: span 2;">
                    <label>Date de l'achat</label>
                    <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                @php
                    $meta = is_string($ticket->metadata) ? json_decode($ticket->metadata) : $ticket->metadata;
                    $persons = $pass->persons_per_pass ?? ($meta->persons_per_pass ?? 1);
                @endphp
                @if($persons > 1)
                <div class="info-item" style="grid-column: span 2; background: #FFFBEB; border: 1px solid #FEF3C7; padding: 10px; border-radius: 12px; margin-top: 10px;">
                    <label style="color: #D97706;">Validité du Pass</label>
                    <span style="color: #B45309; font-weight: 700;">Valable pour {{ $persons }} personnes</span>
                </div>
                @endif
            </div>

            <div class="divider"></div>

            <div class="status-badge {{ $ticket->status == 'USED' ? 'status-used' : 'status-paid' }}">
                {{ $ticket->status == 'USED' ? 'DÉJÀ UTILISÉ' : 'TICKET VALIDE' }}
            </div>
        </div>

        <div class="footer">
            Présentez ce QR Code à l'entrée de l'événement.<br>
            <strong>Propulsé par Jews-world Marketplace</strong>
        </div>
    </div>

</body>
</html>
