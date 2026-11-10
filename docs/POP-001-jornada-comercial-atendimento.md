# POP 001 — Jornada comercial, cadastro e atendimento GEB

**Versão:** 0.1 | **Data:** 10/11/2026 | **Situação:** documento operacional em implantação.

## Objetivo e arquitetura

Identificar o que fazer, em qual sistema, em qual etapa e qual é a próxima decisão. A visão navegável está em `/sistemas/pop.php`, atrás da sessão do Portal de Aplicações. O POP não replica, armazena ou sincroniza dados de clientes. O repositório é público: **nunca registrar CPF, dados de saúde, fichas de clientes, segredos, tokens ou senhas aqui**.

## Etapas universais e evidências

| Etapa | Evento para avançar | Onde agir | Estado |
|---|---|---|---|
| 1. Lead identificado | empresa/pessoa identificada e origem conhecida | Busca Comercial / Radar CNPJ | ferramentas existentes; CRM de leads pendente |
| 2. Primeiro contato | resposta ou aderência qualificada, pessoa decisora identificada | abordagem manual | manual |
| 3. Prospect qualificado | interesse/necessidade e próximo passo documentados | CRM central; formulário PF/PJ se ainda não cadastrado | formulário e integração existentes |
| 4. Cadastro recebido | resposta do formulário | aba Respostas ao formulário 1 → PESSOAS/CRM_TRIAGEM | automático com conferência |
| 5. Validação | conferência/decisão humana | PESSOAS, CRM_TRIAGEM, CRM_LOGS, CRM_AUDITORIA | parcialmente homologado |
| 6. Vínculo PF/PJ | representante e PJ identificados | VINCULOS | aba criada; operação pendente |
| 7. Oportunidade | produto, frente, responsável e próxima ação definidos | RELACIONAMENTOS | aba criada; pipeline pendente |
| 8. Proposta | escopo e condições formalizados | módulo da frente | a definir |
| 9. Aceite | contratação confirmada por evidência | contrato/agenda do serviço | a definir |
| 10. Execução | entrega conforme acordo | módulo operacional | específico |
| 11. Avaliação | devolutiva/indicadores disponíveis | NPS/registro de atendimento | palestras parcialmente existente |
| 12. Continuidade | próxima ação autorizada | relacionamento | a definir |

**Conceitos:** lead é oportunidade identificada; prospect exige qualificação, não apenas clique; cadastro não equivale a cliente; cliente exige contratação ou prestação caracterizada. Não chamar o lead de prospect por automatismo.

## Ferramentas existentes

- **Cadastro geral PF/PJ (link externo):** https://docs.google.com/forms/d/e/1FAIpQLSeLu7FkGvlkYbxeWJ8UvNfg6P2nF9uTMi5TPM4c-HS1-y7mNQ/viewform
- **CRM central (permissões Google):** https://docs.google.com/spreadsheets/d/1foaPkjykwJ0QVQgmy0BkSDI2eV39ywRTNYkB7MzgS9g/edit
- **Base operacional de palestras/NPS (a integrar por identificadores):** https://docs.google.com/spreadsheets/d/1x7pM-J_QNc4Cpbc8GAHUO6n8REaRGjnRipwW9No_eLg/edit
- **Portal de Aplicações:** /sistemas/
- **Busca Comercial:** /busca-comercial/
- **Radar CNPJ:** https://radarcnpj.compassrosesystems.com.br/

## Ramificação prioritária — palestra corporativa

1. **Briefing:** tema, objetivos, público, quantidade, formato, acessibilidade, local, data e duração; identificar PF/PJ e decisor.
2. **Proposta:** escopo, valores, validade, despesas e condições de pagamento (fluxo a homologar).
3. **Aceite:** confirmação escrita e contrato quando aplicável; somente aqui registrar conversão a cliente.
4. **Preparação:** cronograma, materiais, logística, alinhamento com contratante.
5. **Entrega:** execução e registro mínimo de evidência.
6. **NPS:** aplicar fluxo existente, após corrigir vinculação de IDs e a finalidade das comunicações.
7. **Pós-evento:** encaminhar resultados, continuidade, novos serviços e relacionamento autorizado.

## Outras ramificações

**GEB Empresarial:** programas continuados, palestras e desenvolvimento humano aplicado ao trabalho; definir escopo e cadência. **Educação:** oferta validada → inscrição/matrícula específica → turma e suporte. **Saúde:** triagem estritamente administrativa → agenda própria → atendimento sob sigilo (não colocar dados clínicos no CRM geral). **Inclusão:** necessidade → recursos/acessibilidade → proposta → execução. **Tecnologia:** requisitos → proposta → projeto versionado → homologação/suporte. **Parcerias/Institucional:** oportunidade → contrapartidas → formalização → monitoramento.

Esses fluxos são **propostas de estrutura**, não sistemas homologados. Não presumir que existam formulários, contratos ou automações para essas etapas.

## Mensagens e acompanhamento

A interface oferece botões **Copiar mensagem**, que operam somente a área de transferência do navegador (revisão humana antes do envio). **Acompanhar preenchimento** abre a planilha protegida nas abas de resposta/CRM; não é aviso automático. Nenhuma mensagem é enviada pelo POP. A autorização de aniversário é independente da autorização comercial.

## Prioridade da próxima execução

- **P0** Concluir duplicidade fictícia ainda pendente em CRM_TRIAGEM, validar fluxo de decisões da Ação 05 e novo evento do CRM02.
- **P1** Criar estados e transições auditáveis do funil lead → prospect → cadastro → oportunidade, com responsável, data e origem, evitando duplicação de PF/PJ; vincular representantes em VINCULOS.
- **P2** Inventariar artefatos de palestras/NPS, definir ID do pedido e relação com ID_PESSOA/ID_VINCULO, proposta/aceite/agenda/entrega/NPS.
- **P3** Aprovar catálogo real de produtos e esteiras por braço antes de criar automações ou metas.
- **P4** Medir prazo por etapa, conversão e atividades, sem contabilizar testes como clientes.

## Critérios de aceitação e limites

- Preservar projeto Google Apps Script, formulários, integrações existentes e IDs permanentes.
- Nenhum arquivo de dados pessoais no GitHub nem na interface.
- LINKS de serviços privados dependem de permissões independentes; o POP não concede acesso.
- Confirmar publicação na Locaweb separadamente do commit GitHub.
- Registrar revisão deste POP quando a transição sair de “planejado” para “homologado”.
