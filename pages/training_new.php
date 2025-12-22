<?php
// pages/training_new.php
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
if (!canAccessAdmin()) { http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$companyId = currentCompanyId();
$roles = $pdo->query("SELECT id,name FROM roles ORDER BY name")->fetchAll();
?>
<style>
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:980px){.form-grid{grid-template-columns:1fr}}
.videos{display:flex;flex-direction:column;gap:12px}
.video-item{border:1px solid var(--stroke);border-radius:12px;padding:12px;background:rgba(255,255,255,.04)}
.video-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:980px){.video-grid{grid-template-columns:1fr}}
.badge-btn{border:1px dashed var(--stroke);background:transparent;color:#e8edf7;border-radius:12px;padding:.6rem .9rem;cursor:pointer}
.small{font-size:.9rem;color:#9aa4b2}
</style>

<form class="card" style="padding:16px" method="POST" action="<?= url_for('/pages/training_save.php') ?>">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
    <h2 style="margin:0;font-weight:900">Novo Treinamento</h2>
    <button class="button" type="submit">Salvar treinamento</button>
  </div>

  <input type="hidden" name="company_id" value="<?= (int)$companyId ?>"/>

  <div class="form-grid">
    <div>
      <label class="label">Título*</label>
      <input class="input" name="title" required>

      <label class="label" style="margin-top:10px">Objetivo*</label>
      <textarea class="input" name="objective" rows="3" required></textarea>

      <label class="label" style="margin-top:10px">Descrição</label>
      <textarea class="input" name="description" rows="5"></textarea>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
        <div style="flex:1;min-width:220px">
          <label class="label">Dificuldade</label>
          <select class="input" name="difficulty">
            <option>Iniciante</option>
            <option>Intermediário</option>
            <option>Avançado</option>
          </select>
        </div>
        <div style="flex:1;min-width:220px">
          <label class="label">Estimativa (min)</label>
          <input class="input" name="estimated_minutes" type="number" min="0" step="5" value="30">
        </div>
        <div style="flex:1;min-width:220px">
          <label class="label">Ativo?</label>
          <select class="input" name="is_active"><option value="1">Sim</option><option value="0">Não</option></select>
        </div>
      </div>

      <label class="label" style="margin-top:10px">Tags (separe por vírgula)</label>
      <input class="input" name="tags" placeholder="Higiene, Segurança, Atendimento">
    </div>

    <div>
      <label class="label">Capa (URL da imagem)</label>
      <input class="input" name="cover_image" placeholder="/assets/img/capas/arquivo.jpg ou https://...">

      <label class="label" style="margin-top:10px">Recompensa (URL da imagem)</label>
      <input class="input" name="reward_image" placeholder="/assets/img/rewards/icone.png">

      <label class="label" style="margin-top:10px">Visível para Cargos</label>
      <div class="card" style="padding:10px;max-height:220px;overflow:auto">
        <?php foreach($roles as $r): ?>
          <label style="display:flex;align-items:center;gap:8px;margin:6px 0">
            <input type="checkbox" name="roles[]" value="<?= (int)$r['id'] ?>"> <?= htmlspecialchars($r['name']) ?>
          </label>
        <?php endforeach; ?>
        <div class="small">Se nenhum cargo for marcado, somente Admin verá.</div>
      </div>
    </div>
  </div>

  <div style="margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <h3 style="margin:0;font-weight:900">Aulas / Vídeo-aulas</h3>
      <button class="badge-btn" type="button" id="btnAddVideo">+ Adicionar aula</button>
    </div>
    <div class="small" style="margin-top:4px">Provider padrão: YouTube (não listado). Preencha o link ou o ID do vídeo. Ordene pelo campo “Ordem”.</div>

    <div class="videos" id="videos"></div>
  </div>

  <div style="margin-top:14px;display:flex;gap:8px">
    <button class="button" type="submit">Salvar treinamento</button>
    <a class="button ghost" href="<?= url_for('/pages/treinamentos.php') ?>">Cancelar</a>
  </div>
</form>

<template id="tplVideo">
  <div class="video-item">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
      <strong>Aula <span class="vi-num">#</span></strong>
      <button class="badge-btn" type="button" onclick="this.closest('.video-item').remove(); renumber()">Remover</button>
    </div>
    <div class="video-grid" style="margin-top:10px">
      <div>
        <label class="label">Título da aula*</label>
        <input class="input" name="videos[IDX][title]" required>
      </div>
      <div>
        <label class="label">Ordem*</label>
        <input class="input" name="videos[IDX][order_index]" type="number" min="1" step="1" value="1" required>
      </div>
      <div>
        <label class="label">Provider</label>
        <select class="input" name="videos[IDX][video_provider]">
          <option value="youtube" selected>youtube</option>
          <option value="vimeo">vimeo</option>
          <option value="cloudflare">cloudflare</option>
          <option value="mux">mux</option>
          <option value="url">url</option>
        </select>
      </div>
      <div>
        <label class="label">Link/ID do vídeo*</label>
        <input class="input" name="videos[IDX][video_ref]" required placeholder="https://youtube.com/watch?v=...">
      </div>
      <div>
        <label class="label">Miniatura (URL)</label>
        <input class="input" name="videos[IDX][thumb_image]" placeholder="/assets/img/thumbs/aula.jpg">
      </div>
      <div>
        <label class="label">Duração (segundos)</label>
        <input class="input" name="videos[IDX][duration_seconds]" type="number" min="0" step="10" value="0">
      </div>
    </div>
    <label class="label" style="margin-top:10px">Resumo/descrição</label>
    <textarea class="input" name="videos[IDX][summary]" rows="3"></textarea>
  </div>
</template>

<script>
let idx = 0;
const list = document.getElementById('videos');
const tpl  = document.getElementById('tplVideo').innerHTML;
document.getElementById('btnAddVideo').addEventListener('click', addVideo);
function addVideo(){
  const html = tpl.replaceAll('IDX', String(idx));
  const wrap = document.createElement('div');
  wrap.innerHTML = html;
  list.appendChild(wrap.firstElementChild);
  idx++; renumber();
}
function renumber(){
  Array.from(document.querySelectorAll('.video-item .vi-num')).forEach((el,i)=>el.textContent = (i+1));
}
// adiciona 1 bloco inicial
addVideo();
</script>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
