@extends('provider.layout.app')

@section('title', 'Assistance - ')

@section('styles')
<style>
    .pro-support {
        padding: 20px;
        color: var(--navy);
        background: #f8fafc;
        min-height: 100vh;
        padding-bottom: 100px;
    }
    .support-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        border: 1px solid #f1f5f9;
    }
    .support-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--navy);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .support-title i {
        color: var(--gold);
    }
    .support-channel {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    .support-channel:hover {
        transform: translateY(-2px);
        border-color: var(--gold);
        background: #fffdf6;
    }
    .channel-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
    }
    .channel-wa { background: #25d366; }
    .channel-phone { background: var(--navy); }
    .channel-email { background: var(--gold); }
    
    .channel-info {
        flex: 1;
    }
    .channel-name {
        font-weight: 700;
        font-size: 14px;
        color: var(--navy);
    }
    .channel-desc {
        font-size: 12px;
        color: #64748b;
    }
    
    .faq-item {
        border-bottom: 1px solid #f1f5f9;
        padding: 12px 0;
    }
    .faq-item:last-child {
        border-bottom: none;
    }
    .faq-q {
        font-weight: 700;
        font-size: 14px;
        color: var(--navy);
        margin-bottom: 4px;
    }
    .faq-a {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }
</style>
@endsection

@section('content')
<div class="pro-support">
    <div class="support-card" style="margin-top: 50px;">
        <div class="support-title">
            <i class="fa fa-headphones"></i> Centre d'Assistance Chauffeur
        </div>
        <p style="font-size: 14px; color: #475569; margin-bottom: 20px;">
            Notre équipe de support client est disponible 24h/24 et 7j/7 pour répondre à vos questions et vous aider pendant vos courses.
        </p>
        
        <a href="https://wa.me/2250700000000" class="support-channel" target="_blank">
            <div class="channel-icon channel-wa">
                <i class="fa fa-whatsapp"></i>
            </div>
            <div class="channel-info">
                <div class="channel-name">WhatsApp Support</div>
                <div class="channel-desc">Assistance rapide par message écrit ou vocal</div>
            </div>
            <i class="fa fa-chevron-right" style="color: #cbd5e1;"></i>
        </a>

        <a href="tel:+2250700000000" class="support-channel">
            <div class="channel-icon channel-phone">
                <i class="fa fa-phone"></i>
            </div>
            <div class="channel-info">
                <div class="channel-name">Assistance Téléphonique</div>
                <div class="channel-desc">Appelez-nous directement en cas d'urgence</div>
            </div>
            <i class="fa fa-chevron-right" style="color: #cbd5e1;"></i>
        </a>

        <a href="mailto:support@picme225.com" class="support-channel">
            <div class="channel-icon channel-email">
                <i class="fa fa-envelope"></i>
            </div>
            <div class="channel-info">
                <div class="channel-name">Support par E-mail</div>
                <div class="channel-desc font-weight-bold">support@picme225.com</div>
            </div>
            <i class="fa fa-chevron-right" style="color: #cbd5e1;"></i>
        </a>
    </div>

    <div class="support-card">
        <div class="support-title">
            <i class="fa fa-question-circle"></i> Questions Fréquentes
        </div>
        
        <div class="faq-item">
            <div class="faq-q">Comment activer ou désactiver ma disponibilité ?</div>
            <div class="faq-a">Utilisez le bouton de statut sur la page d'accueil pour passer en ligne ou hors ligne.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Mes revenus ne s'affichent pas instantanément ?</div>
            <div class="faq-a">Les revenus sont calculés dès qu'une course est finalisée par vous et confirmée.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Problème avec le GPS ou l'itinéraire ?</div>
            <div class="faq-a">Veuillez autoriser l'accès permanent à la localisation sur votre terminal mobile.</div>
        </div>
    </div>
</div>
@endsection
