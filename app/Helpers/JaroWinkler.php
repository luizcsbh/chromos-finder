<?php

namespace App\Helpers;

class JaroWinkler
{
    public static function compare(string $string1, string $string2): float
    {
        $len1 = mb_strlen($string1);
        $len2 = mb_strlen($string2);

        if ($len1 == 0 || $len2 == 0) {
            return 0.0;
        }

        if ($string1 === $string2) {
            return 1.0;
        }

        $matchDistance = (int) floor(max($len1, $len2) / 2) - 1;

        $matches1 = array_fill(0, $len1, false);
        $matches2 = array_fill(0, $len2, false);

        $matches = 0;
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end = min($i + $matchDistance + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($matches2[$j]) {
                    continue;
                }
                if ($string1[$i] !== $string2[$j]) {
                    continue;
                }
                $matches1[$i] = true;
                $matches2[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches == 0) {
            return 0.0;
        }

        $transpositions = 0;
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$matches1[$i]) {
                continue;
            }
            while (!$matches2[$k]) {
                $k++;
            }
            if ($string1[$i] !== $string2[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $transpositions /= 2;

        $jaro = (($matches / $len1) + ($matches / $len2) + (($matches - $transpositions) / $matches)) / 3.0;

        // Winkler modification
        $prefix = 0;
        $maxPrefix = 4;
        for ($i = 0; $i < min($len1, $len2, $maxPrefix); $i++) {
            if ($string1[$i] === $string2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        $weight = 0.1;
        $jaroWinkler = $jaro + ($prefix * $weight * (1.0 - $jaro));

        return $jaroWinkler;
    }
}
