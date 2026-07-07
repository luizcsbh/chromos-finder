<?php

namespace App\Services;

use App\Helpers\JaroWinkler;

class ComparacaoService
{
    /**
     * Calcula o score final de similaridade entre duas strings normalizadas.
     * Retorna um score de 0 a 100.
     */
    public function calcularScore(string $nome1Normalizado, string $nome2Normalizado, string $nome1Original = '', string $nome2Original = ''): float
    {
        // 1. Nome exatamente igual (Original)
        if ($nome1Original !== '' && $nome2Original !== '' && mb_strtoupper($nome1Original) === mb_strtoupper($nome2Original)) {
            return 100.0;
        }

        // 2. Nome normalizado igual
        if ($nome1Normalizado === $nome2Normalizado) {
            return 99.0;
        }

        $score = 0.0;

        // 3. Levenshtein* (Peso 40%)
        // Mede a distância de edição entre duas strings, contando quantas operações
        // de inserção, remoção ou substituição são necessárias para transformar uma na outra.
        // Quanto menor a distância, maior a similaridade entre os nomes.
        $maxLength = max(mb_strlen($nome1Normalizado), mb_strlen($nome2Normalizado));
        $levenshteinDist = levenshtein($nome1Normalizado, $nome2Normalizado);
        
        $levenshteinScore = 0.0;
        if ($levenshteinDist <= 2 && $maxLength > 0) {
            $levenshteinScore = ((max(0, $maxLength - $levenshteinDist)) / $maxLength) * 100;
        } elseif ($maxLength > 0) {
            $levenshteinScore = ((max(0, $maxLength - $levenshteinDist)) / $maxLength) * 100;
        }
        $score += ($levenshteinScore * 0.40);

        // 4. Similaridade - similar_text (Peso 30%)
        // Compara os caracteres em comum entre as duas strings e calcula a porcentagem
        // de similaridade com base na sobreposição de conteúdo.
        $simPercent = 0.0;
        similar_text($nome1Normalizado, $nome2Normalizado, $simPercent);
        $score += ($simPercent * 0.30);

        // 5. Jaro-Winkler* (Peso 20%)
        // Avalia a similaridade entre nomes considerando prefixos comuns e posições relativas
        // dos caracteres, sendo útil para detectar variações pequenas e trocas de letras.
        $jaroWinklerScore = JaroWinkler::compare($nome1Normalizado, $nome2Normalizado) * 100;
        $score += ($jaroWinklerScore * 0.20);

        // 6. Soundex (Peso 10%)
        // Gera um código fonético para representar o som do nome, ajudando a identificar
        // semelhanças mesmo quando a grafia muda. Como o Soundex nativo do PHP é focado no inglês,
        // aqui usamos o código para comparar a fonética de forma aproximada.
        $soundex1 = soundex($nome1Normalizado);
        $soundex2 = soundex($nome2Normalizado);
        $soundexScore = ($soundex1 === $soundex2) ? 100.0 : 0.0;
        // Se houver coincidência parcial
        if ($soundexScore === 0.0 && substr($soundex1, 0, 3) === substr($soundex2, 0, 3)) {
            $soundexScore = 50.0;
        }
        $score += ($soundexScore * 0.10);

        return min(98.9, round($score, 2));
    }
}
