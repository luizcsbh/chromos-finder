<?php

namespace App\Services;

class NormalizacaoService
{
    /**
     * Remove acentos, pontuações, transforma em maiúsculo, e remove stop words e duplos espaços.
     */
    public function normalizar(string $nome): string
    {
        $nome = mb_strtoupper($nome, 'UTF-8');
        $nome = $this->removerAcentos($nome);
        
        // Substituir caracteres não-alfabéticos por espaço (exceto espaços)
        $nome = preg_replace('/[^A-Z\s]/', ' ', $nome);
        
        // Partículas comuns a remover
        $stopWords = ['DE', 'DA', 'DO', 'DOS', 'DAS', 'E'];
        
        $tokens = explode(' ', $nome);
        $tokens = array_filter($tokens, function($token) use ($stopWords) {
            return !in_array($token, $stopWords) && trim($token) !== '';
        });
        
        return implode(' ', $tokens);
    }

    private function removerAcentos(string $string): string
    {
        $map = [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        return strtr($string, $map);
    }

    /**
     * Retorna tokens limpos.
     */
    public function tokenizar(string $nome): array
    {
        return explode(' ', $this->normalizar($nome));
    }

    public function getPrimeiroNome(string $nomeNormalizado): string
    {
        $tokens = explode(' ', $nomeNormalizado);
        return $tokens[0] ?? '';
    }

    public function getUltimoSobrenome(string $nomeNormalizado): string
    {
        $tokens = explode(' ', $nomeNormalizado);
        return count($tokens) > 1 ? end($tokens) : '';
    }
}
