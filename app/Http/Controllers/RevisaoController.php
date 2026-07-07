<?php

namespace App\Http\Controllers;

use App\Models\StudentMatch;

class RevisaoController extends Controller
{
    public function index()
    {
        $matches = StudentMatch::with(['aluno', 'aprovado'])
            ->whereIn('status', ['REVISAO_MANUAL', 'AMBIGUO'])
            ->orderBy('score', 'desc')
            ->paginate(15);
            
        return view('revisao.index', compact('matches'));
    }

    public function confirmar(StudentMatch $match)
    {
        $match->update(['status' => 'MATCH_EXATO', 'justificativa' => 'Confirmado manualmente']);
        return back()->with('success', 'Correspondência confirmada.');
    }

    public function descartar(StudentMatch $match)
    {
        $match->update(['status' => 'SEM_CORRESPONDENCIA', 'justificativa' => 'Descartado manualmente']);
        return back()->with('success', 'Correspondência descartada.');
    }
}
