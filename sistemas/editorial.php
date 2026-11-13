<?php
declare(strict_types=1);
/* Central Editorial do Painel Mestre. Metadados operacionais, sem copiar acervos. */
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
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'self'; connect-src 'self'; worker-src 'self'; manifest-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') {http_response_code(403);exit('HTTPS obrigatório.');}
if (($_SESSION['auth']??false)!==true || time()-(int)($_SESSION['auth_at']??0)>=28800) {
    header('Location: /sistemas/',true,302);exit;
}
$_SESSION['auth_at']=time();
if (!isset($_SESSION['ed_csrf'])) $_SESSION['ed_csrf']=bin2hex(random_bytes(24));
$config=require __DIR__.'/editorial-config.php';
require __DIR__.'/editorial-core.php';
require __DIR__.'/rotinas-versao.php';
$appVersion=pm_build_version();
$error='';
$brand=(string)($_GET['marca']??'todas');
if ($brand!=='todas' && !isset($config['brands'][$brand])) $brand='todas';
$phase=(string)($_GET['fase']??'todas');
if ($phase!=='todas' && !isset($config['phases'][$phase])) $phase='todas';
$search=trim(substr((string)($_GET['q']??''),0,130));
$showArchived=($_GET['arquivados']??'')==='1';
$focusId=(string)($_GET['registro']??'');
try {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals($_SESSION['ed_csrf'],(string)($_POST['csrf']??''))) throw new DomainException('A sessão de segurança expirou. Recarregue a página.');
        if ((int)($_SERVER['CONTENT_LENGTH']??0)>30000) throw new DomainException('Formulário grande demais.');
        $action=(string)($_POST['acao']??'');
        $id=(string)($_POST['id']??'');
        if ($id!=='' && !preg_match('/^[a-f0-9]{24}$/D',$id)) throw new DomainException('Identificador de registro inválido.');
        if (!in_array($action,['item_save','distribution_add','distribution_update','archive','restore'],true)) throw new DomainException('Ação não reconhecida.');
        $saved=ed_commit(function(array $items) use ($action,$id,$config): array {
            $now=ed_time();
            if ($action==='item_save') {
                $data=ed_form_item($_POST,$config);
                if ($id==='') {
                    ed_duplicate($items,$data['brand'],$data['code'],null);
                    $fresh=[
                        'id'=>bin2hex(random_bytes(12)),
                        'revision'=>1,'created_at'=>$now,'modified_at'=>$now,
                        'archived'=>false,'distributions'=>[]
                    ];
                    return array_merge($fresh,$data,['_action'=>'criado']);
                }
                if (!isset($items[$id])) throw new DomainException('Conteúdo não encontrado.');
                $old=$items[$id];
                ed_expected($old,$_POST['revision']??'');
                ed_duplicate($items,$data['brand'],$data['code'],$id);
                return array_merge($old,$data,[
                    'revision'=>(int)$old['revision']+1,
                    'modified_at'=>$now,'_action'=>'editado'
                ]);
            }
            if (!isset($items[$id])) throw new DomainException('Conteúdo não encontrado.');
            $item=$items[$id];
            ed_expected($item,$_POST['revision']??'');
            if ($action==='archive' || $action==='restore') {
                $item['archived']=$action==='archive';
                $item['_action']=$action;
            } else {
                if (!empty($item['archived'])) throw new DomainException('Restaure o conteúdo arquivado antes de modificar suas distribuições.');
                $dist=ed_form_distribution($_POST,$config);
                $all=$item['distributions']??[];
                if (!is_array($all) || count($all)>25) throw new DomainException('Limite de canais do conteúdo alcançado.');
                if ($action==='distribution_add') {
                    ed_no_dup_distribution($all,$dist,null);
                    $dist['id']=bin2hex(random_bytes(8));
                    $dist['changed_at']=$now;
                    $all[]=$dist;
                } else {
                    $distId=(string)($_POST['distribution_id']??'');
                    if (!preg_match('/^[a-f0-9]{16}$/D',$distId)) throw new DomainException('Distribuição inválida.');
                    $found=false;
                    ed_no_dup_distribution($all,$dist,$distId);
                    foreach ($all as &$oldDistribution) {
                        if (($oldDistribution['id']??'')!==$distId) continue;
                        $oldDistribution=array_merge($oldDistribution,$dist,['changed_at'=>$now]);
                        $found=true;
                        break;
                    }
                    unset($oldDistribution);
                    if (!$found) throw new DomainException('Distribuição não encontrada.');
                }
                $item['distributions']=$all;
                $item['_action']=$action;
            }
            $item['revision']=(int)$item['revision']+1;
            $item['modified_at']=$now;
            return $item;
        });
        $returnBrand=$saved['brand'];
        header('Location: /sistemas/editorial.php?marca='.rawurlencode($returnBrand).'&registro='.rawurlencode($saved['id']).'&salvo=1',true,303);
        exit;
    }
} catch (DomainException $e) {
    http_response_code(422);
    $error=$e->getMessage();
} catch (Throwable $e) {
    http_response_code(503);
    $error='Não foi possível concluir a operação. Seus registros anteriores foram preservados. Verifique as permissões de gravação do servidor ou consulte a auditoria.';
}
try {$items=ed_load();}
catch (Throwable $e) {
    http_response_code(503);
    $items=[];
    $error='Não foi possível ler o histórico editorial. A central não permitirá concluir novas marcações até corrigir o armazenamento.';
}
$focus=(preg_match('/^[a-f0-9]{24}$/D',$focusId) && isset($items[$focusId]))?$items[$focusId]:null;
$list=[];
$counts=[];$total=0;$scheduledCount=0;$publishedCount=0;$overdue=0;
$today=(new DateTimeImmutable('today',new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
foreach ($config['brands'] as $k=>$v) $counts[$k]=0;
foreach ($items as $item) {
    if (!empty($item['archived'])) continue;
    ++$total;
    $b=(string)($item['brand']??'');
    if (isset($counts[$b])) ++$counts[$b];
    $distribution=$item['distributions']??[];
    $hasPublication=false;
    foreach($distribution as $channel) {
        if (($channel['state']??'')==='publicado') {++$publishedCount;$hasPublication=true;}
        if (($channel['state']??'')==='agendado') ++$scheduledCount;
    }
    if (!$hasPublication && !empty($item['planned_date']) && $item['planned_date']<$today) ++$overdue;
}
foreach ($items as $item) {
    if ($brand!=='todas' && ($item['brand']??'')!==$brand) continue;
    if ($phase!=='todas' && ($item['phase']??'')!==$phase) continue;
    if ($showArchived ? empty($item['archived']) : !empty($item['archived'])) continue;
    if ($search!=='') {
        $haystack=mb_strtolower(implode(' ',[
            (string)($item['title']??''),(string)($item['code']??''),
            (string)($item['project']??''),(string)($item['notes']??'')
        ]),'UTF-8');
        if (mb_strpos($haystack,mb_strtolower($search,'UTF-8'))===false) continue;
    }
    $list[]=$item;
}
usort($list,static function(array $a,array $b): int {
    $da=(string)($a['planned_date']??'');
    $db=(string)($b['planned_date']??'');
    if ($da!=='' && $db!=='' && $da!==$db) return strcmp($da,$db);
    if ($da!=='' && $db==='') return -1;
    if ($db!=='' && $da==='') return 1;
    return strcmp((string)($b['modified_at']??''),(string)($a['modified_at']??''));
});
$shown=array_slice($list,0,100);
function ed_link(string $name,string $url): void {
    $internal=str_starts_with($url,'/');
    echo '<a href="'.ed_h($url).'"'.($internal?'':' target="_blank" rel="noopener noreferrer"').'>'.ed_h($name).' <span aria-hidden="true">↗</span></a>';
}
function ed_input(string $name,string $label,string $value='',string $type='text',bool $required=false,int $max=180): void {
    echo '<label class="field">'.ed_h($label).'<input name="'.ed_h($name).'" type="'.ed_h($type).'" value="'.ed_h($value).'" maxlength="'.$max.'"'.($required?' required':'').'></label>';
}
function ed_select(string $name,string $label,array $options,string $current): void {
    echo '<label class="field">'.ed_h($label).'<select name="'.ed_h($name).'">';
    foreach ($options as $key=>$display) echo '<option value="'.ed_h((string)$key).'"'.($key===$current?' selected':'').'>'.ed_h((string)$display).'</option>';
    echo '</select></label>';
}
function ed_form_hidden(string $action,string $id='',int $rev=0): void {
    echo '<input type="hidden" name="csrf" value="'.ed_h($_SESSION['ed_csrf']).'">';
    echo '<input type="hidden" name="acao" value="'.ed_h($action).'">';
    echo '<input type="hidden" name="id" value="'.ed_h($id).'">';
    if ($rev>0) echo '<input type="hidden" name="revision" value="'.$rev.'">';
}
function ed_item_fields(array $item,array $config): void {
    echo '<div class="fields">';
    ed_select('brand','Marca editorial',$config['brands'] ? array_map(static fn($b)=>$b['nome'],$config['brands']):[],(string)($item['brand']??'geb'));
    ed_input('title','Título do conteúdo',(string)($item['title']??''),'text',true);
    ed_input('code','Código do roteiro (se existir)',(string)($item['code']??''));
    ed_input('project','Quadro / projeto',(string)($item['project']??''));
    ed_select('format','Formato',$config['formats'],(string)($item['format']??'video'));
    ed_select('phase','Etapa da produção',$config['phases'],(string)($item['phase']??'pauta'));
    ed_input('planned_date','Prazo editorial',(string)($item['planned_date']??''),'date',false,10);
    ed_input('asset_url','Link do roteiro / arquivo original no Drive',(string)($item['asset_url']??''),'url',false,1500);
    echo '<label class="field full">Observações editoriais (não inserir dados pessoais de pacientes)<textarea name="notes" rows="3" maxlength="2500">'.ed_h((string)($item['notes']??'')).'</textarea></label>';
    echo '</div>';
}
function ed_distribution_fields(array $dist,array $config): void {
    echo '<div class="fields">';
    ed_select('network','Rede ou canal',$config['networks'],(string)($dist['network']??'youtube'));
    ed_input('account','Perfil / conta de publicação',(string)($dist['account']??''),'text',true,100);
    ed_select('state','Situação neste canal',$config['distribution_states'],(string)($dist['state']??'planejado'));
    ed_input('scheduled_date','Data de agendamento/publicação',(string)($dist['scheduled_date']??''),'date',false,10);
    ed_input('published_url','URL publicada (obrigatória quando Publicado)',(string)($dist['published_url']??''),'url',false,1500);
    echo '<label class="field full">Observações deste canal<textarea name="distribution_notes" maxlength="900" rows="2">'.ed_h((string)($dist['notes']??'')).'</textarea></label>';
    echo '</div>';
}
?><!doctype html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#151c17">
<meta name="app-version" content="<?=ed_h($appVersion)?>">
<link rel="manifest" href="/sistemas/rotinas.webmanifest">
<script src="/sistemas/rotinas-update.js?v=<?=ed_h(substr($appVersion,0,12))?>" defer></script>
<title>Central Editorial | Painel Mestre GEB</title>
<style>
:root{--black:#161d18;--muted:#566258;--line:#ccd5ca;--lime:#c9e751;--bg:#f3f5f0;--green:#226b3d;--orange:#ad571c;--blue:#385d74;--white:#fff;color-scheme:light}
*{box-sizing:border-box}body{margin:0;font:14px/1.5 system-ui,-apple-system,"Segoe UI",Arial,sans-serif;background:var(--bg);color:var(--black)}
header{background:var(--black);color:#fff;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:16px max(18px,calc((100vw - 1280px)/2))}
header b{font-size:15px;letter-spacing:.035em}header nav{display:flex;flex-wrap:wrap;gap:14px}header a{color:#e4efd4;font-weight:650;text-decoration:none}
main{max-width:1280px;margin:auto;padding:22px 17px 60px}h1{font-size:clamp(28px,4vw,38px);letter-spacing:-.04em;line-height:1.2;margin:7px 0}
h2{font-size:20px;margin:0 0 10px;letter-spacing:-.015em}h3{font-size:16px;margin:0 0 5px}
.eyebrow{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#456039}
.intro{max-width:890px;color:var(--muted);margin:6px 0 20px}
.panel,.item,.board-card{background:#fff;border:1px solid var(--line);border-radius:5px;padding:17px}
.panel{margin:16px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.kpi{border:1px solid var(--line);background:#fff;padding:13px;border-top:4px solid #8ca12e}
.kpi b{display:block;font-size:28px;line-height:1.1;font-variant-numeric:tabular-nums}.kpi span{display:block;margin-top:4px;font-size:12px;color:var(--muted)}
.controls{display:flex;gap:9px;flex-wrap:wrap;align-items:end}
label{font-weight:650}input,select,textarea{border:1px solid #8b9b8e;background:#fff;color:var(--black);font:inherit;padding:9px 10px;border-radius:3px;max-width:100%}
button,.btn{font:inherit;font-weight:700;padding:9px 13px;border:1px solid #89988b;background:#fff;border-radius:3px;cursor:pointer;color:inherit;text-decoration:none}
button:hover,.btn:hover{background:#eff2ec}.primary{background:var(--lime);border-color:#8ca22e}.primary:hover{background:#b9d840}
.danger{border-color:#94a1ae;color:#364d5c}
a{color:#1d4d32;text-underline-offset:3px}a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,select:focus-visible,summary:focus-visible{outline:3px solid #238247;outline-offset:3px}
.notice{border-left:4px solid #758e3d;background:#f6f9eb;padding:12px 15px;margin:13px 0}
.error{background:#fff2ee;border-color:#bf6c4b}
.board-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:10px;margin-top:12px}
.board-card{padding:13px}.board-card header{background:none;color:var(--black);padding:0;display:flex;justify-content:space-between;gap:6px}.board-card a.brandlink{font-weight:800;color:inherit}
.board-card p{font-size:12px;color:var(--muted);margin:6px 0 8px}.ref-list{display:flex;gap:6px;flex-wrap:wrap}
.ref-list a{font-size:12px;padding:5px 8px;border:1px solid var(--line);border-radius:3px;background:#f9faf7;text-decoration:none}
.headerrow{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:9px}
.small{font-size:12px;color:var(--muted)}.faint{color:var(--muted)}
.cards{display:grid;gap:11px;margin-top:13px}
.item{border-left:5px solid #829184}
.item[data-phase="editado"]{border-left-color:#246b42}
.item[data-phase="gravado"]{border-left-color:#4c7a97}
.item[data-phase="roteiro"]{border-left-color:#a58a34}
.item[data-archive="1"]{opacity:.82;border-left-color:#88939e}
.item h3{margin:2px 0 4px;font-size:18px}
.item-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
.tag{display:inline-block;font-size:11px;font-weight:750;border:1px solid #aebcaf;padding:3px 7px;background:#f1f5ef;margin:0 4px 4px 0;border-radius:2px}
.tag.stage{background:#e8f2d2;border-color:#b6ca86}.tag.pub{background:#e9f4ee;border-color:#a3ceb0}.tag.scheduled{background:#eef1fa;border-color:#b6c4d8}
.dist{background:#f7f9f6;border:1px solid #d5ded6;padding:11px;margin:8px 0;border-radius:3px}
.distrow{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.distrow strong{display:block}.distrow .small{display:block}.tight{margin:0 0 6px}.item-actions{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0 0}
details{border-top:1px dashed #c3cec5;padding-top:9px;margin-top:11px}summary{cursor:pointer;font-weight:750}
.fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:13px 0}.field{display:grid;gap:4px;font-size:12px}.field.full{grid-column:1/-1}
.field input,.field select,.field textarea{width:100%;font-size:14px;font-weight:400}.form-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.state{font-size:11px;letter-spacing:.025em;font-weight:800;text-transform:uppercase;padding:5px 8px;background:#eff1ee;border:1px solid #aab9aa}
.state[data-state="publicado"]{background:#ddf2e1;color:#185b32;border-color:#9acba7}.state[data-state="agendado"]{background:#e9eef9;color:#274f79;border-color:#a1bad8}.state[data-state="cancelado"]{background:#f2e8e5;color:#824527;border-color:#d2aa98}
.update-tools{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-top:12px}.update-tools button{font-size:12px}.update-tools span{font-size:12px;color:var(--muted)}
.app-update-banner{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#f4f8e7;border:1px solid #9da785;border-left:5px solid #778b39;padding:10px 13px;margin:12px 0}
.app-update-actions{display:flex;gap:8px}.app-update-actions button{font-size:12px}.app-update-actions .primary{background:var(--lime)}
[hidden]{display:none!important}
footer{padding:15px 0;color:var(--muted);font-size:12px;border-top:1px solid var(--line);margin-top:28px}
@media(max-width:690px){.fields{grid-template-columns:1fr}.grid{grid-template-columns:repeat(2,minmax(0,1fr))}.board-grid{grid-template-columns:1fr}.item,.panel{padding:12px}header{padding:14px 17px}}
</style></head><body>
<header><b>GEB · Painel Mestre — Central Editorial</b><nav><a href="/sistemas/">Portal</a><a href="/sistemas/rotinas.php">Rotinas</a><a href="/sistemas/rotinas-desempenho.php">Indicadores</a></nav></header>
<main>
<span class="eyebrow">Gestão editorial de marcas e canais</span>
<h1>Central Editorial</h1>
<p class="intro">Uma visão de todos os negócios, sem unificar suas agendas. Um conteúdo possui uma marca responsável, um roteiro original e distribuições independentes por rede/perfil. A produção e o arquivo original continuam no Drive.</p>
<?php if ($error!==''): ?><div class="notice error" role="alert"><?=ed_h($error)?></div><?php endif;?>
<?php if (isset($_GET['salvo'])): ?><div class="notice" role="status">Registro editorial salvo no servidor. Os materiais originais do Drive não foram movidos.</div><?php endif;?>
<div id="app-update-banner" class="app-update-banner" role="status" hidden>
<div><strong>Nova versão disponível</strong><p class="small">Atualize quando terminar o registro atual.</p></div>
<div class="app-update-actions"><button class="primary" type="button" id="app-update-now">Atualizar agora</button><button type="button" id="app-update-later">Depois</button></div>
</div>
<div class="update-tools"><button type="button" id="app-update-check">Verificar versão</button><span id="app-update-status" role="status">Verificando versão...</span></div>
<section class="grid" aria-label="Indicadores da Central Editorial">
<div class="kpi"><b><?=$total?></b><span>Conteúdos cadastrados nesta central</span></div>
<div class="kpi"><b><?=$scheduledCount?></b><span>Distribuições marcadas Agendado</span></div>
<div class="kpi"><b><?=$publishedCount?></b><span>Distribuições marcadas Publicado (com link)</span></div>
<div class="kpi"><b><?=$overdue?></b><span>Conteúdos com prazo vencido sem publicação registrada</span></div>
</section>
<section class="panel" aria-labelledby="fontes"><div class="headerrow"><h2 id="fontes">Acervos e agendas por marca</h2><?php ed_link('Abrir pasta central do Drive',$config['central_drive']);?></div>
<p class="small">Estas referências são acessos aos documentos originais — não importam ou confirmam automaticamente conteúdos, gravações e publicações.</p>
<div class="ref-list"><?php ed_link('Mapa de responsabilidade editorial',$config['mapa_central']);?><?php foreach($config['shared'] as $l) ed_link($l['nome'],$l['url']);?></div>
<div class="board-grid">
<?php foreach ($config['brands'] as $key=>$b): ?>
<article class="board-card">
<header><a class="brandlink" href="?marca=<?=ed_h($key)?>"><?=ed_h($b['nome'])?></a><strong><?=$counts[$key]?> registros</strong></header>
<p><?=ed_h($b['descricao'])?></p>
<div class="ref-list"><?php foreach ($b['links'] as $l) ed_link($l['nome'],$l['url']);?><?php if (!$b['links']): ?><span class="small">Aguardando vinculação do acervo específico.</span><?php endif;?></div>
</article>
<?php endforeach; ?>
</div></section>
<section class="panel" id="novo">
<div class="headerrow"><h2>Cadastrar conteúdo editorial</h2><span class="small">Uma entrada por roteiro; canais adicionados depois</span></div>
<details <?=($_GET['novo']??'')==='1'?'open':''?>>
<summary>Adicionar um conteúdo, pauta ou roteiro</summary>
<form method="post" action="/sistemas/editorial.php">
<?php ed_form_hidden('item_save');ed_item_fields([],$config); ?>
<div class="form-actions"><button class="primary" type="submit">Salvar conteúdo</button><span class="small">Nenhum status de publicação é preenchido automaticamente.</span></div>
</form>
</details>
</section>
<section class="panel" id="registros">
<div class="headerrow"><h2>Controle de produção e distribuição</h2><span class="small"><?=count($list)?> resultados; exibindo até 100</span></div>
<form method="get" action="/sistemas/editorial.php" class="controls">
<label class="field">Marca
<select name="marca"><option value="todas">Todas</option><?php foreach($config['brands'] as $key=>$b): ?><option value="<?=ed_h($key)?>" <?=$brand===$key?'selected':''?>><?=ed_h($b['nome'])?></option><?php endforeach;?></select>
</label>
<label class="field">Etapa
<select name="fase"><option value="todas">Todas</option><?php foreach($config['phases'] as $key=>$name): ?><option value="<?=ed_h($key)?>" <?=$phase===$key?'selected':''?>><?=ed_h($name)?></option><?php endforeach;?></select>
</label>
<label class="field">Pesquisar título ou código
<input name="q" type="search" value="<?=ed_h($search)?>" maxlength="130" placeholder="Código, quadro, título"></label>
<label class="field">Visão
<select name="arquivados"><option value="0" <?=$showArchived?'':'selected'?>>Ativos</option><option value="1" <?=$showArchived?'selected':''?>>Arquivados</option></select>
</label>
<button class="primary" type="submit">Filtrar</button>
</form>
<?php if (!$items): ?><p class="notice"><strong>Controle novo.</strong> Os acervos e agendas do Drive existem, mas ainda não foram copiados para esta base. Para evitar duplicações, cadastre aqui apenas conteúdos que deseje controlar diretamente pelo painel.</p><?php endif;?>
<?php if (!$shown): ?><p class="small">Nenhum conteúdo corresponde a este filtro.</p><?php endif;?>
<div class="cards">
<?php foreach ($shown as $item):
    $id=(string)$item['id'];
    $itemBrand=(string)($item['brand']??'');
    $revision=(int)($item['revision']??1);
    $dists=$item['distributions']??[];
?>
<article class="item" data-phase="<?=ed_h((string)$item['phase'])?>" data-archive="<?=empty($item['archived'])?'0':'1'?>" id="registro-<?=ed_h($id)?>">
<div class="item-top">
<div>
<div class="small"><?=ed_h($config['brands'][$itemBrand]['nome']??$itemBrand)?><?php if (!empty($item['code'])): ?> · <?=ed_h((string)$item['code'])?><?php endif;?></div>
<h3><?=ed_h((string)$item['title'])?></h3>
<div><span class="tag stage"><?=ed_h($config['phases'][$item['phase']]??(string)$item['phase'])?></span>
<span class="tag"><?=ed_h($config['formats'][$item['format']]??(string)$item['format'])?></span>
<?php if (!empty($item['project'])): ?><span class="tag"><?=ed_h((string)$item['project'])?></span><?php endif;?>
<?php if (!empty($item['archived'])): ?><span class="tag">Arquivado</span><?php endif;?></div>
</div>
<div class="small"><strong>Prazo:</strong> <?=!empty($item['planned_date'])?ed_h((string)$item['planned_date']):'sem data'?>
<br><strong>Canais:</strong> <?=count($dists)?>
<br><strong>Última edição:</strong> <?=ed_h(substr((string)$item['modified_at'],0,10))?>
</div></div>
<?php if (!empty($item['asset_url'])): ?><div class="ref-list"><?php ed_link('Abrir roteiro / arquivo original',(string)$item['asset_url']);?></div><?php endif;?>
<?php if (!empty($item['notes'])): ?><details><summary>Observações editoriais</summary><p><?=nl2br(ed_h((string)$item['notes']),false)?></p></details><?php endif;?>
<?php if ($dists): ?><div aria-label="Distribuições deste conteúdo">
<?php foreach ($dists as $dist): ?>
<div class="dist">
<div class="distrow"><div><strong><?=ed_h($config['networks'][$dist['network']]??(string)$dist['network'])?> · <?=ed_h((string)$dist['account'])?></strong>
<span class="small">Data: <?=!empty($dist['scheduled_date'])?ed_h($dist['scheduled_date']):'não definida'?></span></div>
<span class="state" data-state="<?=ed_h((string)$dist['state'])?>"><?=ed_h($config['distribution_states'][$dist['state']]??(string)$dist['state'])?></span></div>
<?php if (!empty($dist['published_url'])): ?><div class="ref-list"><?php ed_link('Ver publicação',(string)$dist['published_url']);?></div><?php endif;?>
<?php if (!empty($dist['notes'])): ?><p class="small tight"><?=nl2br(ed_h((string)$dist['notes']),false)?></p><?php endif;?>
<?php if (empty($item['archived'])): ?>
<details><summary>Atualizar esta distribuição</summary>
<form method="post" action="/sistemas/editorial.php">
<?php ed_form_hidden('distribution_update',$id,$revision); ?>
<input type="hidden" name="distribution_id" value="<?=ed_h((string)$dist['id'])?>">
<?php ed_distribution_fields($dist,$config); ?>
<button type="submit" class="primary">Salvar situação neste canal</button>
</form></details>
<?php endif;?></div>
<?php endforeach; ?></div>
<?php else: ?><p class="small">Ainda não há canais vinculados. A criação do conteúdo não comprova distribuição.</p><?php endif;?>
<?php if (empty($item['archived'])): ?>
<details><summary>Adicionar canal ou perfil de distribuição</summary>
<form method="post" action="/sistemas/editorial.php">
<?php ed_form_hidden('distribution_add',$id,$revision);ed_distribution_fields([],$config); ?>
<button type="submit" class="primary">Adicionar distribuição</button>
</form>
</details>
<details><summary>Editar dados e etapa do conteúdo</summary>
<form method="post" action="/sistemas/editorial.php">
<?php ed_form_hidden('item_save',$id,$revision);ed_item_fields($item,$config); ?>
<button type="submit" class="primary">Salvar edição</button>
</form></details>
<?php endif;?>
<div class="item-actions">
<form method="post" action="/sistemas/editorial.php">
<?php ed_form_hidden(empty($item['archived'])?'archive':'restore',$id,$revision); ?>
<button class="danger" type="submit"><?=empty($item['archived'])?'Arquivar conteúdo':'Restaurar conteúdo'?></button>
</form>
</div>
</article>
<?php endforeach;?>
</div></section>
<footer>
<strong>Governança:</strong> os links e os documentos originais permanecem nas pastas e planilhas próprias; esta Central guarda registros operacionais auditáveis e separados por marca. Nenhuma publicação é realizada por este módulo e não há importação automática. Não registrar pacientes, senhas, documentos pessoais ou dados sensíveis. Histórico de alterações preservado no servidor.
</footer>
</main></body></html>
