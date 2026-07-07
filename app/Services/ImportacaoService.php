<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Aprovado;
use App\Models\Importacao;
use Illuminate\Support\Facades\DB;

class ImportacaoService
{
    protected NormalizacaoService $normalizacaoService;

    public function __construct(NormalizacaoService $normalizacaoService)
    {
        $this->normalizacaoService = $normalizacaoService;
    }

    public function importarAlunos(string $caminhoArquivo): void
    {
        $handle = fopen($caminhoArquivo, 'r');
        $headers = fgetcsv($handle, 1000, ';');

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                if (count($data) < 3) continue;

                $nomeNormalizado = $this->normalizacaoService->normalizar($data[1]);
                
                Aluno::updateOrCreate(
                    ['cpf' => $data[2]],
                    [
                        'id_aluno' => $data[0],
                        'nome_completo' => $data[1],
                        'unidade' => $data[3] ?? null,
                        'turma' => $data[4] ?? null,
                        'ano_matricula' => $data[5] ?? null,
                        'nome_normalizado' => $nomeNormalizado,
                        'hash_primeiro_nome' => $this->normalizacaoService->getPrimeiroNome($nomeNormalizado),
                        'hash_ultimo_sobrenome' => $this->normalizacaoService->getUltimoSobrenome($nomeNormalizado),
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }
    }

    public function importarAprovados(string $caminhoArquivo, Importacao $importacao): void
    {
        $handle = fopen($caminhoArquivo, 'r');
        $headers = fgetcsv($handle, 1000, ';');

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                if (count($data) < 2) continue;

                $nomeNormalizado = $this->normalizacaoService->normalizar($data[0]);
                
                Aprovado::create([
                    'importacao_id' => $importacao->id,
                    'nome' => $data[0],
                    'cpf_mascarado' => $data[1] ?? null,
                    'instituicao' => $data[2] ?? null,
                    'curso' => $data[3] ?? null,
                    'modalidade' => $data[4] ?? null,
                    'nome_normalizado' => $nomeNormalizado,
                    'hash_primeiro_nome' => $this->normalizacaoService->getPrimeiroNome($nomeNormalizado),
                    'hash_ultimo_sobrenome' => $this->normalizacaoService->getUltimoSobrenome($nomeNormalizado),
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }
    }
}
