# CLAUDE.md

# Sistema de Identificação de Aprovados - Rede Chromos

## Objetivo

Construir um sistema web em PHP Laravel capaz de identificar automaticamente quais aprovados das listas públicas pertencem à base de alunos da Rede Chromos.

O sistema foi projetado priorizando:

- Zero falsos positivos
- Alta performance
- Escalabilidade
- Auditoria das decisões
- Facilidade de revisão humana

---

# Stack

Backend

- PHP 8.3+
- Laravel 12
- MySQL 8
- Redis (cache e filas)
- Queue Workers
- Laravel Scheduler

Frontend

- Blade
- Bootstrap 5
- Alpine.js
- Chart.js

Arquitetura

MVC

Organização

```
app/

    Actions/

    Services/

    Repositories/

    Models/

    DTO/

    Rules/

    Jobs/

    Events/

    Listeners/

    Helpers/

```

Toda regra de negócio deverá permanecer em Services.

Controllers apenas orquestram.

Repositories apenas acessam dados.

---

# Regras de negócio

## Pessoa

Cada CPF representa exatamente uma pessoa.

CPF é único.

Nunca poderão existir dois registros com o mesmo CPF.

Caso um CPF seja importado novamente:

Atualizar cadastro.

Nunca duplicar.

---

## Aluno

Representa um aluno da Rede Chromos.

Campos

- id_aluno
- nome_completo
- cpf
- unidade
- turma
- ano_matricula

---

## Lista de Aprovados

Representa uma importação.

Campos

- nome
- cpf_mascarado
- instituição
- curso
- modalidade

---

## Match

Tabela responsável pelas correspondências.

Campos

- id_aluno
- aprovado_id
- score
- confiança
- algoritmo
- status
- justificativa

Status possíveis

MATCH_EXATO

MATCH_PROVAVEL

AMBIGUO

SEM_CORRESPONDENCIA

REVISAO_MANUAL

---

# Fluxo

Upload CSV

↓

Validação

↓

Importação

↓

Normalização

↓

Indexação

↓

Comparação

↓

Classificação

↓

Tela de revisão

↓

Relatório

---

# Importação

Dois arquivos

base_alunos.csv

aprovados_instituicoes.csv

Cada importação cria um histórico.

Nunca apagar dados antigos.

---

# Normalização dos nomes

Antes de qualquer comparação:

Transformar em maiúsculo

Remover:

acentos

pontuação

espaços duplos

artigos

partículas

Exemplo

DA

DE

DO

DOS

DAS

E

Remover caracteres especiais.

Transformar

"José Antônio da Silva"

em

JOSE ANTONIO SILVA

Criar coluna:

nome_normalizado

Indexar essa coluna.

---

# Estratégia de comparação

Nunca comparar todos contra todos.

Criar índice por:

Primeira letra

Último sobrenome

Quantidade de palavras

Hash fonético

Isso reduz drasticamente o número de comparações.

---

# Algoritmos

Executar na seguinte ordem.

## 1 Nome exatamente igual

Score

100

Resultado

MATCH_EXATO

---

## 2 Nome normalizado igual

Score

99

Resultado

MATCH_EXATO

---

## 3 Levenshtein

Distância máxima

2

Peso

40%

---

## 4 Similaridade

similar_text()

Peso

30%

---

## 5 Jaro Winkler

Peso

20%

---

## 6 Soundex Brasileiro

Peso

10%

---

Score final

0 a 100

---

# Classificação

>=99

MATCH_EXATO

95-98

MATCH_PROVAVEL

90-94

REVISAO_MANUAL

80-89

AMBIGUO

<80

SEM_CORRESPONDENCIA

Nunca confirmar automaticamente abaixo de 95.

Este requisito atende o documento do teste, priorizando evitar falsos positivos.

---

# Desempate

Caso existam dois candidatos com diferença menor que 3 pontos.

Não decidir automaticamente.

Criar revisão manual.

---

# Tela principal

Dashboard

Cards

Total de alunos

Total aprovados

Matches exatos

Prováveis

Ambíguos

Sem correspondência

Tempo processamento

---

# Tela de importação

Upload Base

Upload Aprovados

Histórico

Logs

---

# Tela de processamento

Barra de progresso

Fila

Tempo restante

Quantidade processada

---

# Tela de revisão

Tabela

Nome aprovado

Nome aluno

Score

Confiança

Botões

Confirmar

Descartar

Editar

Pesquisar

Filtros

Instituição

Curso

Status

---

# Tela de alunos

CRUD completo

Pesquisa

CPF

Nome

Curso

Unidade

---

# Relatórios

CSV

Excel

JSON

PDF

Filtros

Instituição

Curso

Período

Status

---

# Performance

Nunca usar loops O(n²).

Utilizar:

Chunk()

LazyCollection

Cursor()

Queues

Cache Redis

Batch Processing

Indexação

nome_normalizado

cpf

último sobrenome

hash fonético

---

# Jobs

ImportarBaseJob

ImportarAprovadosJob

NormalizarNomesJob

GerarIndiceJob

ExecutarComparacoesJob

GerarRelatorioJob

---

# Services

AlunoService

ImportacaoService

NormalizacaoService

ComparacaoService

MatchingService

ScoreService

RelatorioService

AuditoriaService

---

# Repositories

AlunoRepository

AprovadoRepository

MatchRepository

ImportacaoRepository

---

# Helpers

NormalizerHelper

SimilarityHelper

StringHelper

CpfHelper

---

# Auditoria

Salvar

algoritmo utilizado

score

tempo

versão do algoritmo

justificativa

Nunca perder histórico.

---

# Segurança

Validação MIME

Validação extensão

Validação tamanho

Sanitização

CSRF

XSS

SQL Injection

Rate Limit

Logs

Permissões

Policies

Form Requests

Nunca confiar no CSV.

---

# Testes

Feature Tests

Importação

Upload

Relatórios

Unit Tests

Normalização

Levenshtein

Jaro Winkler

Score

Match

Repositories

Cobertura mínima

90%

---

# Banco de Dados

Principais tabelas

alunos

aprovados

matches

importacoes

logs_importacao

usuarios

auditorias

---

# Índices

cpf UNIQUE

nome_normalizado

instituição

status

score

---

# README

O README deverá explicar:

Arquitetura

Decisões tomadas

Fluxo

Trade-offs

Como evitar falsos positivos

Como executar

Como testar

Como utilizar IA

Prompts utilizados

O que foi aceito

O que foi descartado

Justificativas

---

# Objetivo principal

A prioridade absoluta do sistema é evitar falsos positivos.

É preferível enviar um caso para revisão humana do que confirmar incorretamente que um aprovado pertence à Rede Chromos.

Toda decisão automática deverá ser auditável, reproduzível e justificável.
