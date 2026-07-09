@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row text-center mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Alunos Base</h5>
                <p class="card-text display-6">{{ $stats['total_alunos'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Aprovados</h5>
                <p class="card-text display-6">{{ $stats['total_aprovados'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Matches Exatos</h5>
                <p class="card-text display-6">{{ $stats['matches_exatos'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Para Revisão</h5>
                <p class="card-text display-6">{{ $stats['ambiguos'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">Distribuição dos Aprovados</div>
            <div class="card-body">
                <canvas id="matchesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Listagem de Situações</span>
                <a href="{{ route('exportar.exatos') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Matches Exatos
                </a>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-success" id="exatos-tab" data-bs-toggle="tab" data-bs-target="#exatos" type="button" role="tab">Exatos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-info" id="provaveis-tab" data-bs-toggle="tab" data-bs-target="#provaveis" type="button" role="tab">Prováveis</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-warning" id="ambiguos-tab" data-bs-toggle="tab" data-bs-target="#ambiguos" type="button" role="tab">Ambíguos/Revisão</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-danger" id="sem-tab" data-bs-toggle="tab" data-bs-target="#sem" type="button" role="tab">Sem Correspondência</button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="myTabContent" style="max-height: 400px; overflow-y: auto;">
                    
                    <!-- Aba Exatos -->
                    <div class="tab-pane fade show active" id="exatos" role="tabpanel">
                        <table class="table table-sm table-striped sortable-table">
                            <thead>
                                <tr>
                                    <th>ID Aluno <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>CPF <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Aprovado <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Instituição <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Base (Candidato) <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Score <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listas['exatos'] as $match)
                                <tr>
                                    <td><strong>{{ $match->aluno->id_aluno ?? '-' }}</strong></td>
                                    <td>{{ $match->aluno->cpf ?? '-' }}</td>
                                    <td>{{ $match->aprovado->nome }}</td>
                                    <td>{{ $match->aprovado->instituicao }}</td>
                                    <td>{{ $match->aluno->nome_completo ?? '' }}</td>
                                    <td><span class="badge bg-success">{{ $match->score }}%</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center">Nenhum registro.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Aba Prováveis -->
                    <div class="tab-pane fade" id="provaveis" role="tabpanel">
                        <table class="table table-sm table-striped sortable-table">
                            <thead>
                                <tr>
                                    <th>ID Aluno <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Aprovado <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Instituição <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Base (Candidato) <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Score <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listas['provaveis'] as $match)
                                <tr>
                                    <td><strong>{{ $match->aluno->id_aluno ?? '-' }}</strong></td>
                                    <td>{{ $match->aprovado->nome }}</td>
                                    <td>{{ $match->aprovado->instituicao }}</td>
                                    <td>{{ $match->aluno->nome_completo ?? '' }}</td>
                                    <td><span class="badge bg-info">{{ $match->score }}%</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">Nenhum registro.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Aba Ambíguos -->
                    <div class="tab-pane fade" id="ambiguos" role="tabpanel">
                        <table class="table table-sm table-striped sortable-table">
                            <thead>
                                <tr>
                                    <th>ID Aluno <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Aprovado <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Instituição <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Base (Candidato) <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Score <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listas['ambiguos'] as $match)
                                <tr>
                                    <td><strong>{{ $match->aluno->id_aluno ?? '-' }}</strong></td>
                                    <td>{{ $match->aprovado->nome }}</td>
                                    <td>{{ $match->aprovado->instituicao }}</td>
                                    <td>{{ $match->aluno->nome_completo ?? 'N/A' }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $match->score }}%</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">Nenhum registro.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if(count($listas['ambiguos']) > 0)
                            <a href="{{ route('revisao.index') }}" class="btn btn-sm btn-outline-warning mt-2">Ir para Tela de Revisão</a>
                        @endif
                    </div>

                    <!-- Aba Sem Correspondência -->
                    <div class="tab-pane fade" id="sem" role="tabpanel">
                        <table class="table table-sm table-striped sortable-table">
                            <thead>
                                <tr>
                                    <th>Aprovado <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Instituição <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                    <th>Curso <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listas['sem_correspondencia'] as $match)
                                <tr>
                                    <td>{{ $match->aprovado->nome }}</td>
                                    <td>{{ $match->aprovado->instituicao }}</td>
                                    <td>{{ $match->aprovado->curso }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center">Nenhum registro.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('matchesChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Exatos', 'Prováveis', 'Ambíguos', 'Sem Correspondência'],
                datasets: [{
                    data: [
                        {{ $stats['matches_exatos'] }}, 
                        {{ $stats['matches_provaveis'] }}, 
                        {{ $stats['ambiguos'] }}, 
                        {{ $stats['sem_correspondencia'] }}
                    ],
                    backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545']
                }]
            }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

    const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
        v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
    )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

    document.querySelectorAll('.sortable-table th').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            // reset icons
            table.querySelectorAll('th i').forEach(icon => {
                icon.className = 'bi bi-arrow-down-up text-muted small';
            });
            
            // direction
            this.asc = !this.asc;
            let icon = this.querySelector('i');
            if(icon) {
                icon.className = this.asc ? 'bi bi-arrow-up-short text-dark' : 'bi bi-arrow-down-short text-dark';
            }

            Array.from(tbody.querySelectorAll('tr'))
                .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc))
                .forEach(tr => tbody.appendChild(tr));
        });
    });
});
</script>
