<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
session_name('GEB_SYSTEMS_SESSION');
session_set_cookie_params(['lifetime'=>0,'path'=>'/sistemas/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: frame-ancestors \'none\'');
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403); exit('HTTPS obrigatório.');
}
if (isset($_POST['logout'])) {
    if (hash_equals((string)($_SESSION['csrf'] ?? ''),(string)($_POST['csrf'] ?? ''))) {
        $_SESSION=[]; session_regenerate_id(true);
    }
    header('Location: /sistemas/'); exit;
}
// Configure SYSTEMS_PASS_HASH on server with a password_hash() hash.
// Never include plaintext password in repo or in a public web directory.
$hash = getenv('SYSTEMS_PASS_HASH');
if (!$hash) {
    $config = dirname(__DIR__, 3).'/.geb-systems-auth.php';
    if (is_file($config)) {
        $loaded=require $config;
        if (is_string($loaded)) $hash=$loaded;
    }
}
if (!is_string($hash) || !password_get_info($hash)['algo']) {
    http_response_code(503);
    exit('Portal temporariamente indisponível: autenticação não configurada.');
}
if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24));
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])) {
    $validCsrf=hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''));
    $attempts=(int)($_SESSION['attempts']??0);
    $since=(int)($_SESSION['attempt_since']??0);
    if (time()-$since>900) { $attempts=0; $_SESSION['attempt_since']=time(); }
    if ($attempts>=5) {
        $error='Muitas tentativas. Tente novamente mais tarde.';
        http_response_code(429);
    } elseif ($validCsrf && is_string($_POST['password']) && password_verify($_POST['password'],$hash)) {
        session_regenerate_id(true);
        $_SESSION['auth']=true; $_SESSION['auth_at']=time();
        $_SESSION['attempts']=0; $_SESSION['csrf']=bin2hex(random_bytes(24));
        header('Location: /sistemas/'); exit;
    } else {
        $_SESSION['attempts']=$attempts+1;
        if (!$since) $_SESSION['attempt_since']=time();
        $error='Senha inválida.';
    }
}
$authenticated=($_SESSION['auth']??false)===true
  && time()-(int)($_SESSION['auth_at']??0)<28800;
if ($authenticated) {
    $_SESSION['auth_at']=time();
    $portal=__DIR__.'/index.html';
    if (!is_readable($portal)) {http_response_code(500);exit('Arquivo indisponível.');}
    header('Content-Type: text/html; charset=UTF-8');
    $content=file_get_contents($portal);
    // Server-side logout form, visible only after authentication.
    $logout='<form method="post" action="/sistemas/" class="systems-logout"><input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf'],ENT_QUOTES,'UTF-8').'"><button type="submit" name="logout" value="1">Sair</button></form>';
    $style='<style>.systems-logout{margin:12px 0}.systems-logout button{font:inherit;cursor:pointer;color:#fff;border:1px solid #7895b1;background:#172b42;border-radius:9px;padding:9px 16px}.systems-logout button:focus-visible{outline:3px solid #8fd8ff}</style>';
    echo str_replace('</header>',$logout.'</header>'.$style,$content);
    exit;
}
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Acesso protegido | GEB Tecnologia</title>
<style>:root{font-family:system-ui,-apple-system,Segoe UI,sans-serif;color:#eef5ff;background:#0b1524;color-scheme:dark}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(ellipse at 80% 0%,#1d3654,transparent 50%),#0b1524}.panel{width:min(440px,100%);padding:36px;border:1px solid #466583;border-radius:18px;background:#172b42}h1{font-size:clamp(28px,5vw,36px);margin:12px 0}p{color:#cbdbea;line-height:1.55}label{display:block;margin:25px 0 9px;font-weight:700}input{width:100%;padding:14px;border:1px solid #7895b1;border-radius:9px;background:#0b1524;color:#fff;font:inherit}button{margin-top:18px;width:100%;padding:14px;border:0;border-radius:9px;background:#e9f3ff;color:#10253c;font:inherit;font-weight:700;cursor:pointer}button:focus-visible,input:focus-visible{outline:3px solid #8fd8ff;outline-offset:3px}.error{color:#ffd1d1}small{color:#aac6de}</style></head>
<body><main class="panel"><small>GEB TECNOLOGIA · ACESSO RESTRITO</small><h1>Portal de Aplicações</h1><p>Informe a senha de acesso para continuar.</p>
<?php if ($error): ?><p class="error" role="alert"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></p><?php endif ?>
<form method="post" action="/sistemas/"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'],ENT_QUOTES,'UTF-8')?>">
<label for="senha">Senha</label><input id="senha" name="password" type="password" required autocomplete="current-password" autofocus>
<button type="submit">Entrar</button></form></main></body></html>
