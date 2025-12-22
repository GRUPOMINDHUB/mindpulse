<?php
// auth/switch_company.php — troca de empresa (JSON + GET fallback)
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

function respond_json($arr, $code=200){
  while (ob_get_level()) ob_end_clean();
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // aceita JSON, POST form ou GET
  $raw = file_get_contents('php://input');
  $in  = json_decode($raw, true);
  $cid = 0;
  if (is_array($in) && isset($in['company_id'])) {
    $cid = (int)$in['company_id'];
  } elseif (isset($_POST['company_id'])) {
    $cid = (int)$_POST['company_id'];
  } elseif (isset($_GET['company_id'])) {
    $cid = (int)$_GET['company_id'];
  }
  if ($cid <= 0) throw new Exception('company_id inválido');

  $userId = (int)$_SESSION['user']['id'];

  // valida se o usuário tem acesso à empresa
  $sql = "SELECT c.id, c.name, c.trade_name
          FROM companies c
          JOIN user_company uc ON uc.company_id=c.id
          WHERE uc.user_id=? AND c.id=? LIMIT 1";
  $st  = $pdo->prepare($sql);
  $st->execute([$userId, $cid]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) throw new Exception('Você não tem acesso a esta empresa.');

  // seta empresa atual na sessão
  $_SESSION['current_company'] = ['id' => (int)$row['id'], 'trade_name' => $row['trade_name']];

  // recarrega a lista de empresas na sessão (garante o select do header)
  $st2 = $pdo->prepare("SELECT c.id, c.name, c.trade_name 
                        FROM companies c
                        JOIN user_company uc ON uc.company_id=c.id
                        WHERE uc.user_id=? ORDER BY c.trade_name");
  $st2->execute([$userId]);
  $_SESSION['companies'] = $st2->fetchAll(PDO::FETCH_ASSOC);

  // Se veio via AJAX/POST → JSON; se foi GET → redireciona de volta
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $back = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL.'/pages/home.php' : '/');
    while (ob_get_level()) ob_end_clean();
    header('Location: '.$back); exit;
  } else {
    respond_json(['status'=>'ok']);
  }
} catch(Throwable $e) {
  // JSON para POST; para GET, mensagem simples
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(400);
    echo 'Erro ao trocar empresa: '.$e->getMessage();
    exit;
  } else {
    respond_json(['status'=>'error','message'=>$e->getMessage()], 400);
  }
}
