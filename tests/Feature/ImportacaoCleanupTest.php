<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Aprovado;
use App\Models\Importacao;
use App\Models\StudentMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportacaoCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cleans_imported_data_and_files(): void
    {
        Storage::disk('local')->put('importacoes/arquivo.csv', 'conteudo');

        $importacao = Importacao::create([
            'nome_arquivo' => 'arquivo.csv',
            'tipo' => 'aprovados',
            'status' => 'concluido',
        ]);

        $aluno = Aluno::create([
            'id_aluno' => '1',
            'nome_completo' => 'Maria Souza',
            'cpf' => '12345678900',
            'nome_normalizado' => 'maria souza',
        ]);

        $aprovado = Aprovado::create([
            'importacao_id' => $importacao->id,
            'nome' => 'Joao Silva',
            'nome_normalizado' => 'joao silva',
        ]);

        StudentMatch::create([
            'aluno_id' => $aluno->id,
            'aprovado_id' => $aprovado->id,
            'score' => 99.00,
            'confianca' => 'alta',
            'algoritmo' => 'nome',
            'status' => 'MATCH_EXATO',
        ]);

        $response = $this->post(route('importacao.limpar'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('importacoes', 0);
        $this->assertDatabaseCount('alunos', 0);
        $this->assertDatabaseCount('aprovados', 0);
        $this->assertDatabaseCount('student_matches', 0);
        $this->assertFalse(Storage::disk('local')->exists('importacoes/arquivo.csv'));
    }
}
