@extends('admin.layout.base')

@section('title', 'Demandes KYC ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Vérifications d'Identité (KYC)</h5>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Type Doc</th>
                        <th>Numéro Permis</th>
                        <th>Recto</th>
                        <th>Verso</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->first_name }} {{ $user->last_name }}<br><small>{{ $user->mobile }}</small></td>
                        <td>{{ $user->kyc_document_type }}</td>
                        <td>{{ $user->kyc_license_number ?: 'N/A' }}</td>
                        <td>
                            @if($user->kyc_document_front)
                                <a href="{{ Helper::get_file($user->kyc_document_front) }}" target="_blank">
                                    <img src="{{ Helper::get_file($user->kyc_document_front) }}" style="height: 50px; width: 80px;">
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($user->kyc_document_back)
                                <a href="{{ Helper::get_file($user->kyc_document_back) }}" target="_blank">
                                    <img src="{{ Helper::get_file($user->kyc_document_back) }}" style="height: 50px; width: 80px;">
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($user->kyc_status == 'PENDING')
                                <span class="tag tag-warning">EN ATTENTE</span>
                            @elseif($user->kyc_status == 'APPROVED')
                                <span class="tag tag-success">APPROUVÉ</span>
                            @else
                                <span class="tag tag-danger">REJETÉ</span>
                                <br><small>{{ $user->kyc_rejected_reason }}</small>
                            @endif
                        </td>
                        <td>
                            @if($user->kyc_status == 'PENDING')
                            <div class="input-group-btn">
                                <form action="{{ route('admin.user.kyc.approve', $user->id) }}" method="POST" style="display:inline;">
                                    {{ csrf_field() }}
                                    <button class="btn btn-success btn-block" onclick="return confirm('Approuver cette identité ?')">Approuver</button>
                                </form>
                                <button class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal{{$user->id}}">Rejeter</button>
                            </div>

                            <!-- Modal Rejet -->
                            <div class="modal fade" id="rejectModal{{$user->id}}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.user.kyc.reject', $user->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            <div class="modal-header">
                                                <h4 class="modal-title">Motif du rejet ({{ $user->first_name }})</h4>
                                            </div>
                                            <div class="modal-body">
                                                <textarea name="reason" class="form-control" rows="3" placeholder="Ex: Photo floue, Document expiré..." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Confirmer Rejet</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
