<?php
declare(strict_types=1);

/* Módulo interno de Web Push; o servidor guarda subscrições e chaves fora do GitHub. */
function rp_jsonHeaders(): void {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-Frame-Options: DENY');
}
function rp_fail(int $status, string $reason): void {
    http_response_code($status);
    echo json_encode(['ok'=>false,'error'=>$reason], JSON_UNESCAPED_UNICODE);
    exit;
}
function rp_authenticate(): void {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') rp_fail(403,'HTTPS obrigatório.');
    ini_set('session.use_strict_mode','1');
    ini_set('session.use_only_cookies','1');
    ini_set('session.cookie_httponly','1');
    ini_set('session.cookie_secure','1');
    session_name('GEB_PORTAL_SESSION_V2');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if (($_SESSION['auth'] ?? false) !== true || time()-(int)($_SESSION['auth_at'] ?? 0)>=28800) rp_fail(401,'Sessão expirada: entre novamente no portal.');
    $_SESSION['auth_at']=time();
}
function rp_base64url(string $binary): string {
    return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
}
function rp_data(string $filename): array {
    $file = __DIR__.'/'.$filename;
    if (!is_file($file)) return [];
    $handle = @fopen($file,'r');
    if (!$handle) throw new RuntimeException('Armazenamento indisponível.');
    if (!flock($handle,LOCK_SH)) { fclose($handle); throw new RuntimeException('Bloqueio indisponível.'); }
    $body = stream_get_contents($handle);
    flock($handle,LOCK_UN); fclose($handle);
    $json = json_decode($body === false ? '' : $body,true);
    if (!is_array($json) || json_last_error()!==JSON_ERROR_NONE) throw new RuntimeException('Arquivo de registros inválido.');
    return $json;
}
/** Lock exclusivo sobre a versão corrente; o callback recebe os registros e retorna a nova versão. */
function rp_mutate(string $filename, callable $change): void {
    $handle = @fopen(__DIR__.'/'.$filename,'c+');
    if (!$handle) throw new RuntimeException('Armazenamento sem permissão de escrita.');
    @chmod(__DIR__.'/'.$filename,0600);
    if (!flock($handle,LOCK_EX)) { fclose($handle); throw new RuntimeException('Bloqueio indisponível.'); }
    try {
        rewind($handle); $raw=stream_get_contents($handle);
        $stored=trim((string)$raw)===''?[]:json_decode((string)$raw,true);
        if (!is_array($stored) || json_last_error()!==JSON_ERROR_NONE) throw new RuntimeException('Arquivo existente inválido; gravação cancelada.');
        $updated=$change($stored);
        if (!is_array($updated)) throw new RuntimeException('Retorno inválido.');
        $serialized=json_encode($updated,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        if ($serialized===false) throw new RuntimeException('Falha ao codificar registro.');
        rewind($handle);
        if (!ftruncate($handle,0) || fwrite($handle,$serialized)!==strlen($serialized) || !fflush($handle)) throw new RuntimeException('Falha ao gravar registro.');
    } finally {
        flock($handle,LOCK_UN); fclose($handle);
    }
}
function rp_vapid(): array {
    $config=[];
    rp_mutate('.rotinas-vapid.json',function(array $existing) use (&$config): array {
        if (isset($existing['publicKey'],$existing['privateKey'],$existing['subject'])) {
            $config=$existing; return $existing;
        }
        if (!function_exists('openssl_pkey_new')) throw new RuntimeException('OpenSSL indisponível.');
        $key=@openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_EC,'curve_name'=>'prime256v1']);
        if (!$key) throw new RuntimeException('Geração de chave não disponível neste PHP.');
        $detail=openssl_pkey_get_details($key);
        if (!is_array($detail) || !isset($detail['ec']['x'],$detail['ec']['y'],$detail['ec']['d'])) throw new RuntimeException('OpenSSL não fornece as chaves P-256 exigidas.');
        $config=[
            'publicKey'=>rp_base64url("\x04".$detail['ec']['x'].$detail['ec']['y']),
            'privateKey'=>rp_base64url($detail['ec']['d']),
            'subject'=>'mailto:contato@grupoeduardabispo.com.br',
        ];
        return $config;
    });
    return $config;
}
function rp_endpointAllowed(string $url): bool {
    if (strlen($url)<20 || strlen($url)>2048 || !filter_var($url,FILTER_VALIDATE_URL)) return false;
    $parts=parse_url($url);
    if (!is_array($parts) || strtolower((string)($parts['scheme']??''))!=='https'
        || isset($parts['user']) || isset($parts['pass'])
        || (isset($parts['port']) && (int)$parts['port']!==443)) return false;
    $host=strtolower((string)($parts['host']??''));
    if (in_array($host,['fcm.googleapis.com','updates.push.services.mozilla.com','push.services.mozilla.com','web.push.apple.com','android.googleapis.com'],true)) return true;
    foreach (['.push.apple.com','.push.services.mozilla.com','.notify.windows.com'] as $suffix) {
        if (str_ends_with($host,$suffix)) return true;
    }
    return false;
}
function rp_subscription(array $sub): ?array {
    $endpoint=(string)($sub['endpoint']??'');
    $keys=$sub['keys']??null;
    if (!rp_endpointAllowed($endpoint) || !is_array($keys)) return null;
    $p256dh=(string)($keys['p256dh']??'');
    $auth=(string)($keys['auth']??'');
    if (!preg_match('/^[A-Za-z0-9_-]{80,100}$/D',$p256dh) || !preg_match('/^[A-Za-z0-9_-]{18,30}$/D',$auth)) return null;
    $p=base64_decode(strtr($p256dh,'-_','+/'),true);
    $a=base64_decode(strtr($auth,'-_','+/'),true);
    if ($p===false || strlen($p)!==65 || $p[0]!=="\x04" || $a===false || strlen($a)!==16) return null;
    return ['endpoint'=>$endpoint,'keys'=>['p256dh'=>$p256dh,'auth'=>$auth]];
}
