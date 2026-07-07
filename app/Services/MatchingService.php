<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Aprovado;
use App\Models\StudentMatch;

class MatchingService
{
    protected ComparacaoService $comparacaoService;

    public function __construct(ComparacaoService $comparacaoService)
    {
        $this->comparacaoService = $comparacaoService;
    }

    public function processarAprovado(Aprovado $aprovado)
    {
        // Estratégia: buscar candidatos que tenham o mesmo primeiro nome OU o mesmo último sobrenome
        // para não comparar com a base inteira.
        $candidatos = Aluno::where('hash_primeiro_nome', $aprovado->hash_primeiro_nome)
            ->orWhere('hash_ultimo_sobrenome', $aprovado->hash_ultimo_sobrenome)
            ->get();

        $melhorScore = 0;
        $melhorCandidato = null;

        foreach ($candidatos as $aluno) {
            $score = $this->comparacaoService->calcularScore(
                $aprovado->nome_normalizado,
                $aluno->nome_normalizado,
                $aprovado->nome,
                $aluno->nome_completo
            );

            if ($score > $melhorScore) {
                $melhorScore = $score;
                $melhorCandidato = $aluno;
            }
        }

        if ($melhorScore >= 99) {
            $status = 'MATCH_EXATO';
            $confianca = 'ALTA';
        } elseif ($melhorScore >= 95) {
            $status = 'MATCH_PROVAVEL';
            $confianca = 'ALTA';
        } elseif ($melhorScore >= 90) {
            $status = 'REVISAO_MANUAL';
            $confianca = 'MEDIA';
        } elseif ($melhorScore >= 80) {
            $status = 'AMBIGUO';
            $confianca = 'BAIXA';
        } else {
            $status = 'SEM_CORRESPONDENCIA';
            $confianca = 'NULA';
            $melhorCandidato = null;
        }

        StudentMatch::create([
            'aluno_id' => $melhorCandidato ? $melhorCandidato->id : null,
            'aprovado_id' => $aprovado->id,
            'score' => $melhorScore,
            'confianca' => $confianca,
            'algoritmo' => 'HIBRIDO_LEV_SIM_JARO_SDX',
            'status' => $status,
            'justificativa' => "Melhor score obtido: {$melhorScore}"
        ]);
    }
}
