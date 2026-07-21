@extends('admin.layout.base')

@section('title', 'Groupes WhatsApp Autorisés')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">
                Groupes WhatsApp Autorisés (Whitelist)
                <a href="{{ route('admin.whatsapp-groups.create') }}" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Ajouter un Groupe</a>
            </h5>
            <p class="mb-2">Liste des groupes WhatsApp surveillés par l'IA pour extraire les annonces Marketplace.</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Group ID</th>
                        <th>Nom du Groupe</th>
                        <th>Catégorie par défaut</th>
                        <th>Mode d'insertion</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                    <tr>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->group_id }}</td>
                        <td>{{ $group->name }}</td>
                        <td><span class="badge badge-info">{{ $group->default_category }}</span></td>
                        <td>
                            @if($group->insert_mode == 'APPROVED')
                                <span class="badge badge-success">Automatique</span>
                            @else
                                <span class="badge badge-warning">Contrôle Manuel</span>
                            @endif
                        </td>
                        <td>
                            @if($group->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.whatsapp-groups.edit', $group->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> Modifier</a>
                            <form action="{{ route('admin.whatsapp-groups.destroy', $group->id) }}" method="POST" style="display:inline-block;">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce groupe ?');"><i class="fa fa-trash"></i> Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
