<?php
require_once __DIR__ . '/config.php';

try {
  $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
  $pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  die("Erro ao conectar no banco.");
}

function findUserByEmail(PDO $pdo, string $email){
  $st = $pdo->prepare("SELECT id,name,email,password_hash,type,avatar_url FROM users WHERE email=? LIMIT 1");
  $st->execute([$email]); return $st->fetch();
}
function getUserCompanies(PDO $pdo, int $userId){
  $sql = "SELECT c.id,c.trade_name FROM user_company uc JOIN companies c ON c.id=uc.company_id WHERE uc.user_id=? ORDER BY c.name";
  $st=$pdo->prepare($sql); $st->execute([$userId]); return $st->fetchAll();
}
function getUserRoles(PDO $pdo, int $userId){
  $sql = "SELECT r.id,r.name FROM user_role ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? ORDER BY r.name";
  $st=$pdo->prepare($sql); $st->execute([$userId]); return $st->fetchAll();
}
