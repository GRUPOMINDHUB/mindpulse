<?php
// pages/collaborator_new.php — Admin > Colaboradores > Novo
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
if (!canAccessAdmin()) { http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$companies = $pdo->query("SELECT id, name FROM companies ORDER BY name")->fetchAll();
$roles     = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();
?>
<style>
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:980px){.form-grid{grid-template-columns:1fr}}
.card-section{padding:12px;border:1px solid var(--stroke);border-radius:12px;background:rgba(255,255,255,.04)}
.badge-btn{border:1px dashed var(--stroke);background:transparent;color:#e8edf7;border-radius:12px;padding:.6rem .9rem;cursor:pointer}
.small{font-size:.9rem;color:#9aa4b2}
.preview{width:96px;height:96px;border-radius:16px;border:1px solid var(--stroke);object-fit:cover;background:rgba(255,255,255,.06)}
</style>

<form class="card" style="padding:16px" method="POST" action="<?= url_for('/pages/collaborator_save.php') ?>">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
    <h2 style="margin:0;font-weight:900">Novo Colaborador</h2>
    <button class="button" type="submit" id="btnSave">Salvar</button>
  </div>

  <div class="form-grid">
    <div class="card-section">
      <div style="display:flex;gap:12px;align-items:center">
        <img id="avatarPrev" class="preview" src="<?= url_for('/assets/img/avatar.svg') ?>" alt="">
        <div style="flex:1">
          <label class="label">Foto (URL)</label>
          <input class="input" name="avatar_url" id="avatarUrl" placeholder="/assets/img/users/julia.png ou https://...">
          <div class="small">Dica: use 256×256px.</div>
        </div>
      </div>

      <label class="label" style="margin-top:10px">Nome completo*</label>
      <input class="input" name="name" required>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
        <div>
          <label class="label">Email*</label>
          <input class="input" name="email" type="email" required>
        </div>
        <div>
          <label class="label">Senha (opcional)</label>
          <input class="input" name="password" type="password" placeholder="deixe vazio p/ definir depois">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
        <div>
          <label class="label">Aniversário</label>
          <input class="input" name="birthday" type="date">
        </div>
        <div>
          <label class="label">Telefone</label>
          <input class="input" name="phone" placeholder="(DDD) 99999-9999">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
        <div>
          <label class="label">Status</label>
          <select class="input" name="status">
            <option value="1">Ativo</option>
            <option value="0">Inativo</option>
          </select>
        </div>
        <div>
          <label class="label">Tipo</label>
          <select class="input" name="type">
            <option value="Colaborador">Colaborador</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card-section">
      <label class="label">Empresas (acesso)</label>
      <div class="card" style="padding:10px;max-height:180px;overflow:auto">
        <?php foreach($companies as $c): ?>
          <label style="display:flex;align-items:center;gap:8px;margin:6px 0">
            <input type="checkbox" name="companies[]" value="<?= (int)$c['id'] ?>"> <?= htmlspecialchars($c['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <label class="label" style="margin-top:10px">Cargo(s)</label>
      <div class="card" style="padding:10px;max-height:220px;overflow:auto">
        <?php foreach($roles as $r): ?>
          <label style="display:flex;align-items:center;gap:8px;margin:6px 0">
            <input type="checkbox" name="roles[]" value="<?= (int)$r['id'] ?>"> <?= htmlspecialchars($r['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <label class="label" style="margin-top:10px">Observações</label>
      <textarea class="input" name="notes" rows="5" placeholder="Ex.: horário, restrições, documentação..."></textarea>
    </div>
  </div>

  <div style="margin-top:14px;display:flex;gap:8px">
    <button class="button" type="submit" id="btnSave2">Salvar</button>
    <a class="button ghost" href="<?= url_for('/pages/colaboradores.php') ?>">Cancelar</a>
  </div>
</form>

<script>
// preview da foto + loading elegante nos botões
const avatarUrl  = document.getElementById('avatarUrl');
const avatarPrev = document.getElementById('avatarPrev');
[avatarUrl].forEach(el=>{
  el.addEventListener('input', ()=>{ if(el.value.trim()) avatarPrev.src = el.value.trim(); });
});
function setLoading(el, isLoading){
  if (!el) return;
  if (isLoading) { el.classList.add("is-loading"); el.setAttribute("disabled","disabled"); }
  else { el.classList.remove("is-loading"); el.removeAttribute("disabled"); }
}
const btnSave = document.getElementById('btnSave');
const btnSave2= document.getElementById('btnSave2');
document.querySelector('form').addEventListener('submit', ()=>{ setLoading(btnSave,true); setLoading(btnSave2,true); });
</script>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
