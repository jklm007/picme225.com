@extends('admin.layout.base')

@section('title', 'Demandes de Location')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif !important; background-color: #f4f6f9; }
        .box { border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03); border: 1px solid rgba(0,0,0,0.05); background: #ffffff; padding: 30px; }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box">
                <h4 class="mb-4">Demandes de Réservation</h4>

                <div class="table-responsive">
                    <table class="table table-hover w-100" id="table-2">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Véhicule</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $index => $booking)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $booking->listing->title ?? 'N/A' }}</td>
                                    <td>
                                        {{ $booking->user->first_name ?? '' }} {{ $booking->user->last_name ?? '' }}<br>
                                        <small>{{ $booking->user->mobile ?? '' }}</small>
                                    </td>
                                    <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $booking->status == 'COMPLETED' ? 'badge-success' : 'badge-info' }}">
                                            {{ $booking->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
