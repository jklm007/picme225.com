-- Recréer les users demo (mots de passe = 123456 hashé bcrypt)
INSERT INTO users (first_name, last_name, mobile, email, password, user_type, payment_mode, rating, social_rating, trust_score, created_at, updated_at) VALUES
('Appoets', 'Demo',     '+2250759747444', 'demo@demo.com',      '.3ilBGuu7DmmPNKDMNqhc6B.n7e3fCK3Bm5gXa', 'USER', 'CASH', 5.00, 5.00, 50, NOW(), NOW()),
('Emilia',  'Epps',     '+2250758286571', 'emilia@demo.com',    '.ys9H.gXA2cAKY8N7IYzj6JfI1CUZUyAGELxG', 'USER', 'CASH', 5.00, 5.00, 50, NOW(), NOW());

-- Recréer les OAuth clients Passport
INSERT INTO oauth_clients (id, user_id, name, secret, provider, redirect, personal_access_client, password_client, revoked, created_at, updated_at) VALUES
(1, NULL, 'PicMe Personal Access Client', 'personal_secret_picme225', NULL, 'http://localhost', true, false, false, NOW(), NOW()),
(3, NULL, 'PicMe User App', '3XunnpG2kTZPOHQA9aF9M49Q9jQWKcxCwz1W9oRJ', 'users', 'http://localhost', false, true, false, NOW(), NOW()),
(4, NULL, 'PicMe Driver App', 'YHrrmLiTzK51sFR2huce8MrzA9lhI0rsUMRxDW9L', 'providers', 'http://localhost', false, true, false, NOW(), NOW());

INSERT INTO oauth_personal_access_clients (id, client_id, created_at, updated_at) VALUES
(1, 1, NOW(), NOW());

SELECT setval('oauth_clients_id_seq', 4, true);
SELECT setval('oauth_personal_access_clients_id_seq', 1, true);

-- Vérification
SELECT id, mobile, email FROM users;
SELECT id, name, personal_access_client FROM oauth_clients;
