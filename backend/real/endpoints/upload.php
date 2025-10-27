<?php
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = $_SESSION['user_id'] ?? 0;
$cid = 6;
$flag = gen_user_flag($uid,$cid);
$updir = __DIR__ . '/../uploads/';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])){
    $fn = basename($_FILES['file']['name']);
    $target = $updir . $fn;
    move_uploaded_file($_FILES['file']['tmp_name'],$target);
    file_put_contents($updir . 'meta_' . intval($uid) . '.txt', $flag);
    echo "Uploaded. Meta file meta_" . intval($uid) . ".txt created.";
    exit;
}
?><!doctype html><html><body>
<form method="post" enctype="multipart/form-data">
  <input type="file" name="file">
  <button>Upload</button>
</form>
</body></html>
