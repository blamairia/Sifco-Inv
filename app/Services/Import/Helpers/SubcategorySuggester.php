<?php

namespace App\Services\Import\Helpers;

class SubcategorySuggester
{
    public static array $rules = [
        'Adhésifs/Colles' => ['COLLE', 'AMIDON', 'ADHESIF'],
        'Rubans/Scellage' => ['SCOTCH', 'SCOTCHE', 'RUBAN', 'SCELLAGE'],
        'Films/Bandes' => ['CELOPHANE', 'FEUILLARD', 'FILM', 'BANDE'],
        'Chimiques' => ['BORAX', 'ACIDE', 'SOLVANT', 'ALCOOL', 'SODA', 'SOUDE', 'ANALY'],
        'Encres/Colorants' => ['ENCRE', 'TEINTE', 'COLOR', 'PIGMENT'],
    ];

    public static function suggest(string $description): string
    {
        $d = strtoupper(trim($description));

        foreach (self::$rules as $category => $tokens) {
            foreach ($tokens as $token) {
                if (str_contains($d, $token)) {
                    return $category;
                }
            }
        }

        return 'Autres';
    }
}
