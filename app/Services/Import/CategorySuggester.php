<?php

namespace App\Services\Import;

class CategorySuggester
{
    protected array $rules = [
        'Adhesifs/Colles' => ['COLLE', 'AMIDON', 'ADHESIF'],
        'Rubans/Scellage' => ['SCOTCH', 'SCOTCHE', 'RUBAN'],
        'Films/Bandes' => ['CELOPHANE', 'FEUILLARD', 'FILM'],
        'Chimiques' => ['BORAX', 'ACIDE', 'SOLVANT', 'ALCOOL', 'SODA', 'SOUDE', 'ANALY'],
        'Encres/Colorants' => ['ENCRE', 'TEINTE', 'COLOR', 'PIGMENT'],
    ];

    public function suggest(string $description): string
    {
        $desc = strtoupper($this->stripAccents($description));

        foreach ($this->rules as $category => $tokens) {
            foreach ($tokens as $token) {
                if (str_contains($desc, $token)) {
                    return $category;
                }
            }
        }

        return 'Autres';
    }

    protected function stripAccents(string $text): string
    {
        return HeaderDetector::removeAccents($text);
    }
}
