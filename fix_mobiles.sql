-- Normaliser les numéros locaux ivoiriens (10 chiffres commençant par 0)
UPDATE users SET mobile = '+225' || mobile WHERE mobile ~ '^0[0-9]{9}$';
-- Normaliser les numéros sans indicatif (9 chiffres ou autres)
UPDATE users SET mobile = '+225' || mobile WHERE mobile ~ '^[0-9]{8,9}$';
-- Vérification
SELECT id, mobile FROM users LIMIT 10;
