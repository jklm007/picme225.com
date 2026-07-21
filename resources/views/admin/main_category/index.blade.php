@extends('admin.layout.base')

@section('title', 'Catégories Principales ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Catégories Principales (Grandes Sections)</h5>
            <a href="{{ route('admin.main-category.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Ajouter une Catégorie Principale</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Icône / Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $service->name }}</td>
                        <td>
                            @if($service->image) 
                                <img src="{{ img($service->image) }}" style="height: 50px;" alt="">
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.main-category.destroy', $service->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <a href="{{ route('admin.main-category.edit', $service->id) }}" class="btn btn-info btn-block">
                                    <i class="fa fa-pencil"></i> Modifier
                                </a>
                                <button class="btn btn-danger btn-block" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ?');">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
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
