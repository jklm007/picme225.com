-- Recréer les users demo avec tous les champs obligatoires
INSERT INTO users (first_name, last_name, mobile, email, password, user_type, payment_mode, rating, social_rating, trust_score, device_type, device_id, device_token, login_by, gender, language, otp, created_at, updated_at) VALUES
('Appoets', 'Demo',  '+2250759747444', 'demo@demo.com',   '.3ilBGuu7DmmPNKDMNqhc6B.n7e3fCK3Bm5gXa', 'USER', 'CASH', 5.00, 5.00, 50, 'android', 'demo_device_1', 'demo_token_1', 'manual', 'MALE', 'en', 0, NOW(), NOW()),
('Emilia',  'Epps',  '+2250758286571', 'emilia@demo.com', '.ys9H.gXA2cAKY8N7IYzj6JfI1CUZUyAGELxG', 'USER', 'CASH', 5.00, 5.00, 50, 'android', 'demo_device_2', 'demo_token_2', 'manual', 'FEMALE', 'en', 0, NOW(), NOW());

-- Vérification
SELECT id, mobile, email, first_name FROM users;
