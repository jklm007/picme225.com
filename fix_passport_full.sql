-- Supprimer les clients existants pour repartir propre
DELETE FROM oauth_clients;
DELETE FROM oauth_personal_access_clients;

-- 1. Client Personal Access (obligatoire pour createToken())
INSERT INTO oauth_clients (id, user_id, name, secret, provider, redirect, personal_access_client, password_client, revoked, created_at, updated_at)
VALUES (1, NULL, 'PicMe Personal Access Client', 'personal_secret_picme225', NULL, 'http://localhost', true, false, false, NOW(), NOW());

-- 2. Personal Access Client link
INSERT INTO oauth_personal_access_clients (id, client_id, created_at, updated_at)
VALUES (1, 1, NOW(), NOW());

-- 3. Password Client pour l'app User
INSERT INTO oauth_clients (id, user_id, name, secret, provider, redirect, personal_access_client, password_client, revoked, created_at, updated_at)
VALUES (3, NULL, 'PicMe User App', '3XunnpG2kTZPOHQA9aF9M49Q9jQWKcxCwz1W9oRJ', 'users', 'http://localhost', false, true, false, NOW(), NOW());

-- 4. Password Client pour l'app Driver
INSERT INTO oauth_clients (id, user_id, name, secret, provider, redirect, personal_access_client, password_client, revoked, created_at, updated_at)
VALUES (4, NULL, 'PicMe Driver App', 'YHrrmLiTzK51sFR2huce8MrzA9lhI0rsUMRxDW9L', 'providers', 'http://localhost', false, true, false, NOW(), NOW());

-- Reset sequence
SELECT setval('oauth_clients_id_seq', 4, true);
SELECT setval('oauth_personal_access_clients_id_seq', 1, true);

-- Verification
SELECT id, name, personal_access_client, password_client FROM oauth_clients;
