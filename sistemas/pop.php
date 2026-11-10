<?php
declare(strict_types=1);
ini_set('display_errors','0');
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_secure','1');
session_name('GEB_SYSTEMS_SESSION');
session_set_cookie_params(['lifetime'=>0,'path'=>'/sistemas/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: frame-ancestors \'none\'');
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') {http_response_code(403);exit('HTTPS obrigatório.');}
if (($_SESSION['auth']??false)!==true || time()-(int)($_SESSION['auth_at']??0)>=28800) {
 header('Location: /sistemas/',true,302); exit;
}
$_SESSION['auth_at']=time();
$file=__DIR__.'/pop.html';
if (!is_readable($file)) {http_response_code(503);exit('POP indisponível.');}
header('Content-Type: text/html; charset=UTF-8');
readfile($file);
