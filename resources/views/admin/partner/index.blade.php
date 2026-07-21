@extends('admin.layout.base')

@section('title', 'Gestion des Partenaires')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">
                Liste des Partenaires
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5>
            <a href="{{ route('admin.partner.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Ajouter un Partenaire</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Tier</th>
                        <th>Statut</th>
                        <th>Rôle Spécifique</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partners as $index => $partner)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="tag tag-info">{{ $partner->partner_code }}</span></td>
                        <td>{{ $partner->type }}</td>
                        <td>
                            @if(Setting::get('demo_mode', 0) == 1)
                            {{ substr($partner->user->first_name, 0, 3).'****' }} {{ substr($partner->user->last_name, 0, 3).'****' }}
                            @else
                            {{ $partner->user->first_name }} {{ $partner->user->last_name }}
                            @endif
                        </td>
                        <td>
                            @if(Setting::get('demo_mode', 0) == 1)
                            {{ substr($partner->user->email, 0, 3).'****' }}<br>
                            {{ substr($partner->user->mobile, 0, 5).'****' }}
                            @else
                            {{ $partner->user->email }}<br>
                            {{ $partner->user->mobile }}
                            @endif
                        </td>
                        <td>{{ $partner->tier }}</td>
                        <td>
                            @if($partner->status == 'ACTIVE' || $partner->status == 'APPROVED')
                                <span class="tag tag-success">{{ $partner->status }}</span>
                            @elseif($partner->status == 'PENDING')
                                <span class="tag tag-warning">{{ $partner->status }}</span>
                            @else
                                <span class="tag tag-danger">{{ $partner->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($partner->type == 'FLEET_OWNER')
                                <b>Flotte:</b> {{ $partner->company_name }}
                            @elseif($partner->type == 'STATION_AGENT')
                                <b>Gare:</b> {{ $partner->pdpStop ? $partner->pdpStop->name : 'N/A' }}
                                @if($partner->interurbanCompany)
                                    <br><small>{{ $partner->interurbanCompany->name }}</small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.partner.destroy', $partner->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                <a href="{{ route('admin.partner.edit', $partner->id) }}" class="btn btn-info btn-block">
                                    <i class="fa fa-pencil"></i> Éditer
                                </a>
                                <button class="btn btn-danger btn-block" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?')">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Tier</th>
                        <th>Statut</th>
                        <th>Rôle Spécifique</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
