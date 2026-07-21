@extends('admin.layout.base')

@section('title', 'Ajouter une Catégorie Principale')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.main-category.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

            <h5 style="margin-bottom: 2em;">Créer une Catégorie Principale</h5>

            <form class="form-horizontal" action="{{route('admin.main-category.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom de la catégorie</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Ex: Livraison, Déménagement...">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="image" class="col-xs-12 col-form-label">Icône / Image de la catégorie</label>
                    <div class="col-xs-10">
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">
                        
                        <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
                            <label style="font-weight: bold; color: #555;">...OU sélectionner une image existante :</label>
                            <select name="image_select" class="form-control" id="image_select" onchange="previewSelectedImage(this)">
                                <option value="">-- Aucune sélection --</option>
                                @foreach($images as $img)
                                    <option value="service/{{ basename($img) }}" data-url="{{ asset('storage/service/'.basename($img)) }}">{{ basename($img) }}</option>
                                @endforeach
                            </select>
                            <div class="mt-2 text-center" id="image_select_preview_container" style="display: none;">
                                <img id="image_select_preview" src="" style="max-height: 80px; border-radius: 8px; border: 1px solid #ddd;" />
                            </div>
                        </div>

                        <script>
                            function previewSelectedImage(selectElement) {
                                var selectedOption = selectElement.options[selectElement.selectedIndex];
                                var previewContainer = document.getElementById('image_select_preview_container');
                                var previewImage = document.getElementById('image_select_preview');
                                
                                if (selectedOption.value !== "") {
                                    previewImage.src = selectedOption.getAttribute('data-url');
                                    previewContainer.style.display = 'block';
                                } else {
                                    previewContainer.style.display = 'none';
                                }
                            }
                        </script>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="status" class="col-xs-12 col-form-label">Statut (Actif par défaut)</label>
                    <div class="col-xs-10">
                        <label class="switch">
                            <input type="checkbox" name="status" id="status" value="1" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Créer la Catégorie Principale</button>
                        <a href="{{route('admin.main-category.index')}}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
