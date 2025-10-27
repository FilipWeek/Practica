<?php
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = $_SESSION['user_id'] ?? 0;
$cid = 2;
$flag = gen_user_flag($uid,$cid);
// si la ruta contiene '.hidden' mostramos
$uri = $_SERVER['REQUEST_URI'] ?? '';
if(strpos($uri,'.hidden') !== false){
    echo "Hidden file content: " . htmlspecialchars($flag);
} else {
    echo "Not found";
}
