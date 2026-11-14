<?php
declare(strict_types=1);

/**
 * Catálogo único de rotinas do Painel Mestre.
 * dias: 1=segunda ... 7=domingo; horário em America/Sao_Paulo.
 * IDs existentes são preservados para manter o histórico.
 */
return [
    'geb-blog-conferir-publicacao' => [
        'hora' => '08:15',
        'titulo' => 'Conferir os sete artigos diários do Blog GEB',
        'area' => 'GEB · Comunicação / Editorial',
        'descricao' => 'Verificar individualmente as sete publicações do dia.',
        'observacoes' => 'Rotina de domingo a domingo. Conferir as sete editorias no site publicado: Desenvolvimento, Empresarial, Educação, Saúde, Tecnologia, Inclusão e Institucional. Confirmar títulos, corpo, links, referências e funcionamento antes de marcar como concluído.',
        'links' => [
            ['titulo' => 'Abrir Blog GEB', 'url' => 'https://blog.grupoeduardabispo.com.br/'],
        ],
        'dias' => [1, 2, 3, 4, 5, 6, 7],
    ],
    'gestao-em-pauta-preparar-gravar' => [
        'hora' => '09:30',
        'titulo' => 'Estruturar e gravar o Gestão em Pauta',
        'area' => 'GEB · Comunicação',
        'descricao' => 'Produzir a pauta, estruturar o conteúdo e gravar a edição.',
        'observacoes' => 'Consultar a apuração no Bastidor GP. Guardar o material correspondente na pasta da edição no Drive. A conclusão desta tarefa não conclui automaticamente a publicação das 11h.',
        'links' => [
            ['titulo' => 'Pasta de edições no Drive', 'url' => 'https://drive.google.com/drive/folders/1YHBXSAahlwfHWeKJ1dg8SfzC5g5Gu-pN?usp=drive_link'],
            ['titulo' => 'Abrir Bastidor GP', 'url' => 'https://bastidorgp.compassrosesystems.com.br/'],
        ],
        'dias' => [1, 2, 3, 4, 5],
    ],
    'gestao-em-pauta-publicar' => [
        'hora' => '11:00',
        'titulo' => 'Publicar a edição do Gestão em Pauta',
        'area' => 'GEB · Comunicação',
        'descricao' => 'Publicar a edição e verificar seu acesso público.',
        'observacoes' => 'Conferir título, descrição, publicação efetiva e link da edição. Manter os documentos e registros da edição na pasta do Drive. A marcação é manual e independente da rotina de gravação.',
        'links' => [
            ['titulo' => 'Publicar no YouTube Studio', 'url' => 'https://studio.youtube.com/channel/UCanvdpcTbEXJa4xXeeHjbig/content'],
        ],
        'dias' => [1, 2, 3, 4, 5],
    ],
    // Identificação apenas por horário: nomes e telefones ficam exclusivamente na Amplimed.
    'saude-seg-2030' => [
        'hora'=>'20:30', 'titulo'=>'Atendimento assistencial · segunda, 20h30–21h10',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir a sessão na agenda oficial e registrar execução após o horário.',
        'observacoes'=>'Recorrência semanal documentada no SGG. O Painel Mestre não mantém dados identificáveis de pacientes nem substitui a agenda clínica. O alerta das 20h30 não terá repetição após 21h.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[1], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1225' => [
        'hora'=>'12:25', 'titulo'=>'Atendimento assistencial · quarta, 12h25–12h55',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'Recorrência semanal documentada no SGG. Sem dados de pacientes no repositório.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1430-quinzenal' => [
        'hora'=>'14:30', 'titulo'=>'Atendimento assistencial quinzenal · quarta, 14h30–15h10',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir ocorrência quinzenal na Amplimed e registrar execução.',
        'observacoes'=>'Quinzenal conforme SGG. Âncora de cálculo: 16/09/2026, data observada no calendário de referência. Confirmar na Amplimed eventuais alterações, cancelamentos ou reposições.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'quinzenal', 'data_ancora'=>'2026-09-16', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1520' => [
        'hora'=>'15:20', 'titulo'=>'Atendimento assistencial · quarta, 15h20–16h',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'Recorrência semanal documentada no SGG.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1650' => [
        'hora'=>'16:50', 'titulo'=>'Atendimento assistencial · quarta, 16h50–17h10',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'Recorrência semanal documentada no SGG.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1720' => [
        'hora'=>'17:20', 'titulo'=>'Atendimento assistencial · quarta, 17h20–17h40',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'Recorrência semanal documentada no SGG.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qua-1800' => [
        'hora'=>'18:00', 'titulo'=>'Atendimento assistencial · quarta, 18h–18h40',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'Recorrência semanal documentada no SGG.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[3], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    'saude-qui-1920' => [
        'hora'=>'19:20', 'titulo'=>'Atendimento assistencial · quinta, 19h20–20h',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Conferir na Amplimed e registrar execução.',
        'observacoes'=>'A base SGG sinaliza conflito com compromisso acadêmico na quinta-feira, 18h55–22h25. Validar a prioridade e a agenda vigente antes de confirmar presença ou programar alterações.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'dias'=>[4], 'frequencia'=>'semanal', 'ativo_desde'=>'2026-11-11'
    ],
    // Mensais de pacientes não têm dia fixo suficientemente confirmado: apenas verificação de agenda.
    'saude-conferir-mensais' => [
        'hora'=>'08:30', 'titulo'=>'Confirmar atendimentos mensais na Amplimed',
        'area'=>'GEB Saúde · Atendimento', 'descricao'=>'Verificar as duas rotinas mensais registradas no SGG e atualizar a agenda efetiva.',
        'observacoes'=>'O SGG documenta uma sessão mensal de quarta-feira às 16h05–16h45 e outra sessão mensal sem horário confirmado. Não gerar notificações de sessões em datas presumidas; confirmar as datas diretamente na Amplimed.',
        'links'=>[['titulo'=>'Abrir agenda Amplimed','url'=>'https://app.amplimed.com.br/consultas']],
        'frequencia'=>'mensal', 'dia_mes'=>1, 'ativo_desde'=>'2026-12-01'
    ],
    'pinterest-planejar-agendar-mes' => [
        'hora'=>'14:00', 'titulo'=>'Programar pins do próximo mês no Pinterest',
        'area'=>'Eduarda Bispo · Marketing', 'descricao'=>'Fazer curadoria, revisar direitos e completar o agendamento mensal dos pins.',
        'observacoes'=>'Dia 20 às 14h é uma referência operacional inicial, ajustável. A conta estava agendada até 25/11/2026, conforme a usuária. Na planilha, 10 pins constam como Agendado até 25/11; os posteriores aparecem Em curadoria e NÃO devem ser considerados agendados. Verificar fotos, direitos, textos alternativos, links e calendário antes de concluir.',
        'links'=>[
            ['titulo'=>'Perfil Pinterest','url'=>'https://br.pinterest.com/aeduardabispo/'],
            ['titulo'=>'Pins agendados','url'=>'https://br.pinterest.com/aeduardabispo/scheduled-pins/'],
            ['titulo'=>'Inventário editorial (Drive)','url'=>'https://docs.google.com/spreadsheets/d/198SfxlHLpOUILmzuuFBBbmDgSI2qta8B9YwEBt6YAYA/edit?usp=sharing']
        ],
        'frequencia'=>'mensal', 'dia_mes'=>20, 'ativo_desde'=>'2026-11-11'
    ],
    'educacao-bolsa-mais-brasil-agendar' => [
        'hora'=>'13:00',
        'titulo'=>'Agendar publicações do Bolsa Mais Brasil para os próximos 30 dias',
        'area'=>'GEB Educação · Marketing / Parcerias',
        'descricao'=>'Selecionar os posts fornecidos pela matriz e agendar as publicações no Facebook e Instagram do Bolsa Mais Brasil.',
        'observacoes'=>'Primeira execução: 11/11/2026. Repetir a cada 30 dias a partir desta data para manter a cobertura programada. Horário de 13h é provisório, sujeito à organização de agenda. Conferir materiais originais disponibilizados pela matriz na pasta do Drive (ou no grupo de WhatsApp quando necessário), adequar textos e formatos conforme orientações recebidas, validar direitos e links, agendar no Meta Business Suite e confirmar os agendamentos em ambas as redes antes de marcar como Feito. Não considerar postagem efetiva apenas pelo agendamento; esta rotina mede a preparação e a programação dos próximos 30 dias.',
        'links'=>[
            ['titulo'=>'Agendar no Meta Business Suite','url'=>'https://business.facebook.com/latest/home?asset_id=219644841229036'],
            ['titulo'=>'Posts da matriz no Drive','url'=>'https://drive.google.com/drive/folders/1JDixQu_uyL_B7PunAatjg5c7CLb7HLnK?usp=drive_link'],
        ],
        'frequencia'=>'intervalo_dias',
        'intervalo_dias'=>30,
        'data_ancora'=>'2026-11-11',
        'ativo_desde'=>'2026-11-11',
    ],
    'eduarda-bispo-gravar-conteudo-mensal' => [
        'hora'=>'14:00',
        'titulo'=>'Gravar os conteúdos mensais de Eduarda Bispo',
        'area'=>'Eduarda Bispo · Comunicação / Produção',
        'descricao'=>'Gravar em lote o conteúdo destinado aos perfis pessoais profissionais do Instagram e Facebook.',
        'observacoes'=>'Primeira execução: quinta-feira, 12/11/2026. Recorrência mensal proposta para o dia 12 de cada mês, às 14h (horário provisório). Conferir o planejamento editorial, preparar roteiros e materiais, gravar os conteúdos e guardar os arquivos organizados no Drive. Esta tarefa mede a gravação, não o agendamento. Marcar Feito somente após concluir a gravação.',
        'links'=>[
            ['titulo'=>'Pasta de conteúdos no Drive','url'=>'https://drive.google.com/drive/folders/1xHpQdCDKUa5RCrC6owR-ITu4OI4-eU3M?usp=drive_link'],
            ['titulo'=>'Perfil Instagram Eduarda Bispo','url'=>'https://www.instagram.com/aeduardabispo'],
            ['titulo'=>'Página Facebook Eduarda Bispo','url'=>'https://www.facebook.com/aeduardabispo/']
        ],
        'frequencia'=>'mensal',
        'dia_mes'=>12,
        'ativo_desde'=>'2026-11-12'
    ],
    'eduarda-bispo-agendar-conteudo-mensal' => [
        'hora'=>'16:00',
        'titulo'=>'Agendar as publicações mensais de Eduarda Bispo',
        'area'=>'Eduarda Bispo · Comunicação / Marketing',
        'descricao'=>'Agendar no Facebook e Instagram os conteúdos preparados para o próximo ciclo mensal.',
        'observacoes'=>'Primeira execução: quinta-feira, 12/11/2026. Recorrência mensal proposta para o dia 12 de cada mês, às 16h (horário provisório). Usar os arquivos organizados na pasta do Drive, revisar legendas, links, formato e adequação a cada rede, programar as publicações e conferir o calendário de ambas as contas. Marcar Feito apenas após confirmar os agendamentos; marcar a gravação como Feito não conclui automaticamente esta atividade.',
        'links'=>[
            ['titulo'=>'Pasta de conteúdos no Drive','url'=>'https://drive.google.com/drive/folders/1xHpQdCDKUa5RCrC6owR-ITu4OI4-eU3M?usp=drive_link'],
            ['titulo'=>'Perfil Instagram Eduarda Bispo','url'=>'https://www.instagram.com/aeduardabispo'],
            ['titulo'=>'Página Facebook Eduarda Bispo','url'=>'https://www.facebook.com/aeduardabispo/']
        ],
        'frequencia'=>'mensal',
        'dia_mes'=>12,
        'ativo_desde'=>'2026-11-12'
    ],

    // GEB Educação: inventário, linha editorial, produção e espelhamento — 13/11/2026.
    'geb-educacao-curadoria-mensal-bolsas' => [
        'hora'=>'12:30',
        'titulo'=>'Atualizar a curadoria mensal de bolsas do GEB Educação',
        'area'=>'GEB Educação · Portfólio / Pesquisa',
        'descricao'=>'Pesquisar as bolsas da unidade e atualizar o inventário de oportunidades verificadas para a produção editorial.',
        'observacoes'=>'Início 13/11/2026, recorrência no dia 13 de cada mês. Horário inicial provisório. Examinar separadamente (A) ofertas de instituições em que o GEB atua como polo, com remuneração informada na adesão e nas mensalidades, e (B) ofertas de outras instituições e localidades, com remuneração informada somente na adesão. No buscador do Bolsa Mais Brasil, Instituição é filtro opcional. Priorizar GO/DF e ofertas online antes de ampliar para outras localidades. Na primeira varredura, excluir Técnico por Competência e Idiomas conforme a seleção visual enviada, mas manter Cursos Técnicos e EJA + Técnico. Registrar modalidade, curso, instituição, cidade/UF, formato, adesão, mensalidades, desconto, disponibilidade, link de atribuição à unidade GEB, data de conferência e classificação comercial. Não apresentar levantamento parcial como catálogo completo nem divulgar valores sem revalidação. Gerar shortlist de pautas. Marcar Feito apenas após salvar a curadoria e as evidências.',
        'links'=>[
            ['titulo'=>'Bolsas vinculadas ao polo GEB','url'=>'https://gebeducacao.lovable.app/bolsas'],
            ['titulo'=>'Buscador de bolsas da unidade GEB','url'=>'https://www.bolsamaisbrasil.com.br/geb/bolsas'],
            ['titulo'=>'Acervo de catálogos no Drive','url'=>'https://drive.google.com/drive/folders/14K8rDaJmjUVql9NjF-YLKI4F7gvlEm0N?usp=drive_link']
        ],
        'frequencia'=>'mensal','dia_mes'=>13,'ativo_desde'=>'2026-11-13'
    ],
    'geb-educacao-revisao-editorial-youtube' => [
        'hora'=>'14:00',
        'titulo'=>'Definir a linha editorial integrada do YouTube e Instagram GEB Educação',
        'area'=>'GEB Educação · Estratégia Editorial',
        'descricao'=>'Deliberar e registrar a matriz editorial dos vídeos, segmentação por oferta e canais de distribuição.',
        'observacoes'=>'Execução única em 13/11/2026. Horário provisório. Definir editorias, público, formato, grade, critérios de reaproveitamento, pauta por necessidade educacional, links e CTAs. Distinguir bolsas do polo (adesão e mensalidades), bolsas externas (somente adesão) e cursos livres com precificação própria; nenhuma oferta livre deve ser chamada de bolsa sem lastro. O YouTube Bolsa Mais deve orientar trajetórias educacionais e não somente listar produtos. Considerar como hipótese de alcance o interesse de homens jovens e a assinatura de membro Patrocinador do canal Sobreviva ao Brasil, sem presumir benefício publicitário ou conversão garantida. Confrontar hipóteses com dados do YouTube Studio. Salvar decisões e calendário; marcar Feito apenas após aprovar a linha editorial.',
        'links'=>[
            ['titulo'=>'YouTube Bolsa Mais Trindade','url'=>'https://www.youtube.com/@bolsamais_trindadego'],
            ['titulo'=>'Instagram Bolsa Mais','url'=>'https://www.instagram.com/bolsamais_trindadego/'],
            ['titulo'=>'Instagram Educação EAD Brasil','url'=>'https://www.instagram.com/educacaoeadbrasil/'],
            ['titulo'=>'Canal de referência Sobreviva ao Brasil','url'=>'https://www.youtube.com/@sobrevivaaobrasil'],
            ['titulo'=>'Catálogo histórico de cursos livres','url'=>'https://gebeducacao.lovable.app/cursos']
        ],
        'frequencia'=>'unica','data_unica'=>'2026-11-13','ativo_desde'=>'2026-11-13'
    ],
    'geb-educacao-videos-mensais' => [
        'hora'=>'15:30',
        'titulo'=>'Produzir e agendar vídeos mensais do GEB Educação',
        'area'=>'GEB Educação · Comunicação / Marketing',
        'descricao'=>'Produzir em lote imagens e vídeos no Canva e agendar conteúdo para YouTube, Instagram e Facebook.',
        'observacoes'=>'Início 13/11/2026, recorrência todo dia 13. Horário inicial provisório. Usar a curadoria verificada e a matriz editorial aprovada. Selecionar pautas, validar a origem da oferta (polo, bolsa externa ou curso livre), gerar imagens, preparar roteiro, capa e legenda, montar os vídeos no modelo do Canva, exportar e organizar arquivos no Drive. Agendar YouTube e perfis Instagram adequados; reaproveitar no Facebook quando pertinente. Cada post do Instagram deve gerar espelho correspondente na página /instagram, de modo manual enquanto a automação não for validada. Registrar URLs, datas, horário e canal de destino; conferir agendamento real. Não declarar publicação efetiva apenas pelo agendamento. Marcar Feito somente após completar e conferir a programação do ciclo.',
        'links'=>[
            ['titulo'=>'Uploads no Canva','url'=>'https://www.canva.com/folder/FAHWBMCg_IE'],
            ['titulo'=>'Modelo de vídeo no Canva','url'=>'https://canva.link/0o9u6kvp34dy8nz'],
            ['titulo'=>'Drive de produção GEB Educação','url'=>'https://drive.google.com/drive/folders/1KMV4-41Gn_S5AvAtn1ut9tqwye1N5OCO?usp=drive_link'],
            ['titulo'=>'YouTube Bolsa Mais Trindade','url'=>'https://www.youtube.com/@bolsamais_trindadego'],
            ['titulo'=>'Instagram Educação EAD Brasil','url'=>'https://www.instagram.com/educacaoeadbrasil/'],
            ['titulo'=>'Facebook Educação EAD Brasil','url'=>'https://www.facebook.com/educacaoeadbrasil'],
            ['titulo'=>'Instagram Bolsa Mais','url'=>'https://www.instagram.com/bolsamais_trindadego/'],
            ['titulo'=>'Página da bio GEB Educação','url'=>'https://gebeducacao.grupoeduardabispo.com.br/instagram']
        ],
        'frequencia'=>'mensal','dia_mes'=>13,'ativo_desde'=>'2026-11-13'
    ],
    'geb-educacao-automacao-espelho-bio' => [
        'hora'=>'17:00',
        'titulo'=>'Implementar agendamento do espelho de posts na bio GEB Educação',
        'area'=>'GEB Educação · Tecnologia / Comunicação',
        'descricao'=>'Auditar e implementar publicação programada de cartões correspondentes aos posts do Instagram.',
        'observacoes'=>'Execução única em 13/11/2026; horário provisório. Auditar código, hospedagem, armazenamento e manutenção da página /instagram antes de alterar o funcionamento. Projetar cadastro único de título, imagem, resumo, perfil de origem, URL do destino, data e hora (America/Sao_Paulo), com status Rascunho/Aprovado/Agendado/Publicado/Cancelado. Programar exibição na bio, permitir editar/cancelar, prevenir duplicidades e verificar links. Não presumir que agendamento na bio comprova publicação efetiva no Instagram: confirmação automática requer integração autorizada; adotar conferência manual até validá-la. Priorizar solução de baixo custo sem APIs pagas e prever rollback. Marcar Feito somente após testes de publicação, cancelamento e visualização pública.',
        'links'=>[
            ['titulo'=>'Página de espelhos GEB Educação','url'=>'https://gebeducacao.grupoeduardabispo.com.br/instagram'],
            ['titulo'=>'Instagram Educação EAD Brasil','url'=>'https://www.instagram.com/educacaoeadbrasil/'],
            ['titulo'=>'Instagram Bolsa Mais','url'=>'https://www.instagram.com/bolsamais_trindadego/']
        ],
        'frequencia'=>'unica','data_unica'=>'2026-11-13','ativo_desde'=>'2026-11-13'
    ],
    'eduarda-bispo-agendar-stories-20261211' => [
        'hora'=>'14:00',
        'titulo'=>'Agendar stories de Eduarda Bispo no Instagram e Facebook',
        'area'=>'Eduarda Bispo · Comunicação / Marketing',
        'descricao'=>'Preparar, programar e conferir os stories destinados aos perfis de Eduarda Bispo.',
        'observacoes'=>'Compromisso pontual para 11/12/2026. A solicitação indicou 11/2/2026, data passada em português brasileiro; interpretado como 11/12/2026 por se referir ao próximo agendamento. Horário operacional de 14h provisório, pois não foi informado um horário. Conferir o planejamento e os materiais no Drive; selecionar stories, revisar texto/legendas e dimensões, programar separadamente no Instagram e Facebook e confirmar os dois agendamentos antes de marcar Feito. Registrar na Central Editorial os canais e datas de distribuição. Esta tarefa no Painel não agenda nem publica automaticamente nas redes.',
        'links'=>[
            ['titulo'=>'Pasta de conteúdos Eduarda Bispo','url'=>'https://drive.google.com/drive/folders/1xHpQdCDKUa5RCrC6owR-ITu4OI4-eU3M?usp=drive_link'],
            ['titulo'=>'Instagram Eduarda Bispo','url'=>'https://www.instagram.com/aeduardabispo'],
            ['titulo'=>'Facebook Eduarda Bispo','url'=>'https://www.facebook.com/aeduardabispo/'],
            ['titulo'=>'Central Editorial do Painel','url'=>'https://painel.compassrosesystems.com.br/sistemas/editorial.php'],
        ],
        'frequencia'=>'unica',
        'data_unica'=>'2026-12-11',
        'ativo_desde'=>'2026-12-11'
    ],
];
