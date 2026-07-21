<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartAndFavoritesToUsers extends Migration
{
    /**
     * Ajouter cart_data et marketplace_favorites à la table users.
     * - cart_data          : JSON du panier (tableau d'articles + quantités)
     * - marketplace_favorites : JSON des IDs d'articles favoris
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cart_data')) {
                $table->json('cart_data')->nullable()->after('wallet_balance')
                    ->comment('Panier marketplace : JSON array [{listing_id, quantity, cart_item_id}]');
            }
            if (!Schema::hasColumn('users', 'marketplace_favorites')) {
                $table->json('marketplace_favorites')->nullable()->after('cart_data')
                    ->comment('Favoris marketplace : JSON array d\'IDs de listings');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cart_data', 'marketplace_favorites']);
        });
    }
}
