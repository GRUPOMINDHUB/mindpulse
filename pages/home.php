<?php
// pages/home.php — Dashboard do Colaborador (Mindhub)
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/training.php';
require_once __DIR__ . '/../includes/checklist.php';

$user      = $_SESSION['user'] ?? [];
$userId    = (int)($user['id'] ?? 0);
$companyId = currentCompanyId();
$avatar    = ($user['avatar_url'] ?? '') ?: url_for('/assets/img/avatar.svg');

// === Treinamentos acessíveis ao usuário
$trainings = trainingsForUser($pdo, $userId, $companyId);

// KPIs de treinamentos
$totalTrainings = count($trainings);
$doneTrainings = $inProgressTrainings = $notStartedTrainings = 0;

// Para o donut: somatório de aulas feitas x total
$totalVideosAll = 0;
$doneVideosAll  = 0;

// Recompensas conquistadas (imagens)
$rewards = [];

foreach ($trainings as $t) {
  $tid = (int)$t['id'];
  $progress = userTrainingProgress($pdo, $userId, $tid); // ['percent','done','total']
  $p = (int)($progress['percent'] ?? 0);

  $totalVideosAll += (int)($progress['total'] ?? 0);
  $doneVideosAll  += (int)($progress['done']  ?? 0);

  if ($p >= 100) {
    $doneTrainings++;
    if (!empty($t['reward_image'])) $rewards[] = $t['reward_image'];
  } elseif ($p > 0) {
    $inProgressTrainings++;
  } else {
    $notStartedTrainings++;
  }
}

// KPIs de checklists
$totalsCL   = totalsForUser($pdo, $userId, $companyId);
$pendingCL  = (int)$totalsCL['today'] + (int)$totalsCL['week'] + (int)$totalsCL['month'];
$overdueCL  = (int)$totalsCL['overdue'];

// Roles (cargos) do usuário (se existir user_role/roles)
$roles = [];
try {
  $st = $pdo->prepare("SELECT r.name FROM roles r JOIN user_role ur ON ur.role_id=r.id WHERE ur.user_id=? ORDER BY r.name");
  $st->execute([$userId]);
  $roles = array_column($st->fetchAll(PDO::FETCH_ASSOC), 'name');
} catch(Throwable $e){ /* ignora */ }
?>
<style>
/* Top hero */
.hero{
  display:grid; grid-template-columns:120px 1fr; gap:14px; align-items:center;
  border:1px solid var(--stroke); border-radius:16px;
  background:linear-gradient(135deg,rgba(255,106,0,.12),rgba(255,106,0,.06));
  padding:14px;
}
@media(max-width:720px){ .hero{ grid-template-columns:80px 1fr } }
.hero .pic{width:120px;height:120px;border-radius:22px;object-fit:cover;border:2px solid var(--stroke);background:#0f1117}
@media(max-width:720px){ .hero .pic{width:80px;height:80px;border-radius:16px} }
.hero h2{margin:0;font-weight:900}
.hero .roles{color:#cbd5e1}
.rewards{display:flex;gap:8px;flex-wrap:wrap}
.rewards img{width:44px;height:44px;border-radius:12px;border:1px solid var(--stroke);object-fit:cover}

/* KPI Cards */
.kpis{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:12px;margin-top:14px}
@media(max-width:980px){.kpis{grid-template-columns:repeat(2,1fr)}}
.kpi{padding:14px;border-radius:16px;border:1px solid var(--stroke);display:flex;gap:10px;align-items:center;
  background:rgba(255,255,255,.04)}
.kpi .n{font-size:1.8rem;font-weight:900}
.kpi .t{color:#cbd5e1;font-size:.92rem}

/* Two columns content */
.cols{display:grid;grid-template-columns:1.1fr .9fr;gap:12px;margin-top:14px}
@media(max-width:980px){.cols{grid-template-columns:1fr}}

/* Cards genericos */
.cardx{border:1px solid var(--stroke);border-radius:16px;background:rgba(255,255,255,.04);padding:14px}
.section-title{margin:0 0 8px;font-weight:900}

/* Lista de treinamentos */
.tgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px}
.tcard{border:1px solid var(--stroke);border-radius:14px;overflow:hidden;background:rgba(255,255,255,.03)}
.tcard img{width:100%;height:120px;object-fit:cover}
.tcard .inner{padding:10px}
.progress{height:8px;background:rgba(255,255,255,.08);border-radius:999px;overflow:hidden}
.progress > span{display:block;height:100%;background:linear-gradient(135deg,var(--brand),var(--brand-2))}

/* Donut */
.donut-wrap{display:flex;gap:16px;align-items:center}
.donut canvas{width:140px;height:140px}
.donut-legend{display:flex;flex-direction:column;gap:6px}
.badge-dot{display:inline-flex;align-items:center;gap:8px}
.badge-dot .dot{width:12px;height:12px;border-radius:50%}
.small{color:#9aa4b2}
</style>

<!-- HERO -->
<div class="hero">
  <img class="pic" src="<?= htmlspecialchars($avatar) ?>" alt="Perfil">
  <div>
    <h2><?= htmlspecialchars($user['name'] ?? 'Colaborador') ?></h2>
    <div class="roles"><?= !empty($roles) ? htmlspecialchars(implode(' • ', $roles)) : '—' ?></div>
    <?php if (!empty($rewards)): ?>
      <div class="small" style="margin:6px 0 4px">Recompensas conquistadas</div>
      <div class="rewards">
        <?php foreach ($rewards as $src): ?>
          <img src="<?= htmlspecialchars($src) ?>" alt="reward">
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="small" style="margin-top:6px">Sem recompensas ainda — bora conquistar! 🚀</div>
    <?php endif; ?>
  </div>
</div>

<!-- KPIs -->
<div class="kpis">
  <div class="kpi">
    <div class="n"><?= $pendingCL ?></div>
    <div><div><strong>Atividades pendentes</strong></div><div class="t">Checklists de hoje/semana/mês</div></div>
  </div>
  <a class="kpi" href="<?= url_for('/pages/checklists.php') ?>?f=overdue" style="text-decoration:none">
    <div class="n"><?= $overdueCL ?></div>
    <div><div><strong>Atividades em atraso</strong></div><div class="t">Ver pendências</div></div>
  </a>
  <div class="kpi">
    <div class="n"><?= $totalTrainings ?></div>
    <div><div><strong>Treinamentos disponíveis</strong></div><div class="t">Nesta organização</div></div>
  </div>
  <div class="kpi">
    <div class="n"><?= $doneTrainings ?></div>
    <div><div><strong>Concluídos</strong></div><div class="t">Você finalizou <?= $doneTrainings ?> trilha(s)</div></div>
  </div>
</div>

<div class="cols">
  <!-- COLUNA ESQ.: Treinamentos -->
  <div class="cardx">
    <h3 class="section-title">Seu avanço em treinamentos</h3>

    <!-- Sub-kpis -->
    <div class="kpis" style="grid-template-columns:repeat(3,1fr);margin-top:4px">
      <div class="kpi"><div class="n"><?= $inProgressTrainings ?></div><div><div><strong>Em andamento</strong></div><div class="t">continue de onde parou</div></div></div>
      <div class="kpi"><div class="n"><?= $notStartedTrainings ?></div><div><div><strong>Não iniciado</strong></div><div class="t">que tal começar um novo?</div></div></div>
      <div class="kpi"><div class="n"><?= $doneTrainings ?></div><div><div><strong>Concluídos</strong></div><div class="t">parabéns! 💪</div></div></div>
    </div>

    <?php if (empty($trainings)): ?>
      <div class="cardx" style="margin-top:10px">Nenhum treinamento disponível para seus cargos.</div>
    <?php else: ?>
      <div class="tgrid" style="margin-top:12px">
        <?php foreach ($trainings as $t):
          $pr = userTrainingProgress($pdo, $userId, (int)$t['id']);
          $pct = (int)($pr['percent'] ?? 0);
        ?>
        <div class="tcard">
          <img src="<?= htmlspecialchars($t['cover_image'] ?: url_for('/assets/img/login_hero.svg')) ?>" alt="">
          <div class="inner">
            <div style="font-weight:800"><?= htmlspecialchars($t['title']) ?></div>
            <div class="small" style="margin:2px 0"><?= htmlspecialchars($t['objective'] ?? '') ?></div>
            <div class="progress" style="margin-top:8px"><span style="width:<?= $pct ?>%"></span></div>
            <div class="small" style="display:flex;justify-content:space-between;margin-top:4px">
              <span><?= $pct ?>%</span>
              <a class="button ghost" href="<?= url_for('/pages/treinamento.php') ?>?id=<?= (int)$t['id'] ?>">
                <?= $pct>0 && $pct<100 ? 'Continuar' : ($pct>=100?'Revisar':'Começar') ?>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- COLUNA DIR.: Donut + Checklists -->
  <div class="cardx">
    <h3 class="section-title">Seu ritmo de aprendizagem</h3>

    <div class="donut-wrap" style="margin-top:6px">
      <div class="donut"><canvas id="donutCanvas" width="180" height="180"></canvas></div>
      <div class="donut-legend">
        <div class="badge-dot"><span class="dot" style="background:#00e0a4"></span> Aulas concluídas: <strong><?= $doneVideosAll ?></strong></div>
        <div class="badge-dot"><span class="dot" style="background:#2b3245"></span> Aulas pendentes: <strong><?= max(0, $totalVideosAll - $doneVideosAll) ?></strong></div>
        <div class="small">Total de aulas nos seus treinamentos: <?= $totalVideosAll ?></div>
        <a class="button" href="<?= url_for('/pages/treinamentos.php') ?>" style="margin-top:6px">Ver treinamentos</a>
      </div>
    </div>

    <div class="cardx" style="margin-top:12px">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <h4 style="margin:0;font-weight:900">Checklists</h4>
        <a class="button ghost" href="<?= url_for('/pages/checklists.php') ?>">Abrir checklists</a>
      </div>
      <div class="kpis" style="grid-template-columns:repeat(2,1fr);margin-top:8px">
        <a class="kpi" href="<?= url_for('/pages/checklists.php') ?>?f=overdue" style="text-decoration:none">
          <div class="n"><?= $overdueCL ?></div>
          <div><div><strong>Em atraso</strong></div><div class="t">corrija já</div></div>
        </a>
        <div class="kpi">
          <div class="n"><?= $pendingCL ?></div>
          <div><div><strong>Pendentes</strong></div><div class="t">para hoje/semana/mês</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Donut minimalista (canvas puro)
(function(){
  const done = <?= (int)$doneVideosAll ?>;
  const total = <?= (int)$totalVideosAll ?>;
  const pend = Math.max(0, total - done);
  const cv = document.getElementById('donutCanvas'); if(!cv) return;
  const ctx = cv.getContext('2d'); const cx = cv.width/2, cy = cv.height/2, r = Math.min(cx,cy)-8;

  function arc(start, value, color){
    const frac = total>0 ? value/total : 0;
    const end = start + frac * Math.PI*2;
    ctx.beginPath(); ctx.arc(cx,cy,r,start,end); ctx.strokeStyle = color; ctx.lineWidth = 22; ctx.lineCap='round'; ctx.stroke();
    return end;
  }
  // Fundo
  ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2); ctx.strokeStyle='#2b3245'; ctx.lineWidth=22; ctx.stroke();
  // Feito
  let a = -Math.PI/2; a = arc(a, done, '#00e0a4');

  // centro
  ctx.fillStyle='#e8edf7'; ctx.font='700 16px Inter, system-ui, sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  const pct = total>0 ? Math.round(done/total*100) : 0;
  ctx.fillText(pct+'%', cx, cy);
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
