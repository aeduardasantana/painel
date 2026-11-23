# Painel — Regra universal de soluções

## Diretriz permanente da Presidência (15/10/2026)

**Aplica-se a TUDO, sem restrição ao Selo G, GEB ou a qualquer projeto específico.**

Sempre que surgir uma ideia de solução que envolva painel, sistema de acompanhamento, indicador, controle, dashboard, ferramenta de gestão, fluxo operacional ou módulo digital, **utilizar como ponto de partida o repositório central [aeduardasantana/painel](https://github.com/aeduardasantana/painel)**.

Antes de sugerir uma solução independente:
1. Consultar este repositório e verificar a arquitetura disponível.
2. Avaliar implementação, reaproveitamento ou integração nele como primeira opção.
3. Centralizar a experiência de gestão sempre que tecnicamente adequado.
4. Evitar multiplicar painéis e sistemas paralelos.
5. Respeitar segurança, segregação dos dados, acesso e governança de cada operação.

**Escopo:** todas as áreas, iniciativas, marcas, projetos e demandas futuras da usuária. O Selo G é apenas um exemplo de aplicação, não uma limitação.

A regra orienta propostas e implementações; não autoriza modificar sistemas ou publicar dados sem as permissões pertinentes.

## Formação executiva — Vocabulário Estratégico

- [Vocabulário Estratégico Executivo (HTML)](./vocabulario-estrategico.html): consulta de 90 termos por área e etapa do negócio, perguntas de aplicação e simulador didático de markup.
- **Situação:** arquivo incorporado ao repositório central. A integração ao menu do Painel Mestre e a publicação em endereço navegável dependem da localização da interface implantada; o repositório consultado apresenta a diretriz de centralização, mas não expõe a aplicação principal.
- **Próxima etapa prevista:** modelar indicadores e calculadoras específicas dos braços GEB, com parâmetros próprios e controle de acesso, sem presumir dados reais.

## Acessos operacionais — GEB Educação

| Recurso | Acesso | Situação |
| --- | --- | --- |
| LP Bolsas de estudo — até 83% | [Revisar a LP e integração no GitHub](https://github.com/aeduardasantana/gebeducacao/pull/1) | Em homologação; não publicada |

**Destino previsto:** `https://gebeducacao.grupoeduardabispo.com.br/bolsas/consultar/`.

**Registro para o Painel Mestre:** [atalhos/geb-educacao-bolsas.json](./atalhos/geb-educacao-bolsas.json). O atalho tem `acesso_publico_habilitado: false` até a confirmação do cadastro no CRM, do protocolo e da mensagem do WhatsApp. Esse registro não significa que o menu da aplicação Painel Mestre já foi alterado; o repositório atual não expõe o código dessa interface.
