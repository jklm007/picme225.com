<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport TVA - {{ Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24pt;
            font-weight: bold;
            color: #667eea;
        }
        .company-info {
            margin: 10px 0;
            font-size: 10pt;
        }
        .report-title {
            font-size: 18pt;
            font-weight: bold;
            margin: 20px 0;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .summary-item {
            display: table-cell;
            width: 50%;
            padding: 5px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        .summary-value {
            font-size: 14pt;
            color: #667eea;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
        }
        th {
            background: #667eea;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .total-row {
            font-weight: bold;
            background: #e9ecef !important;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">PICME225</div>
        <div class="company-info">
            Plateforme de Transport Intelligent<br>
            Abidjan, Côte d'Ivoire<br>
            NIF: [À compléter] - RCCM: [À compléter]
        </div>
        <div class="report-title">
            RAPPORT TVA - {{ strtoupper(Carbon\Carbon::create()->month($month)->format('F Y')) }}
        </div>
        <div style="font-size: 10pt; color: #666;">
            Généré le {{ Carbon\Carbon::now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <h3 style="margin-top: 0;">RÉCAPITULATIF MENSUEL</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Base Imposable (Commission)</div>
                <div class="summary-value">{{ number_format($data->total_commission ?? 0) }} CFA</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">TVA Collectée (18%)</div>
                <div class="summary-value">{{ number_format($data->tva_collected ?? 0) }} CFA</div>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Nombre de Transactions</div>
                <div class="summary-value">{{ number_format($data->total_transactions ?? 0) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Revenu Total</div>
                <div class="summary-value">{{ number_format($data->total_revenue ?? 0) }} CFA</div>
            </div>
        </div>
    </div>

    <h3>DÉTAIL DES TRANSACTIONS</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 18%;">Client</th>
                <th style="width: 18%;">Chauffeur</th>
                <th style="width: 12%; text-align: right;">Montant</th>
                <th style="width: 12%; text-align: right;">Commission</th>
                <th style="width: 12%; text-align: right;">TVA</th>
                <th style="width: 11%;">Mode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->request->user->first_name ?? 'N/A' }}</td>
                <td>{{ $transaction->request->provider->first_name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($transaction->total) }}</td>
                <td style="text-align: right;">{{ number_format($transaction->provider_commission) }}</td>
                <td style="text-align: right;">{{ number_format($transaction->tva_fee) }}</td>
                <td>{{ $transaction->payment_mode }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>TOTAL</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($transactions->sum('total')) }}</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($transactions->sum('provider_commission')) }}</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($transactions->sum('tva_fee')) }}</strong></td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <strong>Certifié conforme</strong><br>
            Le {{ Carbon\Carbon::now()->format('d/m/Y') }}<br>
            <div class="signature-line">
                Directeur Général
            </div>
        </div>
        <div class="signature-box" style="float: right;">
            <strong>Cachet de l'Entreprise</strong><br><br>
            <div class="signature-line">
                Signature et Cachet
            </div>
        </div>
    </div>

    <div class="footer">
        PicMe225 - Rapport TVA {{ Carbon\Carbon::create()->month($month)->format('F Y') }} - Page 1/1
    </div>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Imprimer en PDF
        </button>
    </div>
</body>
</html>
