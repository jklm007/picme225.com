@extends('user.layout.base')

@section('title', 'Notifications')

@section('styles')
<style>
.pm-notif-container {
    padding: 20px 16px;
    padding-top: calc(var(--header-h) + 20px);
    padding-bottom: calc(var(--nav-h) + 20px);
    min-height: 100vh;
    background: #f8fafc;
}

.pm-notif-header {
    font-size: 22px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 20px;
}

.pm-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.pm-empty-state i {
    font-size: 48px;
    color: var(--gray-300);
    margin-bottom: 16px;
}

.pm-empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 8px;
}

.pm-empty-state p {
    font-size: 14px;
    color: var(--gray-500);
}
</style>
@endsection

@section('content')
<div class="pm-notif-container">
    <div class="pm-notif-header">Notifications</div>

    <div class="pm-empty-state">
        <i class="fa fa-bell-slash-o"></i>
        <h3>Aucune notification</h3>
        <p>Vous n'avez pas de nouvelles notifications pour le moment.</p>
    </div>
</div>
@endsection
