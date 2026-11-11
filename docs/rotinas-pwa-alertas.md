# Painel Mestre — rotinas, aplicativo e alertas (11/11/2026)

## Rotinas ativas

- **08h15, domingo a domingo:** conferir se os sete artigos diários do Blog GEB foram publicados corretamente, individualmente. Acesso: https://blog.grupoeduardabispo.com.br/
- **09h30, segunda a sexta:** estruturar o conteúdo e gravar o Gestão em Pauta.
- **11h00, segunda a sexta:** publicar a edição e conferir a publicação efetiva.

Nas rotinas do Gestão em Pauta, consultar a pasta https://drive.google.com/drive/folders/1YHBXSAahlwfHWeKJ1dg8SfzC5g5Gu-pN e o Bastidor GP https://bastidorgp.compassrosesystems.com.br/.

## Exibição e registros

- Cartões compactos com **Observações e acessos** recolhidos por padrão.
- Estados manuais independentes **Feito**, **Pendente** e **Sem marcação**.
- O histórico pré-existente permanece em `.rotinas-gestao.json` no servidor; IDs antigos não foram alterados.
- Não conclui uma tarefa automaticamente por ter chegado o horário ou por outra tarefa ter sido concluída.

## PWA e notificações

- Página de entrada: `/sistemas/rotinas.php`; manifesto e service worker servidos pelo mesmo domínio.
- No computador, usar **Instalar aplicativo**, quando oferecido pelo navegador.
- No iPhone, usar Safari → Compartilhar → **Adicionar à Tela de Início**, abrir o aplicativo instalado e ativar alertas.
- O usuário precisa permitir notificações por dispositivo.
- O servidor envia Web Push quando houver dispositivo inscrito; sem push, há lembrete **somente enquanto a página estiver aberta**.
- Repetição de até 1 hora após o horário de cada tarefa não concluída, em janela **08h–20h59 de Brasília**. Fora dessa janela não envia; a tarefa continua registrada como pendente.
- O agendador executa em 00/15/30 minutos (UTC 11–23) via GitHub Actions e pode ter atraso por filas, navegador, rede ou sistema operacional. Não há garantia de disparo no segundo exato.

## Infraestrutura, confidencialidade e operação

- Web Push usa a biblioteca PHP `minishlink/web-push:^8.0`, instalada por Composer durante o fluxo de publicação.
- A primeira ativação cria as chaves VAPID no servidor (não no GitHub público).
- Subscrições e controle de envios são guardados nos arquivos privados `.rotinas-vapid.json`, `.rotinas-subs.json` e `.rotinas-sent.json`, com bloqueio de acesso HTTP.
- O agendador usa o segredo existente `SYSTEMS_PASS` do GitHub Actions para autenticar a chamada HTTPS. Manter segredo fora de código, logs e documentos públicos.
- Endpoint de execução: `/sistemas/rotinas-disparar.php` (POST autenticado).
- Limite inicial de 12 dispositivos inscritos; cada dispositivo pode desativar alertas.
- A configuração depende das extensões PHP curl, mbstring e openssl e da disponibilidade da hospedagem. Validar nos dispositivos reais e no primeiro envio; a etapa de implantação não prova recebimento de notificações.

## Validações antes de considerar operação integral

1. Inscrição de um navegador no botão **Ativar alertas**.
2. Marcar uma tarefa como **Feito** e confirmar que não recebe novos alertas.
3. Marcar como **Pendente** e confirmar o próximo disparo horário.
4. Instalação em Windows/Android e iPhone (testes por dispositivo).
5. Verificar execução agendada do GitHub Actions em horário de interesse.
6. Verificar que os arquivos de subscrição, chaves e histórico respondem 403/404 sem autorização.

O módulo não modifica os comandos SGG, as rotinas existentes do Blog, nem as esteiras de produtos.
