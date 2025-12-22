<?php
// includes/training.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function userRoles(PDO $pdo, int $userId): array {
  $st = $pdo->prepare("SELECT r.id, r.name
    FROM user_role ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=?");
  $st->execute([$userId]); return $st->fetchAll();
}

function trainingsForUser(PDO $pdo, int $userId, ?int $companyId): array {
  if (!$companyId) return [];
  // Treinamentos ativos da empresa que estejam vinculados a QUALQUER cargo do usuário
  $sql = "SELECT DISTINCT t.*
          FROM trainings t
          JOIN role_training rt ON rt.training_id = t.id
          JOIN user_role ur ON ur.role_id = rt.role_id
          WHERE ur.user_id = ? AND t.company_id = ? AND t.is_active=1
          ORDER BY t.created_at DESC";
  $st = $pdo->prepare($sql);
  $st->execute([$userId, $companyId]);
  return $st->fetchAll();
}

function trainingById(PDO $pdo, int $trainingId, ?int $companyId): ?array {
  $st = $pdo->prepare("SELECT * FROM trainings WHERE id=? AND company_id=? AND is_active=1 LIMIT 1");
  $st->execute([$trainingId, $companyId]);
  $row = $st->fetch();
  return $row ?: null;
}

function trainingVideos(PDO $pdo, int $trainingId): array {
  $st = $pdo->prepare("SELECT * FROM training_videos WHERE training_id=? AND is_active=1 ORDER BY order_index ASC");
  $st->execute([$trainingId]);
  return $st->fetchAll();
}

function userCompletedVideos(PDO $pdo, int $userId, int $trainingId): array {
  $st = $pdo->prepare("SELECT uvp.video_id
      FROM user_video_progress uvp
      JOIN training_videos tv ON tv.id = uvp.video_id
      WHERE uvp.user_id=? AND tv.training_id=?");
  $st->execute([$userId, $trainingId]);
  return array_column($st->fetchAll(), 'video_id');
}

function userTrainingProgress(PDO $pdo, int $userId, int $trainingId): array {
  $videos = trainingVideos($pdo, $trainingId);
  $total = count($videos);
  if ($total === 0) return ['total'=>0,'done'=>0,'percent'=>0,'nextVideoId'=>null];

  $doneIds = userCompletedVideos($pdo, $userId, $trainingId);
  $done = count($doneIds);
  $percent = (int) floor(($done / $total) * 100);

  // próximo vídeo é o 1º não concluído
  $next = null;
  foreach ($videos as $v) { if (!in_array($v['id'], $doneIds)) { $next = $v['id']; break; } }

  return ['total'=>$total,'done'=>$done,'percent'=>$percent,'nextVideoId'=>$next];
}

function awardTrainingIfComplete(PDO $pdo, int $userId, int $trainingId): bool {
  // se usuário já tem, retorna true
  $chk = $pdo->prepare("SELECT 1 FROM user_training_reward WHERE user_id=? AND training_id=? LIMIT 1");
  $chk->execute([$userId, $trainingId]);
  if ($chk->fetch()) return true;

  $videos = trainingVideos($pdo, $trainingId);
  $total = count($videos);
  if ($total === 0) return false;

  $doneIds = userCompletedVideos($pdo, $userId, $trainingId);
  if (count($doneIds) < $total) return false;

  // pega imagem de recompensa do treinamento
  $t = trainingById($pdo, $trainingId, $videos[0]['training_id'] ? null : null); // companyId já foi validado antes
  $st = $pdo->prepare("SELECT reward_image FROM trainings WHERE id=? LIMIT 1");
  $st->execute([$trainingId]);
  $reward = $st->fetchColumn() ?: '/assets/img/reward_default.png';

  $ins = $pdo->prepare("INSERT INTO user_training_reward (user_id, training_id, reward_image) VALUES (?, ?, ?)");
  $ins->execute([$userId, $trainingId, $reward]);
  return true;
}

function userHasAccessToTraining(PDO $pdo, int $userId, int $trainingId): bool {
  $sql = "SELECT 1
    FROM role_training rt
    JOIN user_role ur ON ur.role_id = rt.role_id
    JOIN trainings t ON t.id = rt.training_id
    WHERE ur.user_id=? AND rt.training_id=? AND t.is_active=1
    LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([$userId, $trainingId]);
  return (bool)$st->fetch();
}

function youtube_id_from(string $ref): ?string {
  $ref = trim($ref);
  // aceita ID puro ou URLs comuns
  // Exemplos aceitos:
  // https://www.youtube.com/watch?v=VIDEOID
  // https://youtu.be/VIDEOID
  // https://www.youtube.com/embed/VIDEOID
  // VIDEOID
  if (preg_match('~^(?:https?:)?//(?:www\.)?youtu(?:\.be/|be\.com/(?:watch\?v=|embed/))([A-Za-z0-9_-]{6,})~', $ref, $m)) {
    return $m[1];
  }
  if (preg_match('~^[A-Za-z0-9_-]{6,}$~', $ref)) {
    return $ref;
  }
  return null;
}