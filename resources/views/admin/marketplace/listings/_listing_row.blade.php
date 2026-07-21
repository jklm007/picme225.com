@php
    $allImgs = [];
    if ($listing->cover_image) {
        $src = $listing->cover_image;
        if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = img($src);
        $allImgs[] = $src;
    }
    if (is_array($listing->images)) {
        foreach ($listing->images as $img) {
            if (!$img || $img === $listing->cover_image) continue;
            $src = $img;
            if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = img($src);
            $allImgs[] = $src;
        }
    }
    $totalImgs = count($allImgs);
@endphp
<tr>
    <td><input type="checkbox" class="row-checkbox" value="{{ $listing->id }}"></td>
    <td>{{ $listing->id }}</td>
    <td>{{ $listing->type }}</td>
    <td style="min-width:100px;">
        @if($totalImgs > 0)
            <div style="position:relative; width:80px; height:70px; display:inline-block; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0;">
                @php
                    $firstImg = $allImgs[0];
                    $is_video = str_ends_with(strtolower($firstImg), '.mp4') || str_ends_with(strtolower($firstImg), '.mov') || str_ends_with(strtolower($firstImg), '.avi');
                @endphp
                @if($is_video)
                    <video src="{{ $firstImg }}" style="width:100%; height:100%; object-fit:cover;" muted></video>
                @else
                    <img src="{{ $firstImg }}" alt="Aperçu"
                         style="width:100%; height:100%; object-fit:cover;"
                         onerror="this.style.display='none'">
                @endif
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
        <a href="/marketplace/{{ $listing->id }}" target="_blank" style="color:inherit;font-weight:600;">{{ $listing->title }}</a>
        <br>
        <small class="text-muted">Par: 
            @if($listing->user)
                {{ $listing->user->first_name . ' ' . $listing->user->last_name }}
            @elseif($listing->owner_phone)
                {{ ($listing->whatsappMessage && $listing->whatsappMessage->sender) ? $listing->whatsappMessage->sender->name . ' (' . $listing->owner_phone . ')' : $listing->owner_phone }}
            @else
                Inconnu
            @endif
        </small>
    </td>
    <td>{{ $listing->category }}</td>
    <td>{{ number_format((float)$listing->price, 0, ',', ' ') }} FCFA</td>
    <td>{{ $listing->created_at->format('d/m/Y H:i') }}</td>
    <td>
        <form action="{{ route('admin.marketplace-listings.destroy', $listing->id) }}" method="POST" style="display:inline-block;">
            {{ csrf_field() }}
            {{ method_field('DELETE') }}
            <a href="{{ route('admin.marketplace-listings.edit', $listing->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Éditer</a>
            <a href="{{ url('admin/marketplace-listings/'.$listing->id.'/photos') }}" class="btn btn-warning btn-sm" title="Gérer les photos ({{ count((array)$listing->images) }})">
                <i class="fa fa-camera"></i> Photos
                @if(count((array)$listing->images) > 0)
                    <span class="badge" style="background:#fff;color:#333;">{{ count((array)$listing->images) }}</span>
                @endif
            </a>
            <button class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?')"><i class="fa fa-trash"></i> Supprimer</button>
        </form>
        
        @if($is_pending)
        <hr style="margin:5px 0;">
        <form action="{{ route('admin.marketplace-listings.approve', $listing->id) }}" method="POST" style="display:inline-block;">
            {{ csrf_field() }}
            <button class="btn btn-success btn-sm"><i class="fa fa-check"></i> Valider</button>
        </form>
        <form action="{{ route('admin.marketplace-listings.reject', $listing->id) }}" method="POST" style="display:inline-block;">
            {{ csrf_field() }}
            <button class="btn btn-warning btn-sm" onclick="return confirm('Voulez-vous rejeter cette annonce ?')"><i class="fa fa-times"></i> Rejeter</button>
        </form>
        @endif
    </td>
</tr>
