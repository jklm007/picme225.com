@if(Request::segment(2) == '' || Request::segment(2) == 'index' || Request::is('provider'))
<!-- No header on map page -->
@else
<?php
$avatar = Auth::guard('provider')->user()->avatar;
if ($avatar) {
    if (strpos($avatar, 'lorempixel.com') !== false) {
        $avatar_url = asset('asset/img/provider.jpg');
    } elseif (strpos($avatar, 'http') === 0) {
        $avatar_url = $avatar;
    } else {
        $avatar_url = \Storage::disk('s3')->url( $avatar);
    }
} else {
    $avatar_url = asset('asset/img/provider.jpg');
}
?>
<!-- Dark Header for other pages -->
<header class="mobile-header" style="background-color: #111; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 15px; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; border-bottom: 1px solid #222;">
    <!-- Hamburger Menu Toggle (uses vanilla JS to ensure compatibility) -->
    <button type="button" class="hamburger is-closed" style="background: none; border: none; outline: none; padding: 5px; cursor: pointer;" onclick="var w = document.getElementById('wrapper'); var o = document.getElementById('sidebar-overlay'); if(w){ w.classList.toggle('toggled'); if(o){ if(w.classList.contains('toggled')){ o.style.setProperty('display', 'block', 'important'); } else { o.style.setProperty('display', 'none', 'important'); } } }}">
        <i class="fa fa-bars" style="color: #fff; font-size: 22px;"></i>
    </button>
    
    <!-- Logo (Inverted for dark theme, absolute path) -->
    <a href="{{ url('/provider') }}" style="display: flex; align-items: center;">
        <img src="{{ Setting::get('site_logo') ? (strpos(Setting::get('site_logo'), 'http') === 0 ? Setting::get('site_logo') : asset(Setting::get('site_logo'))) : asset('logo-black.png') }}" style="height: 25px; filter: brightness(0) invert(1);">
    </a>
    
    <!-- Right side avatar (absolute path) -->
    <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
        <a href="{{ route('provider.profile.index') }}" style="color: #fff;">
            <img src="{{ $avatar_url }}" style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #f1c40f; object-fit: cover;">
        </a>
    </div>
</header>
@endif
