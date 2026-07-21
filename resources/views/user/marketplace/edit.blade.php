@extends('user.layout.base')

@section('title', 'Modifier l\'annonce – PicMe225 Marketplace')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --navy:#0D1B2A; --navy-2:#162436; --navy-3:#1e3048;
    --gold:#C9A84C; --gold-light:#E2C06E; --gold-pale:rgba(201,168,76,0.12);
    --white:#fff; --gray-50:#f9fafc; --gray-100:#f0f2f7; --gray-200:#e4e7ef;
    --success:#27ae60; --danger:#e74c3c;
}
header,.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
body,html{margin:0;padding:0;background:var(--gray-50);font-family:'Inter',sans-serif}
.pm-mk-header{
    background:linear-gradient(135deg,var(--lime, #22C55E),var(--lime-dark, #15803D));
    padding:16px 16px 12px; position:sticky; top:0; z-index:50;
    display:flex;align-items:center;gap:12px;
    box-shadow:0 2px 16px rgba(0,0,0,0.1);
}
.pm-mk-header h1{font-size:17px;font-weight:800;color:#ffffff;margin:0;flex:1;text-shadow: 0 1px 2px rgba(0,0,0,0.1);}
.pm-mk-back{color:#ffffff;font-size:20px;text-decoration:none;line-height:1}
.pm-mk-body{padding:14px 14px 160px !important}
.form-card{
    background:var(--white);border-radius:16px;padding:20px;
    box-shadow:0 4px 16px rgba(13,27,42,0.05);
}
.form-group{margin-bottom:16px}
.form-group label{font-weight:700;font-size:12px;color:var(--navy);display:block;margin-bottom:6px;text-transform:uppercase}
.form-control{
    width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:10px;
    font-size:13px;color:var(--navy);outline:none;transition:border-color 0.2s;
    box-sizing:border-box;
}
.form-control:focus{border-color:var(--gold)}
.btn-gold{
    background:linear-gradient(135deg,var(--gold),var(--gold-light));
    color:var(--navy);font-weight:700;border:none;border-radius:20px;
    padding:10px 24px;font-size:13px;width:100%;cursor:pointer;
    transition:transform 0.15s;
}
.btn-gold:active{transform:scale(0.97)}
.current-photos{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.photo-preview{width:70px;height:70px;border-radius:8px;object-fit:cover;border:1px solid var(--gray-200)}
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="pm-mk-header">
        <a href="{{ route('user.marketplace.my') }}" class="pm-mk-back"><i class="fa fa-arrow-left"></i></a>
        <h1>Modifier mon annonce</h1>
    </div>

    <div class="pm-mk-body">
        <div class="form-card">
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 16px; padding: 10px; border-radius: 8px;">
                    <ul style="margin: 0; padding-left: 20px; font-size: 12px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('user.marketplace.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="form-group">
                    <label>Titre de l'annonce</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="5" required>{{ old('description', $listing->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Catégorie principale</label>
                    <select name="category" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ $listing->category == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Sous-catégorie</label>
                    <input type="text" name="sub_category" class="form-control" value="{{ old('sub_category', $listing->sub_category) }}">
                </div>

                <div class="row">
                    <div class="col-xs-6 form-group">
                        <label>Prix</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', (int)$listing->price) }}" required>
                    </div>
                    <div class="col-xs-6 form-group">
                        <label>Unité</label>
                        <select name="price_unit" class="form-control">
                            <option value="DAY" {{ $listing->price_unit == 'DAY' ? 'selected' : '' }}>Par jour</option>
                            <option value="HOUR" {{ $listing->price_unit == 'HOUR' ? 'selected' : '' }}>Par heure</option>
                            <option value="FIXED" {{ $listing->price_unit == 'FIXED' ? 'selected' : '' }}>Prix fixe</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6 form-group">
                        <label>Téléphone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $listing->owner_phone) }}" required>
                    </div>
                    <div class="col-xs-6 form-group">
                        <label>Ville</label>
                        <input type="text" name="location_city" class="form-control" value="{{ old('location_city', $listing->location_city) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6 form-group">
                        <label>État</label>
                        <select name="condition" class="form-control">
                            <option value="new" {{ ($listing->metadata['condition'] ?? '') == 'new' ? 'selected' : '' }}>Neuf</option>
                            <option value="excellent" {{ ($listing->metadata['condition'] ?? '') == 'excellent' ? 'selected' : '' }}>Excellent état</option>
                            <option value="used" {{ ($listing->metadata['condition'] ?? '') == 'used' ? 'selected' : '' }}>Bon état</option>
                        </select>
                    </div>
                    <div class="col-xs-6 form-group">
                        <label>Stock</label>
                        <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $listing->metadata['stock_quantity'] ?? 1) }}">
                    </div>
                </div>
                
                @if(in_array($listing->type, ['TICKETS', 'TRAVEL']))
                <div class="form-group" style="padding: 15px; border: 1px solid var(--gold); border-radius: 10px; background: var(--gold-pale); margin-bottom: 20px;">
                    <h4 style="margin-top:0; font-size: 14px; color: var(--navy);"><i class="fa fa-ticket"></i> Billetterie & Agents</h4>
                    
                    <label style="color: var(--navy-2);">Agents Assignés *</label>
                    @php
                        $assignedIds = $listing->agents->pluck('user_id')->toArray();
                    @endphp
                    <select name="assigned_agents[]" id="assigned_agents" class="form-control" multiple style="height: 100px; margin-bottom: 15px;">
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ in_array($agent->id, $assignedIds) ? 'selected' : '' }}>
                                {{ $agent->first_name }} {{ $agent->last_name }} ({{ $agent->email }})
                            </option>
                        @endforeach
                    </select>
                    <small style="display:block; margin-bottom: 15px; color: var(--navy-2);">Maintenez CTRL ou CMD pour sélectionner plusieurs agents.</small>
                    
                    <label style="color: var(--navy-2);">Passes</label>
                    <div id="passes_container">
                        @forelse($listing->passes as $index => $pass)
                        <div class="pass-row" style="background: var(--white); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                            <div style="display:flex; gap:10px; margin-bottom:10px;">
                                <input type="text" name="passes[{{$index}}][name]" value="{{ $pass->name }}" class="form-control" placeholder="Nom du Pass" required>
                                <input type="number" name="passes[{{$index}}][price]" value="{{ $pass->price }}" class="form-control" placeholder="Prix">
                            </div>
                            <div style="display:flex; gap:10px;">
                                <input type="number" name="passes[{{$index}}][quantity]" value="{{ $pass->quantity }}" class="form-control" placeholder="Stock">
                                <input type="number" name="passes[{{$index}}][persons_per_pass]" value="{{ $pass->persons_per_pass ?: 1 }}" class="form-control" placeholder="Pers./Pass">
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" style="margin-top: 10px; width: 100%;" onclick="this.parentElement.remove()">- Supprimer</button>
                        </div>
                        @empty
                        <div class="pass-row" style="background: var(--white); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                            <div style="display:flex; gap:10px; margin-bottom:10px;">
                                <input type="text" name="passes[0][name]" class="form-control" placeholder="Nom du Pass" required>
                                <input type="number" name="passes[0][price]" class="form-control" placeholder="Prix">
                            </div>
                            <div style="display:flex; gap:10px;">
                                <input type="number" name="passes[0][quantity]" class="form-control" placeholder="Stock">
                                <input type="number" name="passes[0][persons_per_pass]" value="1" class="form-control" placeholder="Pers./Pass">
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" style="margin-top: 10px; width: 100%;" onclick="this.parentElement.remove()">- Supprimer</button>
                        </div>
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-default" style="width: 100%; margin-top: 5px;" onclick="addPass()">+ Ajouter un Pass</button>
                </div>
                
                <script>
                let passCount = {{ count($listing->passes) > 0 ? count($listing->passes) : 1 }};
                function addPass() {
                    const container = document.getElementById('passes_container');
                    const div = document.createElement('div');
                    div.className = 'pass-row';
                    div.style = 'background: var(--white); padding: 10px; border-radius: 8px; margin-bottom: 10px;';
                    div.innerHTML = `
                        <div style="display:flex; gap:10px; margin-bottom:10px;">
                            <input type="text" name="passes[${passCount}][name]" class="form-control" placeholder="Nom du Pass" required>
                            <input type="number" name="passes[${passCount}][price]" class="form-control" placeholder="Prix">
                        </div>
                        <div style="display:flex; gap:10px;">
                            <input type="number" name="passes[${passCount}][quantity]" class="form-control" placeholder="Stock">
                            <input type="number" name="passes[${passCount}][persons_per_pass]" value="1" class="form-control" placeholder="Pers./Pass">
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" style="margin-top: 10px; width: 100%;" onclick="this.parentElement.remove()">- Supprimer</button>
                    `;
                    container.appendChild(div);
                    passCount++;
                }
                </script>
                @endif

                <div class="form-group">
                    <label>Remplacer les photos (Max 6)</label>
                    <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                    
                    @if(!empty($listing->images))
                        <div class="current-photos">
                            @foreach($listing->images as $img)
                                <img class="photo-preview" src="{{ str_starts_with($img, 'http') ? $img : url('storage/' . $img) }}">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label>Infos complémentaires</label>
                    <input type="text" name="extra_info" class="form-control" value="{{ old('extra_info', $listing->metadata['extra_info'] ?? '') }}">
                </div>

                <button type="submit" class="btn-gold" style="margin-top: 10px;">
                    Enregistrer les modifications
                </button>
            </form>
        </div>
</div>
@include('user.include.bottom_nav', ['active' => 'store'])
</div>
@endsection
