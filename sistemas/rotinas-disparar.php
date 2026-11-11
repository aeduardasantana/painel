<?php
declare(strict_types=1);
require __DIR__.'/rotinas-push-common.php';
rp_jsonHeaders();
header('X-Robots-Tag: noindex,nofollow');
if ($_SERVER['REQUEST_METHOD']!=='POST'
    || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') rp_fail(403,'Acesso não autorizado.');
/*
 * Execução privada acionada pelo GitHub Actions.
 * Utiliza o segredo SYSTEMS_PASS já configurado para o portal.
 * Não aceita chamadas não autenticadas e não revela detalhes de dispositivos.
 */
$provided=(string)($_SERVER['HTTP_X_ROTINAS_JOB_KEY']??'');
$hash=getenv('SYSTEMS_PASS_HASH');
if (!$hash && is_file(__DIR__.'/.systems-pass.php')) {
    $candidate=require __DIR__.'/.systems-pass.php';
    if (is_string($candidate)) $hash=$candidate;
}
if (!$hash && is_file(dirname(__DIR__,3).'/.geb-systems-auth.php')) {
    $candidate=require dirname(__DIR__,3).'/.geb-systems-auth.php';
    if (is_string($candidate)) $hash=$candidate;
}
if (!is_string($hash) || !password_get_info($hash)['algo']) rp_fail(503,'Agendador não configurado.');
if ($provided==='' || strlen($provided)>250 || !password_verify($provided,$hash)) rp_fail(403,'Acesso não autorizado.');
if (!function_exists('curl_init')) rp_fail(503,'Extensão cURL indisponível.');
$tz=new DateTimeZone('America/Sao_Paulo');
$now=new DateTimeImmutable('now',$tz);
$today=new DateTimeImmutable('today',$tz);
if ((int)$now->format('G')<8 || (int)$now->format('G')>=21) {
    echo json_encode(['ok'=>true,'sent'=>0,'message'=>'Fora do horário de alertas.']); exit;
}
try {
    $subscriptions=rp_data('.rotinas-subs.json');
    if (!$subscriptions) {
        echo json_encode(['ok'=>true,'sent'=>0,'message'=>'Sem dispositivos inscritos.']); exit;
    }
    $config=rp_data('.rotinas-vapid.json');
    if (!isset($config['publicKey'],$config['privateKey'],$config['subject'])) rp_fail(503,'Chaves de notificações ausentes.');
    $autoload=__DIR__.'/vendor/autoload.php';
    if (!is_file($autoload)) rp_fail(503,'Biblioteca Web Push não instalada.');
    require $autoload;
    $webPush=new \Minishlink\WebPush\WebPush([
        'VAPID'=>[
            'subject'=>$config['subject'],
            'publicKey'=>$config['publicKey'],
            'privateKey'=>$config['privateKey'],
        ],
    ],['TTL'=>300,'urgency'=>'normal']);
    $records=rp_data('.rotinas-gestao.json');
    require __DIR__.'/rotinas-recorrencias.php';
    $tasks=require __DIR__.'/rotinas-catalogo.php';
    $day=$today->format('Y-m-d');
    $sentCount=0; $failedCount=0; $dueCount=0;
    rp_mutate('.rotinas-sent.json',function(array $logged) use ($subscriptions,$webPush,$records,$tasks,$day,$now,$today,&$sentCount,&$failedCount,&$dueCount): array {
        foreach ($tasks as $id=>$task) {
            if (!pm_scheduled($task,$today)) continue;
            if (in_array(($records[$day][$id]['status']??''),['feito','cancelado'],true)) continue;
            [$h,$m]=array_map('intval',explode(':',$task['hora']));
            $due=$today->setTime($h,$m);
            if ($now<$due) continue;
            $slot=(int)floor(($now->getTimestamp()-$due->getTimestamp())/3600);
            ++$dueCount;
            foreach ($subscriptions as $deviceId=>$entry) {
                if ($sentCount+$failedCount>=20) break 2;
                if (empty($entry['active']) || !isset($entry['subscription'])) continue;
                $sub=rp_subscription((array)$entry['subscription']);
                if (!$sub) continue;
                $deviceHash=hash('sha256',$sub['endpoint']);
                if ($deviceId!==$deviceHash) continue;
                if (($logged[$day][$id][$deviceHash]??-1)>=$slot) continue;
                $payload=json_encode([
                    'title'=>'Painel Mestre · '.$task['hora'],
                    'body'=>$task['titulo'].($slot>0?' — atividade não concluída.':''),
                    'tag'=>'rotina-'.$day.'-'.$id,
                    'url'=>'/sistemas/rotinas.php?data='.$day,
                ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                if ($payload===false) continue;
                try {
                    $subscription=\Minishlink\WebPush\Subscription::create($sub);
                    $report=$webPush->sendOneNotification($subscription,$payload,['TTL'=>300]);
                    if ($report->isSuccess()) {
                        $logged[$day][$id][$deviceHash]=$slot;
                        ++$sentCount;
                    } else {
                        ++$failedCount;
                    }
                } catch (Throwable $e) {
                    ++$failedCount;
                }
            }
        }
        $limit=$today->modify('-35 days')->format('Y-m-d');
        foreach (array_keys($logged) as $oldDate) if ($oldDate<$limit) unset($logged[$oldDate]);
        return $logged;
    });
    echo json_encode(['ok'=>true,'sent'=>$sentCount,'failed'=>$failedCount,'due_tasks'=>$dueCount],JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    rp_fail(503,'Falha no motor de notificações; nenhuma conclusão foi alterada.');
}
