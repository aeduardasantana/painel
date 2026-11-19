<?php
declare(strict_types=1);
/** Catálogo de links do Painel Mestre — mantém a mesma sessão protegida do Portal. */
ini_set('display_errors','0');
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_secure','1');
session_name('GEB_PORTAL_SESSION_V2');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; script-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {http_response_code(403);exit('HTTPS obrigatório.');}
if (($_SESSION['auth'] ?? false) !== true || time() - (int)($_SESSION['auth_at'] ?? 0) >= 28800) {
 header('Location: /sistemas/',true,302);exit;
}
$_SESSION['auth_at']=time();
$groups=require __DIR__.'/catalogo-links.php';
if (!is_array($groups)) {http_response_code(503);exit('Catálogo temporariamente indisponível.');}
function cEsc(string $str): string {return htmlspecialchars($str,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$counts=[];$total=0;
foreach ($groups as $group) {$count=count($group['items']??[]);$counts[$group['key']]=$count;$total+=$count;}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="color-scheme" content="dark">
<title>Sites, LPs e Tecnologias | Painel Mestre GEB</title>
<meta name="description" content="Catálogo de links dos braços GEB, LPs, programas e aplicações do GEB Tecnologia. Acesso interno.">
<link rel="stylesheet" href="/sistemas/catalogo.css?v=20261119-01">
<script src="/sistemas/catalogo.js?v=20261119-01" defer></script>
</head>
<body>
<a href="#conteudo" class="skip">Ir para o catálogo</a>
<header class="header"><div class="container header__line">
<a class="brand" href="/sistemas/"><span class="brand__eyebrow">PAINEL MESTRE GEB</span><span>Sites, LPs e Tecnologias</span></a>
<a class="header__back" href="/sistemas/">Voltar ao Portal de Aplicações</a>
</div></header>
<main id="conteudo" class="container">
<section class="hero"><p class="kicker">CATÁLOGO CENTRAL DE ACESSOS</p><h1>Um lugar para encontrar as páginas e soluções do GEB.</h1>
<p class="lead">Consulte os sites dos braços, oportunidades, páginas de campanha, programas, ferramentas de operação e tecnologias desenvolvidas no ecossistema. Cada serviço mantém suas próprias permissões e responsabilidades.</p>
<div class="search-area"><label for="pesquisa">Buscar por nome, área, serviço, ferramenta ou endereço</label><input id="pesquisa" type="search" placeholder="Ex.: assessoria de marketing, Agenda Social, NR-1, Libras..." autocomplete="off" spellcheck="false"><span class="search-hint">Buscar</span></div>
<p class="count" id="contador" aria-live="polite"><?= $total ?> endereços catalogados</p></section>
<nav class="quicknav" aria-label="Categorias do catálogo"><?php foreach($groups as $group): ?>
<a href="#<?=cEsc($group['key'])?>"><?=cEsc($group['title'])?> <span><?= (int)$counts[$group['key']] ?></span></a>
<?php endforeach ?></nav>
<?php foreach($groups as $group):
 $region=$group['key']==='nr1-regionais'; ?>
<?php if($region): ?><details class="group group--regional" id="<?=cEsc($group['key'])?>" data-group><summary class="group__heading"><span><span class="group__eyebrow">COBERTURA REGIONAL</span><strong><?=cEsc($group['title'])?></strong><small><?=cEsc($group['subtitle'])?></small></span><span class="group__count"><?=count($group['items'])?> regiões <span aria-hidden="true">+</span></span></summary>
<?php else: ?><section class="group" id="<?=cEsc($group['key'])?>" data-group><div class="group__heading"><span><span class="group__eyebrow">CATÁLOGO GEB</span><strong><?=cEsc($group['title'])?></strong><small><?=cEsc($group['subtitle'])?></small></span><span class="group__count"><?=count($group['items'])?> acessos</span></div><?php endif ?>
<div class="cards">
<?php foreach($group['items'] as $item):
 $url=(string)$item['url'];$external=str_starts_with($url,'https://');
 if (!$external && !str_starts_with($url,'/')) continue;
 $label=(string)$item['title'];$desc=(string)$item['description'];$type=(string)$item['type'];
 $haystack=implode(' ',[$label,$desc,$type,$url,$group['title']]);
 ?>
<article class="card" data-search="<?=cEsc($haystack)?>">
<div class="card__meta"><span><?=cEsc($type)?></span><span aria-hidden="true">GEB / <?=cEsc($group['key'])?></span></div>
<h2><a href="<?=cEsc($url)?>" <?= $external?'target="_blank" rel="noopener noreferrer"':'' ?> aria-label="Abrir <?=cEsc($label)?><?= $external?' em nova aba':'' ?>"><?=cEsc($label)?><span aria-hidden="true">↗</span></a></h2>
<p><?=cEsc($desc)?></p>
<small><?=cEsc($url)?></small>
</article>
<?php endforeach ?>
</div>
<?php if($region): ?></details><?php else: ?></section><?php endif ?>
<?php endforeach ?>
<p id="sem-resultados" class="no-results" role="status" hidden>Nenhum endereço encontrado. Tente outra palavra.</p>
<aside class="notice"><h2>Como interpretar este catálogo</h2><p>Os links foram organizados a partir dos endereços documentados nos sites, repositórios e registros de trabalho. A presença nesta lista <strong>não comprova disponibilidade em tempo real</strong>, ativação de um produto ou permissão de acesso. Projetos sem URL de publicação confirmada apontam explicitamente para seus repositórios. Não são exibidos dados de clientes nem credenciais.</p></aside>
</main>
<footer class="footer"><div class="container"><span>GEB Tecnologia · Painel Mestre · Catálogo de acessos</span><span>Revisão documental: 19/11/2026</span></div></footer>
</body></html>
