@extends('admin.layout.base')
@section('title', 'Modifier une Annonce')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.marketplace-listings.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Modifier l'Annonce : {{ $listing->title }}</h5>
            <form class="form-horizontal" action="{{ route('admin.marketplace-listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                
                <!-- Type est géré en arrière-plan pour la compatibilité DB -->
                <input type="hidden" name="type" value="{{ $listing->type ?? 'ARTICLE' }}">

                <div class="form-group row">
                    <label for="category" class="col-xs-2 col-form-label">Catégorie</label>
                    <div class="col-xs-10">
                        <select name="category" id="category" class="form-control" required onchange="updateSubCategories()">
                            <option value="">-- Sélectionner une catégorie --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}" data-subs='@json($category->children)' {{ $listing->category == $category->name ? 'selected' : '' }}>
                                    {{ $category->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row" id="sub_category_container">
                    <label for="sub_category" class="col-xs-2 col-form-label">Sous-catégorie</label>
                    <div class="col-xs-10">
                        <select name="sub_category" id="sub_category" class="form-control">
                            <option value="">-- Sélectionner une sous-catégorie --</option>
                        </select>
                    </div>
                </div>

                <!-- CHAMPS DYNAMIQUES (METADATA) -->
                <div id="metadata_fields">
                    <!-- Immobilier -->
                    <div class="category-fields" id="fields_REAL_ESTATE" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Détails Immobilier</label>
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[rooms]" value="{{ $listing->metadata['rooms'] ?? '' }}" placeholder="Chambres"></div>
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[bathrooms]" value="{{ $listing->metadata['bathrooms'] ?? '' }}" placeholder="SDB"></div>
                            <div class="col-xs-4"><input class="form-control" type="text" name="metadata[area]" value="{{ $listing->metadata['area'] ?? '' }}" placeholder="Superficie (m²)"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Options</label>
                            <div class="col-xs-10">
                                <label><input type="checkbox" name="metadata[furnished]" value="1" {{ ($listing->metadata['furnished'] ?? '') == '1' ? 'checked' : '' }}> Meublé</label> &nbsp;
                                <label><input type="checkbox" name="metadata[parking]" value="1" {{ ($listing->metadata['parking'] ?? '') == '1' ? 'checked' : '' }}> Parking</label> &nbsp;
                                <label><input type="checkbox" name="metadata[pool]" value="1" {{ ($listing->metadata['pool'] ?? '') == '1' ? 'checked' : '' }}> Piscine</label>
                            </div>
                        </div>
                    </div>

                    <!-- Véhicules -->
                    <div class="category-fields" id="fields_VEHICLES" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Véhicule</label>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[brand]" value="{{ $listing->metadata['brand'] ?? '' }}" placeholder="Marque"></div>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[model]" value="{{ $listing->metadata['model'] ?? '' }}" placeholder="Modèle"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Détails</label>
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[year]" value="{{ $listing->metadata['year'] ?? '' }}" placeholder="Année"></div>
                            <div class="col-xs-4"><input class="form-control" type="text" name="metadata[mileage]" value="{{ $listing->metadata['mileage'] ?? '' }}" placeholder="Kilométrage"></div>
                            <div class="col-xs-3">
                                <select name="metadata[fuel]" class="form-control">
                                    <option value="">Carburant</option>
                                    <option value="Essence" {{ ($listing->metadata['fuel'] ?? '') == 'Essence' ? 'selected' : '' }}>Essence</option>
                                    <option value="Diesel" {{ ($listing->metadata['fuel'] ?? '') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                    <option value="Electrique" {{ ($listing->metadata['fuel'] ?? '') == 'Electrique' ? 'selected' : '' }}>Electrique</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Vente -->
                    <div class="category-fields" id="fields_SALE" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Détails Produit</label>
                            <div class="col-xs-5">
                                <select name="metadata[condition]" class="form-control">
                                    <option value="Neuf" {{ ($listing->metadata['condition'] ?? '') == 'Neuf' ? 'selected' : '' }}>Neuf</option>
                                    <option value="Occasion" {{ ($listing->metadata['condition'] ?? '') == 'Occasion' ? 'selected' : '' }}>Occasion</option>
                                </select>
                            </div>
                            <div class="col-xs-5">
                                <label><input type="checkbox" name="metadata[delivery]" value="1" {{ ($listing->metadata['delivery'] ?? '') == '1' ? 'checked' : '' }}> Livraison disponible</label>
                            </div>
                        </div>
                    </div>

                    <!-- Billets -->
                    <div class="category-fields" id="fields_TICKETS" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label text-warning"><b>Agents Assignés</b></label>
                            <div class="col-xs-10">
                                @php
                                    $assignedIds = $listing->agents->pluck('user_id')->toArray();
                                @endphp
                                <select name="assigned_agents[]" id="assigned_agents" class="form-control" multiple style="height: 100px;" title="Maintenez CTRL pour en sélectionner plusieurs">
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ in_array($agent->id, $assignedIds) ? 'selected' : '' }}>
                                            {{ $agent->first_name }} {{ $agent->last_name }} ({{ $agent->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Sélectionnez les agents autorisés à scanner pour cet événement. (Maintenez CTRL ou CMD appuyé pour sélectionner plusieurs)</small>
                            </div>
                        </div>

                        <div class="form-group row mt-2">
                            <label class="col-xs-2 col-form-label text-primary"><b>Gestion des Passes</b></label>
                            <div class="col-xs-10">
                                <div id="passes_container">
                                    @forelse($listing->passes as $index => $pass)
                                    <div class="pass-row mb-1" style="border: 1px solid #eee; padding: 10px; border-radius: 5px; margin-top: 5px;">
                                        <div class="row">
                                            <div class="col-xs-3"><input class="form-control" type="text" name="passes[{{ $index }}][name]" value="{{ $pass->name }}" placeholder="Nom du Pass" required></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[{{ $index }}][price]" value="{{ $pass->price }}" placeholder="Prix"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[{{ $index }}][quantity]" value="{{ $pass->quantity }}" placeholder="Quantité"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[{{ $index }}][persons_per_pass]" value="{{ $pass->persons_per_pass ?: 1 }}" placeholder="Pers./Pass" min="1"></div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-xs-5"><label>Valide de :</label><input class="form-control" type="time" name="passes[{{ $index }}][valid_from]" value="{{ $pass->valid_from }}"></div>
                                            <div class="col-xs-5"><label>À :</label><input class="form-control" type="time" name="passes[{{ $index }}][valid_until]" value="{{ $pass->valid_until }}"></div>
                                            <div class="col-xs-2"><br><button type="button" class="btn btn-danger btn-sm" onclick="removePass(this)"><i class="fa fa-trash"></i></button></div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="pass-row mb-1" style="border: 1px solid #eee; padding: 10px; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-xs-3"><input class="form-control" type="text" name="passes[0][name]" placeholder="Nom du Pass" required></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][price]" placeholder="Prix"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][quantity]" placeholder="Quantité"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][persons_per_pass]" placeholder="Pers./Pass" value="1" min="1"></div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-xs-5"><label>Valide de :</label><input class="form-control" type="time" name="passes[0][valid_from]" value="00:00"></div>
                                            <div class="col-xs-5"><label>À :</label><input class="form-control" type="time" name="passes[0][valid_until]" value="23:59"></div>
                                            <div class="col-xs-2"><br><button type="button" class="btn btn-danger btn-sm" onclick="removePass(this)"><i class="fa fa-trash"></i></button></div>
                                        </div>
                                    </div>
                                    @endforelse
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-1" onclick="addPass()"><i class="fa fa-plus"></i> Ajouter un Pass</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    let passCount = {{ $listing->passes->count() ?: 1 }};
                    function addPass() {
                        const container = document.getElementById('passes_container');
                        const div = document.createElement('div');
                        div.className = 'pass-row mb-1';
                        div.style = 'border: 1px solid #eee; padding: 10px; border-radius: 5px; margin-top: 10px;';
                        div.innerHTML = `
                            <div class="row">
                                <div class="col-xs-3"><input class="form-control" type="text" name="passes[${passCount}][name]" placeholder="Nom du Pass" required></div>
                                <div class="col-xs-3"><input class="form-control" type="number" name="passes[${passCount}][price]" placeholder="Prix"></div>
                                <div class="col-xs-3"><input class="form-control" type="number" name="passes[${passCount}][quantity]" placeholder="Quantité"></div>
                                <div class="col-xs-3"><input class="form-control" type="number" name="passes[${passCount}][persons_per_pass]" placeholder="Pers./Pass" value="1" min="1"></div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-xs-5"><label>Valide de :</label><input class="form-control" type="time" name="passes[${passCount}][valid_from]" value="00:00"></div>
                                <div class="col-xs-5"><label>À :</label><input class="form-control" type="time" name="passes[${passCount}][valid_until]" value="23:59"></div>
                                <div class="col-xs-2"><br><button type="button" class="btn btn-danger btn-sm" onclick="removePass(this)"><i class="fa fa-trash"></i></button></div>
                            </div>
                        `;
                        container.appendChild(div);
                        passCount++;
                    }

                    function removePass(btn) {
                        const row = btn.closest('.pass-row');
                        if (document.querySelectorAll('.pass-row').length > 1) {
                            row.remove();
                        } else {
                            alert("Il faut au moins un type de pass.");
                        }
                    }
                    function updateSubCategories() {
                        const catSelect = document.getElementById('category');
                        const subSelect = document.getElementById('sub_category');
                        const container = document.getElementById('sub_category_container');
                        const selectedOption = catSelect.options[catSelect.selectedIndex];
                        
                        // Nettoyer
                        subSelect.innerHTML = '<option value="">-- Sélectionner une sous-catégorie --</option>';
                        
                        // Cacher tous les blocs metadata
                        document.querySelectorAll('.category-fields').forEach(el => el.style.display = 'none');

                        if (selectedOption && selectedOption.value) {
                            // Afficher le bloc de champs spécifique à la catégorie
                            const fieldBlock = document.getElementById('fields_' + selectedOption.value);
                            if (fieldBlock) fieldBlock.style.display = 'block';

                            // Gérer l'affichage de l'unité de prix pour la location
                            const priceUnitContainer = document.getElementById('price_unit_container');
                            if (selectedOption.value === 'REAL_ESTATE' || selectedOption.value === 'VEHICLES') {
                                priceUnitContainer.style.display = 'flex';
                            } else {
                                priceUnitContainer.style.display = 'none';
                            }

                            // Gérer la section des passes (valable pour TICKETS et TRAVEL)
                            const ticketsBlock = document.getElementById('fields_TICKETS');
                            if (selectedOption.value === 'TICKETS' || selectedOption.value === 'TRAVEL') {
                                ticketsBlock.style.display = 'block';
                            }

                            if (selectedOption.dataset.subs) {
                                try {
                                    const subs = JSON.parse(selectedOption.dataset.subs);
                                    if (subs && subs.length > 0) {
                                        container.style.display = 'block';
                                        subs.forEach(sub => {
                                            const opt = document.createElement('option');
                                            opt.value = sub.name;
                                            opt.innerHTML = sub.label;
                                            // Pre-select if matches current sub_category in DB
                                            if (sub.name == "{{ $listing->sub_category ?? '' }}") {
                                                opt.selected = true;
                                            }
                                            subSelect.appendChild(opt);
                                        });
                                    } else {
                                        container.style.display = 'none';
                                    }
                                } catch (e) {
                                    container.style.display = 'none';
                                }
                            } else {
                                container.style.display = 'none';
                            }
                        } else {
                            container.style.display = 'none';
                        }
                    }
                    
                    // Init au chargement
                    document.addEventListener('DOMContentLoaded', updateSubCategories);
                </script>

                <div class="form-group row">
                    <label for="title" class="col-xs-2 col-form-label">Titre</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $listing->title }}" name="title" required id="title">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-2 col-form-label">Prix (FCFA)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ $listing->price }}" name="price" required id="price">
                    </div>
                </div>

                <div class="form-group row" id="price_unit_container" style="display: none;">
                    <label for="price_unit" class="col-xs-2 col-form-label text-info">Unité de tarification</label>
                    <div class="col-xs-10">
                        <select name="metadata[price_unit]" id="price_unit" class="form-control">
                            <option value="total" {{ ($listing->metadata['price_unit'] ?? '') == 'total' ? 'selected' : '' }}>Prix Total (Achat / Forfait)</option>
                            <option value="day" {{ ($listing->metadata['price_unit'] ?? '') == 'day' ? 'selected' : '' }}>Prix par Jour (Location)</option>
                            <option value="month" {{ ($listing->metadata['price_unit'] ?? '') == 'month' ? 'selected' : '' }}>Prix par Mois (Location)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-2 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="5" required>{{ $listing->description }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-xs-2 col-form-label">Photo actuelle</label>
                    <div class="col-xs-10">
                        @if($listing->cover_image)
                            @php
                                $imgSrc = $listing->cover_image;
                                // Avoid double 'storage/' prefix
                                if (!str_starts_with($imgSrc, 'data:') && !str_starts_with($imgSrc, 'http')) {
                                    $imgSrc = str_starts_with($imgSrc, 'storage/') ? asset($imgSrc) : \Storage::disk('s3')->url( $imgSrc);
                                }
                            @endphp
                            <img src="{{ $imgSrc }}" height="80" style="border-radius:8px;margin-bottom:8px;display:block;">
                        @else
                            <span class="text-muted">Aucune photo</span>
                        @endif
                        <input class="form-control" type="file" accept="image/*" name="cover_image" id="cover_image">
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
