<?php
declare(strict_types=1);
ini_set('display_errors','0');
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_secure','1');
session_name('GEB_PORTAL_SESSION_V2');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Cache-Control: no-store,no-cache,must-revalidate,private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') {http_response_code(403);exit('HTTPS obrigatório.');}
if (($_SESSION['auth']??false)!==true || time()-(int)($_SESSION['auth_at']??0)>=28800) {
    header('Location: /sistemas/',true,302);exit;
}
$_SESSION['auth_at']=time();
require __DIR__.'/rotinas-recorrencias.php';
require __DIR__.'/rotinas-versao.php';
$appVersion = pm_build_version();
$tasks=require __DIR__.'/rotinas-catalogo.php';
$period=(int)($_GET['periodo']??30);
if (!in_array($period,[7,30,90],true)) $period=30;
$tz=new DateTimeZone('America/Sao_Paulo');
$now=new DateTimeImmutable('now',$tz);
$today=new DateTimeImmutable('today',$tz);
$store=__DIR__.'/.rotinas-gestao.json';
$entries=[];
if (is_file($store)) {
    $handle=@fopen($store,'r');
    if (!$handle || !flock($handle,LOCK_SH)) {http_response_code(503);exit('Histórico indisponível.');}
    $raw=stream_get_contents($handle);
    flock($handle,LOCK_UN);fclose($handle);
    $entries=json_decode((string)$raw,true);
    if (!is_array($entries) || json_last_error()!==JSON_ERROR_NONE) {
        http_response_code(503);exit('Não foi possível ler os indicadores. Nenhum registro foi modificado.');
    }
}
function pm_e(string $str): string {return htmlspecialchars($str,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function pm_new(): array {return ['feito'=>0,'pendente'=>0,'cancelado'=>0,'sem'=>0,'total'=>0];}
function pm_add(array &$row,string $state): void {
    if ($state==='feito')++$row['feito'];
    elseif ($state==='pendente')++$row['pendente'];
    elseif ($state==='cancelado')++$row['cancelado'];
    else ++$row['sem'];
    ++$row['total'];
}
function pm_percent(int $n,int $total): float {return $total?round(100*$n/$total,2):0.0;}
function pm_bar(array $row): string {
    $den=$row['total'];$parts='';
    foreach (['feito','pendente','cancelado','sem'] as $s) {
        $p=pm_percent($row[$s],$den);
        if ($p>0) $parts.='<span class="bar-'.$s.'" style="width:'.$p.'%"></span>';
    }
    return $parts;
}
$overall=pm_new();$dayRows=[];$areaRows=[];$weekRows=[];$taskRows=[];
$start=$today->modify('-'.($period-1).' days');
for ($offset=0;$offset<$period;$offset++) {
    $day=$start->modify('+'.$offset.' days');
    $key=$day->format('Y-m-d');
    $dayRow=pm_new();
    foreach($tasks as $id=>$task) {
        if (!pm_todayDue($task,$day,$now)) continue;
        $status=(string)($entries[$key][$id]['status']??'');
        if (!in_array($status,['feito','pendente','cancelado'],true))$status='sem';
        $area=(string)$task['area'];
        if (!isset($areaRows[$area]))$areaRows[$area]=pm_new();
        if (!isset($taskRows[$id]))$taskRows[$id]=pm_new();
        $week=$day->format('o-\WW');
        if (!isset($weekRows[$week])) $weekRows[$week]=pm_new();
        pm_add($overall,$status);
        pm_add($dayRow,$status);
        pm_add($areaRows[$area],$status);
        pm_add($weekRows[$week],$status);
        pm_add($taskRows[$id],$status);
    }
    $dayRows[$key]=$dayRow;
}
ksort($areaRows);ksort($weekRows);
$visibleDays=array_slice($dayRows,-min(14,$period),null,true);
$visibleWeeks=array_slice($weekRows,-min(8,count($weekRows)),null,true);
$executables=$overall['total']-$overall['cancelado'];
$doneRate=pm_percent($overall['feito'],$executables);
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="app-version" content="<?=htmlspecialchars($appVersion,ENT_QUOTES,'UTF-8')?>">
<script src="/sistemas/rotinas-update.js?v=<?=htmlspecialchars(substr($appVersion,0,12),ENT_QUOTES,'UTF-8')?>" defer></script>
<title>Indicadores de execução | Painel Mestre GEB</title>
<style>
:root{color-scheme:light;--ink:#141c16;--muted:#59635c;--line:#d5ddd4;--bg:#f5f6f3;--lime:#cdea52;--done:#26713d;--pending:#ae571a;--cancelled:#47677b;--unmarked:#a4aea6}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.5 system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
header,main{max-width:1120px;margin:0 auto;padding:18px}
header{display:flex;justify-content:space-between;align-items:center;gap:14px;border-bottom:1px solid var(--line)}
a{color:inherit}a:focus-visible,button:focus-visible{outline:3px solid #237245;outline-offset:3px}
.brand{font-size:12px;letter-spacing:.075em;text-transform:uppercase;font-weight:800}
h1{font-size:clamp(28px,4vw,42px);letter-spacing:-.035em;margin:8px 0}
h2{font-size:19px;letter-spacing:-.015em;margin:0 0 6px}
.intro{color:var(--muted);margin:0 0 18px}.eyebrow{font-size:11px;font-weight:800;color:#415d3e;text-transform:uppercase;letter-spacing:.1em}

.app-update-banner{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:#f4f8e7;border:1px solid #9da785;border-left:5px solid #778b39;border-radius:4px;padding:10px 13px;margin:14px 0}
.app-update-banner strong{font-size:14px}
.app-update-banner p{font-size:12px;color:#59635c;margin:2px 0}
.app-update-actions{display:flex;gap:8px}
.app-update-actions button,.app-update-check{font:inherit;font-size:12px;padding:7px 10px;border:1px solid #88928a;background:#fff;border-radius:4px;cursor:pointer}
.app-update-actions .primary{background:var(--lime)}
.update-tools{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;color:var(--muted)}
[hidden]{display:none!important}
.filters{display:flex;gap:8px;flex-wrap:wrap;margin:20px 0}
.filters a{padding:8px 13px;background:#fff;border:1px solid var(--line);border-radius:4px;text-decoration:none;font-weight:700}
.filters a[aria-current="true"]{background:var(--lime);border-color:#6e811e}
.kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:16px}
.kpi{border:1px solid var(--line);border-radius:5px;padding:15px;background:#fff}
.kpi b{display:block;font-size:28px;line-height:1.1;font-variant-numeric:tabular-nums}
.kpi span{font-size:12px;color:var(--muted)}
.kpi.done{border-top:4px solid var(--done)}.kpi.pending{border-top:4px solid var(--pending)}.kpi.cancelled{border-top:4px solid var(--cancelled)}.kpi.unmarked{border-top:4px solid var(--unmarked)}
.panel{background:#fff;border:1px solid var(--line);border-radius:5px;padding:18px;margin:15px 0}
.panel p{color:var(--muted);font-size:12px;margin:0 0 13px}
.chart{display:grid;gap:12px;margin-top:16px}
.metric-row{display:grid;grid-template-columns:160px minmax(120px,1fr) 112px;gap:12px;align-items:center}
.metric-row.compact{grid-template-columns:70px minmax(130px,1fr) 112px}
.metric-label{font-weight:650;line-height:1.25;overflow-wrap:anywhere}
.metric-label small{display:block;color:var(--muted);font-weight:400}
.bar{display:flex;width:100%;height:15px;background:#edf0ed;border:1px solid #dce2dc;overflow:hidden;border-radius:2px}
.bar span{height:100%;display:block}.bar-feito{background:var(--done)}.bar-pendente{background:var(--pending)}.bar-cancelado{background:var(--cancelled)}.bar-sem{background:var(--unmarked)}
.metric-count{text-align:right;font-size:12px;color:#48554b;font-variant-numeric:tabular-nums}
.legend{display:flex;gap:15px;flex-wrap:wrap;font-size:12px;color:#425148;margin:8px 0}
.dot{display:inline-block;width:10px;height:10px;margin-right:5px;border-radius:2px}
.note{background:#edf1ec;border-left:3px solid #88998a;font-size:12px;color:#374c3a;padding:12px;margin:22px 0}
footer{font-size:12px;color:var(--muted);margin:30px 0;padding-top:15px;border-top:1px solid var(--line)}
@media(max-width:730px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.metric-row,.metric-row.compact{grid-template-columns:95px minmax(60px,1fr) 64px;gap:7px}.metric-label{font-size:11px}.metric-count{font-size:11px}.panel{padding:13px}}
</style></head><body>
<header><span class="brand">GEB · Painel Mestre</span><a href="/sistemas/rotinas.php">Voltar às rotinas</a></header>
<main>
<span class="eyebrow">Acompanhamento executivo</span>
<h1>Desempenho das rotinas</h1>
<p class="intro">Indicadores baseados nas marcações reais e nos horários já vencidos. Não incluem tarefas futuras.</p>
<div class="update-tools">
  <button type="button" class="app-update-check" id="app-update-check">Verificar versão</button>
  <span id="app-update-status" role="status">Verificando versão...</span>
</div>
<div id="app-update-banner" class="app-update-banner" role="status" hidden>
  <div><strong>Nova versão disponível</strong><p>Atualize quando terminar a consulta.</p></div>
  <div class="app-update-actions">
    <button type="button" class="primary" id="app-update-now">Atualizar agora</button>
    <button type="button" id="app-update-later">Depois</button>
  </div>
</div>
<nav class="filters" aria-label="Selecionar período">
<?php foreach([7,30,90] as $d): ?><a href="?periodo=<?=$d?>" <?=$period===$d?'aria-current="true"':''?>><?=$d?> dias</a><?php endforeach;?>
</nav>
<div class="kpis">
<div class="kpi"><b><?=$overall['total']?></b><span>Compromissos previstos até agora</span></div>
<div class="kpi done"><b><?=$overall['feito']?></b><span>Concluídos · <?=number_format($doneRate,1,',','.')?>% dos não cancelados</span></div>
<div class="kpi pending"><b><?=$overall['pendente']?></b><span>Marcados como pendentes</span></div>
<div class="kpi cancelled"><b><?=$overall['cancelado']?></b><span>Cancelados</span></div>
<div class="kpi unmarked"><b><?=$overall['sem']?></b><span>Sem marcação</span></div>
</div>
<div class="legend" aria-label="Legenda">
<span><i class="dot" style="background:var(--done)"></i>Concluído</span>
<span><i class="dot" style="background:var(--pending)"></i>Pendente</span>
<span><i class="dot" style="background:var(--cancelled)"></i>Cancelado</span>
<span><i class="dot" style="background:var(--unmarked)"></i>Sem marcação</span>
</div>
<section class="panel"><h2>Execução por dia</h2><p>Últimos <?=count($visibleDays)?> dias do período; barra proporcional às tarefas do próprio dia.</p>
<div class="chart">
<?php foreach($visibleDays as $day=>$row): ?>
<div class="metric-row compact">
<span class="metric-label"><?=pm_e((new DateTimeImmutable($day))->format('d/m'))?></span>
<div class="bar" role="img" aria-label="<?=pm_e($day)?>: <?=$row['feito']?> concluídas, <?=$row['pendente']?> pendentes, <?=$row['cancelado']?> canceladas, <?=$row['sem']?> sem marcação"><?=pm_bar($row)?></div>
<span class="metric-count"><?=$row['feito']?> de <?=$row['total']?> feitas</span>
</div>
<?php endforeach; ?>
</div></section>
<section class="panel"><h2>Execução por braço e função</h2><p>Distribuição de compromissos previstos no período selecionado. Contagens operacionais, não faturamento nem resultado clínico.</p>
<div class="chart">
<?php foreach($areaRows as $area=>$row): ?>
<div class="metric-row">
<span class="metric-label"><?=pm_e($area)?><small><?=number_format(pm_percent($row['feito'],$row['total']),0,',','.')?>% concluídas</small></span>
<div class="bar" role="img" aria-label="<?=pm_e($area)?>: <?=$row['feito']?> concluídas de <?=$row['total']?> compromissos"><?=pm_bar($row)?></div>
<span class="metric-count"><?=$row['feito']?> de <?=$row['total']?> feitas</span>
</div>
<?php endforeach; ?>
<?php if (!$areaRows): ?><p>Não há compromissos com horário vencido neste intervalo.</p><?php endif; ?>
</div></section>
<section class="panel"><h2>Evolução semanal</h2><p>Semanas com compromissos previstos dentro do período selecionado.</p>
<div class="chart">
<?php foreach($visibleWeeks as $week=>$row): ?>
<div class="metric-row compact">
<span class="metric-label"><?=pm_e(str_replace('-W',' · sem. ',$week))?></span>
<div class="bar" role="img" aria-label="<?=pm_e($week)?>: <?=$row['feito']?> concluídas, <?=$row['pendente']?> pendentes, <?=$row['cancelado']?> canceladas, <?=$row['sem']?> sem marcação"><?=pm_bar($row)?></div>
<span class="metric-count"><?=$row['feito']?> de <?=$row['total']?></span>
</div>
<?php endforeach; ?>
<?php if (!$visibleWeeks): ?><p>Sem semanas com compromissos vencidos no intervalo.</p><?php endif; ?>
</div></section>
<p class="note"><strong>Como interpretar:</strong> Cancelado é uma ocorrência não executada por decisão/impedimento, não é contado como concluída nem no denominador da taxa de execução; o total de compromissos previstos ainda inclui cancelamentos.  “Sem marcação” significa ausência de confirmação no painel, não ausência, atraso ou falha de atendimento. Nas rotinas assistenciais, a conclusão é uma marcação administrativa, não avaliação clínica. Consultas canceladas ou remarcadas precisam ser conferidas na Amplimed. O painel não armazena nomes de pacientes.</p>
<footer>Indicadores gerados exclusivamente a partir do histórico protegido do Painel Mestre, no horário de Brasília. O SGG e os dados clínicos não são alterados.</footer>
</main></body></html>
