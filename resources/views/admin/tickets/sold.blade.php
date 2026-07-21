@extends('admin.layout.base')

@section('title', 'Billets Vendus ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        
        <!-- STATS CARDS -->
        <div class="row row-md mb-2">
            <div class="col-md-4">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-success"></span><i class="ti-money"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Revenu Total</h6>
                        <h1 class="mb-1">{{ number_format($stats['total_revenue']) }} FCFA</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-primary"></span><i class="ti-wallet"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Ventes Wallet</h6>
                        <h1 class="mb-1">{{ $stats['wallet_count'] }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-warning"></span><i class="ti-hand-point-right"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Ventes Cash (Guichet)</h6>
                        <h1 class="mb-1">{{ $stats['cash_count'] }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-block bg-white">
            <h5 class="mb-1">
                Historique des Billets & Validations
                <a href="{{ route('admin.tickets.sell.create') }}" class="btn btn-primary pull-right"><i class="fa fa-ticket"></i> Vendre un Billet (Guichet)</a>
            </h5>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Acheteur</th>
                        <th>Événement</th>
                        <th>Type de Pass</th>
                        <th>Paiement</th>
                        <th>Prix</th>
                        <th>Date d'Achat</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $index => $ticket)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                         <td>
                            @if($ticket->user)
                                <strong>{{ $ticket->user->first_name }} {{ $ticket->user->last_name }}</strong><br>
                                <span class="text-muted" style="font-size: 11px;">
                                    <i class="fa fa-phone"></i> {{ $ticket->user->mobile }}<br>
                                    @if($ticket->user->email && !str_starts_with($ticket->user->email, 'guest_'))
                                        <i class="fa fa-envelope"></i> {{ $ticket->user->email }}
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $ticket->listing ? $ticket->listing->title : 'N/A' }}</td>
                        <td>
                            <span class="tag tag-info">{{ $ticket->pass ? $ticket->pass->name : 'Standard' }}</span>
                        </td>
                        <td>
                            @if($ticket->payment_mode == 'CASH')
                                <span class="tag tag-warning">CASH</span>
                            @else
                                <span class="tag tag-primary">WALLET</span>
                            @endif
                        </td>
                        <td>{{ number_format($ticket->total_price) }} FCFA</td>
                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @php
                                $meta = is_string($ticket->metadata) ? json_decode($ticket->metadata, true) : $ticket->metadata;
                                $scannedCount = isset($meta['scanned_count']) ? (int)$meta['scanned_count'] : 0;
                                $persons = $ticket->pass ? ($ticket->pass->persons_per_pass ?: 1) : (isset($meta['persons_per_pass']) ? (int)$meta['persons_per_pass'] : 1);
                            @endphp
                            @if($ticket->status == 'BOOKED')
                                @if($persons > 1)
                                    <span class="tag tag-success">Valide ({{ $scannedCount }}/{{ $persons }} entrées)</span>
                                @else
                                    <span class="tag tag-success">Valide</span>
                                @endif
                            @elseif($ticket->status == 'USED')
                                <span class="tag tag-danger">Utilisé ({{ $scannedCount }}/{{ $persons }} entrées)</span>
                            @else
                                <span class="tag tag-inverse">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.tickets.sold.resend', $ticket->id) }}" method="POST" style="display:inline;">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Êtes-vous sûr de vouloir renvoyer ce ticket par WhatsApp ?')">
                                    <i class="fa fa-whatsapp"></i> Renvoyer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
