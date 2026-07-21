<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * BlogController - Isolated Flat-file blog for SEO acquisition
 */
class BlogController extends Controller
{
    /**
     * Data storage for the static blog.
     * Hardcoding it here avoids DB queries, gives instant speed for Google,
     * and isolates the content from the core completely.
     */
    private function getPosts()
    {
        return [
            [
                'id' => 1,
                'slug' => 'prix-taxi-aeroport-abidjan',
                'title' => 'Quel est le prix réel d\'un taxi à l\'aéroport d\'Abidjan (FHB) en 2026 ?',
                'meta_description' => 'Découvrez les vrais tarifs des taxis et VTC à l\'aéroport Félix Houphouët-Boigny d\'Abidjan. Évitez les arnaques et voyagez en toute sécurité avec PicMe.',
                'meta_keywords' => 'prix taxi aeroport abidjan, tarif vtc fhb, arnaque taxi abidjan, transfert aeroport',
                'date' => '2026-04-10',
                'image' => asset('asset/img/airport-driver-bg.png'),
                'content' => '
                    <p>L\'arrivée à Abidjan par l\'Aéroport International Félix Houphouët-Boigny (FHB) est souvent synonyme de grande effervescence. Entre la chaleur tropicale et le monde, trouver un transport fiable et sécurisé est la priorité de nombreux voyageurs. Mais combien doit-on réellement payer pour un taxi ?</p>
                    
                    <h2>Les options de transport à la sortie de l\'Aéroport</h2>
                    <p>Historiquement, les compteurs rouges (taxis interurbains classiques) demandent d\'âpres négociations. Le tarif peut varier du simple au triple si vous n\'avez pas la "dégaine" d\'un local.</p>
                    <ul>
                        <li><strong>Taxi compteur classique :</strong> Environ 10.000 à 20.000 FCFA selon la destination et votre talent de négociateur. Toutefois, les bagages volumineux peuvent générer un "surcoût" imaginaire.</li>
                        <li><strong>Les applications classiques :</strong> Elles sont pratiques, mais le temps d\'attente, le manque de réseau à l\'arrivée, ou le refus de course par certains chauffeurs à cause des embouteillages d\'Abidjan Sud peuvent causer des frustrations.</li>
                    </ul>

                    <h2>L\'alternative Premium & Fixe : La réservation de transfert privé</h2>
                    <p>Pour un homme d\'affaires pressé ou une famille fatiguée par le vol, l\'option idéale reste le transfert privé réservé à l\'avance. Un service de VTC haut de gamme fixe le tarif <strong>dès 15.000 FCFA</strong>. Aucun frais caché.</p>
                    
                    <h3>Les avantages d\'un service premium comme PicMe Proc</h3>
                    <ol>
                        <li><strong>Tarif connu d\'avance :</strong> Entre 15 000 FCFA (Aéroport vers Hôtel) et 32 000 FCFA avec attente de vol garantie.</li>
                        <li><strong>Pancarte personnalisée :</strong> Votre chauffeur vous attend après la douane, prêt à prendre vos bagages.</li>
                        <li><strong>Véhicule climatisé et sûr :</strong> Indispensable pour s\'acclimater doucement à la chaleur ivoirienne tout en profitant du trajet dans des SUV ou berlines récents.</li>
                    </ol>

                    <p>En 2026, la sécurité et le confort n\'ont jamais été aussi abordables à Abidjan. Ne laissez aucune incertitude gâcher votre arrivée en terre d\'Eburnie !</p>
                '
            ],
            [
                'id' => 2,
                'slug' => 'louer-voiture-chauffeur-abidjan-guide',
                'title' => 'Guide Complet : Louer une voiture avec chauffeur à Abidjan',
                'meta_description' => 'Tout ce que vous devez savoir pour louer un SUV, une berline ou un 4x4 avec chauffeur professionnel pour vos déplacements pros ou mariages à Abidjan.',
                'meta_keywords' => 'location voiture avec chauffeur abidjan, louer suv cote divoire, transport mariage abidjan, vtc privé',
                'date' => '2026-04-05',
                'image' => asset('asset/img/banner-bg.jpg'),
                'content' => '
                    <p>Se déplacer à Abidjan peut être un véritable défi, surtout avec les embouteillages légendaires de la capitale économique ivoirienne. C’est pourquoi, que ce soit pour un séjour d\'affaires, des courses personnelles, ou une cérémonie prestigieuse (mariage, baptême), la location d\'un véhicule avec chauffeur est devenue la norme.</p>
                    
                    <h2>Pourquoi opter pour un chauffeur privé plutôt que de conduire soi-même ?</h2>
                    <p>Conduire en Côte d\'Ivoire nécessite une solide expérience des codes de la route locaux, qui sont assez particuliers. Les Woro-woro, les gbaka, et le trafic dense rendent le pilotage stressant.</p>
                    <p>Avoir un chauffeur professionnel, c\'est :</p>
                    <ul>
                        <li><strong>Zéro stress :</strong> Asseyez-vous à l\'arrière, répondez à vos emails, ou préparez vos réunions grâce au Wi-Fi souvent disponible dans les véhicules VIP.</li>
                        <li><strong>Gain de temps :</strong> Les professionnels connaissent les rues d\'Abidjan, les raccourcis et les zones à éviter selon l\'heure de la journée.</li>
                        <li><strong>Sécurité maximale :</strong> Vous ne vous souciez pas des altercations ou de la gestion du parking (souvent complexe au Plateau ou à Cocody).</li>
                    </ul>

                    <h2>Quels sont les tarifs moyens ?</h2>
                    <p>Chez des plateformes de confiance comme PicMe, la transparence est de mise.</p>
                    <p>Vous pouvez louer une berline confortable pour vos courses en ville à la journée, ou opter pour des SUV imposants (Prado, Range Rover) si vous voulez imposer un statut certain lors de rendez-vous B2B. Les locations journalières incluent généralement un certain forfait kilométrique ou horaire.</p>

                    <h2>Comment choisir la bonne agence ?</h2>
                    <p>Assurez-vous que l\'agence garantisse des véhicules <strong>récents</strong>, rigoureusement entretenus, et que les chauffeurs soient bilingues (si vous accueillez des expatriés). Les plateformes qui permettent la réservation directe par WhatsApp offrent aujourd\'hui la plus grande fluidité et réactivité.</p>
                '
            ]
        ];
    }

    public function index()
    {
        $posts = $this->getPosts();
        
        $data = [
            'posts' => $posts,
            'title' => 'Blog & Actualités Transport Abidjan | PicMe',
            'description' => 'Découvrez nos guides, astuces et actualités sur le transport privé, la location de véhicules et les transferts aéroport en Côte d\'Ivoire.',
        ];

        return view('marketing.blog.index', $data);
    }

    public function show($slug)
    {
        $posts = $this->getPosts();
        
        // Find post by slug
        $post = collect($posts)->firstWhere('slug', $slug);

        if (!$post) {
            abort(404);
        }

        $data = [
            'post' => $post,
            'title' => $post['title'],
            'description' => $post['meta_description'],
            // Add global WhatsApp link for CTA insertion
            'whatsapp_link' => 'https://wa.me/2250759747444?text=' . urlencode('Bonjour, je viens de lire votre article : ' . $post['title'] . '. Je souhaite avoir des informations sur vos services.'),
        ];

        return view('marketing.blog.show', $data);
    }
}
