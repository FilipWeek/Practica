<?php
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = intval($_SESSION['user_id'] ?? 0);
$cid = 3; // challenge id para SQLi
$flag = gen_user_flag($uid,$cid);
$q = $_GET['q'] ?? '';
?><!doctype html><html><body style="background:#000;color:#9ef;font-family:monospace;">
<h2>Search</h2>
<p>Query: <?php echo htmlspecialchars($q); ?></p>
<?php
// Comportamiento didáctico: si la query contiene 'admin' mostramos la nota interna con la flag
// (ahora la flag se genera con la misma función gen_user_flag, igual que los demás retos)
if($q !== '' && stripos($q,'admin') !== false){
    echo "<div class='internal-note'>Internal note: ".htmlspecialchars($flag)."</div>";
} else {
    echo "<div>No special results.</div>";
}
?>
</body></html>
