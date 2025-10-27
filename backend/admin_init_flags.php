<?php
require_once __DIR__ . '/ctf_utils.php';
// Ejecutar solo como admin para inicializar flags de usuarios existentes
// Use este script después de crear usuarios.


$users = get_db()->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
foreach($users as $u){ init_flags_for_user($u); }
echo "Flags inicializadas para usuarios: " . implode(',', $users) . "\n";