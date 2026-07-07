<?php

namespace App\Http\Controllers;

use App\Models\Importacao;
use App\Services\ImportacaoService;
use App\Jobs\ExecutarComparacoesJob;
use Illuminate\Http\Request;

class ImportacaoController extends Controller
{
    protected ImportacaoService $importacaoService;

    public function __construct(ImportacaoService $importacaoService)
    {
        $this->importacaoService = $importacaoService;
    }

    public function index()
    {
        $importacoes = Importacao::orderBy('created_at', 'desc')->get();
        return view('importacao.index', compact('importacoes'));
    }

    public function importarBase(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt']);
        
        $path = $request->file('arquivo')->store('importacoes');
        
        $importacao = Importacao::create([
            'nome_arquivo' => $request->file('arquivo')->getClientOriginalName(),
            'tipo' => 'base_alunos',
            'status' => 'concluido'
        ]);

        $this->importacaoService->importarAlunos(storage_path('app/private/' . $path));

        return back()->with('success', 'Base de alunos importada com sucesso.');
    }

    public function importarAprovados(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt']);
        
        $path = $request->file('arquivo')->store('importacoes');
        
        $importacao = Importacao::create([
            'nome_arquivo' => $request->file('arquivo')->getClientOriginalName(),
            'tipo' => 'aprovados',
            'status' => 'pendente'
        ]);

        $this->importacaoService->importarAprovados(storage_path('app/private/' . $path), $importacao);

        // Despachar job de comparação
        ExecutarComparacoesJob::dispatch($importacao);

        return back()->with('success', 'Lista de aprovados importada. Comparações em andamento.');
    }
}
