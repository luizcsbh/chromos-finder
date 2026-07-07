<?php

namespace App\Services;

class NormalizacaoService
{
    /**
     * Normaliza um nome para facilitar a comparação entre registros.
     * A ideia é padronizar a entrada, removendo diferenças superficiais que
     * não alteram a identidade da pessoa, como acentos, pontuação e palavras comuns.
     */
    public function normalizar(string $nome): string
    {
        // Converte o nome para maiúsculas para garantir um padrão uniforme.
        $nome = mb_strtoupper($nome, 'UTF-8');

        // Remove acentos para tratar variações gráficas do mesmo nome.
        $nome = $this->removerAcentos($nome);
        
        // Substitui caracteres não alfabéticos por espaço, mantendo apenas letras e espaços.
        // Isso evita que pontuação ou símbolos interfiram na comparação.
        $nome = preg_replace('/[^A-Z\s]/', ' ', $nome);
        
        // Remove partículas comuns que normalmente não ajudam a identificar a pessoa.
        // Exemplo: "Maria da Silva" vira "MARIA SILVA".
        $stopWords = ['DE', 'DA', 'DO', 'DOS', 'DAS', 'E'];
        
        // Quebra o nome em tokens e remove espaços vazios e palavras indesejadas.
        $tokens = explode(' ', $nome);
        $tokens = array_filter($tokens, function($token) use ($stopWords) {
            return !in_array($token, $stopWords) && trim($token) !== '';
        });
        
        // Junta os tokens novamente em um formato limpo para comparação.
        return implode(' ', $tokens);
    }

    /**
     * Remove acentos de letras com marcas diacríticas.
     * Essa etapa é importante para que nomes com e sem acento sejam tratados como iguais.
     */
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
     * Separa o nome já normalizado em tokens para facilitar indexação e comparação.
     */
    public function tokenizar(string $nome): array
    {
        return explode(' ', $this->normalizar($nome));
    }

    /**
     * Retorna o primeiro nome a partir do texto já normalizado.
     */
    public function getPrimeiroNome(string $nomeNormalizado): string
    {
        $tokens = explode(' ', $nomeNormalizado);
        return $tokens[0] ?? '';
    }

    /**
     * Retorna o último sobrenome a partir do texto já normalizado.
     * Essa informação ajuda na comparação e na indexação de nomes.
     */
    public function getUltimoSobrenome(string $nomeNormalizado): string
    {
        $tokens = explode(' ', $nomeNormalizado);
        return count($tokens) > 1 ? end($tokens) : '';
    }
}
