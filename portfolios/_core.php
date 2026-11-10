<?php
declare(strict_types=1);
// Shared, server-verified login with /sistemas/. No passwords or client-side authentication.
function port_h(string $value): string {return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function port_auth(): void {
    ini_set('display_errors','0');
    ini_set('session.use_strict_mode','1');
    ini_set('session.use_only_cookies','1');
    ini_set('session.cookie_httponly','1');
    ini_set('session.cookie_secure','1');
    session_name('GEB_PORTAL_SESSION_V2');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('Vary: Cookie');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header("Content-Security-Policy: frame-ancestors 'none'");
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') {http_response_code(403);exit('HTTPS obrigatório.');}
    if (!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)) {http_response_code(405);exit('Método não permitido.');}
    $path=(string)(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)?:'/portfolios/');
    $known=['/portfolios/','/portfolios/ecoar/','/portfolios/geb-empresarial/'];
    $redirect=in_array($path,$known,true)?$path:'/portfolios/';
    if (($_SESSION['auth']??false)!==true || time()-(int)($_SESSION['auth_at']??0)>=28800) {
        header('Location: /sistemas/?next='.rawurlencode($redirect),true,302);
        exit;
    }
    $_SESSION['auth_at']=time();
    session_write_close();
}
function port_begin(string $title,string $subtitle,string $current): void {
  $title=port_h($title);$subtitle=port_h($subtitle);
  $tabs=[
  ['/portfolios/','Visão geral'],
  ['/portfolios/ecoar/','ECOAR'],
  ['/portfolios/geb-empresarial/','GEB Empresarial'],
  ['/sistemas/pop.php','Funil comercial · POP']
  ];
  echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer"><title>'.$title.' | Esteiras GEB</title><link rel="stylesheet" href="/portfolios/assets/style.css?v=3"><script src="/portfolios/assets/app.js?v=3" defer></script></head><body>';
  echo '<a class="skip" href="#main">Pular para conteúdo</a><header class="top"><div class="wide top-inner"><div class="top-brand">GEB <span>·</span> Gestão de Portfólios</div><nav aria-label="Navegação entre braços">';
  foreach($tabs as [$url,$label]){echo '<a href="'.port_h($url).'"'.($url===$current?' aria-current="page"':'').'>'.port_h($label).'</a>';}
  echo '</nav><a class="portal-link" href="/sistemas/">Portal de Aplicações</a></div></header>';
  echo '<main class="wide" id="main"><div class="lead"><p class="kicker">AMBIENTE INTERNO · ACESSO COM SENHA</p><h1>'.$title.'</h1><p class="subtitle">'.$subtitle.'</p></div>';
}
function port_end(): void {
  $csrf=port_h((string)($_SESSION['csrf']??''));
  echo '</main><footer class="foot"><div class="wide foot-grid"><span>Grupo Eduarda Bispo · Catálogos e fluxos internos</span><span>Links do Drive seguem as permissões Google — senha do Painel não as substitui.</span><form method="post" action="/sistemas/"><input type="hidden" name="csrf" value="'.$csrf.'"><button type="submit" name="logout" value="1">Sair da sessão</button></form></div></footer></body></html>';
}
function port_funnel(string $context):void{
 $steps=[
 ['Lead identificado','Origem e potencial interesse registrados.'],
 ['Primeiro contato','Contato ou resposta estabelecida.'],
 ['Prospect qualificado','Necessidade e decisor identificados.'],
 ['Cadastro PF/PJ','Dados recebidos no fluxo autorizado.'],
 ['Validação','Conferência humana dos registros.'],
 ['Vínculo PF/PJ','Responsável e organização relacionados.'],
 ['Oportunidade','Produto e próximo passo definidos.'],
 ['Proposta','Escopo, prazo e condições formalizados.'],
 ['Aceite','Contratação comprovada.'],
 ['Execução','Entrega conforme o produto contratado.'],
 ['Avaliação','Feedback e registros pertinentes.'],
 ['Continuidade','Próxima ação autorizada.']
 ];
 echo '<section class="module" aria-labelledby="funil"><div class="module-title"><div><p class="kicker">MAPA OPERACIONAL · 12 ETAPAS</p><h2 id="funil">Funil comercial visível</h2></div><a class="plain-link" href="/sistemas/pop.php">Abrir POP completo ↗</a></div><p class="muted">'.port_h($context).'</p><ol class="funnel">';
 foreach($steps as $i=>$s){echo '<li><details><summary><span class="step-no">'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'</span><strong>'.port_h($s[0]).'</strong><span class="step-chevron" aria-hidden="true">→</span></summary><p>'.port_h($s[1]).'</p></details></li>';}
 echo '</ol><p class="caution">Este funil mostra as etapas e os pontos de decisão. Não representa dados reais do CRM nem status automático de clientes.</p></section>';
}
