<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web3 Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'interaction avec la blockchain Ethereum/Polygon
    |
    */

    'rpc_url' => env('WEB3_RPC_URL', 'https://polygon-rpc.com'),
    
    'dao_contract_address' => env('DAO_CONTRACT_ADDRESS'),
    'dao_contract_abi' => env('DAO_CONTRACT_ABI', '[]'),
    
    'eco_token_contract_address' => env('ECO_TOKEN_CONTRACT_ADDRESS'),
    'eco_token_contract_abi' => env('ECO_TOKEN_CONTRACT_ABI', '[]'),
    
    'minter_address' => env('MINTER_ADDRESS'),
    'minter_private_key' => env('MINTER_PRIVATE_KEY'),
    
    'system_wallet_address' => env('SYSTEM_WALLET_ADDRESS'),
    
    'private_key' => env('WEB3_PRIVATE_KEY'),
    
    'gas_limit' => env('WEB3_GAS_LIMIT', 300000),
    'gas_price' => env('WEB3_GAS_PRICE', 20000000000), // 20 Gwei
];

