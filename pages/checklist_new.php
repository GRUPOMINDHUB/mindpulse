<?php
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
if (!canAccessAdmin()) { http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$companyId = currentCompanyId();
$roles = [];
try { $roles = $pdo->query("SELECT id,name FROM roles ORDER BY name")->fetchAll() ?: []; } catch(Throwable $e){ $roles = []; }
?>
<style>
/* ===== Layout geral ===== */
.form-shell{display:flex;flex-direction:column;gap:12px}
.form-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:14px}
@media(max-width:980px){.form-grid{grid-template-columns:1fr}}

.card-sec{border:1px solid var(--stroke);border-radius:16px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.03));padding:12px}
.header-line{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:10px;flex-wrap:wrap}
.header-line h2{margin:0;font-weight:900}

/* ===== Lista de cargos com scroll suave ===== */
.roles-box{padding:8px;max-height:200px;overflow:auto;border:1px solid var(--stroke);border-radius:12px;background:rgba(255,255,255,.04)}
.roles-box{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.25) transparent}
.roles-box::-webkit-scrollbar{width:8px}
.roles-box::-webkit-scrollbar-thumb{background:rgba(255,255,255,.25);border-radius:10px}

/* ===== Tarefas: linha responsiva ===== */
.row{display:grid;grid-template-columns: 1.2fr 0.9fr 0.9fr 1fr auto;gap:8px;align-items:center;margin-bottom:8px}
@media(max-width:1100px){.row{grid-template-columns:1.2fr 1fr 1fr auto}}
@media(max-width:720px){.row{grid-template-columns:1fr}}
.row .input,.row select{width:100%}

/* Labels mini em mobile para contexto */
@media(max-width:720px){
  .field-label{font-size:.8rem;color:#9aa4b2;margin-top:4px}
}

/* ===== Botões ===== */
.actions{display:flex;gap:8px;flex-wrap:wrap}
.button.is-loading{position:relative;color:transparent;pointer-events:none}
.button.is-loading::after{content:"";position:absolute;left:50%;top:50%;width:16px;height:16px;margin:-8px 0 0 -8px;border-radius:50%;
  border:2px solid rgba(255,255,255,.35);border-top-color:#fff;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ===== Picker de prioridade (🔥) ===== */
.prio-wrap{display:flex;align-items:center;gap:8px}
.prio{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--stroke);border-radius:12px;padding:6px 8px;background:rgba(255,255,255,.04)}
.prio .flame{font-size:20px;opacity:.45;transition:.12s ease;cursor:pointer;user-select:none}
.prio .flame.on{opacity:1; transform:translateY(-1px)}
.prio .flame:hover{transform:translateY(-2px)}
.prio .hint{color:#cbd5e1;font-size:.85rem;margin-left:6px}
.prio .scale{display:flex;gap:2px}
.prio .scale .dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.25)}
.prio .scale .dot.fill{background:linear-gradient(135deg,#ff6a00,#ff9153)}

/* ===== Badges/mini chips ===== */
.badge-soft{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid var(--stroke);background:rgba(255,255,255,.04);font-weight:800}

/* ===== “Adicionar tarefa” CTA ===== */
.add-line{margin-top:10px}
</style>

<form class="card form-shell" method="POST" action="<?= url_for('/pages/checklist_save.php') ?>" id="clForm">
  <div class="header-line">
    <h2>Novo Checklist</h2>
    <div class="actions">
      <button class="button" type="submit" id="btnSubmit">Salvar</button>
      <a class="button ghost" href="<?= url_for('/pages/checklists.php') ?>">Cancelar</a>
    </div>
  </div>

  <div class="form-grid">
    <div class="card-sec">
      <label class="label">Título*</label>
      <input class="input" name="title" required placeholder="Ex.: Abertura do Salão — Manhã">

      <label class="label" style="margin-top:10px">Descrição</label>
      <textarea class="input" name="description" rows="3" placeholder="Objetivo, padrões e observações gerais do checklist."></textarea>
    </div>

    <div class="card-sec">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label class="label">Frequência</label>
          <select class="input" name="frequency">
            <option value="daily">Diária</option>
            <option value="weekly">Semanal</option>
            <option value="biweekly">Quinzenal</option>
            <option value="monthly">Mensal</option>
          </select>
        </div>
        <div>
          <label class="label">Cargo responsável (padrão)</label>
          <select class="input" name="default_role_id">
            <option value="">—</option>
            <?php foreach($roles as $r): ?>
              <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label class="label" style="margin-top:10px">Cargos com acesso</label>
      <div class="roles-box">
        <?php foreach($roles as $r): ?>
          <label style="display:flex;align-items:center;gap:8px;margin:4px 0">
            <input type="checkbox" name="roles[]" value="<?= (int)$r['id'] ?>"> <?= htmlspecialchars($r['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="badge-soft" style="margin-top:10px">
        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 2l4 7h-8l4-7zm0 20c-4.418 0-8-3.582-8-8h2a6 6 0 1012 0h2c0 4.418-3.582 8-8 8z"/></svg>
        Dica: configure o **cargo padrão** para sabermos quem abre este checklist por padrão.
      </div>
    </div>
  </div>

  <div class="card-sec" style="margin-top:12px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
      <div style="font-weight:900">Tarefas</div>
      <span class="small" style="color:#9aa4b2">Período é a janela esperada (início/fim do dia/semana)</span>
    </div>

    <div id="taskList"></div>
    <button type="button" class="button ghost add-line" id="btnAdd">+ Adicionar tarefa</button>
  </div>

  <div class="actions" style="margin-top:6px">
    <button class="button" type="submit" id="btnSubmitBottom">Salvar Checklist</button>
    <a class="button ghost" href="<?= url_for('/pages/checklists.php') ?>">Cancelar</a>
  </div>
</form>

<script>
const taskList = document.getElementById('taskList');
const btnAdd = document.getElementById('btnAdd');
const btnSubmit = document.getElementById('btnSubmit');
const btnSubmitBottom = document.getElementById('btnSubmitBottom');
let idx = 0;

/** Componente: Picker de prioridade (🔥) **/
function prioPickerHTML(name, initial=3){
  const id = 'p'+Math.random().toString(36).slice(2,8);
  const flames = Array.from({length:5}).map((_,i)=>`
    <span class="flame ${i<initial?'on':''}" data-v="${i+1}" title="Prioridade ${i+1}">🔥</span>
  `).join('');
  const dots   = Array.from({length:5}).map((_,i)=>`
    <span class="dot ${i<initial?'fill':''}"></span>
  `).join('');
  return `
    <div class="prio-wrap">
      <div class="prio" data-target="${id}">
        ${flames}
        <span class="hint">x<span class="hint-n">${initial}</span></span>
        <span class="scale" aria-hidden="true">${dots}</span>
      </div>
      <input type="hidden" id="${id}" name="${name}" value="${initial}">
    </div>
  `;
}

function bindPrioPickers(scope=document){
  scope.querySelectorAll('.prio').forEach(pr=>{
    const targetId = pr.dataset.target;
    const input = document.getElementById(targetId);
    const hint = pr.querySelector('.hint-n');
    const flames = pr.querySelectorAll('.flame');
    const dots = pr.querySelectorAll('.dot');

    pr.addEventListener('click', e=>{
      const f = e.target.closest('.flame'); if(!f) return;
      const val = parseInt(f.dataset.v,10);
      input.value = val;
      hint.textContent = val;
      flames.forEach((el,i)=> el.classList.toggle('on', i < val));
      dots.forEach((el,i)=> el.classList.toggle('fill', i < val));
    });
  });
}

/** Linha de tarefa **/
function addRow(values={}){
  const id = idx++;
  const name     = values.name     || '';
  const period   = values.period   || 'final_dia';
  const priority = values.priority || 3;
  const notes    = values.notes    || '';

  const el = document.createElement('div');
  el.className = 'row';
  el.innerHTML = `
    <div>
      <div class="field-label">Tarefa</div>
      <input class="input" name="tasks[${id}][name]" placeholder="Descrever a tarefa" value="${name.replace(/"/g,'&quot;')}" required>
    </div>

    <div>
      <div class="field-label">Período</div>
      <select class="input" name="tasks[${id}][period]">
        <option value="inicio_dia" ${period==='inicio_dia'?'selected':''}>Início do dia</option>
        <option value="final_dia" ${period==='final_dia'?'selected':''}>Até o final do dia</option>
        <option value="inicio_semana" ${period==='inicio_semana'?'selected':''}>Início da semana</option>
        <option value="final_semana" ${period==='final_semana'?'selected':''}>Até o final da semana</option>
      </select>
    </div>

    <div>
      <div class="field-label">Prioridade</div>
      ${prioPickerHTML(`tasks[${id}][priority]`, priority)}
    </div>

    <div>
      <div class="field-label">Observações</div>
      <input class="input" name="tasks[${id}][notes]" placeholder="Opcional" value="${notes.replace(/"/g,'&quot;')}">
    </div>

    <div style="display:flex;align-items:flex-end;justify-content:flex-end">
      <button class="button ghost" type="button" onclick="this.closest('.row').remove()" title="Remover">✕</button>
    </div>
  `;
  taskList.appendChild(el);
  bindPrioPickers(el);
}

btnAdd.addEventListener('click', ()=> addRow());
addRow(); addRow();

/* Loading state no submit */
function setLoading(on){
  [btnSubmit, btnSubmitBottom].forEach(b=>{ if(!b) return; b.classList.toggle('is-loading', on); b.disabled = on; });
}
document.getElementById('clForm').addEventListener('submit', ()=> setLoading(true));
</script>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
