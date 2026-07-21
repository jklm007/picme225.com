SELECT id, mobile, LEFT(password, 30) as pwd_hash FROM users LIMIT 5;
