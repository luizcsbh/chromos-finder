# Sistema de Identificação de Aprovados - Rede Chromos

## Objetivo do Projeto
O objetivo deste sistema é cruzar duas bases de dados (uma lista oficial de aprovados em instituições e uma base de dados interna de alunos da Rede Chromos) para identificar com precisão quais aprovados foram alunos da rede. Como a finalidade principal é o marketing e a divulgação, o sistema foi desenhado com a diretriz absoluta de **evitar falsos positivos**, sendo preferível encaminhar um caso duvidoso para revisão humana do que atestar incorretamente a aprovação.

---

## 🏗️ Arquitetura e Decisões Técnicas

A aplicação foi construída utilizando o framework **Laravel 12** com a linguagem **PHP 8.3+**, banco de dados relacional (**SQLite** para facilitar a avaliação local, com estrutura preparada para **MySQL** via Docker Sail) e **Bootstrap 5 + Alpine.js** no frontend.

A arquitetura adotada baseia-se em princípios de **Clean Architecture** e **SOLID**, separando as responsabilidades nas seguintes camadas:

1. **Controllers (App\Http\Controllers):** Responsáveis apenas por receber a requisição HTTP e orquestrar as chamadas para as *views* e serviços. Não possuem regras de negócio.
2. **Services (App\Services):** Onde reside o "coração" da aplicação. Toda a lógica condicional e matemática do sistema fica restrita a esta área.
3. **Models e Migrations:** Representação do banco e mapeamento objeto-relacional estruturados para alta perfomance na indexação.
4. **Jobs (App\Jobs):** Processamento assíncrono de operações que demandam tempo.

### Justificativas das Escolhas Arquiteturais

- **Laravel e MVC:** O uso de Laravel é justificado pelo robusto sistema nativo de filas, ORM limpo e estruturação sólida que acelera o desenvolvimento sem comprometer a escalabilidade.
- **SQLite vs MySQL:** Foi adotado o SQLite como banco *default* neste teste para maximizar a facilidade do avaliador ao rodar o projeto localmente, dispensando containers. No entanto, a estrutura está `Sail-ready` para receber o MySQL/Redis previstos na documentação original caso seja necessário simular o ambiente de produção.
- **Clean Architecture e Injeção de Dependências:** A separação em serviços (`NormalizacaoService`, `ComparacaoService` e `MatchingService`) garante que a classe de comparação possa ser acionada independentemente do Upload do arquivo, permitindo criação de testes automatizados e atendendo perfeitamente ao Princípio da Responsabilidade Única (SRP).
- **Processamento Assíncrono (Jobs):** A tarefa de string matching numa malha O(n²) pode facilmente derrubar o tempo de requisição de um servidor Web PHP (timeout). Empurrar a rotina para a fila (`ExecutarComparacoesJob`) mantém a UX responsiva.

---

## ⚙️ Funcionamento e Algoritmo

### 1. Importação e Normalização
Ao realizar o upload do arquivo base e dos aprovados, o `ImportacaoService` atua limpando os registros na entrada. O `NormalizacaoService` trata as strings executando os passos:
- Remoção de acentos -> Caixa alta -> Eliminação de pontuação e caracteres especiais -> Exclusão de "Stop Words" (*DE, DA, DO, DOS, E*) -> Remoção de espaços duplos.
- **Desempenho:** Além do nome normalizado, o sistema recorta e salva o `hash_primeiro_nome` e `hash_ultimo_sobrenome`.

### 2. O Fluxo de Comparação Híbrida
Para não iterar sobre a tabela inteira e matar a memória (O(n²)), o `MatchingService` busca na base unicamente os candidatos que cruzam no mínimo o primeiro ou o último nome já recortados via hash.

O `ComparacaoService` aplica um combo estatístico sobre os nomes encontrados:
- **Match Exato (100 a 99%):** Validação direta de igualdade pré e pós normalização.
- **Levenshtein (Peso 40%):** Mede a quantidade de mudanças/caracteres necessários para transformar o Nome A no Nome B.
- **Similar Text (Peso 30%):** Analisa cruzamento percentual matemático.
- **Jaro-Winkler (Peso 20%):** A classe nativa `App\Helpers\JaroWinkler` foi implementada puramente em PHP e bonifica strings que sofrem apenas pequenos erros de tipografia (Typos), mantendo o prefixo idêntico altamente favorecido.
- **Soundex Fonético (Peso 10%):** Avalia aproximações por pronúncia similar.

### 3. A Regra do Falso Positivo
Os percentuais resultam em um grau de correspondência:
- Acima de **95%**: `MATCH_EXATO` e `MATCH_PROVAVEL`. Totalmente aceitos de forma autônoma pelo sistema.
- De **90% a 94%**: `REVISAO_MANUAL`. Nomes com grafia muito próxima mas com pequenos riscos estruturais.
- De **80% a 89%**: `AMBIGUO`. Nomes que batem em alguns pontos fortes, mas levantam red flags do Jaro-Winkler.
Qualquer pontuação ambígua ou manual deve ser avaliada e clicada na interface humana do Dashboard.

### 4. Algoritmos Utilizados na Comparação de Nomes
Foram incorporados algoritmos clássicos de similaridade para melhorar a detecção de nomes parecidos sem comprometer a segurança do processo.

- **Levenshtein**: mede a distância entre duas strings contando quantas operações de inserção, remoção ou substituição são necessárias para transformar uma na outra. Ele foi usado para capturar pequenas variações de grafia, como erros de digitação, letras trocadas ou elementos ausentes.
- **Jaro-Winkler**: é um algoritmo de similaridade especialmente útil para nomes curtos e variações pequenas, pois valoriza prefixos comuns e a posição relativa dos caracteres. Ele foi empregado para reconhecer nomes com pequenas diferenças ortográficas sem aceitar correspondências frágeis demais.

Esses dois algoritmos foram escolhidos porque complementam a normalização textual: enquanto o Levenshtein foca na edição estrutural da string, o Jaro-Winkler reforça a semelhança entre nomes que compartilham prefixos e estrutura semelhante.

### 5. Interface Web
Construída como Single-View SPA com abas utilizando **Bootstrap 5** e uma funcionalidade nativa Javascript em **DOM Vanilla** para reordenar tabelas e colunas, além de geração limpa em _streams_ de um arquivo `CSV`.

---

## 🚀 Como Executar o Projeto

1. Instale todas as dependências PHP/Node:
```bash
composer install
npm install
npm run build
```

2. Configure o ambiente (.env) e as tabelas SQLite:
```bash
- crie a base de dados dentro do projeto
touch database/database.sqlite
````
crie .env na raiz do projeto
```bash
APP_NAME="Chromos Finder"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

DB_CONNECTION=sqlite
DB_DATABASE=/caminho do caminho da base de dados/database.sqlite

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=array
```
```bash
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

3. Suba a aplicação:
```bash
php artisan serve
```

*(Observação: A fila do projeto foi comutada para driver "sync" apenas para viabilizar testes instantâneos. Caso rode num ambiente MySQL via Docker com Redis, use `php artisan queue:work`)*

Abra o painel em `http://localhost:8000`. Efetue o upload da Base de Alunos primeiro e a Lista de Aprovados depois.

---

## 🧪 Como Testar Automatizado

Foram elaborados **Unit Tests** via PHPUnit cobrindo os serviços de matemática fonética e distanciamento estrutural.
```bash
php artisan test
```
Esses testes asseguram que o motor principal que evita *falsos positivos* cumpra seu papel independentemente de refatorações futuras no código HTTP.

---

## 🤖 Uso de Inteligência Artificial e Prompts

Este desenvolvimento utilizou IA focada em gerar boilerplate para Clean Architecture e validação de algoritmos de correlação, obedecendo à exigência de documentar *pair-programming*.

### Prompt 1
- **Prompt Utilizado:** *"leia os arquivos da raiz e crie"* (Com base no prompt original incompleto e lendo o CLAUDE.md)
- **O que foi aceito:** Configuração do ecossistema e hierarquia Service/Job separada dos Models.
- **Justificativas:** A IA elaborou e propôs o *Implementation Plan* identificando a ausência do projeto Laravel. Foi aceita a premissa de estruturar primeiro as tabelas (Importacao > Alunos > Aprovados > Match) para garantir um pipeline linear, além da criação e implementação do Algoritmo híbrido (Levenshtein + similar_text + JaroWinkler + Soundex).

### Prompt 2
- **Prompt Utilizado:** *"no dashboard mostre a lista de cada situação e a opção de exportar para .csv para matches exatos"*
- **O que foi aceito:** Criação de queries baseadas no método estático `StudentMatch::where()->get()` associadas a abas do Bootstrap, e um método no *Controller* que processa output streams CSV diretos sem bibliotecas de terceiros (usando `fputcsv`).
- **O que foi descartado:** O uso da biblioteca robusta *Laravel Excel*.
- **Justificativas:** Para um simples CSV nativo, adicionar uma dependência grande não traz bons trade-offs computacionais. O stream é rápido e consome menos recurso de memória.

### Prompt 3
- **Prompt Utilizado:** *"adicione na lista de exatos o cpf do aluno e crie a opção de ordenação em todas as colunas das listas"*
- **O que foi aceito:** Adicionar o CPF em coluna apartada no Blade, e um snipet Vanilla JS injetado ao pé da página interagindo com `comparer` sobre NodeLists DOM.
- **O que foi descartado:** Bibliotecas externas famosas em JQuery como *DataTables*, ou migração para um componente robusto Vue/Livewire.
- **Justificativas:** Em nome da leveza (Performance) estipulada em requisitos originais, injetar 20 linhas de JS resolvendo a dor do ordenamento para N colunas prova mais fluidez do framework atual.

### Trade-offs Conhecidos de IA e Decisão Humana
1. **Phonetic Matching (Soundex) vs Brasileiro Nativo:** Utilizar o Soundex integrado no PHP tem ótima fluidez, mas o idioma base é o inglês. Para contornar uma falha nativa do pacote do PHP num software corporativo complexo, optou-se por atribuir ao Soundex um **peso reduzido (10%)** balanceado com o **Levenshtein/JaroWinkler**. 
2. **Busca Textual do BD:** Embora as consultas SQL via chaves extraídas da string (First Name, Last Name) evitem loops infinitos de verificação, em uma corporação gigantesca o indicado seria enviar e indexar no **Elasticsearch** (buscas *Fuzzy*). Neste teste a abordagem ORM otimizado via Hash atende a demanda da escalabilidade simples.
