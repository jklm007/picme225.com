<?php

namespace App\Services;

class ClientSmsParser
{
    /**
     * Tente de parser un SMS client pour extraire la commande de course.
     * Format attendu: COURSE [SERVICE] DE [DEPART] A [ARRIVEE]
     * 
     * @param string $message
     * @return array|null [ 'service' => string, 'origin' => string, 'destination' => string ]
     */
    public function parseCommand($message)
    {
        $message = trim($message);
        
        // Regex expliquée :
        // ^COURSE\s+        : Commence par "COURSE " (insensible à la casse)
        // ([a-zA-Z0-9]+)    : Le type de service (ex: TAXI, MOTO, VIP) (1 seul mot généralement)
        // \s+DE\s+          : " DE "
        // (.+?)             : L'adresse de départ (lazy match)
        // \s+A\s+           : " A " ou " À "
        // (.+)$             : L'adresse d'arrivée
        $pattern = '/^COURSE\s+([a-zA-Z0-9]+)\s+DE\s+(.+?)\s+(?:A|À)\s+(.+)$/i';
        
        if (preg_match($pattern, $message, $matches)) {
            return [
                'service' => strtoupper(trim($matches[1])),
                'origin' => trim($matches[2]),
                'destination' => trim($matches[3])
            ];
        }
        
        return null;
    }
}
