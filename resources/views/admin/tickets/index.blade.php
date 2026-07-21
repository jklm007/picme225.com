@extends('admin.layout.base')

@section('title', 'Gestion des Événements ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Événements & Billetterie</h5>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Organisateur</th>
                        <th>Titre de l'événement</th>
                        <th>Lieu</th>
                        <th>Date de début</th>
                        <th>Passes Actifs</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $index => $event)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $event->provider ? $event->provider->first_name : 'N/A' }}</td>
                        <td>{{ $event->title }}</td>
                        <td>{{ $event->s_address }}</td>
                        <td>{{ $event->departure_time }}</td>
                        <td>
                            @foreach($event->passes as $pass)
                                <span class="tag tag-info">{{ $pass->name }} ({{ $pass->valid_from }} - {{ $pass->valid_until }})</span><br>
                            @endforeach
                        </td>
                        <td>
                            @if($event->status == 'SCHEDULED')
                                <span class="tag tag-success">Planifié</span>
                            @else
                                <span class="tag tag-warning">{{ $event->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="#" class="btn btn-info"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
