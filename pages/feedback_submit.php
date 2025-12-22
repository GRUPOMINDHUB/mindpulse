<?php
if (session_status()===PHP_SESSION_NONE) session_start();
ob_start(); header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
requireLogin();

try {
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $userId = (int)$_SESSION['user']['id'];
  $companyId = currentCompanyId();

  $data = [
    'sentiment_key'  => $in['sentiment_key'] ?? '',
    'sentiment_score'=> (int)($in['sentiment_score'] ?? 0),
    'category'       => $in['category'] ?? 'feedback_geral',
    'subject'        => trim($in['subject'] ?? ''),
    'message'        => trim($in['message'] ?? ''),
  ];
  if ($data['sentiment_key']==='' || $data['sentiment_score']<1) throw new Exception('Selecione o sentimento.');
  if ($data['message']==='') throw new Exception('Escreva sua mensagem.');

  // subject é opcional, guarde junto na mensagem (ou crie coluna se preferir)
  if ($data['subject']!=='') $data['message'] = 'Assunto: '.$data['subject']."\n\n".$data['message'];

  fb_create($pdo,$companyId,$userId,$data);

  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok']);
} catch(Throwable $e){
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
