@extends('admin.layout.base')

@section('title', 'Mutuelle Assurance DAO')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">

            <div class="row row-md">
                <div class="col-lg-4 col-md-6 col-xs-12">
                    <div class="box box-block bg-white tile tile-1 mb-2">
                        <div class="t-icon right"><i class="ti-shield"></i></div>
                        <div class="t-content">
                            <h6 class="text-uppercase mb-1">Pool Assurance (Virtuel)</h6>
                            <h1 class="mb-1">{{ currency($totalPool) }}</h1>
                            <i class="fa fa-caret-up text-success mr-0-5"></i><span>Total collecté via les courses</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-xs-12">
                    <div class="box box-block bg-white tile tile-1 mb-2">
                        <div class="t-icon right"><i class="ti-exchange-vertical"></i></div>
                        <div class="t-content">
                            <h6 class="text-uppercase mb-1">Déboursé (Aide)</h6>
                            <h1 class="mb-1 text-danger">{{ currency($totalDisbursed) }}</h1>
                            <i class="fa fa-caret-down text-danger mr-0-5"></i><span>Aide mutualiste versée aux
                                chauffeurs</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-xs-12">
                    <div class="box box-block bg-white tile tile-1 mb-2">
                        <div class="t-icon right"><i class="ti-pulse"></i></div>
                        <div class="t-content">
                            <h6 class="text-uppercase mb-1">Demandes en attente</h6>
                            <h1 class="mb-1">{{ $pendingClaims }}</h1>
                            <span class="text-muted text-uppercase">Sinistres à analyser</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-block bg-white">
                <h5 class="mb-1">Historique des Sinistres & Demandes d'Aide</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>Date Incident</th>
                            <th>Chauffeur</th>
                            <th>Description</th>
                            <th>Montant Demandé</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claims as $claim)
                            <tr>
                                <td>{{ $claim->incident_date->format('Y-m-d') }}</td>
                                <td>{{ $claim->provider->first_name }} {{ $claim->provider->last_name }}</td>
                                <td>{{ $claim->incident_description }}</td>
                                <td>{{ currency($claim->amount_requested) }}</td>
                                <td>
                                    @if($claim->status == 'PENDING')
                                        <span class="tag tag-warning">En attente</span>
                                    @elseif($claim->status == 'APPROVED')
                                        <span class="tag tag-success">Approuvé: {{ currency($claim->amount_approved) }}</span>
                                    @else
                                        <span class="tag tag-danger">Rejeté</span>
                                    @endif
                                </td>
                                <td>
                                    @if($claim->status == 'PENDING')
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#approveModal{{$claim->id}}">Procéder</button>
                                        <form action="{{ route('admin.insurance.reject', $claim->id) }}" method="POST"
                                            style="display:inline;">
                                            {{ csrf_field() }}
                                            <button class="btn btn-danger"
                                                onclick="return confirm('Voulez-vous rejeter cette demande ?')">Rejeter</button>
                                        </form>

                                        <!-- Modal Approve -->
                                        <div class="modal fade" id="approveModal{{$claim->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <form action="{{ route('admin.insurance.approve', $claim->id) }}" method="POST">
                                                    {{ csrf_field() }}
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Approuver l'Aide Mutualiste</h5>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Montant à verser (ECO/CFA)</label>
                                                                <input type="number" name="amount_approved" class="form-control"
                                                                    value="{{ $claim->amount_requested }}" max="{{ $totalPool }}"
                                                                    required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Commentaire Admin (raison de l'approbation)</label>
                                                                <textarea name="admin_comment" class="form-control"
                                                                    rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Confirmer le Versement
                                                                ECO/CFA</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Fermer</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Traité</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $claims->links() }}
            </div>

        </div>
    </div>
@endsection