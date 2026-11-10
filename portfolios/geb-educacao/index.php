<?php
require_once dirname(__DIR__).'/_core.php';
port_auth();
port_begin('GEB Educação — Jornada e Sistemas','Da escolha da formação ao cadastro de interesse, triagem, encaminhamento, matrícula e relacionamento.','/portfolios/geb-educacao/');
?>
<section class="module"><div class="module-title"><div><p class="kicker">JORNADA DO CLIENTE · MAPA DO PROCESSO</p><h2>Percurso de ponta a ponta</h2></div><a class="plain-link" href="/sistemas/pop.php">Consultar POP geral ↗</a></div>
<p class="muted">Esta visualização apresenta a sequência operacional e o nível de evidência consultado, não os dados de clientes nem indicadores fictícios de conversão.</p><div class="grid">
<article class="card"><span class="state">01 · Navegação verificada</span><h3>Descoberta e escolha</h3><p>Site institucional, modalidades e encaminhamento para catálogo ou programa.</p></article>
<article class="card"><span class="state">02 · Código e catálogo verificados</span><h3>Busca e comparação</h3><p>Filtros por curso, instituição, nível e área; página de formação.</p></article>
<article class="card"><span class="state">03 · Formulário verificado</span><h3>Registro do interesse</h3><p>Nome, WhatsApp, consentimento, formação e dados opcionais.</p></article>
<article class="card"><span class="state">04 · Chamada implementada</span><h3>Processamento da solicitação</h3><p>POST para Apps Script e resultado na interface; registros dependem de backend.</p></article>
<article class="card"><span class="state">05 · Tratamento em código</span><h3>Preço ou elegibilidade</h3><p>Investimento quando aplicável; técnico por competência requer avaliação prévia.</p></article>
<article class="card"><span class="state">06 · Canal verificado</span><h3>Atendimento humano</h3><p>Contato do GEB Educação, esclarecimento do curso e condições de inscrição.</p></article>
<article class="card"><span class="state">07 · Comprovar registro</span><h3>Encaminhamento</h3><p>Instituição/plataforma responsável, requisitos, documentação e proposta.</p></article>
<article class="card"><span class="state">08 · Comprovar documento</span><h3>Matrícula e pagamento</h3><p>Confirmação formal do operador educacional, registro de valor e situação.</p></article>
<article class="card"><span class="state">09 · Comprovar sistema</span><h3>Acesso e apoio</h3><p>Início da formação, acesso à plataforma e apoio nas atribuições do GEB.</p></article>
<article class="card"><span class="state">10 · Comprovar instrumento</span><h3>Relacionamento</h3><p>Dúvidas, satisfação, necessidades subsequentes e acompanhamento.</p></article>
</div></section>
<section class="module"><p class="kicker">DOCUMENTAÇÃO NO DRIVE</p><h2>Mapas, evidências e acervo operacional</h2>
<div class="quick">
<a href="https://drive.google.com/drive/folders/1KSLKBLrXZQ4jXKqc-z2pT1KCKDV9r68U" target="_blank" rel="noopener noreferrer">Pasta da jornada ↗</a>
<a href="https://docs.google.com/document/d/1PyFctn8bkNYZ0ocAJE-J4nNXR4G5-q99iHUlWDlieFs/edit" target="_blank" rel="noopener noreferrer">Mapa detalhado da jornada ↗</a>
<a href="https://docs.google.com/document/d/14Y-I-YAQdrLMh1Klfs_LxhEIXETOzPFr12293za9G6M/edit" target="_blank" rel="noopener noreferrer">Sistemas, dados e evidências ↗</a>
<a href="https://docs.google.com/document/d/1Mn9R94bPPlQbDEZPJNgDM7BBA5vun5bh_bqr23got4A/edit" target="_blank" rel="noopener noreferrer">Índice geral das jornadas ↗</a>
</div><p class="caution">O compartilhamento dos documentos continua controlado pelo Google Drive; o login do Painel não altera permissões.</p></section>
<section class="module"><p class="kicker">SISTEMAS DO BRAÇO</p><h2>Canais e registros de origem</h2><div class="quick"><a href="https://gebeducacao.grupoeduardabispo.com.br/" target="_blank" rel="noopener noreferrer">Site GEB Educação ↗</a>
<a href="https://gebeducacao.grupoeduardabispo.com.br/catalogo/" target="_blank" rel="noopener noreferrer">Catálogo de formações ↗</a>
<a href="https://gebeducacao.grupoeduardabispo.com.br/contato/" target="_blank" rel="noopener noreferrer">Canal de atendimento ↗</a>
<a href="https://github.com/aeduardasantana/gebeducacao" target="_blank" rel="noopener noreferrer">Repositório ↗</a></div>
<div class="strip"><strong>Observação operacional</strong><p>Registro de interesse, preço e elegibilidade são diferentes de matrícula. A interface possui uma contingência de confirmação após aproximadamente nove segundos; verificar a gravação no backend antes de dar um lead como recebido.</p></div></section>
<?php port_end(); ?>
