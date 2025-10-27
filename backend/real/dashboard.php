<?php
session_start();
if(empty($_SESSION['user_id'])){ header('Location: index.php'); exit; }
require_once __DIR__ . '/../ctf_utils.php';
$db = get_db();
$uid = intval($_SESSION['user_id']);
$user = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

// obtener retos y estado
$stmt = $db->prepare('SELECT uf.id, uf.challenge_id, c.name, c.points, uf.resolved FROM user_flags uf JOIN challenges c ON c.id=uf.challenge_id WHERE uf.user_id=:u ORDER BY c.id');
$stmt->execute([':u'=>$uid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// obtener ip registrada para mostrar
$stmt_ip = $db->prepare('SELECT registration_ip FROM users WHERE id=:id LIMIT 1'); $stmt_ip->execute([':id'=>$uid]); $rip = $stmt_ip->fetchColumn();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard — CTF</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-black text-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="text-success">CTF Lab — <?php echo $user; ?></h2>
      <div class="text-muted small">IP registrada: <?php echo htmlspecialchars($rip ?: 'no registrada'); ?></div>
    </div>
    <div>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Salir</a>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="row gy-3" id="challenges-list">
        <?php foreach($rows as $r): ?>
          <div class="col-md-6" data-challenge-card="<?php echo intval($r['challenge_id']); ?>">
            <div class="card bg-dark border-secondary">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="text-warning mb-1"><?php echo htmlspecialchars($r['name']); ?></h5>
                    <div class="text-muted small">Reto #<?php echo intval($r['challenge_id']); ?> — <?php echo intval($r['points']); ?> pts</div>
                  </div>
                  <div class="challenge-badge">
                    <?php if($r['resolved']): ?>
                      <span class="badge bg-success">Resuelto</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Pendiente</span>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if(!$r['resolved']): ?>
                  <form class="mt-3 submit-flag-form" method="post" data-challenge-id="<?php echo intval($r['challenge_id']); ?>">
                    <div class="input-group">
                      <input name="flag" class="form-control form-control-sm bg-black text-light" placeholder="Pega tu flag aquí" required>
                      <input type="hidden" name="challenge_id" value="<?php echo intval($r['challenge_id']); ?>">
                      <button class="btn btn-sm btn-primary">Enviar</button>
                    </div>
                    <div class="mt-2 small text-success d-none form-success">Resuelto — buen trabajo</div>
                    <div class="mt-2 small text-danger d-none form-error"></div>
                  </form>
                <?php else: ?>
                  <div class="mt-3 text-success small">Resuelto — buen trabajo</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-lg-4">
      <!-- Leaderboard -->
      <div class="card bg-dark mb-3">
        <div class="card-body" style="color:#a0f0d1; font-family: 'Courier New', monospace; font-size:0.95rem;">
          <h5 class="text-success">Leaderboard</h5>
          <div id="leaderboard">Cargando...</div>
        </div>
      </div>

      <!-- Consejos rápidos -->
      <div class="card bg-dark">
        <div class="card-body" style="color:#a0f0d1; font-family: 'Courier New', monospace; font-size:0.95rem;">
          <h5 class="text-success">Consejos Rápidos</h5>
          <ul style="padding-left:1rem; list-style-type:disc;">
            <li>Recon: nmap -sC -sV -Pn -p 80,443,8080,8000 192.168.1.87 -oN nmap_web.txt</li>
            <li>Dir enum: gobuster dir -u http://192.168.1.87:8080/ -w /usr/share/wordlists/dirb/common.txt -x php,txt,html</li>
            <li>Inspect with Burp Suite para XSS/SQLi</li>
            <li>Subir archivo para practicar upload (Upload)</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
(function(){
  // Refresca el leaderboard pidiendo al endpoint /real/leaderboard.php
  function refreshLeaderboard(){
    axios.get('leaderboard.php').then(function(res){
      var el = document.getElementById('leaderboard');
      if(!el) return;
      var html = '';
      res.data.forEach(function(r,i){
        // La API devuelve username y flags_count
        var n = (typeof r.flags_count !== 'undefined') ? r.flags_count : (r.total || 0);
        html += '<div class="mb-1">'+(i+1)+'. <strong>'+r.username+'</strong> — '+ n +'</div>';
      });
      el.innerHTML = html;
    }).catch(function(err){
      // si falla, mostramos mensaje simple
      var el = document.getElementById('leaderboard');
      if(el) el.innerHTML = '<div class="small text-muted">No se pudo cargar leaderboard</div>';
      console.error('Leaderboard error', err);
    });
  }
  // refrescar primero y luego cada 10s
  refreshLeaderboard();
  setInterval(refreshLeaderboard, 10000);

  // manejo de envío de flags por AJAX (mantener funcionalidad previa)
  function onSubmit(ev){
    ev.preventDefault();
    var form = ev.currentTarget;
    var challengeId = form.getAttribute('data-challenge-id') || (form.querySelector('input[name="challenge_id"]')||{}).value;
    var input = form.querySelector('input[name="flag"]');
    var btn = form.querySelector('button');
    var successEl = form.querySelector('.form-success');
    var errEl = form.querySelector('.form-error');

    // limpiar mensajes
    if(successEl) successEl.classList.add('d-none');
    if(errEl) { errEl.classList.add('d-none'); errEl.textContent = ''; }

    if(!input || !input.value.trim()){
      if(errEl){ errEl.textContent = 'Ingresa una flag'; errEl.classList.remove('d-none'); }
      return;
    }

    btn.disabled = true;

    var fd = new FormData();
    fd.append('challenge_id', challengeId);
    fd.append('flag', input.value.trim());

    axios.post('submit_flag.php', fd).then(function(resp){
      var data = resp.data;
      if(data && data.ok){
        // actualizar badge y reemplazar form por estado "Resuelto"
        var cardWrapper = form.closest('[data-challenge-card]');
        if(cardWrapper){
          var badge = cardWrapper.querySelector('.challenge-badge span');
          if(badge){
            badge.textContent = 'Resuelto';
            badge.classList.remove('bg-danger');
            badge.classList.add('bg-success');
          }
          if(successEl){ successEl.classList.remove('d-none'); }
          setTimeout(function(){
            if(form.parentNode){
              form.parentNode.innerHTML = '<div class="mt-3 text-success small">Resuelto — buen trabajo</div>';
            }
          }, 600);
        }
        // refrescar leaderboard
        refreshLeaderboard();
      } else {
        if(errEl){ errEl.textContent = data && data.msg ? data.msg : 'Flag inválida'; errEl.classList.remove('d-none'); }
      }
    }).catch(function(error){
      if(errEl){ errEl.textContent = 'Error de red o servidor'; errEl.classList.remove('d-none'); }
      console.error('Submit error', error);
    }).finally(function(){
      btn.disabled = false;
    });
  }

  // atachamos listeners a todos los forms disponibles
  var forms = document.querySelectorAll('.submit-flag-form');
  forms.forEach(function(f){
    f.addEventListener('submit', onSubmit);
  });

})();
</script>
</body>
</html>
