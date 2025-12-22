<?php
// pages/collaborator_save.php — tolerante + TX segura
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!canAccessAdmin()) { http_response_code(403); exit('Acesso negado'); }

function hasColumn(PDO $pdo, string $table, string $col): bool {
  $st = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
  $st->execute([$table, $col]);
  return (bool)$st->fetchColumn();
}

$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = trim($_POST['password'] ?? '');
$avatar     = trim($_POST['avatar_url'] ?? '');
$birthday   = ($_POST['birthday'] ?? '') ?: null;
$phone      = trim($_POST['phone'] ?? '');
$status     = (int)($_POST['status'] ?? 1);
$type       = ($_POST['type'] ?? 'Colaborador') === 'Admin' ? 'Admin' : 'Colaborador';
$companies  = $_POST['companies'] ?? [];
$roles      = $_POST['roles'] ?? [];
$notes      = trim($_POST['notes'] ?? '');

if ($name==='' || $email==='') { header('Location: '.url_for('/pages/collaborator_new.php')); exit; }

// Evita e-mail duplicado
$dup = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$dup->execute([$email]);
if ($dup->fetch()) { http_response_code(409); exit('Email já cadastrado.'); }

$inTx = false;
try {
  // Inicia a transação de forma segura
  if (method_exists($pdo, 'beginTransaction')) {
    if (!$pdo->inTransaction()) { $inTx = $pdo->beginTransaction(); }
  }

  // Monta INSERT apenas com colunas existentes
  $cols = ['name','email','password_hash','type'];
  $vals = [ $name, $email, ($password ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT)), $type ];

  if (hasColumn($pdo,'users','avatar_url')) { $cols[]='avatar_url'; $vals[] = $avatar ?: null; }
  if (hasColumn($pdo,'users','birthday'))   { $cols[]='birthday';   $vals[] = $birthday; }
  if (hasColumn($pdo,'users','phone'))      { $cols[]='phone';      $vals[] = $phone ?: null; }
  if (hasColumn($pdo,'users','status'))     { $cols[]='status';     $vals[] = $status; }
  if (hasColumn($pdo,'users','created_at')) { $cols[]='created_at'; $vals[] = date('Y-m-d H:i:s'); }

  $place = rtrim(str_repeat('?,', count($vals)), ',');
  $sql = "INSERT INTO users (".implode(',', $cols).") VALUES ($place)";
  $st  = $pdo->prepare($sql);
  $st->execute($vals);
  $userId = (int)$pdo->lastInsertId();

  // user_meta (notes)
  $pdo->exec("CREATE TABLE IF NOT EXISTS user_meta (
    user_id INT NOT NULL, meta_key VARCHAR(64) NOT NULL, meta_value TEXT,
    PRIMARY KEY (user_id, meta_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if ($notes !== '') {
    $m = $pdo->prepare("INSERT INTO user_meta (user_id, meta_key, meta_value)
                        VALUES (?,?,?) ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value)");
    $m->execute([$userId,'notes',$notes]);
  }

  // empresas
  if (!empty($companies)) {
    $insUC = $pdo->prepare("INSERT IGNORE INTO user_company (user_id, company_id) VALUES (?,?)");
    foreach ($companies as $cid) { $cid=(int)$cid; if($cid>0) $insUC->execute([$userId,$cid]); }
  } else {
    $currentCompany = currentCompanyId();
    if ($currentCompany) {
      $pdo->prepare("INSERT IGNORE INTO user_company (user_id, company_id) VALUES (?,?)")->execute([$userId,(int)$currentCompany]);
    }
  }

  // cargos
  if (!empty($roles)) {
    $insUR = $pdo->prepare("INSERT IGNORE INTO user_role (user_id, role_id) VALUES (?,?)");
    foreach ($roles as $rid) { $rid=(int)$rid; if($rid>0) $insUR->execute([$userId,$rid]); }
  }

  if ($inTx && $pdo->inTransaction()) { $pdo->commit(); }
  header('Location: '.url_for('/pages/colaboradores.php')); exit;

} catch (Throwable $e) {
  if ($inTx && method_exists($pdo,'inTransaction') && $pdo->inTransaction()) {
    try { $pdo->rollBack(); } catch (Throwable $ignore) {}
  }
  http_response_code(500);
  echo "Erro ao salvar colaborador: ".$e->getMessage();
}
