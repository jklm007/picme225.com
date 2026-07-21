<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Normalizes a phone number to standard format with country code.
     * Automatically handles missing + and defaults to +225 for local numbers.
     *
     * @param string $mobile
     * @return string
     */
    public static function normalize($mobile)
    {
        if (empty($mobile)) {
            return $mobile;
        }

        // 1. Nettoyer le numéro (enlever espaces, tirets, parenthèses)
        $mobile = preg_replace('/[^0-9+]/', '', $mobile);

        // 2. Remplacer '00' au début par '+'
        if (strpos($mobile, '00') === 0) {
            $mobile = '+' . substr($mobile, 2);
        }

        // 3. S'il commence déjà par '+', on considère qu'il est déjà au bon format
        if (strpos($mobile, '+') === 0) {
            return $mobile;
        }

        // 4. Liste des indicatifs internationaux courants (sans le +)
        $knownCodes = [
            // Afrique
            '221', '222', '223', '224', '225', '226', '227', '228', '229', 
            '234', '237', '241', '242', '243', '244', '250', '254', '255', '256',
            '20', '27', '212', '213', '216', '218',
            // Europe
            '31', '32', '33', '34', '39', '41', '43', '44', '45', '46', '47', '48', '49',
            // Amériques
            '1', '52', '54', '55', '56', '57', '58',
            // Asie / Moyen-Orient / Océanie
            '61', '64', '81', '82', '86', '91', '92', '93', '98', '971', '966'
        ];

        // Trier par longueur décroissante pour éviter qu'un préfixe court ne matche avant un long
        // ex: 225 matche avant 22
        usort($knownCodes, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($knownCodes as $code) {
            // Si le numéro commence par l'un de ces indicatifs et a une longueur raisonnable
            if (strpos($mobile, $code) === 0 && strlen($mobile) >= 10) {
                return '+' . $mobile;
            }
        }

        // 4. Si c'est un numéro local ivoirien (10 chiffres commençant par 0)
        // ex: 0759747444 -> +2250759747444
        if (strlen($mobile) === 10 && strpos($mobile, '0') === 0) {
            return '+225' . $mobile;
        }

        // 5. Autre cas de figure (ex: 8 chiffres sans le 0), on force le +225
        return '+225' . $mobile;
    }
}
