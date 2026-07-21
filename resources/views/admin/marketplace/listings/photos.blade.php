@extends('admin.layout.base')
@section('title', 'Gestion des Photos – ' . $listing->title)

@section('styles')
<style>
:root {
    --pm-navy: #0D1B2A;
    --pm-gold: #C9A84C;
    --pm-gold-light: #E2C06E;
    --pm-danger: #e74c3c;
    --pm-success: #27ae60;
    --pm-primary: #2563eb;
    --pm-bg: #f1f5f9;
    --pm-white: #ffffff;
    --pm-radius: 12px;
    --pm-shadow: 0 4px 16px rgba(0,0,0,0.10);
}

body { background: var(--pm-bg); }

.pm-photos-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.pm-photos-header h5 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--pm-navy);
    flex: 1;
}
.pm-photos-meta {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pm-badge-count {
    background: var(--pm-navy);
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
}

/* ---- GRID ---- */
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

/* ---- UPLOAD CARD ---- */
.upload-card {
    border: 2px dashed #cbd5e1;
    border-radius: var(--pm-radius);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: var(--pm-white);
    transition: border-color 0.2s, background 0.2s;
    aspect-ratio: 1;
    padding: 20px;
    gap: 8px;
}
.upload-card:hover {
    border-color: var(--pm-gold);
    background: #fffbf0;
}
.upload-card i { font-size: 32px; color: #94a3b8; }
.upload-card span { font-size: 13px; color: #64748b; font-weight: 500; text-align: center; }
.upload-card.dragging { border-color: var(--pm-primary); background: #eff6ff; }

/* ---- PHOTO CARD ---- */
.photo-card {
    position: relative;
    border-radius: var(--pm-radius);
    overflow: hidden;
    background: #e2e8f0;
    box-shadow: var(--pm-shadow);
    aspect-ratio: 1;
    cursor: grab;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid transparent;
}
.photo-card:active { cursor: grabbing; }
.photo-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.photo-card.is-cover { border-color: var(--pm-gold); }

.photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.photo-card .img-error-msg {
    display: none;
    position: absolute;
    inset: 0;
    background: #f8fafc;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
    padding: 10px;
    gap: 6px;
}
.photo-card img.broken + .img-error-msg { display: flex; }

/* Cover badge */
.badge-cover {
    position: absolute;
    top: 8px;
    left: 8px;
    background: var(--pm-gold);
    color: var(--pm-navy);
    font-size: 10px;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* Index badge */
.badge-index {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,0.55);
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 20px;
}

/* URL debug badge (shown on error) */
.badge-url {
    position: absolute;
    bottom: 40px;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.72);
    color: #fde68a;
    font-size: 9px;
    padding: 4px 6px;
    word-break: break-all;
    display: none;
}
.photo-card:hover .badge-url { display: block; }

/* Action bar */
.photo-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.72));
    padding: 20px 10px 10px;
    display: flex;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.2s;
}
.photo-card:hover .photo-actions { opacity: 1; }
.photo-actions .btn { font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600; }

/* Loading overlay */
.uploading-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
    gap: 16px;
    font-size: 18px;
    font-weight: 600;
}
.uploading-overlay.active { display: flex; }

/* Sortable ghost */
.sortable-ghost { opacity: 0.3; }

/* Lightbox */
#lightbox {
    display: none;
    position: fixed;
    z-index: 10000;
    inset: 0;
    background: rgba(0,0,0,0.92);
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 16px;
}
#lightbox img {
    max-width: 92vw;
    max-height: 82vh;
    border-radius: 10px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.5);
}
#lightbox-close {
    position: absolute;
    top: 20px;
    right: 28px;
    color: white;
    font-size: 32px;
    cursor: pointer;
    line-height: 1;
    opacity: 0.8;
    transition: opacity 0.2s;
}
#lightbox-close:hover { opacity: 1; }
#lightbox-url {
    color: #94a3b8;
    font-size: 11px;
    max-width: 80vw;
    word-break: break-all;
    text-align: center;
}
</style>
@endsection

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">

            @php
                // Helper function to resolve the full URL for each image
                // Uses the same Storage disk the app uses (reads from env injected by K8s)
                $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.default', 's3'));
                
                function resolveImgUrl($path, $disk) {
                    if (empty($path)) return null;
                    if (str_starts_with($path, 'http') || str_starts_with($path, 'data:')) return $path;
                    $url = $disk->url($path);
                    // If Storage returned an absolute URL (R2/S3), return directly
                    if (str_starts_with($url, 'http')) return $url;
                    // Otherwise make it absolute
                    return url($url);
                }
                
                $allImages = is_array($listing->images) ? $listing->images : [];
                $coverImage = $listing->cover_image;
                // Ensure cover is always first if present and not already first
                if ($coverImage && !empty($allImages) && $allImages[0] !== $coverImage) {
                    $allImages = array_filter($allImages, fn($i) => $i !== $coverImage);
                    $allImages = array_values($allImages);
                    array_unshift($allImages, $coverImage);
                }
                // Ensure cover_image is in allImages
                if ($coverImage && !in_array($coverImage, $allImages)) {
                    array_unshift($allImages, $coverImage);
                }
            @endphp

            <div class="pm-photos-header">
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Retour</a>
                <h5><i class="fa fa-images" style="color:var(--pm-gold);"></i> Photos — {{ $listing->title }}</h5>
                <a href="{{ route('admin.marketplace-listings.edit', $listing->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Modifier l'annonce</a>
            </div>

            <div class="pm-photos-meta">
                <span class="pm-badge-count">{{ count($allImages) }} photo(s)</span>
                <span>Glissez-déposez pour réorganiser. La première image est la couverture.</span>
                @if(count($allImages) === 0)
                    <span class="badge badge-warning">Aucune photo</span>
                @endif
            </div>

            @if(session('flash_success'))
                <div class="alert alert-success">{{ session('flash_success') }}</div>
            @endif
            @if(session('flash_error'))
                <div class="alert alert-danger">{{ session('flash_error') }}</div>
            @endif

            <div class="photo-grid" id="photo-grid">

                {{-- Upload Box --}}
                <div class="upload-card" id="upload-card"
                     onclick="document.getElementById('photo-upload').click()"
                     ondragover="event.preventDefault(); this.classList.add('dragging')"
                     ondragleave="this.classList.remove('dragging')"
                     ondrop="handleDrop(event)">
                    <i class="fa fa-cloud-upload"></i>
                    <span>Ajouter une photo<br><small style="color:#94a3b8;">ou glisser-déposer ici</small></span>
                    <input type="file" id="photo-upload" accept="image/*" multiple
                           style="display: none;" onchange="uploadPhotos(this.files)">
                </div>

                {{-- Photo cards --}}
                @foreach($allImages as $index => $imgPath)
                @php
                    $imgUrl = resolveImgUrl($imgPath, $disk);
                    $isCover = ($imgPath === $coverImage);
                    $is_video = str_ends_with(strtolower($imgUrl), '.mp4') || str_ends_with(strtolower($imgUrl), '.mov') || str_ends_with(strtolower($imgUrl), '.avi');
                @endphp
                <div class="photo-card {{ $isCover ? 'is-cover' : '' }}" data-path="{{ $imgPath }}">
                    @if($is_video)
                        <video src="{{ $imgUrl }}"
                               style="width:100%; height:100%; object-fit:cover; display:block;"
                               onclick="openLightbox('{{ $imgUrl }}', '{{ $imgPath }}')"
                               muted loop autoplay></video>
                    @else
                        <img src="{{ $imgUrl }}"
                             alt="Photo {{ $index + 1 }}"
                             onclick="openLightbox('{{ $imgUrl }}', '{{ $imgPath }}')"
                             onerror="this.classList.add('broken'); this.style.display='none';">
                    @endif
                    {{-- Error state --}}
                    <div class="img-error-msg">
                        <i class="fa fa-image" style="font-size:24px;"></i>
                        <span>Image non disponible</span>
                        <small style="color:#e74c3c; font-size:9px; word-break:break-all;">{{ $imgPath }}</small>
                    </div>

                    {{-- URL debug badge (visible on hover) --}}
                    <div class="badge-url">{{ $imgUrl }}</div>

                    @if($isCover)
                        <div class="badge-cover"><i class="fa fa-star"></i> Couverture</div>
                    @endif
                    <div class="badge-index">#{{ $index + 1 }}</div>

                    <div class="photo-actions">
                        @if(!$isCover)
                            <button class="btn btn-warning btn-sm" onclick="setMainPhoto('{{ $imgPath }}')" title="Définir comme couverture">
                                <i class="fa fa-star"></i> Couv.
                            </button>
                        @endif
                        <button class="btn btn-danger btn-sm" onclick="deletePhoto('{{ $imgPath }}', this)" title="Supprimer">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach

            </div>{{-- /photo-grid --}}

        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox()">
    <span id="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="Aperçu grande taille" style="display:none;">
    <video id="lightbox-video" src="" controls style="max-width:92vw; max-height:82vh; display:none; border-radius:10px;" onclick="event.stopPropagation()"></video>
    <div id="lightbox-url"></div>
</div>

{{-- Uploading overlay --}}
<div class="uploading-overlay" id="uploading-overlay">
    <i class="fa fa-spinner fa-spin" style="font-size:36px;"></i>
    <span id="uploading-label">Envoi en cours...</span>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    const csrfToken = '{{ csrf_token() }}';
    const listingId = '{{ $listing->id }}';
    const baseUrl = '{{ url("admin/marketplace-listings") }}';

    // ---- SORTABLE ----
    var sortable = Sortable.create(document.getElementById('photo-grid'), {
        animation: 150,
        filter: '.upload-card',
        ghostClass: 'sortable-ghost',
        onEnd: function() { saveOrder(); }
    });

    // ---- UPLOAD ----
    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('upload-card').classList.remove('dragging');
        uploadPhotos(e.dataTransfer.files);
    }

    function uploadPhotos(files) {
        if (!files || files.length === 0) return;
        let pending = Array.from(files);
        uploadNext(pending, 0);
    }

    function uploadNext(files, index) {
        if (index >= files.length) {
            hideOverlay();
            window.location.reload();
            return;
        }
        showOverlay(`Envoi ${index + 1}/${files.length}...`);
        let formData = new FormData();
        formData.append('photo', files[index]);
        formData.append('_token', csrfToken);
        fetch(`${baseUrl}/${listingId}/photos`, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { alert(data.message || "Erreur upload"); hideOverlay(); return; }
                uploadNext(files, index + 1);
            })
            .catch(() => { alert("Erreur upload"); hideOverlay(); });
        document.getElementById('photo-upload').value = '';
    }

    // ---- SAVE ORDER ----
    function saveOrder() {
        let items = document.querySelectorAll('.photo-card');
        let order = Array.from(items).map(el => el.getAttribute('data-path'));
        fetch(`${baseUrl}/${listingId}/photos/reorder`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ order })
        }).then(r => r.json()).then(data => { if(data.success) window.location.reload(); });
    }

    // ---- SET COVER ----
    function setMainPhoto(path) {
        if(!confirm("Définir cette image comme couverture ?")) return;
        fetch(`${baseUrl}/${listingId}/photos/main`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ path })
        }).then(r => r.json()).then(data => { if(data.success) window.location.reload(); else alert(data.message); });
    }

    // ---- DELETE ----
    function deletePhoto(path, btn) {
        if(!confirm("Supprimer définitivement cette image ?")) return;
        btn.disabled = true;
        fetch(`${baseUrl}/${listingId}/photos`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ path })
        }).then(r => r.json()).then(data => { if(data.success) window.location.reload(); else { alert(data.message); btn.disabled = false; } });
    }

    function openLightbox(src, path) {
        const isVideo = src.toLowerCase().endsWith('.mp4') || src.toLowerCase().endsWith('.mov') || src.toLowerCase().endsWith('.avi');
        const imgEl = document.getElementById('lightbox-img');
        const videoEl = document.getElementById('lightbox-video');
        
        if (isVideo) {
            imgEl.style.display = 'none';
            videoEl.src = src;
            videoEl.style.display = 'block';
            videoEl.play();
        } else {
            videoEl.style.display = 'none';
            videoEl.src = '';
            imgEl.src = src;
            imgEl.style.display = 'block';
        }
        document.getElementById('lightbox-url').textContent = src;
        document.getElementById('lightbox').style.display = 'flex';
    }
    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.getElementById('lightbox-img').src = '';
        const videoEl = document.getElementById('lightbox-video');
        videoEl.pause();
        videoEl.src = '';
    }
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeLightbox(); });

    // ---- OVERLAY ----
    function showOverlay(msg) {
        document.getElementById('uploading-label').textContent = msg;
        document.getElementById('uploading-overlay').classList.add('active');
    }
    function hideOverlay() {
        document.getElementById('uploading-overlay').classList.remove('active');
    }
</script>
@endsection
