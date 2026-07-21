@extends('user.layout.app')

@section('content')
<style>
    .help-header {
        background: linear-gradient(180deg, #0a1628 0%, #152744 100%);
        padding: 100px 0 40px;
        text-align: center;
        border-bottom: 2px solid #C9A84C;
    }
    .help-header h1 {
        color: #fff;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 36px;
        margin-bottom: 15px;
    }
    .help-header p {
        color: rgba(201,168,76,0.9);
        font-size: 16px;
    }
    .help-content {
        padding: 60px 0;
        background: #F8FAFC;
    }
    .help-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .help-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    .help-card .icon-box {
        width: 60px;
        height: 60px;
        background: rgba(201,168,76,0.15);
        color: #C9A84C;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 20px;
    }
    .help-card h3 {
        color: #0a1628;
        font-size: 20px;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 15px;
    }
    .help-card p {
        color: #4a5568;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 0;
    }
    .contact-box {
        background: #0a1628;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        margin-top: 20px;
        border: 1px solid #C9A84C;
    }
    .contact-box h3 {
        color: #fff;
        font-size: 24px;
        margin-bottom: 15px;
        margin-top: 0;
    }
    .contact-box p {
        color: rgba(255,255,255,0.7);
        margin-bottom: 25px;
        font-size: 16px;
    }
    .contact-btn {
        display: inline-block;
        background: #C9A84C;
        color: #fff;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .contact-btn:hover {
        background: #B89535;
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
    }
</style>

<div class="help-header">
    <div class="container">
        <h1>Centre d'Aide PicMe225</h1>
        <p>Trouvez rapidement des réponses ou contactez notre équipe</p>
    </div>
</div>

<div class="help-content">
    <div class="container">
        <div class="row">
            <!-- Comment ça marche -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-car"></i></div>
                    <h3>Commander une course</h3>
                    <p>Ouvrez l'application, saisissez votre destination, et choisissez la catégorie de véhicule qui vous convient. Suivez l'arrivée de votre chauffeur en temps réel sur la carte.</p>
                </div>
            </div>
            <!-- Marketplace -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-shopping-cart"></i></div>
                    <h3>Marketplace</h3>
                    <p>Naviguez parmi les annonces (Véhicules, Immobilier, Services) proposées par notre communauté. Contactez directement les vendeurs via l'application ou le site web.</p>
                </div>
            </div>
            <!-- Sécurité -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-shield"></i></div>
                    <h3>Sécurité & Confiance</h3>
                    <p>Tous nos chauffeurs sont vérifiés (permis, assurance, identité). Utilisez le bouton SOS intégré dans l'application pour partager votre trajet avec un proche en cas de besoin.</p>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 20px;">
            <!-- Tarification -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-money"></i></div>
                    <h3>Tarifs & Paiements</h3>
                    <p>Le prix estimé s'affiche avant de valider votre commande. Vous pouvez payer en espèces directement au chauffeur, ou via Mobile Money de manière sécurisée.</p>
                </div>
            </div>
            <!-- Devenir Chauffeur -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-id-card"></i></div>
                    <h3>Devenir Chauffeur</h3>
                    <p>Téléchargez l'App Driver, soumettez vos documents (CNI, Permis de conduire, Carte Grise) et commencez à générer des revenus de façon flexible après validation de votre profil.</p>
                </div>
            </div>
            <!-- Objets Perdus -->
            <div class="col-md-4">
                <div class="help-card">
                    <div class="icon-box"><i class="fa fa-suitcase"></i></div>
                    <h3>Objets perdus</h3>
                    <p>Vous avez oublié un objet dans un véhicule ? Consultez l'historique de vos courses dans l'application pour contacter votre chauffeur, ou écrivez au support client.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="contact-box">
                    <h3>Besoin de plus d'aide ?</h3>
                    <p>Notre service client est disponible pour répondre à toutes vos préoccupations.</p>
                    <a href="mailto:support@picme225.site" class="contact-btn"><i class="fa fa-envelope" style="margin-right:8px;"></i> Nous écrire</a>
                    <a href="tel:+22500000000" class="contact-btn" style="background: transparent; border: 1px solid #C9A84C; margin-left: 10px;"><i class="fa fa-phone" style="margin-right:8px;"></i> Appeler</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection