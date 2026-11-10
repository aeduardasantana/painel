<?php
require_once dirname(__DIR__).'/_core.php';
port_auth();
$products=[
 ['EC-01','Guia ECOAR','Digital','pendente','Livro e fluxo de entrega pendentes','Livro digital de preparação de apresentações.','1zHhk2LsLNmqZoLGQ-7WrsAPJs5g2BPtT','1dkm72gClVGdhHAWm2rTlNhkUHuS7XL6iM1jQ6WLOTSA','/aprender/'],
 ['EC-02','ECOAR Essencial','Digital','pendente','Curso gravado e acesso pendentes','Formação em vídeo com exercícios de comunicação.','1uJBXK4dJpEh1gnRO5_6Oazm5ws7tG6Z-','18_z2PBwZd59Rmc637Byb3alyvsmWyYNvNyqwYgmcrd8','/aprender/'],
 ['EC-03','ECOAR Lab','Coletivo','historico','Experiência histórica; próxima agenda pendente','Participação como público ou orador com prática e feedback.','1tM5mMUoBB_t0WWcInf6xNVpwWLhe_7FW','15e6rrf9IHSi75-XHib3260ljGdhPs-y9GHFUZnXs3vA','/laboratorio/'],
 ['EC-04','ECOAR Seu Palco','Projeto','pendente','Contrato e operação pendentes','Orientação ou coprodução para apresentações do cliente.','1z4hS_5n03JNwivieugFx9pLb_YpDso_c','1l1tQIP3cY3Dumy8GerpoY9TRM-urnwz_ijV6_hVbbzI','/seu-palco/'],
 ['EC-05','Mentoria ECOAR','Individual','pendente','Agenda e condições pendentes','Acompanhamento em quatro ou oito encontros.','1LjCtkgZKkli3fZnG36XcLyYdHnsY4Yhn','1ohTtEspjwCQR4PuB-sR3G64aHSelGzLVyNZG92NKje0','/mentoria/'],
 ['EC-06','ECOAR Organizações','Institucional','pendente','Proposta e capacidade pendentes','Laboratórios para equipes e instituições.','1Ul-Wdogi2db1AkOzoXZYaQmrRUbn2xKr','1YaQsH-JhZsc-dw_AfMf-AlwJL-JA0nbUBl_i_P3PbHE','/organizacoes/']
];
port_begin('ECOAR — Portfólio e funil','Seis produtos comerciais independentes, documentos próprios, evidências e metodologia transversal, reunidos para consulta e organização.','/portfolios/ecoar/');
port_funnel('No ECOAR, o produto é definido na etapa de Oportunidade. Contrato, data, equipe e recursos são confirmados antes da Execução. Não há integração automática com o CRM nesta tela.');
?>
<section class="module"><p class="kicker">ACESSOS OFICIAIS</p><h2>Documentos, site e implementação</h2>
<div class="quick">
<a href="https://docs.google.com/document/d/1YSbKcxqw3aptj-u7i4Ps-e2NK7T62IYjucS9Xd9tzsA/edit" target="_blank" rel="noopener noreferrer">Esteira mestre no Drive ↗</a>
<a href="https://drive.google.com/drive/folders/1m_uKGpfHZM8owriuGCJgQPuOUDFuPtBo" target="_blank" rel="noopener noreferrer">Pasta ECOAR ↗</a>
<a href="https://ecoar.grupoeduardabispo.com.br/" target="_blank" rel="noopener noreferrer">Site ECOAR ↗</a>
<a href="https://github.com/aeduardasantana/ecoar" target="_blank" rel="noopener noreferrer">Repositório ECOAR ↗</a>
<a href="https://docs.google.com/document/d/1M1mSqlGR_vX_pCbSI3cw9w35j_oYY4kepFo9Ue3Jk2M/edit" target="_blank" rel="noopener noreferrer">Catálogo mestre anterior ↗</a>
</div></section>
<section class="module" aria-labelledby="produtos"><div class="module-title"><div><p class="kicker">ESTEIRA COMERCIAL · 6 OFERTAS</p><h2 id="produtos">Produtos, fichas e pendências</h2></div><span class="muted">Situação documentada em novembro de 2026</span></div>
<div data-catalog>
<div class="filters"><div class="field"><label for="ecoar-q">Buscar por produto, entrega ou pendência</label><input id="ecoar-q" type="search" autocomplete="off" placeholder="Ex.: curso, Libras, evento" data-search-input></div><div class="field sm"><label for="ecoar-tipo">Tipo de produto</label><select id="ecoar-tipo" data-type-input><option value="todos">Todos os tipos</option><option value="Digital">Digital</option><option value="Coletivo">Coletivo</option><option value="Projeto">Projeto</option><option value="Individual">Individual</option><option value="Institucional">Institucional</option></select></div><div class="field sm"><label for="ecoar-status">Situação</label><select id="ecoar-status" data-status-input><option value="todos">Todas as situações</option><option value="historico">Experiência anterior documentada</option><option value="pendente">Entrega/operação pendente</option></select></div></div>
<div class="result-count" data-result-count aria-live="polite" role="status"></div>
<div class="products">
<?php foreach($products as [$code,$name,$type,$status,$note,$desc,$folder,$doc,$site]): ?>
<article class="product <?= $status==='historico'?'historical':'' ?>" data-item data-type="<?=port_h($type)?>" data-status="<?=port_h($status)?>" data-search="<?=port_h($code.' '.$name.' '.$type.' '.$note.' '.$desc)?>">
<span class="code"><?=port_h($code)?> · <?=port_h($type)?></span><h3><?=port_h($name)?></h3><p><?=port_h($desc)?></p><p class="status"><?=port_h($note)?></p>
<div class="links"><a href="https://docs.google.com/document/d/<?=port_h($doc)?>/edit" target="_blank" rel="noopener noreferrer">Ficha e pendências ↗</a><a href="https://drive.google.com/drive/folders/<?=port_h($folder)?>" target="_blank" rel="noopener noreferrer">Pasta do produto ↗</a><a href="https://ecoar.grupoeduardabispo.com.br<?=port_h($site)?>" target="_blank" rel="noopener noreferrer">Página comercial ↗</a></div>
</article>
<?php endforeach; ?>
</div><p class="empty" data-empty hidden>Nenhum produto corresponde aos filtros selecionados.</p></div></section>
<section class="module"><p class="kicker">BASE METODOLÓGICA · SEM PRODUTO EC-07</p><h2>Jornada ECOAR</h2><div class="strip"><strong>A Jornada é metodologia, não uma sétima venda.</strong><p>Doze temas e três níveis orientam atividades do laboratório, mentoria e programas institucionais. Não há preço, assinatura, agenda obrigatória ou CTA comercial independente.</p><div class="links"><a href="https://docs.google.com/document/d/10zQYWXJJpD6K6-uqv71IZeSz7vu6dIRJmzODLGNj7DI/edit" target="_blank" rel="noopener noreferrer">Documento metodológico ↗</a><a href="https://drive.google.com/drive/folders/1NyVanndwC3ZyqIqXUIWrDKLTYSE-lbrh" target="_blank" rel="noopener noreferrer">Pasta de metodologia ↗</a><a href="https://ecoar.grupoeduardabispo.com.br/metodologia/" target="_blank" rel="noopener noreferrer">Explicação no site ↗</a></div></div></section>
<section class="module"><p class="kicker">EXECUÇÃO E REGISTROS</p><h2>Relação com o atendimento</h2><div class="grid"><article class="card"><h3>Início comercial</h3><p>Todos os CTAs públicos do ECOAR encaminham ao WhatsApp (11) 2110-5473. A conversa não é cadastro de cliente nem contrato até a qualificação e o aceite.</p><div class="links"><a href="/sistemas/pop.php">POP · Jornada de atendimento ↗</a></div></article><article class="card"><h3>Disponibilidade real</h3><p>Livro, curso, contratos, agenda, equipe, valores e condições devem ser comprovados antes da oferta como pronta. A documentação registra pendências por produto.</p><div class="links"><a href="https://drive.google.com/drive/folders/1K4attDzcNCh0D8_Z3KEM4x3ViklPjhBM" target="_blank" rel="noopener noreferrer">Acervo estratégico histórico ↗</a></div></article></div></section>
<?php port_end(); ?>
