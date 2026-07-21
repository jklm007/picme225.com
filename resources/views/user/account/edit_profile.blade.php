@extends('user.layout.base')

@section('title', 'Modifier le Profil')
@php $bottomNavActive = 'profil'; @endphp

@section('styles')
<style>
    /* Override to remove header space since there's no visible header */
    .pwa-header, header, .dash-left, .footer-content, .menu-toggle, .overlay { display: none !important; }
    .page-content.dashboard-page { padding-top: 0 !important; }

    /* ── PROFILE EDIT PAGE ── */
    body, html {
        background: #F0F2F5;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .edit-page-wrapper {
        min-height: calc(100vh - 64px);
        padding-bottom: 90px;
    }

    /* ── HEADER (BACK BAR) ── */
    .pm-edit-header {
        background: #ffffff;
        padding: 16px; position: sticky; top: 0; z-index: 50;
        display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .pm-edit-header h1 {
        font-size: 17px; font-weight: 800; color: #0D1B2A; margin: 0; flex: 1;
    }
    .pm-edit-back {
        color: #0D1B2A; font-size: 20px; text-decoration: none;
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; background: #F0F2F5;
    }

    /* ── HERO / AVATAR ── */
    .edit-hero {
        background: linear-gradient(160deg, #0D1B2A 0%, #1a3050 60%, #0f2540 100%);
        padding: 40px 20px 50px 20px;
        text-align: center;
    }
    .edit-avatar-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 14px;
    }
    .edit-avatar {
        width: 110px; height: 110px;
        border-radius: 50%;
        border: 4px solid #C9A84C;
        box-shadow: 0 8px 32px rgba(201,168,76,0.4);
        background-size: cover;
        background-position: center;
        background-color: #1a3050;
        display: flex; align-items: center; justify-content: center;
        font-size: 40px; font-weight: 800; color: #C9A84C;
        margin: 0 auto;
    }
    .edit-avatar-upload {
        position: absolute;
        bottom: 0; right: 0;
        width: 34px; height: 34px;
        border-radius: 50%;
        background: #C9A84C;
        border: 3px solid #0D1B2A;
        display: flex; align-items: center; justify-content: center;
        color: #0D1B2A;
        font-size: 14px;
        cursor: pointer;
        overflow: hidden;
    }
    .edit-avatar-upload input[type="file"] {
        position: absolute; left: 0; top: 0; width: 100%; height: 100%;
        opacity: 0; cursor: pointer;
    }

    /* ── FORM CARDS ── */
    .edit-cards {
        padding: 20px 16px;
        position: relative;
        z-index: 2;
        margin-top: -30px;
    }
    .edit-card {
        background: #fff;
        border-radius: 18px;
        margin-bottom: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        padding: 20px 16px;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #1C2E4A;
        transition: all 0.2s;
        box-shadow: none;
        height: auto;
    }
    .form-control:focus {
        background: #fff;
        border-color: #C9A84C;
        outline: none;
        box-shadow: 0 0 0 3px rgba(201,168,76,0.15);
    }
    .form-control[readonly] {
        background: #F1F5F9;
        color: #94A3B8;
        cursor: not-allowed;
    }
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px;
    }

    /* ── SAVE BUTTON ── */
    .save-btn-container {
        margin-top: 10px;
    }
    .pm-save-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #C9A84C, #E2C06E);
        color: #0D1B2A;
        border-radius: 16px;
        font-size: 16px;
        font-weight: 800;
        text-decoration: none;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(201,168,76,0.4);
        transition: transform 0.2s;
    }
    .pm-save-btn:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="edit-page-wrapper">
    
    <div class="pm-edit-header">
        <a href="{{ url('profile') }}" class="pm-edit-back"><i class="fa fa-arrow-left"></i></a>
        <h1>@lang('user.profile.edit_information')</h1>
    </div>

    <form action="{{url('profile')}}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}

        {{-- ── HERO ── --}}
        <div class="edit-hero">
            <div class="edit-avatar-wrap">
                @if(Auth::user()->picture)
                    <div class="edit-avatar" id="avatarPreview" style="background-image:url('{{ img(Auth::user()->picture) }}')"></div>
                @else
                    <div class="edit-avatar" id="avatarPreview">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}</div>
                @endif
                <div class="edit-avatar-upload">
                    <i class="fa fa-camera"></i>
                    <input type="file" name="picture" accept="image/x-png, image/jpeg" onchange="previewImage(this)">
                </div>
            </div>
            <div style="color: rgba(255,255,255,0.7); font-size: 12px; margin-top: 10px;">@lang('user.profile.profile_picture')</div>
        </div>

        {{-- ── FORM CARDS ── --}}
        <div class="edit-cards">
            @include('common.notify')

            <div class="edit-card">
                <div class="form-group">
                    <label>@lang('user.profile.first_name')</label>
                    <input type="text" class="form-control" name="first_name" required placeholder="@lang('user.profile.first_name')" value="{{Auth::user()->first_name}}">
                </div>
                
                <div class="form-group">
                    <label>@lang('user.profile.last_name')</label>
                    <input type="text" class="form-control" name="last_name" required placeholder="@lang('user.profile.last_name')" value="{{Auth::user()->last_name}}">
                </div>

                <div class="form-group">
                    <label>@lang('user.profile.email')</label>
                    <input type="email" class="form-control" placeholder="@lang('user.profile.email')" readonly value="{{Auth::user()->email}}">
                </div>

                <div class="form-group">
                    <label>@lang('user.profile.mobile')</label>
                    <input type="text" class="form-control" name="mobile" required placeholder="@lang('user.profile.mobile')" value="{{Auth::user()->mobile}}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>@lang('user.profile.language')</label>
                    <select class="form-control" name="language">
                        <option <?php if(Auth::user()->language=='fr')  { echo 'selected=selected'; } ?> value="fr">🇫🇷 Français</option>
                        <option <?php if(Auth::user()->language=='en')  { echo 'selected=selected'; } ?> value="en">🇬🇧 English</option>
                    </select>
                </div>
            </div>

            <div class="save-btn-container">
                <button type="submit" class="pm-save-btn">
                    <i class="fa fa-check"></i> @lang('user.profile.save')
                </button>
            </div>
        </div>
    </form>
</div>

@include('user.include.bottom_nav', ['active' => 'profil'])
@endsection

@section('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').style.backgroundImage = 'url(' + e.target.result + ')';
                document.getElementById('avatarPreview').innerHTML = ''; // Remove initials if present
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection