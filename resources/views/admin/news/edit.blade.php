@extends('admin.layout.base')
@section('title', 'Modifier une Actualité')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.news.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Modifier l'Actualité</h5>
            @php
                $parts = explode("\n\n", $news->content, 2);
                $newsTitle = $parts[0] ?? '';
                $newsContent = $parts[1] ?? '';
            @endphp
            <form class="form-horizontal" action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                
                <div class="form-group row">
                    <label for="source" class="col-xs-2 col-form-label">Auteur / Source</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $news->source }}" name="source" required id="source">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="content" class="col-xs-2 col-form-label">Contenu complet</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="content" id="content" rows="8" required>{{ $news->content }}</textarea>
                        <small class="text-muted">Format : <code>Titre\n\nContenu du corps de l'article</code></small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="external_link" class="col-xs-2 col-form-label">Lien Externe</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="url" value="{{ $news->external_link }}" name="external_link" id="external_link">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-xs-2 col-form-label">Image actuelle</label>
                    <div class="col-xs-10">
                        @if($news->media_url)
                            <img src="{{ Str::startsWith($news->media_url, 'http') ? $news->media_url : \Storage::disk('s3')->url($news->media_url) }}" height="80" style="border-radius:8px;margin-bottom:8px;display:block;">
                        @endif
                        <input class="form-control" type="file" accept="image/*" name="media_url" id="media_url">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10 col-xs-offset-2">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
