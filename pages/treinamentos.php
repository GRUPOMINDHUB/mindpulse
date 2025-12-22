<?php
// pages/treinamentos.php
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/training.php';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$companyId = currentCompanyId();
$items = trainingsForUser($pdo, $userId, $companyId);
?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px">
  <h2 style="margin:0; font-weight:900">Treinamentos</h2>
  <span class="badge"><span class="brand-dot"></span> Suas jornadas</span>
</div>

<?php if (empty($items)): ?>
  <div class="card" style="padding:20px">Nenhum treinamento disponível para seus cargos nesta empresa.</div>
<?php else: ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:16px">
    <?php foreach($items as $t):
      $progress = userTrainingProgress($pdo, $userId, (int)$t['id']);
      $p = $progress['percent'];
    ?>
      <div class="card" style="overflow:hidden">
        <div style="position:relative">
          <img src="<?= htmlspecialchars($t['cover_image'] ?: url_for('/assets/img/login_hero.svg')) ?>" alt="" style="width:100%; height:160px; object-fit:cover">
          <?php if (!empty($t['reward_image'])): ?>
            <img src="<?= htmlspecialchars($t['reward_image']) ?>" alt="recompensa" style="position:absolute; right:10px; bottom:10px; width:48px; height:48px; border-radius:12px; border:1px solid var(--stroke)">
          <?php endif; ?>
        </div>
        <div style="padding:14px">
          <div style="font-weight:800; font-size:1.05rem"><?= htmlspecialchars($t['title']) ?></div>
          <div style="color:#9aa4b2; margin:6px 0"><?= htmlspecialchars($t['objective']) ?></div>
          <div style="display:flex; gap:8px; flex-wrap:wrap; color:#cbd5e1; font-size:.85rem">
            <span class="badge" style="background:rgba(255,255,255,.06)"><?= htmlspecialchars($t['difficulty']) ?></span>
            <?php if(!empty($t['estimated_minutes'])): ?>
              <span class="badge" style="background:rgba(255,255,255,.06)"><?= (int)$t['estimated_minutes'] ?> min</span>
            <?php endif; ?>
            <?php if(!empty($t['tags'])): ?>
              <span class="badge" style="background:rgba(255,255,255,.06)"><?= htmlspecialchars($t['tags']) ?></span>
            <?php endif; ?>
          </div>

          <div style="margin-top:10px">
            <div style="height:8px; background:rgba(255,255,255,.08); border-radius:999px; overflow:hidden">
              <div style="height:100%; width:<?= (int)$p ?>%; background:linear-gradient(135deg,var(--brand),var(--brand-2))"></div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:6px; color:#cbd5e1; font-size:.85rem">
              <span>Progresso</span><strong><?= (int)$p ?>%</strong>
            </div>
          </div>

          <div style="margin-top:12px">
            <a class="button" href="<?= url_for('/pages/treinamento.php') ?>?id=<?= (int)$t['id'] ?>">
              <?= $p>0 ? 'Continuar' : 'Começar' ?>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
