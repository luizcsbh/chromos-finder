### Roteiro de Explicação do Sistema

Este roteiro pode ser usado para apresentar o projeto de forma clara, tanto em uma demonstração técnica quanto em uma explicação de negócio.

1. **Contexto do problema**
   - O sistema recebe duas bases: a lista de aprovados e a base de alunos da Rede Chromos.
   - O desafio é identificar, com segurança, quais aprovados pertencem à rede sem gerar falsos positivos.

2. **Objetivo do sistema**
   - Automatizar o cruzamento de nomes, reduzir trabalho manual e aumentar a precisão.
   - Priorizar segurança, auditoria e revisão humana quando a correspondência não for suficientemente forte.

3. **Fluxo completo do processo**
   - O usuário faz o upload das duas bases.
   - O sistema importa os dados e armazena o histórico de cada carga.
   - Cada nome passa por normalização para remover ruídos e padronizar o formato.
   - O motor de matching escolhe candidatos potenciais com base em índices simples e hashes.
   - O algoritmo de comparação calcula um score de similaridade.
   - O resultado é classificado como exato, provável, revisão manual, ambíguo ou sem correspondência.
   - A decisão é registrada para auditoria e pode ser revisada manualmente.

4. **Decisões de arquitetura**
   - O projeto foi estruturado com Laravel para ganhar produtividade, ORM, filas e rotas já prontas.
   - A lógica de negócio foi separada em serviços para manter o código organizado e testável.
   - Controllers ficaram enxutos, apenas orquestrando requisições.
   - Models representam os dados e as relações do banco.
   - Jobs foram usados para processar comparações em background, preservando a experiência do usuário.
   - A arquitetura foi pensada para evitar falsos positivos, então a decisão automática só acontece em cenários com alta confiança.

5. **Funções principais do sistema**
   - `ImportacaoService`: recebe os arquivos, valida entrada e organiza a importação.
   - `NormalizacaoService`: transforma nomes em um formato padronizado para comparação.
   - `ComparacaoService`: calcula a similaridade entre dois nomes usando múltiplos critérios.
   - `MatchingService`: escolhe o melhor candidato e define o status da correspondência.
   - `ExecutarComparacoesJob`: executa o processamento em fila para não travar a aplicação.
   - `StudentMatch`: armazena o resultado final da comparação para auditoria.

6. **Por que cada decisão foi tomada**
   - A normalização foi adotada para reduzir diferenças superficiais, como acentos, pontuação e partículas.
   - A busca foi limitada a candidatos potenciais para evitar comparações massivas e melhorar performance.
   - O score foi dividido em múltiplos algoritmos para aumentar a robustez do matching.
   - Pontuações intermediárias foram encaminhadas para revisão manual para proteger contra erros.
   - O sistema registra cada decisão para permitir rastreabilidade e análise posterior.

7. **Resumo da proposta de valor**
   - O sistema transforma um processo manual e sujeito a erro em um fluxo automatizado, rápido e audível.
   - Ele não tenta “adivinhar” demais; prefere revisar do que errar.
   - Essa postura é a principal vantagem do projeto.

---
