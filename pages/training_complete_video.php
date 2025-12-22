<?php
// pages/training_complete_video.php
// Garante que qualquer echo anterior não quebre o JSON de resposta
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
  $videoId    = (int)($input['video_id'] ?? 0);
  $userId     = (int)($_SESSION['user']['id'] ?? 0);
  $companyId  = currentCompanyId();

  if ($trainingId<=0 || $videoId<=0) {
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['status'=>'error','message'=>'Parâmetros inválidos']); exit;
  }

  // valida
  $training = trainingById($pdo, $trainingId, $companyId);
  if (!$training) {
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['status'=>'error','message'=>'Treinamento inválido.']); exit;
  }
  if (!userHasAccessToTraining($pdo, $userId, $trainingId)) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Acesso negado.']); exit;
  }

  // valida vídeo
  $st = $pdo->prepare("SELECT id FROM training_videos WHERE id=? AND training_id=? AND is_active=1");
  $st->execute([$videoId, $trainingId]);
  if (!$st->fetch()) {
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['status'=>'error','message'=>'Vídeo inválido.']); exit;
  }

  // marca progresso (idempotente)
  $ins = $pdo->prepare("INSERT INTO user_video_progress (user_id, video_id) VALUES (?,?)
                        ON DUPLICATE KEY UPDATE completed_at = VALUES(completed_at)");
  $ins->execute([$userId, $videoId]);

  $completed = awardTrainingIfComplete($pdo, $userId, $trainingId);

  // limpa qualquer saída anterior e responde puro JSON
  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok','training_completed'=>$completed], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Falha no servidor.'], JSON_UNESCAPED_UNICODE);
}
