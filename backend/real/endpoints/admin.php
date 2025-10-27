<?php
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = $_SESSION['user_id'] ?? 0;
$cid = 5;
$flag = gen_user_flag($uid,$cid);
$user = $_GET['user'] ?? '';
$pass = $_GET['pass'] ?? '';
?><!doctype html><html><body style="background:#001;color:#afa;">
<h2>Admin Panel</h2>
<?php
// credenciales débiles predefinidas sólo para demo
if($user === 'admin' && $pass === 'admin123'){
    echo "<div>Welcome admin. Footer token: ".htmlspecialchars($flag)."</div>";
} else {
    echo "<div>Access denied</div>";
}
?>
</body></html>
