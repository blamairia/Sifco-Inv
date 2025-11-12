<?php

namespace App\Services\Import;

class HeaderDetector
{
    protected array $canonical = [
        'DESCRIPTION' => ['description', 'descr', 'libelle', 'article', 'designation'],
        'STOCK_INT' => ['stock_int', 'stock_ini', 'stock_initial', 'stock ouverture', 'opening_stock'],
    'RECEPTION' => ['reception', 'received', 'in', 'arrivee', 'entree', 'entrees', 'entrée', 'entrees'],
        'UNITE' => ['unite', 'unit', 'uom', 'u'],
    'SORTIE' => ['sortie', 'issue', 'out', 'consommation', 'sortier', 'sortie '],
        'PRIX' => ['prix', 'price', 'unit_price', 'price_ttc'],
        'VALEUR' => ['valeur', 'value', 'line_value'],
        'STOCK' => ['stock', 'closing_stock', 'stock_fin'],
    ];

    public function detect(array $rows, int $scanRows = 15): ?array
    {
        $limit = min(count($rows), $scanRows);
        for ($i = 0; $i < $limit; $i++) {
            $row = $rows[$i];
            if (!is_array($row)) {
                continue;
            }
            $normalized = $this->normalizeHeaderRow($row);
            $mapped = $this->mapHeaders($normalized);
            // require at least 3 canonical fields to be confident it's a header row
            $commonFields = ['DESCRIPTION','STOCK_INT','RECEPTION','UNITE','SORTIE','PRIX','VALEUR','STOCK'];
            $foundCount = 0;
            foreach ($commonFields as $k) {
                if (isset($mapped[$k])) $foundCount++;
            }
            if ($foundCount >= 3) {
                return ['rowIndex' => $i, 'mapping' => $mapped];
            }

            // If header is split across two rows, combine row i and i+1
            if ($i + 1 < count($rows) && is_array($rows[$i+1])) {
                $combined = [];
                $r1 = $this->normalizeHeaderRow($row);
                $r2 = $this->normalizeHeaderRow($rows[$i+1]);
                $max = max(count($r1), count($r2));
                for ($j=0; $j < $max; $j++) {
                    $combined[] = $r1[$j] ?? $r2[$j] ?? '';
                }
                $mapped2 = $this->mapHeaders($combined);
                $foundCount2 = 0;
                foreach ($commonFields as $k) {
                    if (isset($mapped2[$k])) $foundCount2++;
                }
                if ($foundCount2 >= 3) {
                    return ['rowIndex' => $i, 'mapping' => $mapped2];
                }
            }
        }

        return null;
    }

    protected function normalizeHeaderRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $cell) {
            $txt = (string) $cell;
            $txt = $this->stripAccents($txt);
            $txt = strtoupper(trim($txt));
            $txt = preg_replace('/\s+/', '_', $txt);
            $normalized[] = $txt;
        }

        return $normalized;
    }

    protected function mapHeaders(array $headers): array
    {
        $mapping = [];
        foreach ($headers as $idx => $h) {
            foreach ($this->canonical as $key => $variants) {
                $variantsClean = array_map(function ($v) { return strtoupper($this->stripAccents($v)); }, $variants);
                if (in_array($h, $variantsClean, true) || $h === $key) {
                    $mapping[$key] = $idx;
                    break;
                }
            }
        }

        return $mapping;
    }

    public static function removeAccents(string $text): string
    {
        $detector = new self();
        return $detector->stripAccents($text);
    }

    protected function stripAccents(string $text): string
    {
        $trans = array(
            'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Æ'=>'AE',
            'Ç'=>'C','È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I',
            'Ð'=>'D','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O',
            'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','ß'=>'ss','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'ae',
            'ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ð'=>'d','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o',
            'õ'=>'o','ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y'
        );

        return strtr($text, $trans);
    }
}
