@extends('admin.layout.base')
@section('title', 'Publier une Actualité')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.news.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Publier une Nouvelle Actualité</h5>
            <form class="form-horizontal" action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                
                <div class="form-group row">
                    <label for="title" class="col-xs-2 col-form-label">Titre</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('title') }}" name="title" required id="title" placeholder="Ex: Flash Info: Nouveau Trafic...">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="source" class="col-xs-2 col-form-label">Auteur / Source</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('source', 'PicMe INFO') }}" name="source" required id="source" placeholder="Ex: PicMe INFO ou Abidjan.net">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="content" class="col-xs-2 col-form-label">Contenu</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="content" id="content" rows="6" required>{{ old('content') }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="external_link" class="col-xs-2 col-form-label">Lien Externe (Optionnel)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="url" value="{{ old('external_link') }}" name="external_link" id="external_link" placeholder="Ex: https://...">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="media_url" class="col-xs-2 col-form-label">Image illustrative</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="file" accept="image/*" name="media_url" id="media_url">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10 col-xs-offset-2">
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
