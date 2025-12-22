<?php
if (session_status()===PHP_SESSION_NONE) session_start();
ob_start(); header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
requireLogin(); if(!canAccessAdmin()){ http_response_code(403); echo json_encode(['status'=>'error','message'=>'Acesso negado']); exit; }

try{
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $id = (int)($in['id'] ?? 0);
  $status = $in['status'] ?? 'aberto';
  if(!$id) throw new Exception('ID inválido');
  fb_update_status($pdo,$id,currentCompanyId(),$status);

  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok']);
}catch(Throwable $e){
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
