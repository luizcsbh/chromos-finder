<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Aprovado;
use App\Models\StudentMatch;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_alunos' => Aluno::count(),
            'total_aprovados' => Aprovado::count(),
            'matches_exatos' => StudentMatch::where('status', 'MATCH_EXATO')->count(),
            'matches_provaveis' => StudentMatch::where('status', 'MATCH_PROVAVEL')->count(),
            'ambiguos' => StudentMatch::where('status', 'REVISAO_MANUAL')->count() + StudentMatch::where('status', 'AMBIGUO')->count(),
            'sem_correspondencia' => StudentMatch::where('status', 'SEM_CORRESPONDENCIA')->count(),
        ];

        $listas = [
            'exatos' => StudentMatch::with(['aluno', 'aprovado'])->where('status', 'MATCH_EXATO')->orderByDesc('score')->get(),
            'provaveis' => StudentMatch::with(['aluno', 'aprovado'])->where('status', 'MATCH_PROVAVEL')->orderByDesc('score')->get(),
            'ambiguos' => StudentMatch::with(['aluno', 'aprovado'])->whereIn('status', ['REVISAO_MANUAL', 'AMBIGUO'])->orderByDesc('score')->get(),
            'sem_correspondencia' => StudentMatch::with(['aluno', 'aprovado'])->where('status', 'SEM_CORRESPONDENCIA')->orderByDesc('score')->get(),
        ];

        return view('dashboard', compact('stats', 'listas'));
    }

    public function exportarExatos()
    {
        $matches = StudentMatch::with(['aluno', 'aprovado'])->where('status', 'MATCH_EXATO')->get();
        
        $filename = "matches_exatos_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID Aluno', 'Nome Aluno', 'CPF Aluno', 'Unidade Aluno', 'Turma Aluno', 'Nome Aprovado', 'Instituicao', 'Curso', 'Score'];
        
        $callback = function() use($matches, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';');
            
            foreach ($matches as $match) {
                fputcsv($file, [
                    $match->aluno->id_aluno ?? '',
                    $match->aluno->nome_completo ?? '',
                    $match->aluno->cpf ?? '',
                    $match->aluno->unidade ?? '',
                    $match->aluno->turma ?? '',
                    $match->aprovado->nome ?? '',
                    $match->aprovado->instituicao ?? '',
                    $match->aprovado->curso ?? '',
                    $match->score
                ], ';');
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
