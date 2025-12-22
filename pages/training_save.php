<?php
// pages/training_save.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!canAccessAdmin()) { http_response_code(403); exit('Acesso negado'); }

$company_id = (int)($_POST['company_id'] ?? 0);
$title      = trim($_POST['title'] ?? '');
$objective  = trim($_POST['objective'] ?? '');
$description= trim($_POST['description'] ?? '');
$cover      = trim($_POST['cover_image'] ?? '');
$reward     = trim($_POST['reward_image'] ?? '');
$difficulty = $_POST['difficulty'] ?? 'Iniciante';
$est_min    = (int)($_POST['estimated_minutes'] ?? 0);
$tags       = trim($_POST['tags'] ?? '');
$is_active  = (int)($_POST['is_active'] ?? 1);
$roles      = $_POST['roles'] ?? [];
$videos     = $_POST['videos'] ?? [];

if ($company_id<=0 || $title==='' || $objective==='') {
  header('Location: '.url_for('/pages/training_new.php')); exit;
}

try {
  $pdo->beginTransaction();

  // cria treinamento
  $insT = $pdo->prepare("INSERT INTO trainings
    (company_id,title,objective,description,cover_image,reward_image,difficulty,estimated_minutes,tags,is_active)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
  $insT->execute([$company_id,$title,$objective,$description,$cover,$reward,$difficulty,$est_min,$tags,$is_active]);
  $training_id = (int)$pdo->lastInsertId();

  // vincula cargos
  if (!empty($roles)) {
    $insRT = $pdo->prepare("INSERT IGNORE INTO role_training (role_id, training_id) VALUES (?,?)");
    foreach ($roles as $rid) {
      $rid = (int)$rid; if ($rid>0) $insRT->execute([$rid,$training_id]);
    }
  }

  // aulas
  if (!empty($videos)) {
    $insV = $pdo->prepare("INSERT INTO training_videos
      (training_id,title,summary,video_provider,video_ref,thumb_image,duration_seconds,order_index,is_active)
      VALUES (?,?,?,?,?,?,?,?,1)");
    foreach ($videos as $v) {
      $titleV = trim($v['title'] ?? '');
      $summary= trim($v['summary'] ?? '');
      $prov   = trim($v['video_provider'] ?? 'youtube');
      $ref    = trim($v['video_ref'] ?? '');
      $thumb  = trim($v['thumb_image'] ?? '');
      $dur    = (int)($v['duration_seconds'] ?? 0);
      $ord    = max(1, (int)($v['order_index'] ?? 1));
      if ($titleV!=='' && $ref!=='') {
        $insV->execute([$training_id,$titleV,$summary,$prov,$ref,$thumb,$dur,$ord]);
      }
    }
  }

  $pdo->commit();
  header('Location: '.url_for('/pages/treinamento.php').'?id='.$training_id); exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo "Erro ao salvar: ".$e->getMessage();
}
