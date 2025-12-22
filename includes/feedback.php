<?php
// includes/feedback.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function fb_sentiments(): array {
  // score 1..5 (pior -> melhor), label, emoji, título gamificado
  return [
    ['score'=>5,'key'=>'excelente','emoji'=>'🚀','title'=>'No topo!'],
    ['score'=>4,'key'=>'bem','emoji'=>'🙂','title'=>'Mandando bem'],
    ['score'=>3,'key'=>'ok','emoji'=>'😐','title'=>'Tudo ok'],
    ['score'=>2,'key'=>'sobrecarregado','emoji'=>'😓','title'=>'Correria'],
    ['score'=>1,'key'=>'estressado','emoji'=>'😣','title'=>'Precisando de apoio'],
  ];
}

function fb_categories(): array {
  return [
    'melhoria_processo' => 'Sugestão de melhoria',
    'suporte_operacional'=> 'Preciso de ajuda',
    'ocorrencia'         => 'Ocorrência / incidente',
    'feedback_geral'     => 'Feedback construtivo',
    'reconhecimento'     => 'Reconhecimento / elogio',
    'infra_recursos'     => 'Infraestrutura / recursos',
  ];
}

function fb_status_badge(string $st): string {
  $map = [
    'aberto'       => 'background:#ff4d4f;color:#0f1117',
    'em_andamento' => 'background:#ffd666;color:#0f1117',
    'concluido'    => 'background:#36cfc9;color:#0f1117',
  ];
  $style = $map[$st] ?? 'background:#9aa4b2;color:#0f1117';
  return "<span class=\"badge\" style=\"$style\">".ucwords(str_replace('_',' ',$st))."</span>";
}

function fb_create(PDO $pdo, int $companyId, int $userId, array $data): int {
  $sql="INSERT INTO feedback_tickets
        (company_id,user_id,sentiment_key,sentiment_score,category,message,status,created_at,updated_at)
        VALUES (?,?,?,?,?,?, 'aberto', NOW(), NOW())";
  $st = $pdo->prepare($sql);
  $st->execute([
    $companyId,$userId,
    $data['sentiment_key'],$data['sentiment_score'],
    $data['category'], $data['message']
  ]);
  return (int)$pdo->lastInsertId();
}

function fb_my_tickets(PDO $pdo, int $companyId, int $userId): array {
  $st=$pdo->prepare("SELECT * FROM feedback_tickets WHERE company_id=? AND user_id=? ORDER BY created_at DESC");
  $st->execute([$companyId,$userId]);
  return $st->fetchAll() ?: [];
}

function fb_list_admin(PDO $pdo, int $companyId): array {
  $st=$pdo->prepare("SELECT t.*, u.name AS user_name, u.avatar_url
                     FROM feedback_tickets t
                     JOIN users u ON u.id=t.user_id
                     WHERE t.company_id=?
                     ORDER BY FIELD(t.status,'aberto','em_andamento','concluido'), t.created_at DESC");
  $st->execute([$companyId]);
  return $st->fetchAll() ?: [];
}

function fb_update_status(PDO $pdo, int $id, int $companyId, string $status): void {
  $allowed=['aberto','em_andamento','concluido'];
  if(!in_array($status,$allowed,true)) throw new Exception('Status inválido');
  $st=$pdo->prepare("UPDATE feedback_tickets SET status=?, updated_at=NOW() WHERE id=? AND company_id=?");
  $st->execute([$status,$id,$companyId]);
}
