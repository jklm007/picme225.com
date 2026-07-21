-- Désactiver les FK pour PostgreSQL
SET session_replication_role = 'replica';

-- Vider les tables liées
TRUNCATE TABLE service_service_type CASCADE;
TRUNCATE TABLE km_hour_service_type_prices CASCADE;
TRUNCATE TABLE service_type_rentals CASCADE;
TRUNCATE TABLE km_hours CASCADE;
TRUNCATE TABLE services CASCADE;

-- Insérer les catégories de services (rides)
INSERT INTO services (name, image, created_at, updated_at) VALUES
('Taxi',      'service/standard.jpg',       NOW(), NOW()),
('Livraison', 'service/delivery_main.png',  NOW(), NOW()),
('Location',  'service/rental.jpg',         NOW(), NOW()),
('Voyage',    'service/outstation.jpg',     NOW(), NOW()),
('Urgence',   'service/ambulance.jpg',      NOW(), NOW()),
('Partage',   'service/shared_ride.jpg',    NOW(), NOW());

-- Insérer les forfaits km/heure
INSERT INTO km_hours (hour, kilometer, created_at, updated_at) VALUES
(1,  20,  NOW(), NOW()),
(2,  30,  NOW(), NOW()),
(4,  45,  NOW(), NOW()),
(8,  80,  NOW(), NOW()),
(12, 150, NOW(), NOW()),
(24, 300, NOW(), NOW());

-- Réactiver les FK
SET session_replication_role = 'origin';

-- Vérification
SELECT id, name FROM services;
SELECT id, hour, kilometer FROM km_hours;
