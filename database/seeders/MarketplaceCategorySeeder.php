<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketplaceCategory;

class MarketplaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Immobilier
        $immo = MarketplaceCategory::updateOrCreate(['name' => 'REAL_ESTATE'], [
            'label' => '🏠 Immobilier',
            'icon' => 'home',
            'order_index' => 1
        ]);
        $this->createSubs($immo, [
            'Vente maison', 'Location maison', 'Terrain', 'Bureau/commercial', 'Colocation', 'Location courte durée'
        ]);

        // 2. Véhicules
        $vehic = MarketplaceCategory::updateOrCreate(['name' => 'VEHICLES'], [
            'label' => '🚗 Véhicules',
            'icon' => 'directions_car',
            'order_index' => 2
        ]);
        $this->createSubs($vehic, [
            'Voiture', 'Moto', 'Camion', 'Pièces détachées', 'Location véhicule'
        ]);

        // 3. Vente
        $vente = MarketplaceCategory::updateOrCreate(['name' => 'SALE'], [
            'label' => '🛒 Vente',
            'icon' => 'shopping_bag',
            'order_index' => 3
        ]);
        $this->createSubs($vente, [
            'Maison', 'Beauté', 'Téléphone', 'Meuble', 'Sport', 'Jouets', 'Autres'
        ]);

        // 4. Convoi
        $convoi = MarketplaceCategory::updateOrCreate(['name' => 'CONVOY'], [
            'label' => '🚚 Convoi / Transport',
            'icon' => 'local_shipping',
            'order_index' => 4
        ]);
        $this->createSubs($convoi, [
            'Envoi colis', 'Déménagement', 'Transport marchandises', 'Livraison express'
        ]);

        // 5. Billets
        $billets = MarketplaceCategory::updateOrCreate(['name' => 'TICKETS'], [
            'label' => '🎫 Billets',
            'icon' => 'confirmation_number',
            'order_index' => 5
        ]);
        $this->createSubs($billets, [
            'Concert', 'Voyage', 'Match sportif', 'Festival', 'Cinéma', 'Conférence', 'Spectacle', 'Événement privé'
        ]);

        // 6. Électronique
        $elec = MarketplaceCategory::updateOrCreate(['name' => 'ELECTRONICS'], [
            'label' => '📱 Électronique',
            'icon' => 'devices',
            'order_index' => 6
        ]);
        $this->createSubs($elec, [
            'Téléphones', 'PC', 'TV', 'Gaming', 'Accessoires'
        ]);

        // 7. Mode
        $mode = MarketplaceCategory::updateOrCreate(['name' => 'FASHION'], [
            'label' => '👗 Mode',
            'icon' => 'checkroom',
            'order_index' => 7
        ]);
        $this->createSubs($mode, [
            'Homme', 'Femme', 'Enfant', 'Luxe', 'Chaussures', 'Accessoires'
        ]);

        // 8. Alimentaire
        $food = MarketplaceCategory::updateOrCreate(['name' => 'FOOD'], [
            'label' => '🍽 Alimentaire',
            'icon' => 'restaurant',
            'order_index' => 8
        ]);
        $this->createSubs($food, [
            'Restaurant', 'Produits frais', 'Gâteaux', 'Boissons', 'Traiteur'
        ]);

        // 9. Services
        $serv = MarketplaceCategory::updateOrCreate(['name' => 'SERVICES'], [
            'label' => '🔧 Services',
            'icon' => 'build',
            'order_index' => 9
        ]);
        $this->createSubs($serv, [
            'Réparation', 'Développeur', 'Coiffure', 'Ménage', 'Construction', 'Design', 'Transport', 'Formation'
        ]);
    }

    private function createSubs($parent, $subs)
    {
        foreach ($subs as $index => $sub) {
            // Normaliser le nom sans accents pour éviter les mismatch
            $normalized = $this->removeAccents($sub);
            MarketplaceCategory::updateOrCreate([
                'name' => $parent->name . '_' . strtoupper(str_replace([' ', '/'], '_', $normalized))
            ], [
                'parent_id' => $parent->id,
                'label' => $sub,
                'order_index' => $index
            ]);
        }
    }

    /**
     * Retire tous les accents d'une chaîne UTF-8.
     */
    private function removeAccents(string $str): string
    {
        $map = [
            'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A',
            'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a',
            'È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E',
            'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
            'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I',
            'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
            'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O',
            'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
            'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
            'Ý'=>'Y','ý'=>'y','ÿ'=>'y',
            'Ñ'=>'N','ñ'=>'n',
            'Ç'=>'C','ç'=>'c',
        ];
        return strtr($str, $map);
    }
}
