<?php
require_once __DIR__ . '/../../ctf_utils.php';
session_start();
$uid = $_SESSION['user_id'] ?? 0;
$cid = 1;
$flag = gen_user_flag($uid,$cid);
?><!doctype html><html><body style="background:#000;color:#9ef;font-family:monospace;">
<h2>Recon — Quick Start</h2>
<p>Este reto (Recon) está hecho para que los alumnos encuentren su primera flag con facilidad.</p>
<div style="margin-top:20px;padding:12px;background:#071018;border:1px solid #0f766e;">
<strong>Tu flag (usuario <?php echo intval($uid ?: 0); ?>):</strong>
<pre><?php echo htmlspecialchars($flag); ?></pre>
</div>
<p class="small text-muted">Nota: la flag es única por usuario + IP registrada.</p>
</body></html>
