@extends('admin.layout.base')

@section('title', 'Modifier la Catégorie Principale')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.main-category.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

            <h5 style="margin-bottom: 2em;">Modifier la Catégorie Principale</h5>

            <form class="form-horizontal" action="{{route('admin.main-category.update', $service->id )}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="PATCH">
                
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom de la catégorie</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->name }}" name="name" required id="name" placeholder="Nom">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="image" class="col-xs-12 col-form-label">Icône / Image</label>
                    <div class="col-xs-10">
                        @if(isset($service->image))
                            <img style="height: 90px; margin-bottom: 15px;" src="{{ img($service->image) }}">
                        @endif
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">

                        <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
                            <label style="font-weight: bold; color: #555;">...OU sélectionner une image existante :</label>
                            <select name="image_select" class="form-control" id="image_select" onchange="previewSelectedImage(this)">
                                <option value="">-- Conserver l'image actuelle / Aucune sélection --</option>
                                @foreach($images as $img)
                                    @php $imgPath = 'service/' . basename($img); @endphp
                                    <option value="{{ $imgPath }}" data-url="{{ \Storage::disk('s3')->url($imgPath) }}" {{ $service->image == $imgPath ? 'selected' : '' }}>
                                        {{ basename($img) }}
                                    </option>
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
                            // Initial preview if selected
                            window.onload = function() {
                                var select = document.getElementById('image_select');
                                if(select.value !== "") {
                                    previewSelectedImage(select);
                                }
                            }
                        </script>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="status" class="col-xs-12 col-form-label">Statut</label>
                    <div class="col-xs-10">
                        <label class="switch">
                            <input type="checkbox" name="status" id="status" value="1" {{ (isset($service->status) && $service->status == 1) || !isset($service->status) ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
                        <a href="{{route('admin.main-category.index')}}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
