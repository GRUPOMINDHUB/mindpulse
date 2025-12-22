<?php
// pages/treinamento.php — Mindhub (YouTube-ready + UX/Loading + finalize fix)
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/training.php';

// Fallback local para extrair ID do YouTube (se não estiver no helper)
if (!function_exists('youtube_id_from')) {
  function youtube_id_from(string $ref): ?string {
    $ref = trim($ref);
    if (preg_match('~^(?:https?:)?//(?:www\.)?youtu(?:\.be/|be\.com/(?:watch\?v=|embed/))([A-Za-z0-9_-]{6,})~', $ref, $m)) return $m[1];
    if (preg_match('~^[A-Za-z0-9_-]{6,}$~', $ref)) return $ref;
    return null;
  }
}

$userId     = (int)($_SESSION['user']['id'] ?? 0);
$companyId  = currentCompanyId();
$trainingId = (int)($_GET['id'] ?? 0);

if ($trainingId <= 0) { echo '<div class="card" style="padding:20px">Treinamento inválido.</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

$training = trainingById($pdo, $trainingId, $companyId);
if (!$training) { echo '<div class="card" style="padding:20px">Treinamento não encontrado.</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; }

if (!userHasAccessToTraining($pdo, $userId, $trainingId)) {
  http_response_code(403);
  echo '<div class="card" style="padding:20px">Você não tem acesso a este treinamento.</div>';
  require_once __DIR__ . '/../includes/layout_end.php'; exit;
}

$videos         = trainingVideos($pdo, $trainingId);
$progress       = userTrainingProgress($pdo, $userId, $trainingId);
$doneIdsArr     = userCompletedVideos($pdo, $userId, $trainingId);
$doneIds        = array_flip($doneIdsArr);
$currentVideoId = (int)($_GET['v'] ?? 0);
if (!$currentVideoId) $currentVideoId = $progress['nextVideoId'] ?: ($videos[0]['id'] ?? 0);

// localizar vídeo atual
$currentVideo = null;
foreach ($videos as $v) { if ((int)$v['id'] === $currentVideoId) { $currentVideo = $v; break; } }
if (!$currentVideo && !empty($videos)) $currentVideo = $videos[0];

// próximo vídeo
$nextId = null;
$ordered = array_values($videos);
for ($i=0; $i<count($ordered); $i++) {
  if ((int)$ordered[$i]['id'] === (int)($currentVideo['id'] ?? 0) && isset($ordered[$i+1])) { $nextId = (int)$ordered[$i+1]['id']; break; }
}

// estilos responsivos + botões com loading
echo '<style>
@media (max-width: 980px){ .train-grid{ grid-template-columns: 1fr !important; } }

/* Efeitos globais dos botões */
.button, .button.ghost{
  position:relative; overflow:hidden;
  transition: transform .18s ease, filter .18s ease, opacity .18s ease, box-shadow .18s ease;
}
.button:hover{ filter:brightness(1.05) }
.button:active{ transform: translateY(1px) }
.button[disabled]{ opacity:.65; cursor:not-allowed }

/* Loading spinner laranja (aplicado com .is-loading) */
.button.is-loading, .button.ghost.is-loading{ pointer-events:none; color:transparent !important }
.button.is-loading::after, .button.ghost.is-loading::after{
  content:""; position:absolute; inset:auto auto auto auto; left:50%; top:50%;
  width:18px; height:18px; margin-left:-9px; margin-top:-9px; border-radius:50%;
  border:3px solid rgba(255,106,0,.25); border-top-color:#ff6a00; animation:spin .8s linear infinite;
}
@keyframes spin{ to{ transform:rotate(360deg) } }
</style>';
?>

<div class="train-grid" style="display:grid; grid-template-columns: 1.1fr .9fr; gap:18px">
  <section class="card" style="padding:0; overflow:hidden">
    <div style="position:relative">
      <img src="<?= htmlspecialchars($training['cover_image'] ?: url_for('/assets/img/login_hero.svg')) ?>" alt="" style="width:100%; height:200px; object-fit:cover">
      <?php if (!empty($training['reward_image'])): ?>
        <img src="<?= htmlspecialchars($training['reward_image']) ?>" alt="recompensa" style="position:absolute; right:12px; bottom:12px; width:56px; height:56px; border-radius:14px; border:1px solid var(--stroke)">
      <?php endif; ?>
    </div>

    <div style="padding:18px">
      <h2 style="margin:0; font-weight:900"><?= htmlspecialchars($training['title']) ?></h2>
      <div style="color:#9aa4b2; margin-top:6px"><?= nl2br(htmlspecialchars($training['objective'])) ?></div>

      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; color:#cbd5e1; font-size:.92rem">
        <span class="badge"><?= htmlspecialchars($training['difficulty']) ?></span>
        <?php if(!empty($training['estimated_minutes'])): ?><span class="badge"><?= (int)$training['estimated_minutes'] ?> min</span><?php endif; ?>
        <?php if(!empty($training['tags'])): ?><span class="badge"><?= htmlspecialchars($training['tags']) ?></span><?php endif; ?>
      </div>

      <div style="margin-top:12px">
        <div style="height:10px; background:rgba(255,255,255,.08); border-radius:999px; overflow:hidden">
          <div style="height:100%; width:<?= (int)$progress['percent'] ?>%; background:linear-gradient(135deg,var(--brand),var(--brand-2))"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:6px; color:#cbd5e1; font-size:.9rem">
          <span>Progresso</span><strong><?= (int)$progress['percent'] ?>% (<?= (int)$progress['done'] ?>/<?= (int)$progress['total'] ?>)</strong>
        </div>
      </div>
    </div>

    <?php if ($currentVideo): ?>
      <div style="padding:0 18px 18px">
        <div class="card" style="padding:12px; overflow:hidden">
          <div style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap">
            <img src="<?= htmlspecialchars($currentVideo['thumb_image'] ?: url_for('/assets/img/avatar.svg')) ?>" style="width:120px; height:68px; object-fit:cover; border-radius:12px; border:1px solid var(--stroke)">
            <div>
              <div style="font-weight:800"><?= htmlspecialchars($currentVideo['title']) ?></div>
              <?php if(!empty($currentVideo['duration_seconds'])): ?>
                <div style="color:#9aa4b2; font-size:.9rem; margin-top:2px">Duração: ~<?= (int)ceil($currentVideo['duration_seconds']/60) ?> min</div>
              <?php endif; ?>
              <?php if(!empty($currentVideo['summary'])): ?>
                <div style="color:#cbd5e1; margin-top:6px"><?= nl2br(htmlspecialchars($currentVideo['summary'])) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- PLAYER -->
          <div style="margin-top:12px; background:rgba(255,255,255,.06); border:1px solid var(--stroke); border-radius:12px; overflow:hidden">
            <?php
              $src = '';
              switch ($currentVideo['video_provider']) {
                case 'youtube':
                  $vid = youtube_id_from($currentVideo['video_ref']);
                  if ($vid) {
                    $params = 'rel=0&modestbranding=1&controls=1&playsinline=1';
                    $src = "https://www.youtube-nocookie.com/embed/{$vid}?{$params}";
                    echo "<iframe src=\"$src\" style=\"width:100%; aspect-ratio:16/9\" allow=\"autoplay; encrypted-media; picture-in-picture\" allowfullscreen></iframe>";
                  } else {
                    echo "<div style='padding:16px;color:#fecaca'>Link do YouTube inválido.</div>";
                  }
                  break;
                case 'cloudflare':
                  $src = htmlspecialchars($currentVideo['video_ref']);
                  echo "<iframe src=\"$src\" style=\"width:100%; aspect-ratio:16/9\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>";
                  break;
                case 'vimeo':
                  $src = htmlspecialchars($currentVideo['video_ref']);
                  echo "<iframe src=\"$src\" style=\"width:100%; aspect-ratio:16/9\" frameborder=\"0\" allow=\"autoplay; fullscreen; picture-in-picture\" allowfullscreen></iframe>";
                  break;
                case 'mux':
                  $src = htmlspecialchars($currentVideo['video_ref']);
                  echo "<video controls style=\"width:100%; aspect-ratio:16/9\"><source src=\"$src\" type=\"application/x-mpegURL\"></video>";
                  break;
                default:
                  $src = htmlspecialchars($currentVideo['video_ref']);
                  echo "<video controls style=\"width:100%; aspect-ratio:16/9\"><source src=\"$src\" type=\"video/mp4\"></video>";
              }
            ?>
          </div>

          <div style="display:flex; gap:10px; margin-top:12px; flex-wrap:wrap">
            <?php $isDone = isset($doneIds[$currentVideo['id']]); ?>
            <button class="button" id="btnComplete" <?= $isDone ? 'disabled' : '' ?>><?= $isDone ? 'Aula concluída' : 'Concluir aula' ?></button>

            <?php
              $trainingAlready100 = ((int)$progress['percent'] === 100);
            ?>
            <?php if ($nextId): ?>
              <a class="button ghost" id="btnNext"
                 href="<?= url_for('/pages/treinamento.php') ?>?id=<?= (int)$trainingId ?>&v=<?= (int)$nextId ?>"
                 <?= $isDone ? '' : 'style="pointer-events:none; opacity:.6"'?>>Próxima aula →</a>
            <?php else: ?>
              <button class="button ghost" id="btnFinish" <?= $trainingAlready100 ? '' : 'disabled' ?>>Finalizar Treinamento</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <aside class="card" style="padding:12px">
    <div style="font-weight:900; padding:6px 6px 10px">Trilha de aulas</div>
    <div style="display:flex; flex-direction:column; gap:8px; max-height:72vh; overflow:auto; padding-right:6px">
      <?php foreach($videos as $v): $done = isset($doneIds[$v['id']]); ?>
        <a class="card" href="<?= url_for('/pages/treinamento.php') ?>?id=<?= (int)$trainingId ?>&v=<?= (int)$v['id'] ?>"
           style="display:flex; gap:10px; padding:8px; align-items:center; border:1px solid var(--stroke)">
          <img src="<?= htmlspecialchars($v['thumb_image'] ?: url_for('/assets/img/avatar.svg')) ?>" style="width:72px; height:40px; object-fit:cover; border-radius:8px; border:1px solid var(--stroke)">
          <div style="flex:1">
            <div style="font-weight:700; font-size:.98rem"><?= htmlspecialchars($v['title']) ?></div>
            <?php if(!empty($v['duration_seconds'])): ?><div style="color:#9aa4b2; font-size:.85rem">~<?= (int)ceil($v['duration_seconds']/60) ?> min</div><?php endif; ?>
          </div>
          <?php if ($done): ?><span class="badge" title="Concluída">✔</span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:12px">
      <div style="font-weight:800; margin-bottom:4px">Recompensa</div>
      <?php if(!empty($training['reward_image'])): ?>
        <img src="<?= htmlspecialchars($training['reward_image']) ?>" alt="recompensa" style="width:80px; height:80px; border-radius:16px; border:1px solid var(--stroke)">
      <?php else: ?>
        <div style="color:#9aa4b2">Sem imagem definida.</div>
      <?php endif; ?>
      <div style="color:#cbd5e1; font-size:.9rem; margin-top:6px">Conclua todas as aulas para ganhar este ícone no seu perfil.</div>
    </div>
  </aside>
</div>

<script>
// efeitos de loading (já usados)
function setLoading(el, isLoading){
  if (!el) return;
  if (isLoading) { el.classList.add("is-loading"); el.setAttribute("disabled","disabled"); }
  else { el.classList.remove("is-loading"); el.removeAttribute("disabled"); }
}

// POST robusto (tolera lixo antes do JSON)
async function postJSON(url, payload){
  const r = await fetch(url, { method:"POST", headers:{ "Content-Type":"application/json" }, body: JSON.stringify(payload||{}) });
  const text = await r.text();
  // tenta JSON direto
  let data = null;
  try { data = JSON.parse(text); }
  catch(e){
    // extrai o ÚLTIMO bloco { ... } (caso algum include tenha impresso algo antes)
    const m = text.match(/\{[\s\S]*\}$/);
    if (m) { data = JSON.parse(m[0]); }
  }
  if (!data) throw new Error("Resposta inválida do servidor.");
  if (!r.ok || data.status !== "ok") {
    const msg = data.message || ("HTTP " + r.status);
    throw new Error(msg);
  }
  return data;
}

const btnComplete = document.getElementById('btnComplete');
const btnNext     = document.getElementById('btnNext');
const btnFinish   = document.getElementById('btnFinish');

if (btnComplete) {
  btnComplete.addEventListener('click', async () => {
    try {
      setLoading(btnComplete, true);
      const data = await postJSON('<?= url_for("/pages/training_complete_video.php") ?>', {
        training_id: <?= (int)$trainingId ?>,
        video_id: <?= (int)($currentVideo['id'] ?? 0) ?>
      });

      // sucesso: marca concluída, libera próxima/finish
      btnComplete.textContent = 'Aula concluída';
      if (btnNext) { btnNext.style.pointerEvents = 'auto'; btnNext.style.opacity = '1'; }
      if (btnFinish) { btnFinish.removeAttribute('disabled'); }

      // se o servidor sinalizou que concluiu o treinamento, já habilita/feedback
      if (btnFinish && data.training_completed) {
        btnFinish.classList.add('pulse-win');
      }
    } catch (err) {
      alert(err.message || 'Falha ao concluir.');
      btnComplete.textContent = 'Tentar novamente';
      setLoading(btnComplete, false);
    }
  });
}

if (btnFinish) {
  btnFinish.addEventListener('click', async () => {
    try {
      setLoading(btnFinish, true);
      await postJSON('<?= url_for("/pages/training_finalize.php") ?>', { training_id: <?= (int)$trainingId ?> });
      // redireciona elegante
      setTimeout(()=>{ window.location.href = '<?= url_for("/pages/treinamentos.php") ?>'; }, 600);
    } catch (err) {
      alert(err.message || 'Não foi possível finalizar.');
      setLoading(btnFinish, false);
    }
  });
}
</script>


<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
