-- Fix labels: retire les préfixes dans les sous-catégories
-- Exemple: "SALE_MAISON" -> label "Maison", "REAL_ESTATE_VENTE_MAISON" -> label "Vente maison"

-- Sous-catégories de SALE
UPDATE marketplace_categories SET label = 'Maison'      WHERE name = 'SALE_MAISON';
UPDATE marketplace_categories SET label = 'Beauté'      WHERE name = 'SALE_BEAUTE';
UPDATE marketplace_categories SET label = 'Téléphone'   WHERE name = 'SALE_TELEPHONE';
UPDATE marketplace_categories SET label = 'Meuble'      WHERE name = 'SALE_MEUBLE';
UPDATE marketplace_categories SET label = 'Sport'       WHERE name = 'SALE_SPORT';
UPDATE marketplace_categories SET label = 'Jouets'      WHERE name = 'SALE_JOUETS';
UPDATE marketplace_categories SET label = 'Autres'      WHERE name = 'SALE_AUTRES';

-- Sous-catégories de REAL_ESTATE
UPDATE marketplace_categories SET label = 'Vente maison'        WHERE name = 'REAL_ESTATE_VENTE_MAISON';
UPDATE marketplace_categories SET label = 'Location maison'     WHERE name = 'REAL_ESTATE_LOCATION_MAISON';
UPDATE marketplace_categories SET label = 'Terrain'             WHERE name = 'REAL_ESTATE_TERRAIN';
UPDATE marketplace_categories SET label = 'Bureau / Commercial' WHERE name = 'REAL_ESTATE_BUREAU_COMMERCIAL';
UPDATE marketplace_categories SET label = 'Colocation'          WHERE name = 'REAL_ESTATE_COLOCATION';
UPDATE marketplace_categories SET label = 'Location courte durée' WHERE name = 'REAL_ESTATE_LOCATION_COURTE_DUREE';

-- Sous-catégories de VEHICLES
UPDATE marketplace_categories SET label = 'Voiture'         WHERE name = 'VEHICLES_VOITURE';
UPDATE marketplace_categories SET label = 'Moto'            WHERE name = 'VEHICLES_MOTO';
UPDATE marketplace_categories SET label = 'Camion'          WHERE name = 'VEHICLES_CAMION';
UPDATE marketplace_categories SET label = 'Pièces détachées' WHERE name = 'VEHICLES_PIECES_DETACHEES';
UPDATE marketplace_categories SET label = 'Location véhicule' WHERE name = 'VEHICLES_LOCATION';

-- Sous-catégories de CONVOY
UPDATE marketplace_categories SET label = 'Envoi colis'          WHERE name = 'CONVOY_ENVOI_COLIS';
UPDATE marketplace_categories SET label = 'Déménagement'         WHERE name = 'CONVOY_DEMENAGEMENT';
UPDATE marketplace_categories SET label = 'Transport marchandises' WHERE name = 'CONVOY_TRANSPORT_MARCHANDISES';
UPDATE marketplace_categories SET label = 'Livraison express'    WHERE name = 'CONVOY_LIVRAISON_EXPRESS';

-- Sous-catégories de TICKETS
UPDATE marketplace_categories SET label = 'Concert'         WHERE name = 'TICKETS_CONCERT';
UPDATE marketplace_categories SET label = 'Voyage'          WHERE name = 'TICKETS_VOYAGE';
UPDATE marketplace_categories SET label = 'Match sportif'   WHERE name = 'TICKETS_MATCH_SPORTIF';
UPDATE marketplace_categories SET label = 'Festival'        WHERE name = 'TICKETS_FESTIVAL';
UPDATE marketplace_categories SET label = 'Cinéma'          WHERE name = 'TICKETS_CINEMA';
UPDATE marketplace_categories SET label = 'Conférence'      WHERE name = 'TICKETS_CONFERENCE';
UPDATE marketplace_categories SET label = 'Spectacle'       WHERE name = 'TICKETS_SPECTACLE';
UPDATE marketplace_categories SET label = 'Événement privé' WHERE name = 'TICKETS_EVENEMENT_PRIVE';

-- Sous-catégories de ELECTRONICS
UPDATE marketplace_categories SET label = 'Téléphones'   WHERE name = 'ELECTRONICS_TELEPHONES';
UPDATE marketplace_categories SET label = 'PC'           WHERE name = 'ELECTRONICS_PC';
UPDATE marketplace_categories SET label = 'TV'           WHERE name = 'ELECTRONICS_TV';
UPDATE marketplace_categories SET label = 'Gaming'       WHERE name = 'ELECTRONICS_GAMING';
UPDATE marketplace_categories SET label = 'Accessoires'  WHERE name = 'ELECTRONICS_ACCESSOIRES';

-- Sous-catégories de FASHION
UPDATE marketplace_categories SET label = 'Homme'       WHERE name = 'FASHION_HOMME';
UPDATE marketplace_categories SET label = 'Femme'       WHERE name = 'FASHION_FEMME';
UPDATE marketplace_categories SET label = 'Enfant'      WHERE name = 'FASHION_ENFANT';
UPDATE marketplace_categories SET label = 'Chaussures'  WHERE name = 'FASHION_CHAUSSURES';
UPDATE marketplace_categories SET label = 'Accessoires' WHERE name = 'FASHION_ACCESSOIRES';

-- Sous-catégories de FOOD
UPDATE marketplace_categories SET label = 'Restaurant'    WHERE name = 'FOOD_RESTAURANT';
UPDATE marketplace_categories SET label = 'Produits frais' WHERE name = 'FOOD_PRODUITS_FRAIS';
UPDATE marketplace_categories SET label = 'Gâteaux'       WHERE name = 'FOOD_GATEAUX';
UPDATE marketplace_categories SET label = 'Boissons'      WHERE name = 'FOOD_BOISSONS';
UPDATE marketplace_categories SET label = 'Traiteur'      WHERE name = 'FOOD_TRAITEUR';

-- Sous-catégories de SERVICES
UPDATE marketplace_categories SET label = 'Réparation'  WHERE name = 'SERVICES_REPARATION';
UPDATE marketplace_categories SET label = 'Développeur' WHERE name = 'SERVICES_DEVELOPPEUR';
UPDATE marketplace_categories SET label = 'Coiffure'    WHERE name = 'SERVICES_COIFFURE';
UPDATE marketplace_categories SET label = 'Ménage'      WHERE name = 'SERVICES_MENAGE';
UPDATE marketplace_categories SET label = 'Construction' WHERE name = 'SERVICES_CONSTRUCTION';
UPDATE marketplace_categories SET label = 'Design'      WHERE name = 'SERVICES_DESIGN';
UPDATE marketplace_categories SET label = 'Transport'   WHERE name = 'SERVICES_TRANSPORT';
UPDATE marketplace_categories SET label = 'Formation'   WHERE name = 'SERVICES_FORMATION';

-- Fallback générique: retire tout ce qui ressemble à PREFIX_ dans le label si pas déjà fait
-- (cleanup pour tous les labels qui contiendraient encore un _ )
UPDATE marketplace_categories
SET label = REGEXP_REPLACE(
    INITCAP(LOWER(REPLACE(
        CASE
            WHEN name LIKE '%\_%' THEN SUBSTRING(name FROM POSITION('_' IN name) + 1)
            ELSE name
        END,
        '_', ' '
    ))),
    '\s+', ' ', 'g'
)
WHERE label = name OR label LIKE '%\_%';
