<?php

namespace App\Jobs;

use App\Models\Aprovado;
use App\Models\Importacao;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecutarComparacoesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Importacao $importacao;

    public function __construct(Importacao $importacao)
    {
        $this->importacao = $importacao;
    }

    public function handle(MatchingService $matchingService): void
    {
        $this->importacao->update(['status' => 'processando']);

        $aprovados = Aprovado::where('importacao_id', $this->importacao->id)->get();

        foreach ($aprovados as $aprovado) {
            $matchingService->processarAprovado($aprovado);
        }

        $this->importacao->update([
            'status' => 'concluido',
            'linhas_processadas' => $aprovados->count()
        ]);
    }
}
