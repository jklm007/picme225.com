<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .header-critical {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .header-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #333;
        }
        .header-notice {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .countdown {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        .alert-box {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .alert-critical {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
        }
        .alert-notice {
            background: #d1ecf1;
            border-color: #17a2b8;
        }
        .action-button {
            display: inline-block;
            padding: 15px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header header-{{ $urgency }}">
        @if($urgency === 'critical')
            <h1>🚨 ALERTE URGENTE TVA</h1>
        @elseif($urgency === 'warning')
            <h1>⚠️ RAPPEL TVA</h1>
        @else
            <h1>ℹ️ NOTIFICATION TVA</h1>
        @endif
        <div class="countdown">{{ $days_remaining }} JOURS</div>
        <p>avant l'échéance de déclaration</p>
    </div>

    <p>Bonjour {{ $admin->first_name }},</p>

    <div class="alert-box alert-{{ $urgency }}">
        @if($urgency === 'critical')
            <strong>🚨 ACTION IMMÉDIATE REQUISE</strong><br>
            L'échéance de déclaration TVA est dans <strong>{{ $days_remaining }} jour(s)</strong> seulement !
        @elseif($urgency === 'warning')
            <strong>⚠️ RAPPEL IMPORTANT</strong><br>
            L'échéance de déclaration TVA approche : <strong>{{ $days_remaining }} jours</strong> restants.
        @else
            <strong>ℹ️ INFORMATION</strong><br>
            L'échéance de déclaration TVA est dans <strong>{{ $days_remaining }} jours</strong>.
        @endif
    </div>

    <h3>📅 Détails de l'Échéance</h3>
    <ul>
        <li><strong>Date limite :</strong> {{ $deadline->format('d/m/Y') }}</li>
        <li><strong>Période concernée :</strong> {{ $period }}</li>
        <li><strong>Jours restants :</strong> {{ $days_remaining }}</li>
    </ul>

    <h3>✅ Actions à Effectuer</h3>
    <ol>
        <li>Consulter le rapport TVA du mois concerné</li>
        <li>Vérifier les montants et transactions</li>
        <li>Préparer la déclaration fiscale</li>
        <li>Soumettre avant le {{ $deadline->format('d/m/Y') }}</li>
    </ol>

    <center>
        <a href="{{ url('/admin/tva-accounting') }}" class="action-button">
            📊 Accéder au Dashboard TVA
        </a>
    </center>

    @if($urgency === 'critical')
    <div class="alert-box alert-critical">
        <strong>⚠️ ATTENTION</strong><br>
        Le non-respect de l'échéance peut entraîner des pénalités fiscales. Veuillez agir rapidement.
    </div>
    @endif

    <div class="footer">
        <p>Cette alerte a été générée automatiquement par le système PicMe225.</p>
        <p>© {{ date('Y') }} PicMe225 - Tous droits réservés</p>
    </div>
</body>
</html>
