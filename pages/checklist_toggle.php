<?php
// Endpoint AJAX — marcar/desmarcar (com buffer limpo)
if (session_status()===PHP_SESSION_NONE) session_start();
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/checklist.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

try {
  $in     = json_decode(file_get_contents('php://input'), true) ?: [];
  $action = $in['action'] ?? '';
  $taskId = (int)($in['task_id'] ?? 0);
  $clId   = (int)($in['checklist_id'] ?? 0);
  $freq   = $in['frequency'] ?? 'daily';
  $mode   = $in['period'] ?? 'current';

  $userId    = (int)$_SESSION['user']['id'];
  $companyId = currentCompanyId();
  if (!$taskId || !$clId || !$companyId) throw new Exception('Parâmetros inválidos');

  $pkey = ($mode==='prev') ? period_key_prev($freq) : period_key_for($freq);
  if ($action==='check') {
    $late = ($mode==='current') ? dueWindowLate($freq) : true;
    markTask($pdo,$clId,$taskId,$userId,$companyId,$pkey,$late);
  } elseif ($action==='uncheck') {
    unmarkTask($pdo,$taskId,$companyId,$pkey);
  } else {
    throw new Exception('Ação inválida');
  }

  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok','period_key'=>$pkey], JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
