@extends('admin.layout.base')

@section('title', 'Gestionnaire de Catégories Marketplace')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <div class="row mb-1">
                <div class="col-md-8">
                    <h5 class="mb-1">Structure du Marketplace</h5>
                    <p class="text-muted">Gérez vos menus et sous-menus. Cliquez sur <b>+</b> pour ajouter une sous-catégorie.</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.marketplace-categories.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Nouvelle Catégorie Principale
                    </a>
                </div>
            </div>

            <div class="row">
                @foreach($categories as $category)
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card card-block shadow-sm" style="border: 1px solid #ddd; border-radius: 10px;">
                        <div class="card-header bg-faded d-flex justify-content-between align-items-center" style="background: #f8f9fa; padding: 10px; border-bottom: 1px solid #eee;">
                            <h6 class="mb-0">
                                <strong>{{ $category->label }}</strong> 
                                <small class="text-muted">({{ $category->name }})</small>
                            </h6>
                            <div class="btn-group">
                                <a href="{{ route('admin.marketplace-categories.create') }}?parent_id={{ $category->id }}" class="btn btn-success btn-sm" title="Ajouter une sous-catégorie">
                                    <i class="fa fa-plus"></i>
                                </a>
                                <a href="{{ route('admin.marketplace-categories.edit', $category->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <form action="{{ route('admin.marketplace-categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette catégorie et toutes ses sous-catégories ?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px; min-height: 100px;">
                            @if($category->children->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($category->children as $child)
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="padding: 5px 0; border: none; border-bottom: 1px dashed #eee;">
                                        <span>— {{ $child->label }}</span>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.marketplace-categories.edit', $child->id) }}" class="btn btn-link btn-sm text-info"><i class="fa fa-pencil"></i></a>
                                            <form action="{{ route('admin.marketplace-categories.destroy', $child->id) }}" method="POST" style="display:inline;">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button class="btn btn-link btn-sm text-danger" onclick="return confirm('Supprimer ?')"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-center text-muted small mt-2">Aucune sous-catégorie</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card { transition: all 0.3s; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .btn-link { padding: 0 5px; }
</style>
@endsection
