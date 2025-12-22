<?php
require_once __DIR__ . '/../includes/layout_start.php';
$userId = (int)($_SESSION['user']['id'] ?? 0);

$st = $pdo->prepare("SELECT utr.*, t.title 
  FROM user_training_reward utr
  JOIN trainings t ON t.id=utr.training_id
  WHERE utr.user_id=? ORDER BY utr.awarded_at DESC");
$st->execute([$userId]);
$rewards = $st->fetchAll();
?>
<h2 style="margin:0 0 12px; font-weight:900">Minhas Recompensas</h2>
<?php if(empty($rewards)): ?>
  <div class="card" style="padding:20px">Você ainda não concluiu nenhum treinamento.</div>
<?php else: ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px">
    <?php foreach($rewards as $r): ?>
      <div class="card" style="padding:14px; text-align:center">
        <img src="<?= htmlspecialchars($r['reward_image']) ?>" style="width:84px; height:84px; border-radius:16px; border:1px solid var(--stroke)">
        <div style="font-weight:800; margin-top:8px"><?= htmlspecialchars($r['title']) ?></div>
        <div style="color:#9aa4b2; font-size:.9rem; margin-top:4px">Conquistada em <?= htmlspecialchars($r['awarded_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
