@extends('admin.layout.base')

@section('title', 'Historique des Commandes SMS ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        
        {{-- STATS CARDS --}}
        <div class="row row-md mb-2">
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-primary"></span><i class="ti-layers"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Total Requêtes SMS</h6>
                        <h1 class="mb-1">{{ $stats['total'] }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-warning"></span><i class="ti-timer"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">En Attente</h6>
                        <h1 class="mb-1">{{ $stats['pending'] }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-success"></span><i class="ti-check-box"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Acceptées</h6>
                        <h1 class="mb-1">{{ $stats['accepted'] }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-danger"></span><i class="ti-close"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">Expirées / Refusées</h6>
                        <h1 class="mb-1">{{ $stats['expired'] }}</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER FORM --}}
        <div class="box box-block bg-white">
            <h5 class="mb-1">Filtres de recherche</h5>
            <form action="{{ route('admin.sms-booking.index') }}" method="GET" class="form-inline mb-2">
                <div class="form-group mr-1 mb-1">
                    <label class="sr-only">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="form-group mr-1 mb-1">
                    <label class="sr-only">Numéro Chauffeur</label>
                    <input type="text" name="phone" class="form-control" placeholder="Numéro (ex: +225...)" value="{{ request('phone') }}">
                </div>
                <div class="form-group mr-1 mb-1">
                    <label class="sr-only">Statut</label>
                    <select name="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                        <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                        <option value="EXPIRED" {{ request('status') == 'EXPIRED' ? 'selected' : '' }}>EXPIRED</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-1"><i class="fa fa-search"></i> Filtrer</button>
                <a href="{{ route('admin.sms-booking.index') }}" class="btn btn-secondary mb-1"><i class="fa fa-refresh"></i> Réinitialiser</a>
            </form>
            
            <div class="clearfix"></div>

            {{-- TABLE --}}
            <h5 class="mb-1">📋 Liste des envois de dispatch SMS</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered dataTable">
                    <thead>
                        <tr>
                            <th>ID Req</th>
                            <th>Code SMS</th>
                            <th>Chauffeur Ciblé</th>
                            <th>Course ID</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Expire le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td><span class="tag tag-default">{{ $booking->sms_code }}</span></td>
                            <td>
                                @if($booking->provider)
                                    <strong>{{ $booking->provider->first_name }} {{ $booking->provider->last_name }}</strong><br>
                                    <small>{{ $booking->provider_phone }}</small>
                                @else
                                    {{ $booking->provider_phone }}
                                @endif
                            </td>
                            <td>
                                @if($booking->userRequest)
                                    <a href="{{ route('admin.requests.show', $booking->request_id) }}" target="_blank">#{{ $booking->request_id }}</a>
                                @else
                                    #{{ $booking->request_id }}
                                @endif
                            </td>
                            <td>
                                @if($booking->status == 'PENDING')
                                    <span class="tag tag-warning">PENDING</span>
                                @elseif($booking->status == 'ACCEPTED')
                                    <span class="tag tag-success">ACCEPTED</span>
                                @elseif($booking->status == 'REJECTED')
                                    <span class="tag tag-danger">REJECTED</span>
                                @elseif($booking->status == 'EXPIRED')
                                    <span class="tag tag-secondary">EXPIRED</span>
                                @else
                                    <span class="tag tag-default">{{ $booking->status }}</span>
                                @endif
                            </td>
                            <td>{{ $booking->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                {{ $booking->expires_at ? $booking->expires_at->format('Y-m-d H:i:s') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Aucun enregistrement trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION --}}
            <div class="mt-2">
                {{ $bookings->appends(request()->except('page'))->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
