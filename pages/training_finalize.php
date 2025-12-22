<?php
// pages/training_finalize.php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/training.php';
requireLogin();

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $trainingId = (int)($input['training_id'] ?? 0);
  $userId     = (int)($_SESSION['user']['id'] ?? 0);
  $companyId  = currentCompanyId();

  if ($trainingId<=0) { while (ob_get_level()) ob_end_clean(); echo json_encode(['status'=>'error','message'=>'Parâmetros inválidos']); exit; }

  $training = trainingById($pdo, $trainingId, $companyId);
  if (!$training) { while (ob_get_level()) ob_end_clean(); echo json_encode(['status'=>'error','message'=>'Treinamento inválido.']); exit; }
  if (!userHasAccessToTraining($pdo, $userId, $trainingId)) {
    while (ob_get_level()) ob_end_clean(); http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Acesso negado.']); exit;
  }

  $ok = awardTrainingIfComplete($pdo, $userId, $trainingId);

  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok','awarded'=>$ok], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Falha no servidor.'], JSON_UNESCAPED_UNICODE);
}
