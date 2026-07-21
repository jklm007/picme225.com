@extends('admin.layout.base')
@section('title', 'Publier une Annonce')
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.marketplace-listings.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 class="mb-2">Publier sur la Marketplace</h5>
            <form class="form-horizontal" action="{{ route('admin.marketplace-listings.store') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                
                <!-- Type est géré en arrière-plan pour la compatibilité DB -->
                <input type="hidden" name="type" value="ARTICLE">

                <div class="form-group row">
                    <label for="category" class="col-xs-2 col-form-label">Catégorie</label>
                    <div class="col-xs-10">
                        <select name="category" id="category" class="form-control" required onchange="updateSubCategories()">
                            <option value="">-- Sélectionner une catégorie --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}" data-subs='@json($category->children)'>
                                    {{ $category->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row" id="sub_category_container" style="display: none;">
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
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[rooms]" placeholder="Chambres"></div>
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[bathrooms]" placeholder="SDB"></div>
                            <div class="col-xs-4"><input class="form-control" type="text" name="metadata[area]" placeholder="Superficie (m²)"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Options</label>
                            <div class="col-xs-10">
                                <label><input type="checkbox" name="metadata[furnished]" value="1"> Meublé</label> &nbsp;
                                <label><input type="checkbox" name="metadata[parking]" value="1"> Parking</label> &nbsp;
                                <label><input type="checkbox" name="metadata[pool]" value="1"> Piscine</label>
                            </div>
                        </div>
                    </div>

                    <!-- Véhicules -->
                    <div class="category-fields" id="fields_VEHICLES" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Véhicule</label>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[brand]" placeholder="Marque"></div>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[model]" placeholder="Modèle"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Détails</label>
                            <div class="col-xs-3"><input class="form-control" type="number" name="metadata[year]" placeholder="Année"></div>
                            <div class="col-xs-4"><input class="form-control" type="text" name="metadata[mileage]" placeholder="Kilométrage"></div>
                            <div class="col-xs-3">
                                <select name="metadata[fuel]" class="form-control">
                                    <option value="">Carburant</option>
                                    <option value="Essence">Essence</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electrique">Electrique</option>
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
                                    <option value="Neuf">Neuf</option>
                                    <option value="Occasion">Occasion</option>
                                </select>
                            </div>
                            <div class="col-xs-5">
                                <label><input type="checkbox" name="metadata[delivery]" value="1"> Livraison disponible</label>
                            </div>
                        </div>
                    </div>

                    <!-- Voyage -->
                    <div class="category-fields" id="fields_TRAVEL" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label text-success"><b>Itinéraire de Voyage</b></label>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[departure_city]" placeholder="Ville de Départ"></div>
                            <div class="col-xs-5"><input class="form-control" type="text" name="metadata[arrival_city]" placeholder="Destination"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label">Départ Prévu</label>
                            <div class="col-xs-5"><input class="form-control" type="date" name="metadata[departure_date]"></div>
                            <div class="col-xs-5"><input class="form-control" type="time" name="metadata[departure_time]"></div>
                        </div>
                    </div>

                    <!-- Billets -->
                    <div class="category-fields" id="fields_TICKETS" style="display: none;">
                        <div class="form-group row">
                            <label class="col-xs-2 col-form-label text-warning"><b>Agents Assignés</b></label>
                            <div class="col-xs-10">
                                <select name="assigned_agents[]" id="assigned_agents" class="form-control" multiple style="height: 100px;" title="Maintenez CTRL pour en sélectionner plusieurs">
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->first_name }} {{ $agent->last_name }} ({{ $agent->email }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Sélectionnez les agents autorisés à scanner pour cet événement. (Maintenez CTRL ou CMD appuyé pour sélectionner plusieurs)</small>
                            </div>
                        </div>

                        <div class="form-group row mt-2">
                            <label class="col-xs-2 col-form-label text-primary"><b>Configuration Billetterie</b></label>
                            <div class="col-xs-10">
                                <div id="passes_container">
                                    <div class="pass-row mb-1" style="border: 1px solid #eee; padding: 10px; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-xs-3"><input class="form-control" type="text" name="passes[0][name]" placeholder="Nom du Pass (ex: Buffet Midi)" required></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][price]" placeholder="Prix"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][quantity]" placeholder="Quantité (Stock)"></div>
                                            <div class="col-xs-3"><input class="form-control" type="number" name="passes[0][persons_per_pass]" placeholder="Pers./Pass" value="1" min="1"></div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-xs-5"><label>Valide de :</label><input class="form-control" type="time" name="passes[0][valid_from]" value="00:00"></div>
                                            <div class="col-xs-5"><label>À :</label><input class="form-control" type="time" name="passes[0][valid_until]" value="23:59"></div>
                                            <div class="col-xs-2"><br><button type="button" class="btn btn-danger btn-sm" onclick="removePass(this)"><i class="fa fa-trash"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-1" onclick="addPass()"><i class="fa fa-plus"></i> Ajouter un Pass</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    let passCount = 1;
                    function addPass() {
                        const container = document.getElementById('passes_container');
                        const div = document.createElement('div');
                        div.className = 'pass-row mb-1';
                        div.style = 'border: 1px solid #eee; padding: 10px; border-radius: 5px; margin-top: 10px;';
                        div.innerHTML = `
                            <div class="row">
                                <div class="col-xs-3"><input class="form-control" type="text" name="passes[${passCount}][name]" placeholder="Nom du Pass" required></div>
                                <div class="col-xs-3"><input class="form-control" type="number" name="passes[${passCount}][price]" placeholder="Prix"></div>
                                <div class="col-xs-3"><input class="form-control" type="number" name="passes[${passCount}][quantity]" placeholder="Quantité (Stock)"></div>
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
                            alert("Il faut au moins un type de pass pour un ticket.");
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
                                document.getElementById('price_unit').value = 'total'; // Reset to default
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
                                            opt.value = sub.label;
                                            opt.innerHTML = sub.label;
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
                </script>

                <div class="form-group row">
                    <label for="title" class="col-xs-2 col-form-label">Titre</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('title') }}" name="title" required id="title" placeholder="Ex: Belle voiture d'occasion">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-2 col-form-label">Prix (FCFA)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('price', 0) }}" name="price" required id="price">
                    </div>
                </div>

                <div class="form-group row" id="price_unit_container" style="display: none;">
                    <label for="price_unit" class="col-xs-2 col-form-label text-info">Unité de tarification</label>
                    <div class="col-xs-10">
                        <select name="metadata[price_unit]" id="price_unit" class="form-control">
                            <option value="total">Prix Total (Achat / Forfait)</option>
                            <option value="day">Prix par Jour (Location)</option>
                            <option value="month">Prix par Mois (Location)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-2 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="5" required>{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cover_image" class="col-xs-2 col-form-label">Photo de couverture</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="file" accept="image/*" name="cover_image" id="cover_image">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10 col-xs-offset-2">
                        <button type="submit" class="btn btn-primary">Publier l'Annonce</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
