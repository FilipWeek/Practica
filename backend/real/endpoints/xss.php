<?php
// Reflected XSS didáctico — revela la flag cuando el parámetro 'msg' contiene indicadores claros
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = intval($_SESSION['user_id'] ?? 0);
$cid = 4; // challenge id para XSS
$flag = gen_user_flag($uid,$cid);
$msg = $_GET['msg'] ?? '';
?><!doctype html><html><head><meta charset="utf-8"></head><body style="background:#000;color:#9ef;font-family:monospace;">
<h2>Reflected tester</h2>
<p>Input: <?php echo htmlspecialchars($msg); ?></p>
<?php
// Condición simple: si el input parece contener un payload reflectivo (script) o keywords 'reveal'/'flag'
// mostramos la flag (didáctico). Esto unifica la lógica con los demás endpoints.
$needle_checks = ['<script','reveal','flag','exploit'];
$found = false;
foreach($needle_checks as $n){
    if($msg !== '' && stripos($msg,$n) !== false){
        $found = true; break;
    }
}

if($found){
    echo "<div class='xss-note'>Reflected note: ".htmlspecialchars($flag)."</div>";
} else {
    echo "<div>No reflected content detected.</div>";
}
?>
</body></html>
