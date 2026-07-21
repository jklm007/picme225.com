@extends('admin.layout.base')

@section('title', 'Edit Provider Document')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">

        <div class="box box-block bg-white">
            <h5 class="mb-1">@lang('admin.provides.provider_name'): {{ $Document->provider->first_name }} {{ $Document->provider->last_name }}</h5>
            <h5 class="mb-1">Document: {{ $Document->document->name }}</h5>

 {{-- Ajout : Affichage de l'image du document --}}
           
            @if($Document->url)
    <div class="document-image-preview">
        <img src="{{ Storage::url($Document->url) }}" alt="{{ $Document->document->name }}" style="...">
    </div>
@endif


            {{-- Fin ajout affichage image --}}


            <embed src="{{ \Storage::disk('s3')->url($Document->url) }}" width="100%" height="600px" style="display:none;"/> {{-- Hide Embed - Not needed for image display anymore --}}


            <div class="row">
                <div class="col-xs-6">
                    <form action="{{ route('admin.provider.document.update', [$Document->provider->id, $Document->id]) }}" method="POST">
                        {{ csrf_field() }}
                        {{ method_field('PATCH') }}
                        <button class="btn btn-block btn-primary" type="submit">@lang('admin.provides.approve')</button>
                    </form>
                </div>

                <div class="col-xs-6">
                    <form action="{{ route('admin.provider.document.destroy', [$Document->provider->id, $Document->id]) }}" method="POST">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button class="btn btn-block btn-danger" type="submit">@lang('admin.provides.delete')</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('styles')
<style>
    /* Ajout de styles pour la prévisualisation de l'image */
    .document-image-preview {
        margin-bottom: 20px;
        text-align: center; /* Centrer l'image */
    }
    .document-image-preview img {
        max-width: 100%; /* Image responsive */
        height: auto;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
</style>
@endsection
