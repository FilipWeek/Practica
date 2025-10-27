<?php
require_once __DIR__ . '/ctf_db.php';
$config = require __DIR__ . '/ctf_config.php';

// genera flag determinista por user + challenge + ip_registrada (si existe)
function gen_user_flag($user_id, $challenge_id, $ip=null){
    global $config;
    $db = get_db();

    // si no dan ip, intentamos obtener la ip registrada del usuario
    if($ip === null){
        $stmt = $db->prepare("SELECT registration_ip FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id'=>$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $ip = $row['registration_ip'] ?? '';
    }

    $payload = $user_id . '|' . $challenge_id . '|' . $ip . '|' . $config['flag_secret'];
    $h = hash_hmac('sha256', $payload, $config['flag_secret']);
    return 'FLAG-' . strtoupper(substr($h, 0, $config['flag_length']));
}

// crea filas user_flags para un usuario (usa la ip registrada para generar)
function init_flags_for_user($user_id){
    $db = get_db();
    // obtener ip registrada si la hay
    $stmt = $db->prepare("SELECT registration_ip FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id'=>$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $rip = $row['registration_ip'] ?? '';

    $challenges = $db->query("SELECT id FROM challenges")->fetchAll(PDO::FETCH_COLUMN);
    $ins = $db->prepare("INSERT IGNORE INTO user_flags (user_id,challenge_id,flag_value,resolved) VALUES (:u,:c,:f,0)");
    foreach($challenges as $c){
        $flag = gen_user_flag($user_id, $c, $rip);
        $ins->execute([':u'=>$user_id,':c'=>$c,':f'=>$flag]);
    }
}

// verifica y registra envío
function verify_and_register($user_id, $challenge_id, $flag_submitted, $ip, $ua){
    $db = get_db();
    $stmt = $db->prepare("SELECT id,flag_value,resolved FROM user_flags WHERE user_id=:u AND challenge_id=:c LIMIT 1");
    $stmt->execute([':u'=>$user_id,':c'=>$challenge_id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$r) return ['ok'=>false,'msg'=>'Flag no encontrada para este usuario'];
    $valid = ($r['flag_value'] === $flag_submitted) && !$r['resolved'];
    $db->prepare("INSERT INTO submissions (user_id,challenge_id,flag_submitted,valid,ip,user_agent) VALUES (:u,:c,:f,:v,:ip,:ua)")
       ->execute([':u'=>$user_id,':c'=>$challenge_id,':f'=>$flag_submitted,':v'=>$valid?1:0,':ip'=>$ip,':ua'=>$ua]);
    if($valid){
        $db->prepare("UPDATE user_flags SET resolved=1, solved_at=CURRENT_TIMESTAMP WHERE id=:id")->execute([':id'=>$r['id']]);
        $db->prepare("INSERT INTO captures (user_id,challenge_id,flag_value,points) VALUES (:u,:c,:f,(SELECT points FROM challenges WHERE id=:c2))")
          ->execute([':u'=>$user_id,':c'=>$challenge_id,':f'=>$flag_submitted,':c2'=>$challenge_id]);
        return ['ok'=>true,'msg'=>'Flag válida'];
    }
    return ['ok'=>false,'msg'=>'Flag inválida o ya resuelta'];
}
