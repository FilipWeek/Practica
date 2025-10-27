<?php
require_once __DIR__ . '/../ctf_utils.php';
$db = get_db();
/*
  Ahora contamos capturas por usuario (flags capturadas) y ordenamos por cantidad (desc).
  En caso de empate, ordenamos por la primera captura (created_at asc) para dar ventaja al que las capturó antes.
*/
$rows = $db->query("
    SELECT u.username,
           COALESCE(COUNT(c.id),0) AS flags_count,
           MIN(c.created_at) as first_cap
    FROM users u
    LEFT JOIN captures c ON u.id = c.user_id
    GROUP BY u.id
    ORDER BY flags_count DESC, first_cap ASC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
