<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{


    public $timestamps = false; // Indique que les colonnes created_at et updated_at ne sont pas utilisées

    // Liste des attributs pouvant être remplis par masse
    protected $fillable = [
        'provider_search_radius',
        'tax_percentage',
        'surge_trigger',
        'surge_percentage',
        // ... autres paramètres ...
    ];

    // Définit les types de données des attributs
    protected $casts = [
        'provider_search_radius' => 'integer',
        'tax_percentage' => 'double',
        'surge_trigger' => 'integer',
        'surge_percentage' => 'double',
    ];
}

