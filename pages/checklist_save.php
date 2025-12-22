<?php
// Salvar checklist (Admin)
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(); if(!canAccessAdmin()){ http_response_code(403); exit('Acesso negado'); }

$title   = trim($_POST['title'] ?? '');
$desc    = trim($_POST['description'] ?? '');
$freq    = $_POST['frequency'] ?? 'daily';
$defRole = (int)($_POST['default_role_id'] ?? 0);
$roles   = $_POST['roles'] ?? [];
$tasks   = $_POST['tasks'] ?? [];

$companyId = currentCompanyId();
$userId    = (int)$_SESSION['user']['id'];
if ($title==='' || !$companyId) { header('Location: '.url_for('/pages/checklist_new.php')); exit; }

try {
  if (!$pdo->inTransaction()) $pdo->beginTransaction();

  $st = $pdo->prepare("INSERT INTO checklists (company_id,title,description,frequency,default_role_id,created_by)
                       VALUES (?,?,?,?,?,?)");
  $st->execute([$companyId,$title,$desc,$freq,$defRole?:null,$userId]);
  $clId = (int)$pdo->lastInsertId();

  if (!empty($roles)) {
    $ins = $pdo->prepare("INSERT INTO checklist_role (checklist_id,role_id) VALUES (?,?)");
    foreach($roles as $rid){ $rid=(int)$rid; if($rid>0) $ins->execute([$clId,$rid]); }
  }

  if (!empty($tasks)) {
    $insT = $pdo->prepare("INSERT INTO checklist_tasks (checklist_id,priority,name,period,notes) VALUES (?,?,?,?,?)");
    foreach($tasks as $t){
      $name = trim($t['name'] ?? ''); if ($name==='') continue;
      $pri  = max(1, min(5, (int)($t['priority'] ?? 3)));
      $per  = $t['period'] ?? 'final_dia';
      $notes= trim($t['notes'] ?? '') ?: null;
      $insT->execute([$clId,$pri,$name,$per,$notes]);
    }
  }

  $pdo->commit();
  header('Location: '.url_for('/pages/checklists.php')); exit;

} catch(Throwable $e){
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo "Erro ao salvar: ".$e->getMessage();
}
