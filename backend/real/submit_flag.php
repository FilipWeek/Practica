<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if(empty($_SESSION['user_id'])){ http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'No autenticado']); exit; }
require_once __DIR__ . '/../ctf_utils.php';
$uid = intval($_SESSION['user_id']);
$cid = intval($_POST['challenge_id'] ?? 0);
$flag = trim($_POST['flag'] ?? '');
if(!$cid || $flag === ''){ http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Parámetros faltantes']); exit; }
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$res = verify_and_register($uid,$cid,$flag,$ip,$ua);

// Devolvemos resolved=true si válida; esto facilita la UI
if($res['ok']){
    echo json_encode(['ok'=>true,'msg'=>$res['msg'],'resolved'=>true,'challenge_id'=>$cid]);
} else {
    echo json_encode(['ok'=>false,'msg'=>$res['msg'],'resolved'=>false,'challenge_id'=>$cid]);
}
