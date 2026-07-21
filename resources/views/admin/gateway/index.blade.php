@extends('admin.layout.base')

@section('title', 'Gateway Hub - Multi-SIM ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white mb-2">
            <h5 class="mb-1">💰 Résumé de Rentabilité (P2P)</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-block card-inverse card-primary">
                        <div class="p-2">
                            <h6 class="text-uppercase mb-1">Volume Rechargé</h6>
                            <h1 class="mb-1">{{ currency($total_p2p_recharges) }}</h1>
                            <div class="font-90">Via Gateway P2P</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-block card-inverse card-success">
                        <div class="p-2">
                            <h6 class="text-uppercase mb-1">Économies Réalisées</h6>
                            <h1 class="mb-1">{{ currency($total_savings) }}</h1>
                            <div class="font-90">Argent sauvé vs Agrégateurs</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-block card-inverse card-danger" style="background-color: #f59345; border:none;">
                        <div class="p-2">
                            <h6 class="text-uppercase mb-1">Bénéfices Nets (Commissions)</h6>
                            <h1 class="mb-1">{{ currency($total_commissions) }}</h1>
                            <div class="font-90">Prêt à être transféré sur Node PROFIT</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-block bg-white">
            <div class="clearfix mb-1">
                <h5 class="float-xs-left">📱 Pool de Cartes SIM (Nodes)</h5>
                <button class="btn btn-primary float-xs-right" data-toggle="modal" data-target="#addNodeModal">
                    <i class="fa fa-plus"></i> Ajouter un Numéro
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Nom / Numéro</th>
                            <th>Réseau</th>
                            <th>Rôle (Type)</th>
                            <th>Solde Actuel</th>
                            <th>Limite Journalière (2M)</th>
                            <th>Limite Mensuelle (10M)</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodes as $node)
                        <tr>
                            <td>
                                <strong>{{ $node->name }}</strong><br>
                                <span class="text-muted">{{ $node->phone_number }}</span>
                            </td>
                            <td>
                                <span class="tag tag-info">{{ $node->network }}</span>
                            </td>
                            <td>
                                @if($node->type == 'RECEIVER')
                                    <span class="tag tag-primary">RECEPTION</span>
                                @elseif($node->type == 'PAYOUT')
                                    <span class="tag tag-warning">TRANSFERT</span>
                                @elseif($node->type == 'VAULT')
                                    <span class="tag tag-success">COFFRE</span>
                                @else
                                    <span class="tag tag-danger">PROFIT</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ currency($node->current_balance) }}</strong>
                            </td>
                            <td>
                                @php $daily_perc = ($node->daily_volume / $node->daily_limit) * 100; @endphp
                                <div class="progress progress-sm mb-0-5">
                                    <div class="progress-bar {{ $daily_perc > 80 ? 'progress-bar-danger' : 'progress-bar-success' }}" 
                                         role="progressbar" style="width: {{ $daily_perc }}%"></div>
                                </div>
                                <small>{{ currency($node->daily_volume) }} / {{ currency($node->daily_limit) }}</small>
                            </td>
                            <td>
                                @php $monthly_perc = ($node->monthly_volume / $node->monthly_limit) * 100; @endphp
                                <div class="progress progress-sm mb-0-5">
                                    <div class="progress-bar {{ $monthly_perc > 80 ? 'progress-bar-danger' : 'progress-bar-info' }}" 
                                         role="progressbar" style="width: {{ $monthly_perc }}%"></div>
                                </div>
                                <small>{{ currency($node->monthly_volume) }} / {{ currency($node->monthly_limit) }}</small>
                            </td>
                            <td>
                                @if($node->status == 'ACTIVE')
                                    <span class="tag tag-success">ACTIF</span>
                                @else
                                    <span class="tag tag-danger">INACTIF</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.gateway.toggle', $node->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-refresh"></i> Switch
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- ===== SECTION SMS BOOKING OFFLINE ===== --}}
        <div class="box box-block bg-white mt-2">
            <h5 class="mb-1">📲 Monitoring Courses SMS Offline <small class="text-muted font-80">(24 dernières heures)</small></h5>

            {{-- Compteurs --}}
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="card card-block card-inverse" style="background:#f0ad4e;border:none;">
                        <div class="p-2 text-center">
                            <h6 class="text-uppercase mb-1">⏳ En attente</h6>
                            <h1 class="mb-0">{{ $sms_pending }}</h1>
                            <small>Courses en cours de dispatch</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-block card-inverse card-success">
                        <div class="p-2 text-center">
                            <h6 class="text-uppercase mb-1">✅ Acceptées (24h)</h6>
                            <h1 class="mb-0">{{ $sms_accepted }}</h1>
                            <small>Chauffeurs ont répondu OUI</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-block card-inverse card-danger">
                        <div class="p-2 text-center">
                            <h6 class="text-uppercase mb-1">❌ Expirées (24h)</h6>
                            <h1 class="mb-0">{{ $sms_expired }}</h1>
                            <small>Aucun chauffeur disponible</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table des courses offline --}}
            <h6 class="mb-1">📋 Historique des 50 dernières courses offline</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Chauffeur</th>
                            <th>Client</th>
                            <th>Départ → Arrivée</th>
                            <th>Code SMS</th>
                            <th>Statut</th>
                            <th>Envoyé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offline_bookings as $ob)
                        @php
                            $req = $ob->userRequest;
                            $prov = $ob->provider;
                        @endphp
                        <tr>
                            <td>{{ $ob->id }}</td>
                            <td>
                                @if($prov)
                                    <strong>{{ $prov->first_name }} {{ $prov->last_name }}</strong><br>
                                    <small class="text-muted">{{ $ob->provider_phone }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($req && $req->user)
                                    {{ $req->user->first_name }} {{ $req->user->last_name }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($req)
                                    <small>{{ $req->s_address ?? '?' }} → {{ $req->d_address ?? '?' }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="tag tag-default">{{ $ob->sms_code }}</span></td>
                            <td>
                                @if($ob->status == 'PENDING')
                                    <span class="tag tag-warning">EN ATTENTE</span>
                                @elseif($ob->status == 'ACCEPTED')
                                    <span class="tag tag-success">ACCEPTÉ</span>
                                @elseif($ob->status == 'REJECTED')
                                    <span class="tag tag-danger">REFUSÉ</span>
                                @elseif($ob->status == 'EXPIRED')
                                    <span class="tag tag-secondary">EXPIRÉ</span>
                                @else
                                    <span class="tag tag-default">{{ $ob->status }}</span>
                                @endif
                            </td>
                            <td><small>{{ $ob->created_at->format('d/m H:i') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucune course SMS pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== LOG SMS SORTANTS ===== --}}
        <div class="box box-block bg-white mt-2">
            <h5 class="mb-1">📤 File SMS Sortants <small class="text-muted font-80">(30 derniers)</small></h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Destinataire</th>
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sms_outbox as $sms)
                        <tr>
                            <td>{{ $sms->id }}</td>
                            <td><strong>{{ $sms->phone_number }}</strong></td>
                            <td><small>{{ $sms->message }}</small></td>
                            <td>
                                @if(($sms->status ?? 'PENDING') == 'SENT')
                                    <span class="tag tag-success">ENVOYÉ</span>
                                @elseif(($sms->status ?? 'PENDING') == 'FAILED')
                                    <span class="tag tag-danger">ÉCHEC</span>
                                @else
                                    <span class="tag tag-warning">EN ATTENTE</span>
                                @endif
                            </td>
                            <td><small>{{ $sms->created_at->format('d/m H:i') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucun SMS en file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Ajouter Node -->
<div class="modal fade" id="addNodeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.gateway.store') }}" method="POST" class="modal-content">
            {{ csrf_field() }}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Ajouter une nouvelle SIM au Pool</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nom de l'appareil (ex: SIM Réception 1)</label>
                    <input type="text" name="name" class="form-control" required placeholder="Samsung A10 - Puce 1">
                </div>
                <div class="form-group">
                    <label>Numéro de téléphone (avec indicatif)</label>
                    <input type="text" name="phone_number" class="form-control" required placeholder="+2250700000000">
                </div>
                <div class="form-group">
                    <label>Réseau</label>
                    <select name="network" class="form-control">
                        <option value="WAVE">WAVE</option>
                        <option value="ORANGE">ORANGE MONEY</option>
                        <option value="MTN">MTN MONEY</option>
                        <option value="MOOV">MOOV MONEY</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rôle de cette SIM</label>
                    <select name="type" class="form-control">
                        <option value="RECEIVER">RÉCEPTION (Argent entrant clients)</option>
                        <option value="PAYOUT">TRANSFERT (Argent sortant chauffeurs)</option>
                        <option value="VAULT">COFFRE (Stockage capital)</option>
                        <option value="PROFIT">PROFIT (Vos gains nets)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer la SIM</button>
            </div>
        </form>
    </div>
</div>
@endsection
