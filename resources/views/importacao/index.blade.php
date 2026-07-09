@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Importação de Arquivos</h1>
    <form action="{{ route('importacao.limpar') }}" method="POST" onsubmit="return confirm('Deseja realmente limpar todos os dados importados e os arquivos armazenados?');">
        @csrf
        <button type="submit" class="btn btn-outline-danger">Limpar banco e arquivos</button>
    </form>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">1. Importar Base de Alunos</div>
            <div class="card-body">
                <form action="{{ route('importacao.base') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="arquivoBase" class="form-label">Arquivo CSV (separador ;)</label>
                        <input type="file" class="form-control" id="arquivoBase" name="arquivo" required accept=".csv,.txt">
                    </div>
                    <button type="submit" class="btn btn-primary">Importar Base</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">2. Importar Lista de Aprovados</div>
            <div class="card-body">
                <form action="{{ route('importacao.aprovados') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="arquivoAprovados" class="form-label">Arquivo CSV (separador ;)</label>
                        <input type="file" class="form-control" id="arquivoAprovados" name="arquivo" required accept=".csv,.txt">
                    </div>
                    <button type="submit" class="btn btn-success">Importar e Comparar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<h4>Histórico de Importações</h4>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Arquivo</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($importacoes as $imp)
            <tr>
                <td>{{ $imp->id }}</td>
                <td>{{ $imp->nome_arquivo }}</td>
                <td>{{ $imp->tipo }}</td>
                <td>
                    <span class="badge bg-{{ $imp->status == 'concluido' ? 'success' : ($imp->status == 'erro' ? 'danger' : 'warning') }}">
                        {{ $imp->status }}
                    </span>
                </td>
                <td>{{ $imp->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
