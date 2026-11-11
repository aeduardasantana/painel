<?php
declare(strict_types=1);
require __DIR__.'/rotinas-push-common.php';
rp_jsonHeaders();
rp_authenticate();
$tz=new DateTimeZone('America/Sao_Paulo');
$today=new DateTimeImmutable('today',$tz);
require __DIR__.'/rotinas-recorrencias.php';
$catalog=require __DIR__.'/rotinas-catalogo.php';
try { $records=rp_data('.rotinas-gestao.json'); }
catch (Throwable $e) { rp_fail(503,'Não foi possível consultar o registro de tarefas.'); }
$statuses=[];
foreach ($catalog as $id=>$task) {
    if (!pm_scheduled($task,$today)) continue;
    $statuses[$id]=[
        'hour'=>$task['hora'],
        'title'=>$task['titulo'],
        'status'=>$records[$today->format('Y-m-d')][$id]['status']??'',
    ];
}
echo json_encode(['ok'=>true,'date'=>$today->format('Y-m-d'),'tasks'=>$statuses],JSON_UNESCAPED_UNICODE);
