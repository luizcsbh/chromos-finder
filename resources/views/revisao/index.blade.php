@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Revisão Manual (Casos Ambíguos)</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
        <thead>
            <tr>
                <th>Score</th>
                <th>Aprovado (Instituição)</th>
                <th>Candidato (Base)</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matches as $match)
            <tr>
                <td>
                    <span class="badge bg-{{ $match->score >= 90 ? 'warning text-dark' : 'danger' }}">
                        {{ $match->score }}%
                    </span>
                </td>
                <td>
                    <strong>{{ $match->aprovado->nome }}</strong><br>
                    <small class="text-muted">{{ $match->aprovado->instituicao }} - {{ $match->aprovado->curso }}</small>
                </td>
                <td>
                    @if($match->aluno)
                        <strong>{{ $match->aluno->nome_completo }}</strong><br>
                        <small class="text-muted">CPF: {{ $match->aluno->cpf }} | {{ $match->aluno->unidade }}</small>
                    @else
                        <em>Sem correspondência encontrada</em>
                    @endif
                </td>
                <td>{{ $match->status }}</td>
                <td>
                    @if($match->aluno)
                    <form action="{{ route('revisao.confirmar', $match->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Confirmar Match"><i class="bi bi-check-lg"></i> Confirmar</button>
                    </form>
                    <form action="{{ route('revisao.descartar', $match->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-danger" title="Descartar Match"><i class="bi bi-x-lg"></i> Descartar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Nenhum caso pendente de revisão.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    {{ $matches->links('pagination::bootstrap-5') }}
</div>
@endsection
