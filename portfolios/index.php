<?php
require_once __DIR__.'/_core.php';
port_auth();
port_begin('Portfólios e funis por braço','Uma página central para localizar cada braço. Em cada área, encontre produtos, evidências, documentos e o fluxo comercial correspondente.','/portfolios/');
port_funnel('Fluxo de referência do POP 001. As etapas não representam um pipeline de oportunidades sincronizado.');
?>
<section class="module" aria-labelledby="bracos"><div class="module-title"><div><p class="kicker">NAVEGAÇÃO POR BRAÇO</p><h2 id="bracos">Escolha uma área</h2></div><a class="plain-link" href="/sistemas/pop.php">POP comercial ↗</a></div>
<div data-catalog>
<div class="filters"><div class="field"><label for="buscar-braco">Buscar por braço, serviço ou palavra-chave</label><input id="buscar-braco" type="search" placeholder="Ex.: ECOAR, oratória, empresarial" data-search-input autocomplete="off"></div></div>
<div class="result-count" role="status" aria-live="polite" data-result-count></div>
<div class="grid">
<article class="card featured" data-item data-search="ECOAR laboratorio oratoria bilíngue libras cursos livro guia mentoria seu palco organizações" data-type="ecoar" data-status="estruturado"><span class="state">6 produtos documentados · Homologação comercial pendente</span><h3>ECOAR</h3><p>Catálogo completo, fichas individuais, Jornada como metodologia e links para o site, repositório e Drive. Histórico de laboratório disponível; os demais itens têm pendências identificadas.</p><div class="links"><a href="/portfolios/ecoar/">Abrir área ECOAR ↗</a></div></article>
<article class="card" data-item data-search="GEB Empresarial gestão processos lideranças equipe nr-1 palestras administrativo diagnóstico" data-type="empresarial" data-status="reservado"><span class="state pending">Estrutura reservada · Não homologada</span><h3>GEB Empresarial</h3><p>Ainda em organização. A página concentra as referências e a pasta reservada, mas não apresenta a esteira como portfólio comercial aprovado.</p><div class="links"><a href="/portfolios/geb-empresarial/">Abrir área GEB Empresarial ↗</a></div></article>
</div><p class="empty" data-empty hidden>Nenhum braço corresponde à busca.</p></div>
</section>
<section class="module"><p class="kicker">GOVERNANÇA</p><h2>Uma fonte para cada informação</h2><div class="grid"><article class="card"><h3>Documentos e evidências</h3><p>Fichas e acervos permanecem no Drive, sujeitos às permissões de cada arquivo. O Painel reúne atalhos, não duplica dados pessoais.</p></article><article class="card"><h3>Funil x Portfólio</h3><p>O funil acompanha a trajetória comercial. A esteira indica o que pode ser oferecido. A disponibilidade real de cada produto depende de homologação própria.</p></article></div></section>
<?php port_end(); ?>
