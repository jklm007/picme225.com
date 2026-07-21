-- Désactiver FK
SET session_replication_role = 'replica';

-- Insérer le driver de test
INSERT INTO providers (first_name, last_name, mobile, email, password, login_by, status, available, commune, created_at, updated_at) VALUES
('Jean', 'VTC', '+2250101010101', 'driver.vtc@picme.com', '.WoQupoZLSz26cQz/NmaX0v0.tzDeNA5/rYm', 'manual', 'approved', true, 'Cocody', NOW(), NOW());

-- Assigner un service type existant (id = 1)
INSERT INTO provider_services (provider_id, service_type_id, status, service_number, service_model, created_at, updated_at) VALUES
((SELECT id FROM providers WHERE mobile = '+2250101010101' LIMIT 1), 1, 'active', 'ABC-123', 'Modele VTC', NOW(), NOW());

-- Réactiver FK
SET session_replication_role = 'origin';

-- Vérification
SELECT id, mobile, email, status FROM providers;
