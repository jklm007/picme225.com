@extends('admin.layout.base')
@section('title', 'Actualités')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Toutes les Actualités</h5>
            <a href="{{ route('admin.news.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Publier une actualité</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($news as $index => $post)
                    <tr>
                        <td>{{ $post->id }}</td>
                        <td>{{ $post->type }}</td>
                        <td>{{ $post->source }}</td>
                        <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.news.destroy', $post->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <a href="{{ route('admin.news.edit', $post->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Éditer</a>
                                <button class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')"><i class="fa fa-trash"></i> Supprimer</button>
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
