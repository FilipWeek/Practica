<?php
// login sin contraseña (solo username) - laboratorio aislado
session_start();
require_once __DIR__ . '/../ctf_utils.php';
$db = get_db();
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    if($username === '') { $error = 'Ingresa un nombre de usuario'; }
    else {
        // crear usuario si no existe (guardamos IP de registro)
        $stmt = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u'=>$username]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $regip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if(!$r){
            $ins = $db->prepare('INSERT INTO users (username, registration_ip) VALUES (:u, :ip)');
            $ins->execute([':u'=>$username, ':ip'=>$regip]);
            $uid = $db->lastInsertId();
        } else {
            $uid = $r['id'];
            // si usuario existe pero no tiene ip, actualizarla (no sobreescribimos si ya tiene)
            $stmt2 = $db->prepare('SELECT registration_ip FROM users WHERE id=:id LIMIT 1');
            $stmt2->execute([':id'=>$uid]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if(empty($row['registration_ip'])){
                $db->prepare('UPDATE users SET registration_ip=:ip WHERE id=:id')->execute([':ip'=>$regip,':id'=>$uid]);
            }
        }
        $_SESSION['user_id'] = intval($uid);
        $_SESSION['username'] = $username;
        init_flags_for_user($uid);
        header('Location: dashboard.php'); exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>CTF — Login (sin contraseña)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-black text-light">
  <div class="vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card bg-dark border-success shadow-lg">
            <div class="card-body">
              <h3 class="text-center text-success mb-3">CTF Lab — Acceso</h3>
              <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
              <form method="post">
                <div class="mb-3">
                  <label class="form-label">Nombre de usuario (sin contraseña)</label>
                  <input name="username" class="form-control form-control-lg bg-black text-light" placeholder="ej. alumno01" required>
                </div>
                <button class="btn btn-success w-100">Entrar / Registrar</button>
              </form>
              <p class="mt-3 text-muted small">Login sin contraseña para prácticas. No usar en producción.</p>
              <p class="mt-1 small text-muted">Nota: tu IP de registro (<?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'); ?>) se usa para generar flags únicas.</p>
            </div>
          </div>
          <div class="text-center mt-3 text-muted small">Ataca desde Kali: nmap, gobuster, burp, sqlmap (solo con permiso).</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
