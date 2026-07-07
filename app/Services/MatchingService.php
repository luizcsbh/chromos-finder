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
        // Estratégia de filtragem: buscamos apenas alunos que compartilham o mesmo
        // primeiro nome ou o mesmo último sobrenome do aprovado.
        // Essa decisão reduz drasticamente o número de comparações e evita custo O(n²).
        // O objetivo é manter desempenho alto sem perder a capacidade de encontrar matches válidos.
        $candidatos = Aluno::where('hash_primeiro_nome', $aprovado->hash_primeiro_nome)
            ->orWhere('hash_ultimo_sobrenome', $aprovado->hash_ultimo_sobrenome)
            ->get();

        // Inicializamos o melhor score com zero para garantir que qualquer candidato
        // com semelhança positiva seja considerado.
        $melhorScore = 0;
        $melhorCandidato = null;

        // Avaliamos cada candidato potencial e escolhemos aquele com maior score.
        // Essa decisão é importante porque cada aprovado pode ter mais de um nome parecido,
        // e queremos preservar a correspondência mais plausível.
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

        // A classificação do resultado foi definida para priorizar a segurança.
        // Pontuações muito altas geram match automático, enquanto valores intermediários
        // são enviados para revisão manual para evitar falsos positivos.
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
            // Abaixo de 80, a correspondência é tratada como insuficiente e não é considerada válida.
            // Essa decisão reforça a política de não confirmar automaticamente nomes duvidosos.
            $status = 'SEM_CORRESPONDENCIA';
            $confianca = 'NULA';
            $melhorCandidato = null;
        }

        // Salva o resultado do matching para auditoria.
        // A justificativa simples registra o melhor score encontrado, mas a decisão final
        // de aceitar, revisar ou descartar fica explícita no status e na confiança atribuída.
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
