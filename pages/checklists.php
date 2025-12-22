<?php
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/checklist.php';

$userId    = (int)$_SESSION['user']['id'];
$companyId = currentCompanyId();
$lists     = checklistsForUser($pdo,$userId,$companyId);
$tot       = totalsForUser($pdo,$userId,$companyId);

// map de cargos
$rolesMap = [];
foreach($pdo->query("SELECT id,name FROM roles") as $r){ $rolesMap[(int)$r['id']]=$r['name']; }

// filtro
$filter = $_GET['f'] ?? '';
function overdueItems(PDO $pdo,$lists,$companyId){
  $items=[];
  foreach($lists as $cl){
    $freq=$cl['frequency'];
    $curr=period_key_for($freq); $prev=period_key_prev($freq);
    foreach(checklistTasks($pdo,(int)$cl['id']) as $t){
      $lateNow = isTaskDone($pdo,(int)$t['id'],$companyId,$curr);
      $missPrev= isTaskPendingPrev($pdo,(int)$t['id'],$companyId,$freq);
      if (($lateNow && !empty($lateNow['was_late'])) || $missPrev){
        $items[]=[
          'checklist'=>$cl['title'],'task'=>$t['name'],'freq'=>$freq,
          'period'=>$missPrev?$prev:$curr,'prev'=>$missPrev
        ];
      }
    }
  }
  return $items;
}
$overList = $filter==='overdue' ? overdueItems($pdo,$lists,$companyId) : [];
?>
<style>
/* ====== Layout base ====== */
.page-wrap{display:flex;flex-direction:column;gap:12px}

/* ====== KPIs (cards responsivos) ====== */
.kpis{
  display:grid; gap:12px; margin-bottom:6px;
  grid-template-columns:repeat(4,minmax(160px,1fr));
}
@media(max-width:980px){ .kpis{ grid-template-columns:repeat(2,1fr) } }
@media(max-width:560px){ .kpis{ grid-template-columns:1fr } }

.kpi{
  position:relative; padding:14px; border-radius:16px; border:1px solid var(--stroke);
  background:linear-gradient(135deg,rgba(255,106,0,.12),rgba(255,106,0,.06));
  display:flex; align-items:center; gap:12px; cursor:pointer; overflow:hidden;
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.kpi:hover{ transform:translateY(-1px); border-color:rgba(255,255,255,.22); box-shadow:0 10px 24px rgba(0,0,0,.25) }
.kpi:active{ transform:translateY(0) scale(.99) }
.kpi::after{
  content:""; position:absolute; inset:auto -20% -40% -20%; height:50%;
  background:radial-gradient(60% 60% at 50% 0%, rgba(255,255,255,.14), rgba(255,255,255,0));
  pointer-events:none;
}
.kpi .n{font-size:1.8rem;font-weight:900;line-height:1}
.kpi .txt{display:flex;flex-direction:column}
.kpi .txt strong{font-weight:900}
.kpi .txt .small{color:#cbd5e1}

/* ====== Grid de cartões ====== */
.grid{
  display:grid; gap:12px;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
}
@media(max-width:420px){ .grid{ grid-template-columns:1fr } }

.cl-card{
  position:relative; border:1px solid var(--stroke); border-radius:16px;
  background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.03));
  padding:12px; display:flex; flex-direction:column; gap:8px;
  transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.cl-card:hover{ border-color:rgba(255,255,255,.22); box-shadow:0 10px 24px rgba(0,0,0,.25); transform:translateY(-1px) }

.badge{display:inline-flex;align-items:center;gap:8px;padding:6px 8px;border-radius:999px;border:1px solid var(--stroke);font-weight:800}
.badge.warn{background:#ff4d4f;color:#0f1117;border:none}
.tag{font-size:.82rem;color:#0f1117;background:#fff;border:1px solid #e5e7eb;padding:2px 10px;border-radius:999px;display:inline-flex;font-weight:700}

/* ====== Botões ====== */
.actions{display:flex;gap:8px;flex-wrap:wrap}
.button, .btn{
  display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;border:1px solid var(--stroke);
  background:linear-gradient(135deg,#ff6a00,#ff9153); color:#0f1117; font-weight:900; text-decoration:none;
  transition:transform .15s ease, filter .15s ease;
}
.button:hover, .btn:hover{ transform:translateY(-1px) }
.button.ghost{
  background:rgba(255,255,255,.04); color:#e8edf7; border:1px solid var(--stroke);
}
.button.ghost:hover{ filter:brightness(1.05) }

/* ====== Barras de progresso ====== */
.progress{margin-top:2px}
.progress .bar{height:10px;background:rgba(255,255,255,.08);border-radius:999px;overflow:hidden}
.progress .fill{height:100%;background:linear-gradient(135deg,var(--brand),var(--brand-2))}
.progress .meta{display:flex;justify-content:space-between;margin-top:4px;color:#cbd5e1;font-size:.85rem}

/* ====== Tabela responsiva ====== */
.table-wrap{overflow:auto; -webkit-overflow-scrolling:touch; border-radius:12px; border:1px solid var(--stroke)}
.table{width:100%;border-collapse:separate;border-spacing:0}
.table thead th{
  position:sticky; top:0; background:rgba(255,255,255,.06); backdrop-filter:blur(4px);
  color:#e8edf7; font-weight:900; border-bottom:1px solid var(--stroke); padding:10px 8px; text-align:left
}
.table td{border-bottom:1px solid var(--stroke);padding:10px 8px;vertical-align:top;color:#e8edf7}
.table tr:hover td{background:rgba(255,255,255,.03)}
.small{color:#9aa4b2}

/* ====== Header do bloco de pendências ====== */
.block-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.block{border:1px solid var(--stroke);border-radius:16px;background:rgba(255,255,255,.04);padding:14px}

/* ====== Micro helper: linha do título no card ====== */
.card-top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.subtitle{color:#cbd5e1;font-size:.9rem}

/* ====== Toque-friendly ====== */
@media (hover:none){
  .kpi:hover,.cl-card:hover,.button:hover{transform:none;filter:none}
}
</style>

<div class="page-wrap">

  <!-- KPIs -->
  <div class="kpis">
    <a class="kpi" href="<?= url_for('/pages/checklists.php') ?>?f=overdue" title="Ver pendências/atrasos">
      <div class="n"><?= (int)$tot['overdue'] ?></div>
      <div class="txt"><strong>Atrasadas</strong><div class="small">toque para detalhes</div></div>
    </a>
    <div class="kpi" title="Tarefas diárias pendentes">
      <div class="n"><?= (int)$tot['today'] ?></div>
      <div class="txt"><strong>Hoje</strong><div class="small">diárias pendentes</div></div>
    </div>
    <div class="kpi" title="Tarefas da semana (semanais/quinzenais)">
      <div class="n"><?= (int)$tot['week'] ?></div>
      <div class="txt"><strong>Semana</strong><div class="small">semanais &amp; quinzenais</div></div>
    </div>
    <div class="kpi" title="Tarefas do mês (mensais)">
      <div class="n"><?= (int)$tot['month'] ?></div>
      <div class="txt"><strong>Mês</strong><div class="small">mensais</div></div>
    </div>
  </div>

  <!-- Pendências / Atrasos -->
  <?php if($filter==='overdue'): ?>
    <div class="block">
      <div class="block-head">
        <h3 style="margin:0;font-weight:900">Pendências/Atrasos</h3>
        <a class="button ghost" href="<?= url_for('/pages/checklists.php') ?>">Fechar</a>
      </div>

      <?php if(empty($overList)): ?>
        <div class="small" style="margin-top:8px">Sem atrasos no momento 🎉</div>
      <?php else: ?>
        <div class="table-wrap" style="margin-top:10px">
          <table class="table">
            <thead><tr><th>Checklist</th><th>Tarefa</th><th>Período</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($overList as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['checklist']) ?></td>
                <td><?= htmlspecialchars($it['task']) ?></td>
                <td><?= htmlspecialchars($it['period']) ?> (<?= strtoupper($it['freq']) ?>)</td>
                <td><span class="badge warn"><?= $it['prev']?'Não feito (período anterior)':'Marcado em atraso' ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Listagem de Checklists -->
  <?php if(empty($lists)): ?>
    <div class="block">Nenhum checklist para seus cargos nesta empresa.</div>
  <?php else: ?>
    <div class="grid">
      <?php foreach($lists as $cl):
        $pend   = checklistPendingNow($pdo,$cl,$companyId);
        $hasPrev= checklistHasPrevOverdue($pdo,$cl,$companyId);
        $totalT = count(checklistTasks($pdo,(int)$cl['id']));
        $done   = max(0,$totalT - $pend);
        $pct    = $totalT ? (int)round(($done/$totalT)*100) : 0;
        $roleNm = !empty($cl['default_role_id']) && isset($rolesMap[(int)$cl['default_role_id']]) ? $rolesMap[(int)$cl['default_role_id']] : '—';
      ?>
        <div class="cl-card">
          <?php if($hasPrev): ?>
            <span class="badge warn" style="position:absolute;right:10px;top:10px">Atrasos</span>
          <?php endif; ?>

          <div class="card-top">
            <div style="font-weight:900; font-size:1.02rem"><?= htmlspecialchars($cl['title']) ?></div>
            <span class="tag"><?= strtoupper($cl['frequency']) ?></span>
          </div>

          <div class="subtitle">Cargo responsável: <strong><?= htmlspecialchars($roleNm) ?></strong></div>
          <?php if(!empty($cl['description'])): ?>
            <div class="small"><?= nl2br(htmlspecialchars($cl['description'])) ?></div>
          <?php endif; ?>

          <div class="progress" style="margin:6px 0 2px">
            <div class="bar"><div class="fill" style="width:<?= $pct ?>%"></div></div>
            <div class="meta"><span><?= $done ?>/<?= $totalT ?> no período</span><strong><?= $pct ?>%</strong></div>
          </div>

          <div class="actions" style="margin-top:6px">
            <a class="button" href="<?= url_for('/pages/checklist_run.php') ?>?id=<?= (int)$cl['id'] ?>&period=current" title="Executar agora">
              Executar agora<?= $pend ? " ({$pend})" : "" ?>
            </a>
            <a class="button ghost" href="<?= url_for('/pages/checklist_run.php') ?>?id=<?= (int)$cl['id'] ?>&period=prev" title="Ver período anterior">
              Período anterior
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
