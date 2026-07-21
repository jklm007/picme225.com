@extends('admin.layout.base')

@section('title', 'WhatsApp AI Listings')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">
                Annonces WhatsApp (Générées par l'IA)
            </h5>
            <p class="mb-2">Voici les annonces extraites automatiquement des groupes WhatsApp.</p>

            @if(isset($stats))
            <div class="row mb-2">
                <div class="col-md-3">
                    <div class="box box-block bg-white border border-primary">
                        <div class="clearfix mb-1">
                            <h5 class="float-left">Total</h5>
                        </div>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box box-block bg-white border border-warning">
                        <div class="clearfix mb-1">
                            <h5 class="float-left">En attente</h5>
                        </div>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box box-block bg-white border border-success">
                        <div class="clearfix mb-1">
                            <h5 class="float-left">Validées</h5>
                        </div>
                        <h3 class="mb-0">{{ $stats['active'] }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box box-block bg-white border border-danger">
                        <div class="clearfix mb-1">
                            <h5 class="float-left">Spammeurs (Blacklistés)</h5>
                        </div>
                        <h3 class="mb-0">{{ $stats['blacklisted'] }}</h3>
                    </div>
                </div>
            </div>
            @endif

            @if(session('flash_success'))
                <div class="alert alert-success">{{ session('flash_success') }}</div>
            @endif

            <form action="{{ route('admin.whatsapp.index') }}" method="GET" class="form-inline mb-2">
                <div class="form-group">
                    <label for="status">Filtrer par Statut :</label>
                    <select name="status" id="status" class="form-control ml-1" onchange="this.form.submit()">
                        <option value="PENDING_VALIDATION" {{ $status == 'PENDING_VALIDATION' ? 'selected' : '' }}>En attente de validation</option>
                        <option value="ACTIVE" {{ $status == 'ACTIVE' ? 'selected' : '' }}>Publiées (Approuvées)</option>
                        <option value="REJECTED" {{ $status == 'REJECTED' ? 'selected' : '' }}>Rejetées</option>
                        <option value="ALL" {{ $status == 'ALL' ? 'selected' : '' }}>Toutes</option>
                    </select>
                </div>
            </form>

            <div class="mb-2 mt-2">
                <form id="bulk-action-form" method="POST" action="{{ route('admin.whatsapp.bulk-action') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="action" id="bulk-action-input" value="">
                    <div id="hidden-ids-container"></div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="btn-bulk-action" disabled>
                            Actions Groupées (<span id="selected-count">0</span>)
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="approve"><i class="fa fa-check text-success"></i> Approuver la sélection</a>
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="reject"><i class="fa fa-times text-warning"></i> Rejeter la sélection</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="delete"><i class="fa fa-trash text-danger"></i> Supprimer la sélection</a>
                        </div>
                    </div>
                </form>
            </div>

            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>Expéditeur</th>
                        <th>Image</th>
                        <th>Titre / Catégorie</th>
                        <th>Prix</th>
                        <th>Score IA</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listings as $listing)
                    <tr>
                        <td><input type="checkbox" class="row-checkbox" value="{{ $listing->id }}"></td>
                        <td>{{ $listing->id }}</td>
                        <td>
                            @if($listing->whatsappMessage && $listing->whatsappMessage->sender)
                                <strong>{{ $listing->whatsappMessage->sender->name }}</strong><br>
                                <small>{{ $listing->whatsappMessage->sender->phone_number }}</small>
                            @else
                                N/A
                            @endif
                        </td>
                        <td style="min-width:100px;">
                            @php
                                $allImgs = [];
                                // cover_image
                                if ($listing->cover_image) {
                                    $src = $listing->cover_image;
                                    if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = url('storage/'.$src);
                                    $allImgs[] = $src;
                                }
                                // images array
                                if (is_array($listing->images)) {
                                    foreach ($listing->images as $img) {
                                        if (!$img || $img === $listing->cover_image) continue;
                                        $src = $img;
                                        if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = url('storage/'.$src);
                                        $allImgs[] = $src;
                                    }
                                }
                                $totalImgs = count($allImgs);
                            @endphp
                            @if($totalImgs > 0)
                                <div style="position:relative; width:80px; height:70px; display:inline-block; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0;">
                                    <img src="{{ $allImgs[0] }}" alt="Aperçu" 
                                         style="width:100%; height:100%; object-fit:cover;"
                                         onerror="this.style.display='none'">
                                    @if($totalImgs > 1)
                                    <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; line-height:1; z-index:10;">
                                        <i class="fa fa-camera"></i> {{ $totalImgs }}
                                    </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">Aucune</span>
                            @endif
                        </td>
                        <td>
                            <strong>
                                <a href="/marketplace/{{ $listing->id }}" target="_blank" style="color:inherit;">{{ $listing->title }}</a>
                            </strong><br>
                            <span class="badge badge-info">{{ $listing->category }}</span> | <small>{{ $listing->type }}</small>
                            <hr style="margin: 5px 0;">
                            <small class="text-muted">Original: {{ \Illuminate\Support\Str::limit($listing->whatsappMessage->content ?? '', 50) }}</small>
                        </td>
                        <td>{{ $listing->price ? number_format((float)$listing->price, 0, ',', ' ') : 'N/A' }} {{ $listing->price_unit }}</td>
                        <td>
                            @if($listing->ai_confidence_score >= 85)
                                <span class="badge badge-success">{{ $listing->ai_confidence_score }}%</span>
                            @else
                                <span class="badge badge-warning">{{ $listing->ai_confidence_score }}%</span>
                            @endif
                        </td>
                        <td>
                            @if($listing->status == 'PENDING_VALIDATION')
                                <span class="badge badge-warning">En attente</span>
                            @elseif($listing->status == 'ACTIVE' || $listing->status == 'APPROVED')
                                <span class="badge badge-success">Publié</span>
                            @else
                                <span class="badge badge-danger">{{ $listing->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Action
                                </button>
                                <div class="dropdown-menu">
                                    @if($listing->status == 'PENDING_VALIDATION')
                                        <form action="{{ route('admin.whatsapp.approve', $listing->id) }}" method="POST" style="display:inline-block;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="dropdown-item"><i class="fa fa-check"></i> Valider (Publier)</button>
                                        </form>
                                        <form action="{{ route('admin.whatsapp.reject', $listing->id) }}" method="POST" style="display:inline-block;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="dropdown-item"><i class="fa fa-times"></i> Rejeter</button>
                                        </form>
                                    @endif
                                    
                                    @if($listing->owner_phone)
                                        <form action="{{ route('admin.whatsapp.blacklist', $listing->owner_phone) }}" method="POST" style="display:inline-block;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Voulez-vous vraiment changer le statut blacklist de ce numéro ({{ $listing->owner_phone }}) ?');"><i class="fa fa-ban"></i> Toggle Blacklist</button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.whatsapp.destroy', $listing->id) }}" method="POST" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Supprimer cette annonce définitivement ?');"><i class="fa fa-trash"></i> Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $listings->links() }}
            </div>
            
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    function updateSelectedCount() {
        var count = $('.row-checkbox:checked').length;
        $('#selected-count').text(count);
        if(count > 0) {
            $('#btn-bulk-action').prop('disabled', false);
        } else {
            $('#btn-bulk-action').prop('disabled', true);
        }
    }

    $('#select-all').change(function() {
        var isChecked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });

    $('.row-checkbox').change(function() {
        var total = $('.row-checkbox').length;
        var checked = $('.row-checkbox:checked').length;
        
        if(total == checked) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }
        
        updateSelectedCount();
    });

    $('.bulk-action-btn').click(function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var count = $('.row-checkbox:checked').length;
        
        if (count === 0) return;
        
        var confirmMsg = "Êtes-vous sûr de vouloir appliquer cette action ?";
        if (action === 'delete') confirmMsg = "Êtes-vous sûr de vouloir SUPPRIMER DÉFINITIVEMENT ces " + count + " annonces ?";
        
        if (confirm(confirmMsg)) {
            $('#bulk-action-input').val(action);
            $('#hidden-ids-container').empty();
            
            $('.row-checkbox:checked').each(function() {
                $('#hidden-ids-container').append('<input type="hidden" name="selected_ids[]" value="'+$(this).val()+'">');
            });
            
            $('#bulk-action-form').submit();
        }
    });
});
</script>
@endsection
