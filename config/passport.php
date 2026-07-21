<?php

$privateKey = __DIR__ . '/../storage/oauth-private.key';
$publicKey = __DIR__ . '/../storage/oauth-public.key';

$privateKeyContent = env('PASSPORT_PRIVATE_KEY', file_exists($privateKey) ? file_get_contents($privateKey) : null);
$publicKeyContent = env('PASSPORT_PUBLIC_KEY', file_exists($publicKey) ? file_get_contents($publicKey) : null);

return [
    'private_key' => $privateKeyContent,
    'public_key' => $publicKeyContent,
    'client_uuids' => env('PASSPORT_CLIENT_UUIDS', false),
    'personal_access_client' => [
        'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
    ],
    'password_grant_client' => [
        'id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
        'secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
    ],
];
