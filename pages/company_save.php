<?php
// pages/company_save.php — versão alinhada ao seu schema MySQL 5.6
if (session_status()===PHP_SESSION_NONE) session_start();
ob_start(); header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(); if(!canAccessAdmin()){ http_response_code(403); echo json_encode(['status'=>'error','message'=>'Acesso negado']); exit; }

try{
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $name       = trim($in['name'] ?? '');
  $trade_name = trim($in['trade_name'] ?? '');
  $document   = trim($in['document'] ?? '');
  $logo_url   = trim($in['logo_url'] ?? '');
  $is_active  = !empty($in['is_active']) ? 1 : 0;

  if($name==='')       throw new Exception('Nome da empresa é obrigatório.');
  if($trade_name==='') throw new Exception('Nome fantasia é obrigatório.');
  if($document==='')   throw new Exception('CNPJ é obrigatório.');
  if($logo_url==='')   $logo_url = ''; // NOT NULL → salva string vazia se não vier nada

  // INSERT explícito para seu schema:
  // id (AI), name, trade_name, document, logo_url, is_active, created_at(NOW())
  $sql = "INSERT INTO companies
          (name, trade_name, document, logo_url, is_active, created_at)
          VALUES (?, ?, ?, ?, ?, NOW())";
  $st  = $pdo->prepare($sql);
  $st->execute([$name, $trade_name, $document, $logo_url, (int)$is_active]);
  $companyId = (int)$pdo->lastInsertId();

  // Vincula o usuário atual à nova empresa
  $userId = (int)$_SESSION['user']['id'];
  $pdo->prepare("INSERT IGNORE INTO user_company (user_id, company_id) VALUES (?,?)")
      ->execute([$userId,$companyId]);

  // Atualiza empresas na sessão (para o select do header)
  $st2 = $pdo->prepare("SELECT c.id, c.name
                        FROM companies c
                        JOIN user_company uc ON uc.company_id=c.id
                        WHERE uc.user_id=?
                        ORDER BY c.name");
  $st2->execute([$userId]);
  $_SESSION['companies'] = $st2->fetchAll(PDO::FETCH_ASSOC);
  $_SESSION['current_company'] = ['id'=>$companyId,'name'=>$name];

  while (ob_get_level()) ob_end_clean();
  echo json_encode(['status'=>'ok','company_id'=>$companyId], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
