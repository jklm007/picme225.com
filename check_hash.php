<?php
// Générer le bon hash pour 123456
echo password_hash('123456', PASSWORD_BCRYPT) . PHP_EOL;
// Vérifier le hash actuel
echo password_verify('123456', '.3ilBGuu7DmmPNKDMNqhc6B.n7e3fCK3Bm5gXa') ? 'MATCH' : 'FAIL';
