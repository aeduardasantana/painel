<?php
declare(strict_types=1);
require __DIR__.'/rotinas-push-common.php';
rp_jsonHeaders();
rp_authenticate();
if ($_SERVER['REQUEST_METHOD']==='GET') {
    try {
        $vapid=rp_vapid();
        echo json_encode(['ok'=>true,'publicKey'=>$vapid['publicKey']],JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        rp_fail(503,'Notificações em segundo plano indisponíveis nesta hospedagem.');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD']!=='POST') rp_fail(405,'Método não permitido.');
if (!hash_equals((string)($_SESSION['rotinas_csrf']??''),(string)($_SERVER['HTTP_X_CSRF_TOKEN']??''))) rp_fail(403,'Validação de segurança expirada.');
if (strlen((string)($_SERVER['CONTENT_LENGTH']??'0'))>5000) rp_fail(413,'Dados grandes demais.');
$body=json_decode((string)file_get_contents('php://input'),true);
if (!is_array($body)) rp_fail(400,'JSON inválido.');
$action=(string)($body['action']??'');
$sub=rp_subscription((array)($body['subscription']??[]));
if (!in_array($action,['subscribe','unsubscribe'],true) || $sub===null) rp_fail(400,'Inscrição inválida.');
$id=hash('sha256',$sub['endpoint']);
try {
    rp_mutate('.rotinas-subs.json',function(array $all) use ($action,$sub,$id): array {
        if ($action==='unsubscribe') {unset($all[$id]);return $all;}
        if (count($all)>=12 && !array_key_exists($id,$all)) throw new RuntimeException('Limite de dispositivos alcançado.');
        $all[$id]=['subscription'=>$sub,'active'=>true,'registered_at'=>date(DATE_ATOM)];
        return $all;
    });
} catch (Throwable $e) {
    rp_fail(503,'Não foi possível salvar a configuração de notificações.');
}
echo json_encode(['ok'=>true,'action'=>$action],JSON_UNESCAPED_UNICODE);
