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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .footer {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Rapport TVA Mensuel</h1>
        <p>{{ $month_name }}</p>
    </div>

    <p>Bonjour {{ $admin->first_name }},</p>
    
    <p>Voici le rapport TVA automatique pour le mois de <strong>{{ $month_name }}</strong>.</p>

    @if($exemption_active)
    <div class="alert alert-info">
        <strong>ℹ️ Exonération Active</strong><br>
        Votre entreprise bénéficie actuellement d'une exonération de TVA. Les montants ci-dessous représentent la TVA virtuelle qui aurait été collectée.
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ number_format($data->tva_collected ?? 0) }} CFA</div>
            <div class="stat-label">TVA Collectée</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($data->total_transactions ?? 0) }}</div>
            <div class="stat-label">Transactions</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($data->effective_rate ?? 0, 2) }}%</div>
            <div class="stat-label">Taux Effectif</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($data->total_commission ?? 0) }} CFA</div>
            <div class="stat-label">Base Imposable</div>
        </div>
    </div>

    <h3>Détails Financiers</h3>
    <table>
        <tr>
            <th>Indicateur</th>
            <th style="text-align: right;">Montant (CFA)</th>
        </tr>
        <tr>
            <td>Revenu Total</td>
            <td style="text-align: right;">{{ number_format($data->total_revenue ?? 0) }}</td>
        </tr>
        <tr>
            <td>Commission Totale (Base TVA)</td>
            <td style="text-align: right;">{{ number_format($data->total_commission ?? 0) }}</td>
        </tr>
        <tr>
            <td><strong>TVA Collectée</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($data->tva_collected ?? 0) }}</strong></td>
        </tr>
        <tr>
            <td>TVA Paiements en Ligne</td>
            <td style="text-align: right;">{{ number_format($data->tva_paid_online ?? 0) }}</td>
        </tr>
        <tr>
            <td>TVA Paiements Cash</td>
            <td style="text-align: right;">{{ number_format($data->tva_cash ?? 0) }}</td>
        </tr>
    </table>

    <div class="alert alert-warning">
        <strong>📅 Prochaine Échéance</strong><br>
        La déclaration pour cette période doit être effectuée avant le <strong>15 {{ \Carbon\Carbon::create($year, $month)->addMonth()->format('F Y') }}</strong>.
    </div>

    <p>Pour consulter le rapport détaillé, connectez-vous au <a href="{{ url('/admin/tva-accounting') }}">dashboard admin</a>.</p>

    <div class="footer">
        <p>Ce rapport a été généré automatiquement par le système PicMe225.</p>
        <p>© {{ date('Y') }} PicMe225 - Tous droits réservés</p>
    </div>
</body>
</html>
