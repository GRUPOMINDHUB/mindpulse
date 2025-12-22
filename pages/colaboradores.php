<?php
// pages/colaboradores.php — Admin > Colaboradores (responsivo + CTA "Adicionar colaborador")
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!canAccessAdmin()) { http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$companyId = currentCompanyId();
if (!$companyId) { echo '<div class="card" style="padding:20px">Selecione uma empresa no topo para ver colaboradores.</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

/* === Helpers === */
function userRewards(PDO $pdo, int $userId, int $companyId): array {
  $st = $pdo->prepare("SELECT utr.reward_image
    FROM user_training_reward utr
    JOIN trainings t ON t.id = utr.training_id
    WHERE utr.user_id=? AND t.company_id=? ORDER BY utr.awarded_at DESC");
  $st->execute([$userId, $companyId]);
  return array_values(array_filter(array_column($st->fetchAll(), 'reward_image')));
}
function userProgressPercent(PDO $pdo, int $userId, int $companyId): int {
  $sqlTotal = "SELECT COUNT(DISTINCT tv.id)
    FROM training_videos tv
    JOIN trainings t      ON t.id = tv.training_id AND t.company_id = ? AND t.is_active=1
    JOIN role_training rt ON rt.training_id = t.id
    JOIN user_role ur     ON ur.role_id = rt.role_id AND ur.user_id = ?
    WHERE tv.is_active=1";
  $stT = $pdo->prepare($sqlTotal); $stT->execute([$companyId,$userId]);
  $total = (int)($stT->fetchColumn() ?: 0);
  if ($total === 0) return 0;

  $sqlDone = "SELECT COUNT(DISTINCT uvp.video_id)
    FROM user_video_progress uvp
    JOIN training_videos tv ON tv.id = uvp.video_id AND tv.is_active=1
    JOIN trainings t        ON t.id = tv.training_id AND t.company_id = ? AND t.is_active=1
    JOIN role_training rt   ON rt.training_id = t.id
    JOIN user_role ur       ON ur.role_id = rt.role_id AND ur.user_id = ?
    WHERE uvp.user_id = ?";
  $stD = $pdo->prepare($sqlDone); $stD->execute([$companyId,$userId,$userId]);
  $done = (int)($stD->fetchColumn() ?: 0);
  return (int) floor(($done / $total) * 100);
}

/* === Base de colaboradores + cargos === */
$st = $pdo->prepare("
  SELECT u.id, u.name, u.avatar_url, COALESCE(u.status,1) AS status, u.birthday,
         r.id AS role_id, r.name AS role_name
  FROM users u
  JOIN user_company uc ON uc.user_id = u.id AND uc.company_id = ?
  LEFT JOIN user_role ur ON ur.user_id = u.id
  LEFT JOIN roles r ON r.id = ur.role_id
  ORDER BY u.name, r.name
");
$st->execute([$companyId]);
$rows = $st->fetchAll();

/* === Organiza por usuário e seus cargos === */
$users = [];
foreach ($rows as $r) {
  $id = (int)$r['id'];
  if (!isset($users[$id])) {
    $users[$id] = [
      'id' => $id, 'name' => $r['name'], 'avatar_url' => $r['avatar_url'],
      'status' => (int)$r['status'], 'birthday' => $r['birthday'], 'roles' => []
    ];
  }
  if (!empty($r['role_id'])) $users[$id]['roles'][(int)$r['role_id']] = $r['role_name'];
}

/* === Totalizador === */
$totalColabs = count($users);

/* === Agrupamento === */
$singleRoleGroups = []; $multiRolePeople = [];
foreach ($users as $u) {
  $roleCount = count($u['roles']);
  if ($roleCount <= 1) {
    if ($roleCount === 1) {
      $rid = (int)array_key_first($u['roles']);
      $rname = $u['roles'][$rid];
      if (!isset($singleRoleGroups[$rid])) $singleRoleGroups[$rid] = ['role_name'=>$rname, 'people'=>[]];
      $singleRoleGroups[$rid]['people'][] = $u;
    } else {
      $multiRolePeople[] = $u; // sem cargo
    }
  } else {
    $multiRolePeople[] = $u; // >1 cargo
  }
}

/* === Próximos aniversariantes (6 meses) === */
$bdays = $pdo->prepare("
  SELECT u.id, u.name, u.avatar_url, u.birthday,
  CASE
    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), DATE_FORMAT(u.birthday,'-%m-%d')), '%Y-%m-%d') < CURDATE()
    THEN DATE_ADD(STR_TO_DATE(CONCAT(YEAR(CURDATE()), DATE_FORMAT(u.birthday,'-%m-%d')), '%Y-%m-%d'), INTERVAL 1 YEAR)
    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()), DATE_FORMAT(u.birthday,'-%m-%d')), '%Y-%m-%d')
  END AS next_bday
  FROM users u
  JOIN user_company uc ON uc.user_id = u.id AND uc.company_id = ?
  WHERE u.birthday IS NOT NULL
  HAVING next_bday <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
  ORDER BY next_bday ASC
  LIMIT 30
");
$bdays->execute([$companyId]);
$birthdayRows = $bdays->fetchAll();

/* === Estilos === */
?>
<style>
/* Toolbar top com CTA */
.toolbar{
  position:sticky; top:calc(var(--mh-header-h) + 0px); z-index:5;
  display:flex; gap:10px; align-items:center; justify-content:space-between;
  margin-bottom:12px; padding:8px 10px; border-radius:14px;
  background:linear-gradient(180deg,rgba(20,24,36,.75),rgba(20,24,36,.55));
  border:1px solid var(--mh-stroke); backdrop-filter: blur(6px);
}
.toolbar .title{font-weight:900; display:flex; align-items:center; gap:10px}
.toolbar .meta{color:#9aa4b2; font-weight:600}
.btn-primary{
  display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius:12px;
  background:linear-gradient(135deg,#ff6a00,#ff9153); border:1px solid rgba(255,255,255,.12);
  color:#0f1117; font-weight:900; text-decoration:none; transition:.18s ease;
}
.btn-primary:hover{transform:translateY(-1px)}
.btn-primary svg{width:18px;height:18px}

/* KPIs */
.grid-top{display:flex;align-items:stretch;gap:14px;margin-bottom:14px;flex-wrap:wrap}
.kpi{flex:1;min-width:220px;display:flex;align-items:center;gap:12px;padding:14px;border-radius:16px;border:1px solid var(--stroke);
     background:linear-gradient(135deg,rgba(255,106,0,.12),rgba(255,106,0,.06))}
.kpi .big{font-weight:900;font-size:1.8rem;line-height:1}
.kpi .meta{color:#9aa4b2}

/* Grids principais */
.grid-main{display:grid;grid-template-columns:1.25fr .75fr;gap:18px}
@media(max-width: 1100px){.grid-main{grid-template-columns:1fr}}

/* Cards de pessoas */
.role-block{margin-bottom:18px}
.role-title{display:flex;align-items:center;gap:8px;margin:0 0 8px;font-weight:900}
.badge-dot{display:inline-block;width:8px;height:8px;border-radius:999px;background:var(--brand)}
.people-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
@media(max-width:560px){.people-grid{grid-template-columns:1fr}}
.person{display:flex;gap:12px;align-items:flex-start;border:1px solid var(--stroke);background:rgba(255,255,255,.04);border-radius:14px;padding:10px}
.person img.avatar{width:56px;height:56px;border-radius:14px;border:1px solid var(--stroke);object-fit:cover;background:#0f1117}
.p-name{font-weight:900}
.p-roles{color:#9aa4b2;font-size:.86rem;margin-top:2px}
.rewards{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.rewards img{width:28px;height:28px;border-radius:7px;border:1px solid var(--stroke);background:#0f1117}
.progress{margin-top:8px}
.progress .bar{height:8px;background:rgba(255,255,255,.08);border-radius:999px;overflow:hidden}
.progress .fill{height:100%;background:linear-gradient(135deg,var(--brand),var(--brand-2))}
.progress .meta{display:flex;justify-content:space-between;margin-top:4px;color:#cbd5e1;font-size:.85rem}

/* Aniversariantes */
.bday-card{padding:14px;border-radius:16px;border:1px solid var(--stroke);
  background:linear-gradient(160deg,rgba(18,18,22,.95),rgba(255,106,0,.18)); box-shadow:0 10px 20px rgba(0,0,0,.25)}
.bday-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.bday-head h3{margin:0;font-weight:900}
.bday-badge{background:#ff6a00;color:#0f1117;border:none}
.bday-list{display:flex;flex-direction:column;gap:8px;max-height:70vh;overflow:auto;padding-right:6px}
.bday-item{display:flex;gap:10px;align-items:center;border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:8px;background:rgba(255,255,255,.06)}
.bday-item img{width:40px;height:40px;border-radius:10px;border:1px solid var(--stroke);object-fit:cover;background:#0f1117}
.bday-name{font-weight:800;color:#fff}
.bday-date{color:#f2f5ff;font-size:.9rem;opacity:.9}

/* Responsividade do topo */
@media(max-width:720px){
  .toolbar{flex-direction:column; align-items:stretch; gap:8px}
  .toolbar .title{justify-content:space-between}
  .btn-primary{justify-content:center}
}
</style>

<!-- Toolbar com CTA -->
<div class="toolbar">
  <div class="title">
    <span style="display:flex;align-items:center;gap:10px">
      <span class="brand-dot" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--brand2))"></span>
      <span>Colaboradores</span>
    </span>
    <span class="meta"><?= (int)$totalColabs ?> no total</span>
  </div>
  <a class="btn-primary" href="<?= url_for('/pages/collaborator_new.php') ?>">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
    Adicionar colaborador
  </a>
</div>

<!-- KPIs -->
<div class="grid-top">
  <div class="kpi">
    <div class="big"><?= (int)$totalColabs ?></div>
    <div>
      <div style="font-weight:800">Total de colaboradores</div>
      <div class="meta">Empresa selecionada</div>
    </div>
  </div>
</div>

<div class="grid-main">
  <section>
    <?php if (empty($users)): ?>
      <div class="card" style="padding:20px">Nenhum colaborador nesta empresa.</div>
    <?php else: ?>
      <?php
      uasort($singleRoleGroups, fn($a,$b)=>strcmp($a['role_name'],$b['role_name']));
      foreach ($singleRoleGroups as $rid => $grp):
        $people = $grp['people']; if (empty($people)) continue;
      ?>
        <div class="role-block">
          <div class="role-title">
            <span class="badge-dot"></span>
            <span><?= htmlspecialchars($grp['role_name']) ?></span>
            <span style="color:#9aa4b2;font-weight:600">(<?= count($people) ?>)</span>
          </div>

          <div class="people-grid">
            <?php foreach ($people as $p):
              $uid = (int)$p['id'];
              $rewards = userRewards($pdo, $uid, $companyId);
              $percent = userProgressPercent($pdo, $uid, $companyId);
              $avatar = $p['avatar_url'] ?: url_for('/assets/img/avatar.svg');
            ?>
              <div class="person">
                <img class="avatar" src="<?= htmlspecialchars($avatar) ?>" alt="">
                <div style="flex:1">
                  <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
                  <?php if (empty($rewards)): ?>
                    <div class="rewards" style="color:#9aa4b2;font-size:.9rem">Sem recompensas… ainda 😉</div>
                  <?php else: ?>
                    <div class="rewards" title="Recompensas">
                      <?php foreach($rewards as $img): ?><img src="<?= htmlspecialchars($img) ?>" alt="reward"><?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <div class="progress">
                    <div class="bar"><div class="fill" style="width:<?= (int)$percent ?>%"></div></div>
                    <div class="meta"><span>Progresso</span><strong><?= (int)$percent ?>%</strong></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- +1 cargo -->
      <div class="role-block">
        <div class="role-title">
          <span class="badge-dot"></span>
          <span>+1 cargo</span>
          <span style="color:#9aa4b2;font-weight:600">(<?= count($multiRolePeople) ?>)</span>
        </div>

        <?php if (empty($multiRolePeople)): ?>
          <div class="card" style="padding:12px">Nenhum colaborador multi-cargo (ou sem cargo) no momento.</div>
        <?php else: ?>
          <div class="people-grid">
            <?php foreach ($multiRolePeople as $p):
              $uid = (int)$p['id'];
              $rewards = userRewards($pdo, $uid, $companyId);
              $percent = userProgressPercent($pdo, $uid, $companyId);
              $avatar = $p['avatar_url'] ?: url_for('/assets/img/avatar.svg');
              $rolesStr = empty($p['roles']) ? 'Sem cargo' : implode(' • ', array_values($p['roles']));
            ?>
              <div class="person">
                <img class="avatar" src="<?= htmlspecialchars($avatar) ?>" alt="">
                <div style="flex:1">
                  <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
                  <div class="p-roles"><?= htmlspecialchars($rolesStr) ?></div>
                  <?php if (empty($rewards)): ?>
                    <div class="rewards" style="color:#9aa4b2;font-size:.9rem">Sem recompensas… ainda 😉</div>
                  <?php else: ?>
                    <div class="rewards" title="Recompensas">
                      <?php foreach($rewards as $img): ?><img src="<?= htmlspecialchars($img) ?>" alt="reward"><?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <div class="progress">
                    <div class="bar"><div class="fill" style="width:<?= (int)$percent ?>%"></div></div>
                    <div class="meta"><span>Progresso</span><strong><?= (int)$percent ?>%</strong></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <aside>
    <div class="bday-card">
      <div class="bday-head">
        <h3>Próximos Aniversariantes</h3>
        <span class="badge bday-badge">6 meses</span>
      </div>

      <?php if (empty($birthdayRows)): ?>
        <div class="card" style="padding:12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15)">Sem aniversários próximos.</div>
      <?php else: ?>
        <div class="bday-list">
          <?php foreach($birthdayRows as $b):
            $next = new DateTime($b['next_bday']);
            $dateBr = $next->format('d/m');
            $av = $b['avatar_url'] ?: url_for('/assets/img/avatar.svg');
          ?>
            <div class="bday-item">
              <img src="<?= htmlspecialchars($av) ?>" alt="">
              <div style="flex:1">
                <div class="bday-name"><?= htmlspecialchars($b['name']) ?></div>
                <div class="bday-date"><?= $dateBr ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </aside>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
