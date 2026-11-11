<?php
declare(strict_types=1);

/* Painel Mestre · Rotinas de execução.
 * Dados são gravados no servidor; não são enviados ao GitHub.
 * Requer a sessão autenticada do Portal de Aplicações.
 */
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
session_name('GEB_PORTAL_SESSION_V2');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'self'; connect-src 'self'; worker-src 'self'; manifest-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403); exit('HTTPS obrigatório.');
}
if (($_SESSION['auth'] ?? false) !== true || time() - (int)($_SESSION['auth_at'] ?? 0) >= 28800) {
    header('Location: /sistemas/', true, 302); exit;
}
$_SESSION['auth_at'] = time();
if (!isset($_SESSION['rotinas_csrf'])) $_SESSION['rotinas_csrf'] = bin2hex(random_bytes(24));

$tz = new DateTimeZone('America/Sao_Paulo');
$today = new DateTimeImmutable('today', $tz);
$tasks = require __DIR__ . '/rotinas-catalogo.php';

function validDay(string $value, DateTimeZone $tz): ?DateTimeImmutable {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) return null;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $tz);
    if (!$parsed || $parsed->format('Y-m-d') !== $value) return null;
    $year = (int)$parsed->format('Y');
    if ($year < 2020 || $year > 2100) return null;
    return $parsed;
}
function esc(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function scheduled(array $task, DateTimeImmutable $date): bool {
    return in_array((int)$date->format('N'), $task['dias'] ?? [], true);
}
function countScheduled(array $tasks, DateTimeImmutable $date): int {
    $n = 0;
    foreach ($tasks as $task) if (scheduled($task, $date)) ++$n;
    return $n;
}
function readEntries($handle): array {
    rewind($handle);
    $raw = stream_get_contents($handle);
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(503); exit('Não foi possível interpretar os registros. Nenhum registro foi alterado.');
    }
    return $data;
}
function openStore(string $path, string $mode) {
    $handle = @fopen($path, $mode);
    if (!$handle) {
        http_response_code(503); exit('Armazenamento indisponível. Nenhuma marcação foi salva. Verifique as permissões do servidor.');
    }
    return $handle;
}

$requested = (string)($_POST['data'] ?? $_GET['data'] ?? $today->format('Y-m-d'));
$selected = validDay($requested, $tz) ?? $today;
$key = $selected->format('Y-m-d');
$store = __DIR__ . '/.rotinas-gestao.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    $task = (string)($_POST['tarefa'] ?? '');
    $status = (string)($_POST['status'] ?? '');
    if (!hash_equals($_SESSION['rotinas_csrf'], $csrf) || !array_key_exists($task, $tasks)
        || !scheduled($tasks[$task], $selected) || !in_array($status, ['feito','pendente','limpar'], true)) {
        http_response_code(400); exit('Solicitação inválida. Nenhuma marcação foi alterada.');
    }
    $handle = openStore($store, 'c+');
    if (!flock($handle, LOCK_EX)) {
        fclose($handle); http_response_code(503); exit('Não foi possível bloquear o histórico para escrita.');
    }
    $entries = readEntries($handle);
    if ($status === 'limpar') {
        unset($entries[$key][$task]);
        if (empty($entries[$key])) unset($entries[$key]);
    } else {
        $entries[$key][$task] = [
            'status' => $status,
            'alterado_em' => (new DateTimeImmutable('now', $tz))->format(DATE_ATOM)
        ];
    }
    $encoded = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        flock($handle, LOCK_UN); fclose($handle);
        http_response_code(503); exit('Falha na codificação. Nenhuma marcação foi salva.');
    }
    rewind($handle);
    $ok = ftruncate($handle, 0) && fwrite($handle, $encoded) === strlen($encoded) && fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    if (!$ok) {
        http_response_code(503); exit('Falha ao salvar a marcação. Confirme o estado antes de tentar novamente.');
    }
    header('Location: /sistemas/rotinas.php?data=' . rawurlencode($key) . '&salvo=1', true, 303);
    exit;
}
$entries = [];
if (is_file($store)) {
    $handle = openStore($store, 'r');
    if (!flock($handle, LOCK_SH)) {
        fclose($handle); http_response_code(503); exit('Não foi possível consultar o histórico.');
    }
    $entries = readEntries($handle);
    flock($handle, LOCK_UN); fclose($handle);
}
$dayRecords = $entries[$key] ?? [];
$total = countScheduled($tasks, $selected);
$done = 0;
$pending = 0;
foreach ($tasks as $id => $_task) {
    $state = (string)($dayRecords[$id]['status'] ?? '');
    if ($state === 'feito' && scheduled($_task, $selected)) ++$done;
    if ($state === 'pendente' && scheduled($_task, $selected)) ++$pending;
}
$unmarked = $total - $done - $pending;
$days = ['domingo','segunda-feira','terça-feira','quarta-feira','quinta-feira','sexta-feira','sábado'];
$weekdayName = $days[(int)$selected->format('w')];
$prev = $selected->modify('-1 day')->format('Y-m-d');
$next = $selected->modify('+1 day')->format('Y-m-d');
$history = [];
$cursor = $today;
while (count($history) < 10) {
    $dk = $cursor->format('Y-m-d');
    $complete = 0;
    $scheduledCount = 0;
    foreach ($tasks as $id => $item) {
        if (!scheduled($item, $cursor)) continue;
        ++$scheduledCount;
        if (($entries[$dk][$id]['status'] ?? '') === 'feito') ++$complete;
    }
    $history[] = ['date'=>$dk, 'done'=>$complete, 'total'=>$scheduledCount];
    $cursor = $cursor->modify('-1 day');
}

?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#151c17">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Painel Mestre">
<meta name="csrf-token" content="<?=esc($_SESSION['rotinas_csrf'])?>">
<link rel="manifest" href="/sistemas/rotinas.webmanifest">
<link rel="icon" href="/sistemas/rotinas-icon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/sistemas/rotinas-icon.svg">
<script src="/sistemas/rotinas-app.js" defer></script>
<title>Rotinas de execução | Painel Mestre GEB</title>
<style>
:root{color-scheme:light;--ink:#161b1a;--muted:#52605b;--line:#d8dfdb;--green:#cced51;--bg:#f4f5f2}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.5 system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
main,header{max-width:1030px;margin:auto;padding:20px}
header{display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid var(--line)}
a{color:inherit;text-underline-offset:3px}
.brand{font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
nav a{font-size:13px;font-weight:700}
h1{font-size:clamp(28px,5vw,43px);letter-spacing:-.035em;margin:8px 0}
.subtitle{color:var(--muted);margin:0 0 26px}
.eyebrow{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#384d36}
.toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:16px 0 20px}
.toolbar form{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
input[type=date]{font:inherit;padding:10px;border:1px solid #8b9991;border-radius:5px;background:#fff}
button,.btn{font:inherit;font-weight:700;cursor:pointer;border:1px solid #809088;border-radius:5px;padding:9px 15px;background:#fff;text-decoration:none;display:inline-block}
button:hover,.btn:hover{background:#ecf0e5}
button:focus-visible,a:focus-visible,input:focus-visible{outline:3px solid #31754a;outline-offset:3px}
.primary{background:var(--green);border-color:#70871d}
.primary:hover{background:#bbdb46}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:22px}
.stat{padding:15px;background:#fff;border:1px solid var(--line);border-radius:5px}
.stat strong{font-size:30px;line-height:1;display:block}.stat span{font-size:12px;color:var(--muted)}
.tasks{display:grid;gap:12px}
.task{
  background:#fff;border:1px solid var(--line);
  border-left:6px solid #929e98;border-radius:5px;padding:12px 16px;
}
.task[data-state="feito"]{
  background:#eff8ef;border-color:#aacdb0;border-left-color:#2b7543;
}
.task[data-state="pendente"]{
  background:#fff3e9;border-color:#e4b695;border-left-color:#ab4d17;
}
.task-header{display:flex;justify-content:space-between;gap:14px;align-items:flex-start}
.time{font-size:18px;font-weight:800;font-variant-numeric:tabular-nums}
.task h2{font-size:16px;margin:2px 0 0;line-height:1.35}.task p{margin:0 0 9px;color:var(--muted)}
.status{
  display:inline-flex;align-items:center;gap:9px;flex-shrink:0;
  font-size:12px;font-weight:800;letter-spacing:.035em;text-transform:uppercase;
  border:1px solid #ccd4cf;border-radius:4px;padding:8px 12px;
  background:#eef1ef;color:#44534a;white-space:nowrap;
}
.status::before{content:"○";font-size:17px;line-height:1}
.task[data-state="feito"] .status{
  color:#fff;background:#21633c;border-color:#21633c;
}
.task[data-state="feito"] .status::before{content:"✓"}
.task[data-state="pendente"] .status{
  color:#fff;background:#99400e;border-color:#99400e;
}
.task[data-state="pendente"] .status::before{content:"!";font-size:16px}
.task form{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.task form button{min-width:90px;padding:7px 10px;font-size:13px;background:#fff;color:var(--ink);border:1px solid #89978d}
.task form button:hover{background:#f0f2ed}
.task form button.primary{background:#fff;border-color:#89978d}
.task form button.primary:hover{background:#e7f1d5}
.task[data-state="feito"] button[value="feito"]{
  background:#21633c;border-color:#21633c;color:#fff;
  box-shadow:inset 0 0 0 1px #21633c;
}
.task[data-state="pendente"] button[value="pendente"]{
  background:#99400e;border-color:#99400e;color:#fff;
  box-shadow:inset 0 0 0 1px #99400e;
}
.task button[value="limpar"]{font-weight:600}
.task-details{margin:7px 0 2px;font-size:13px;color:#405348}
.task-details summary{cursor:pointer;font-weight:700;display:inline-block;padding:2px 0;text-decoration:underline;text-decoration-color:#a7b2a9;text-underline-offset:3px}
.task-details[open]{border-top:1px dashed #afbbb1;margin-top:12px;padding-top:9px}
.task-details p{max-width:760px;margin-top:7px}
.task-links{display:flex;flex-wrap:wrap;gap:9px;margin:10px 0 6px}
.task-links a{font-weight:700;background:#fff;border:1px solid #aab7ad;border-radius:3px;padding:5px 9px;text-decoration:none}
.task-rest{margin-top:5px}
.notifications-panel{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;background:#fff;border:1px solid var(--line);border-radius:5px;padding:12px 16px;margin:0 0 15px}
.notifications-panel strong{font-size:14px}
.notifications-panel p{font-size:12px;color:var(--muted);margin:2px 0 0}
.notify-buttons{display:flex;gap:8px;flex-wrap:wrap}
.notify-buttons button{font-size:12px;padding:8px 12px}
[hidden]{display:none!important}
@media(forced-colors:active){
  .task[data-state="feito"],.task[data-state="pendente"]{border-left-width:9px}
  .task button[aria-pressed="true"]{outline:2px solid Highlight}
}
.meta{font-size:12px;color:var(--muted)}
.notice{padding:13px 15px;background:#fff;border:1px solid var(--line);border-radius:4px;margin-bottom:16px}
.section{margin-top:36px}
.section h2{font-size:21px;margin:0 0 12px}
.history{display:grid;grid-template-columns:repeat(auto-fit,minmax(132px,1fr));gap:9px}
.history a{padding:12px;background:#fff;border:1px solid var(--line);border-radius:4px;text-decoration:none}
.history a[aria-current="date"]{border-color:#4b6d26;background:#f2f8d9}
.history b{display:block;font-size:12px}.history span{font-size:12px;color:var(--muted)}
footer{font-size:12px;color:var(--muted);margin:35px 0;padding-top:14px;border-top:1px solid var(--line)}
@media(max-width:570px){.stats{gap:6px}.stat{padding:10px}.task-header{flex-direction:row;gap:8px}.status{padding:5px 7px;font-size:10px}.task{padding:11px}.toolbar{gap:7px}.task form button{min-width:75px}}
</style>
</head><body>
<header><div class="brand">GEB · Painel Mestre</div><nav><a href="/sistemas/">Voltar ao Portal de Aplicações</a></nav></header>
<main>
<div class="eyebrow">Execução e acompanhamento</div>
<h1>Rotinas diárias</h1>
<p class="subtitle">Atividades recorrentes, com marcações por data e alertas no horário de Brasília.</p>
<section class="notifications-panel" aria-label="Alertas e instalação do aplicativo">
  <div><strong>Alertas de compromissos</strong><p id="notify-status" role="status">Verificando disponibilidade das notificações.</p></div>
  <div class="notify-buttons">
    <button type="button" class="primary" id="notify-enable">Ativar alertas</button>
    <button type="button" id="notify-disable" hidden>Desativar alertas</button>
    <button type="button" id="install-app" hidden>Instalar aplicativo</button>
  </div>
</section>
<div class="toolbar">
<a class="btn" href="?data=<?=esc($prev)?>" aria-label="Dia anterior">← Anterior</a>
<form action="/sistemas/rotinas.php" method="get">
<label for="data">Data</label><input id="data" type="date" name="data" min="2020-01-01" max="2100-12-31" value="<?=esc($key)?>">
<button type="submit">Consultar</button>
</form>
<a class="btn" href="?data=<?=esc($next)?>" aria-label="Próximo dia">Próximo →</a>
<a class="btn" href="/sistemas/rotinas.php">Hoje</a>
</div>
<p><strong><?=esc($weekdayName)?>, <?=esc($selected->format('d/m/Y'))?></strong></p>
<?php if (isset($_GET['salvo'])): ?><p class="notice" role="status">Marcação registrada no servidor.</p><?php endif; ?>
<div class="stats" aria-label="Resumo do dia">
<div class="stat"><strong><?=$done?></strong><span>Feitas</span></div>
<div class="stat"><strong><?=$pending?></strong><span>Pendentes marcadas</span></div>
<div class="stat"><strong><?=$unmarked?></strong><span>Sem marcação</span></div>
</div>
<?php if ($total === 0): ?>
<p class="notice">Não há rotinas programadas para este dia.</p>
<?php else: ?>
<div class="tasks">
<?php foreach ($tasks as $id => $task):
if (!scheduled($task, $selected)) continue;
$record=$dayRecords[$id] ?? [];
$state=(string)($record['status'] ?? '');
$label=$state==='feito'?'Concluído':($state==='pendente'?'Pendente':'Sem marcação');
?>
<article class="task" data-state="<?=esc($state)?>" data-task-id="<?=esc($id)?>" data-hour="<?=esc($task['hora'])?>">
<div class="task-header"><div>
<div class="time"><?=esc($task['hora'])?></div>
<div class="meta"><?=esc($task['area'])?></div>
<h2><?=esc($task['titulo'])?></h2>
</div><span class="status"><?=esc($label)?></span></div>
<details class="task-details">
  <summary>Observações e acessos</summary>
  <p><?=esc($task['descricao'])?></p>
  <?php if (!empty($task['observacoes'])): ?><p><?=esc($task['observacoes'])?></p><?php endif; ?>
  <?php if (!empty($task['links'])): ?><div class="task-links"><?php foreach ($task['links'] as $link): ?><a href="<?=esc($link['url'])?>" target="_blank" rel="noopener noreferrer"><?=esc($link['titulo'])?> ↗</a><?php endforeach; ?></div><?php endif; ?>
</details>
<div class="task-rest">
<?php if (!empty($record['alterado_em'])): ?><div class="meta">Última marcação: <?=esc((string)$record['alterado_em'])?></div><?php endif; ?>
<form action="/sistemas/rotinas.php" method="post">
<input type="hidden" name="csrf" value="<?=esc($_SESSION['rotinas_csrf'])?>">
<input type="hidden" name="data" value="<?=esc($key)?>">
<input type="hidden" name="tarefa" value="<?=esc($id)?>">
<button class="primary" name="status" value="feito" type="submit" aria-pressed="<?=$state==='feito'?'true':'false'?>" aria-label="Marcar como feito: <?=esc($task['titulo'])?>">Feito</button>
<button name="status" value="pendente" type="submit" aria-pressed="<?=$state==='pendente'?'true':'false'?>" aria-label="Marcar como pendente: <?=esc($task['titulo'])?>">Pendente</button>
<?php if ($state !== ''): ?><button name="status" value="limpar" type="submit" aria-label="Remover marcação: <?=esc($task['titulo'])?>">Desmarcar</button><?php endif; ?>
</form>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<section class="section"><h2>Últimos 10 dias</h2>
<div class="history">
<?php foreach ($history as $item): ?>
<a href="?data=<?=esc($item['date'])?>" <?=$item['date']===$key?'aria-current="date"':''?>>
<b><?=esc((new DateTimeImmutable($item['date'], $tz))->format('d/m/Y'))?></b>
<span><?=$item['done']?> de <?=$item['total']?> concluídas</span>
</a>
<?php endforeach; ?>
</div></section>
<footer>As marcações são manuais e não certificam automaticamente a execução externa. O histórico permanece no servidor, não no GitHub. Os lembretes dependem da permissão de notificações e da compatibilidade do navegador/dispositivo.</footer>
</main></body></html>
