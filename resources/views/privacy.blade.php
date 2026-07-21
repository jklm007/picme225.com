@extends('user.layout.app')

@section('content')
<style>
    .legal-header {
        background: linear-gradient(180deg, #0a1628 0%, #152744 100%);
        padding: 100px 0 40px;
        text-align: center;
        border-bottom: 2px solid #C9A84C;
    }
    .legal-header h1 {
        color: #fff;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 36px;
        margin-bottom: 15px;
    }
    .legal-header p {
        color: rgba(201,168,76,0.9);
        font-size: 16px;
    }
    .legal-content {
        padding: 60px 0;
        background: #F8FAFC;
    }
    .legal-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 40px;
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .legal-card h2 {
        color: #0a1628;
        font-size: 24px;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(201,168,76,0.3);
    }
    .legal-card h3 {
        color: #152744;
        font-size: 18px;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 10px;
    }
    .legal-card p, .legal-card ul li {
        color: #4a5568;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 15px;
    }
    .legal-card ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    .legal-card ul li::marker {
        color: #C9A84C;
    }
    .legal-contact {
        background: rgba(201,168,76,0.1);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #C9A84C;
        margin-top: 30px;
    }
</style>

<div class="legal-header">
    <div class="container">
        <h1>Politique de Confidentialité</h1>
        <p>Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>
</div>

<div class="legal-content">
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="legal-card">
                    <h2>1. Cadre Légal et Conformité</h2>
                    <p>
                        Chez <strong>PicMe225</strong>, la protection de vos données personnelles est notre priorité. 
                        La présente Politique de Confidentialité est rédigée en conformité avec la <strong>Loi n°2013-450 du 19 juin 2013 relative à la protection des données à caractère personnel en Côte d'Ivoire</strong>, ainsi qu'en respect des principes commerciaux régis par l'espace <strong>OHADA</strong> (Organisation pour l'Harmonisation en Afrique du Droit des Affaires).
                    </p>
                    <p>
                        En utilisant l'application PicMe225 (en tant que Client ou Chauffeur) ou notre plateforme Marketplace, vous acceptez les pratiques décrites dans ce document, similaires aux standards des leaders de la mobilité (comme Yango ou inDrive), tout en garantissant un hébergement et un traitement conformes aux lois ivoiriennes.
                    </p>
                </div>

                <div class="legal-card">
                    <h2>2. Données Collectées</h2>
                    <p>Nous collectons les données strictement nécessaires au bon fonctionnement de nos services :</p>
                    <ul>
                        <li><strong>Données d'inscription :</strong> Nom, prénom, numéro de téléphone, adresse e-mail.</li>
                        <li><strong>Données de géolocalisation :</strong> Coordonnées GPS pour le suivi des courses, l'estimation des tarifs et la sécurité des utilisateurs. Pour les chauffeurs, la localisation est suivie en arrière-plan lorsque l'application est active.</li>
                        <li><strong>Données de transaction :</strong> Historique des courses, détails des commandes sur la Marketplace, informations de facturation (les données de cartes bancaires sont traitées par nos partenaires certifiés PCI-DSS).</li>
                        <li><strong>Données des chauffeurs (KYC) :</strong> Permis de conduire, pièce d'identité, documents du véhicule, assurance (obligations légales OHADA pour le transport).</li>
                        <li><strong>Appareil et Usage :</strong> Adresse IP, type d'appareil, version de l'application, rapports de crash.</li>
                    </ul>
                </div>

                <div class="legal-card">
                    <h2>3. Utilisation de vos Données</h2>
                    <p>Vos données sont utilisées de manière transparente et sécurisée pour :</p>
                    <ul>
                        <li>Faciliter la mise en relation entre passagers et chauffeurs (ou acheteurs et vendeurs sur la Marketplace).</li>
                        <li>Calculer les tarifs, estimer les temps d'arrivée et optimiser les trajets.</li>
                        <li>Assurer la sécurité des utilisateurs (vérification de l'identité des chauffeurs, bouton SOS).</li>
                        <li>Fournir une assistance client personnalisée en cas de litige.</li>
                        <li>Vous envoyer des reçus, des notifications de course et des offres promotionnelles (avec votre consentement).</li>
                        <li>Se conformer aux obligations légales, fiscales et comptables ivoiriennes.</li>
                    </ul>
                </div>

                <div class="legal-card">
                    <h2>4. Partage et Transmission des Données</h2>
                    <p>PicMe225 ne revend <strong>jamais</strong> vos données personnelles à des tiers à des fins publicitaires. Les partages sont strictement encadrés :</p>
                    <ul>
                        <li><strong>Entre Utilisateurs :</strong> Le Chauffeur voit votre prénom, votre note et votre point de prise en charge/destination. Le Passager voit le nom du Chauffeur, sa photo, la marque et l'immatriculation du véhicule.</li>
                        <li><strong>Autorités Compétentes :</strong> Sur réquisition judiciaire ou pour prévenir une fraude, conformément à la loi ivoirienne.</li>
                        <li><strong>Prestataires de Services :</strong> Plateformes de paiement (ex: Mobile Money, passerelles bancaires), services d'envoi de SMS, d'hébergement cloud, soumis à des clauses strictes de confidentialité.</li>
                    </ul>
                </div>

                <div class="legal-card">
                    <h2>5. Sécurité et Conservation</h2>
                    <p>
                        Nous utilisons des protocoles de chiffrement (SSL/TLS) pour sécuriser le transfert de vos données. Vos informations sont stockées sur des serveurs sécurisés. 
                    </p>
                    <p>
                        La durée de conservation est déterminée par les finalités du traitement et les obligations légales de conservation commerciale (loi comptable OHADA : 10 ans pour les transactions financières et factures). Les données de géolocalisation non anonymisées sont supprimées ou archivées de manière sécurisée après un délai raisonnable.
                    </p>
                </div>

                <div class="legal-card">
                    <h2>6. Vos Droits (Loi Ivoirienne)</h2>
                    <p>Conformément à la législation ARTCI (Autorité de Régulation des Télécommunications/TIC de Côte d'Ivoire), vous disposez des droits suivants :</p>
                    <ul>
                        <li><strong>Droit d'accès :</strong> Connaître les données que nous possédons sur vous.</li>
                        <li><strong>Droit de rectification :</strong> Corriger des informations inexactes depuis votre profil.</li>
                        <li><strong>Droit d'effacement (Droit à l'oubli) :</strong> Demander la suppression de votre compte et de vos données (sous réserve de nos obligations légales de conservation).</li>
                        <li><strong>Droit d'opposition :</strong> Refuser de recevoir des communications marketing.</li>
                    </ul>
                    
                    <div class="legal-contact">
                        <strong>Pour exercer vos droits :</strong><br>
                        Veuillez contacter notre Délégué à la Protection des Données (DPO) par e-mail à : 
                        <a href="mailto:privacy@picme225.site" style="color: #C9A84C; font-weight: 600;">privacy@picme225.site</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
