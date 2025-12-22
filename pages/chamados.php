<?php
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/feedback.php';
if (!canAccessAdmin()) { http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$companyId = currentCompanyId();
$rows = fb_list_admin($pdo,$companyId);
$cats = fb_categories();
$sentMap = []; foreach (fb_sentiments() as $s){ $sentMap[$s['key']]=$s; }
?>
<style>
.table{width:100%;border-collapse:separate;border-spacing:0 10px}
.table thead th{font-size:.9rem;color:#9aa4b2;text-align:left;padding:0 8px}
.row{display:grid;grid-template-columns: 1.8fr .9fr .9fr 1.2fr .9fr 140px; gap:10px; align-items:center;
     border:1px solid var(--stroke); border-radius:12px; background:rgba(255,255,255,.04); padding:10px}
.avatar{width:40px;height:40px;border-radius:12px;object-fit:cover;border:1px solid var(--stroke)}
.badge.sent{border:1px solid var(--stroke);border-radius:999px;padding:4px 10px;display:inline-flex;gap:6px;align-items:center}
.select{appearance:none;background:transparent;border:1px solid var(--stroke);padding:8px 12px;border-radius:10px;color:#e8edf7}
.select.is-loading{color:transparent;position:relative}
.select.is-loading::after{content:"";position:absolute;right:10px;top:50%;width:14px;height:14px;margin-top:-7px;border-radius:50%;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.small{color:#9aa4b2}
</style>

<h2 style="margin:0 0 10px;font-weight:900">Chamados Internos</h2>
<div class="small" style="margin-bottom:8px">Abertos primeiro • atualize o status em linha</div>

<?php if(empty($rows)): ?>
  <div class="card" style="padding:20px">Sem chamados no momento.</div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:10px">
    <?php foreach($rows as $r):
      $s = $sentMap[$r['sentiment_key']] ?? null;
      $emoji = $s['emoji'] ?? '🙂';
      $title = $s['title'] ?? ucfirst($r['sentiment_key']);
    ?>
      <div class="row" data-id="<?= (int)$r['id'] ?>">
        <div style="display:flex;align-items:center;gap:10px">
          <img class="avatar" src="<?= htmlspecialchars(($r['avatar_url'] ?: url_for('/assets/img/avatar.svg'))) ?>" alt="">
          <div>
            <div style="font-weight:800"><?= htmlspecialchars($r['user_name']) ?></div>
            <div class="small"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
          </div>
        </div>

        <div><span class="badge sent"><?= $emoji ?> <strong><?= htmlspecialchars($title) ?></strong></span></div>

        <div class="small"><?= htmlspecialchars($cats[$r['category']] ?? $r['category']) ?></div>

        <div style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($r['message'])) ?></div>

        <div><?= fb_status_badge($r['status']) ?></div>

        <div>
          <select class="select statusSel">
            <option value="aberto"      <?= $r['status']=='aberto'?'selected':'' ?>>Aberto</option>
            <option value="em_andamento"<?= $r['status']=='em_andamento'?'selected':'' ?>>Em andamento</option>
            <option value="concluido"   <?= $r['status']=='concluido'?'selected':'' ?>>Concluído</option>
          </select>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
async function postJSON(url, payload){
  const r = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const t = await r.text(); try{return JSON.parse(t)}catch(e){throw new Error(t)}
}
document.querySelectorAll('.statusSel').forEach(sel=>{
  sel.addEventListener('change', async ()=>{
    const row = sel.closest('.row'); const id = parseInt(row.dataset.id,10);
    sel.classList.add('is-loading'); sel.disabled = true;
    try{
      const res = await postJSON('<?= url_for("/pages/chamados_update.php") ?>', {id, status: sel.value});
      if(res.status==='ok'){ location.reload(); }
      else{ alert(res.message || 'Falha ao atualizar'); }
    }catch(e){ alert('Erro: '+e.message); }
    finally{ sel.classList.remove('is-loading'); sel.disabled = false; }
  });
});
</script>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
