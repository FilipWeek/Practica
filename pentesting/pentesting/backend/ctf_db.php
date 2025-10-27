<?php
$config = require __DIR__ . '/ctf_config.php';
function get_db(){
    global $config;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
        ]);
        $pdo->exec("SET NAMES utf8mb4");
    }
    return $pdo;
}
