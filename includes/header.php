<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';

$user            = $_SESSION['user'] ?? null;
$companies       = $_SESSION['companies'] ?? [];
$currentCompany  = $_SESSION['current_company'] ?? null;
$avatar          = ($user['avatar_url'] ?? '') ?: url_for('/assets/img/avatar.svg');
?>
<style>
:root{
  --mh-header-h: 64px; --mh-stroke: rgba(255,255,255,.12);
  --mh-text:#e8edf7; --mh-muted:#9aa4b2; --mh-brand:#ff6a00; --mh-brand2:#ff9153;
}

/* ========== HEADER FIXO ========== */
.mh-header{
  position:fixed;left:0;right:0;top:0;z-index:1000;height:var(--mh-header-h);
  display:grid;grid-template-columns:1fr auto;align-items:center;padding:10px 14px;
  background:linear-gradient(180deg,rgba(20,24,36,.96),rgba(20,24,36,.88));
  border-bottom:1px solid var(--mh-stroke);backdrop-filter:blur(8px)
}
.mh-left{display:flex;align-items:center;gap:10px}
.mh-title{font-weight:900;color:var(--mh-text)}
.mh-sub{color:var(--mh-muted);margin-left:6px}
@media (max-width:980px){.mh-title,.mh-sub{display:none}}
.mh-right{display:flex;align-items:center;gap:10px}

/* ========== SELECT DA EMPRESA (claro em todas telas) ========== */
.mh-org{
  position:relative; display:flex; align-items:center; gap:8px;
  padding:6px 12px; min-width:220px; border-radius:999px;
  background:#ffffff; border:1px solid #e5e7eb; box-shadow:0 1px 0 rgba(0,0,0,.05);
}
.mh-org select{
  appearance:none; background:transparent; border:none; width:100%;
  color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  color-scheme: light;  /* força tema claro nativo no mobile */
}
.mh-org select option{ background:#ffffff; color:#111827 }
.mh-org .chev{position:absolute; right:10px; width:16px; height:16px; color:#111827; opacity:.95}

/* Responsividade do select */
@media (max-width:980px){
  .mh-right{gap:8px}
  .mh-org{min-width:0; width:min(70vw, 320px); padding-right:34px}
  .mh-org .chev{right:8px}
}
@media (max-width:560px){ .mh-org{width:calc(100vw - 120px)} }
@media (max-width:420px){
  .mh-org{width:calc(100vw - 110px)}
  .mh-user img{width:34px; height:34px}
}

/* ========== AVATAR + SUBMENU ========== */
.mh-userwrap{position:relative}
.mh-user{
  display:flex;align-items:center;gap:8px;padding:4px;border-radius:14px;
  background:rgba(255,255,255,.04);border:1px solid var(--mh-stroke);cursor:pointer
}
.mh-user img{width:40px;height:40px;border-radius:12px;object-fit:cover;border:1px solid var(--mh-stroke)}
.mh-menu{
  position:absolute;right:0;top:calc(100% + 8px);min-width:180px;z-index:1100;display:none;
  background:linear-gradient(160deg,rgba(20,24,36,.98),rgba(20,24,36,.92));
  border:1px solid var(--mh-stroke);border-radius:14px;padding:6px;box-shadow:0 12px 28px rgba(0,0,0,.35)
}
.mh-menu.show{display:block}
.mh-menu a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#e8edf7;text-decoration:none}
.mh-menu a:hover{background:rgba(255,255,255,.06)}
.mh-menu svg{width:18px;height:18px}

/* ========== BURGER (mobile) ========== */
.mh-burger{
  display:none;width:40px;height:40px;border-radius:12px;border:1px solid var(--mh-stroke);
  background:rgba(255,255,255,.04);align-items:center;justify-content:center;gap:4px;cursor:pointer
}
.mh-burger span{display:block;width:18px;height:2px;background:#fff;border-radius:2px}
@media (max-width:980px){.mh-burger{display:flex}}

/* ========== MENU MOBILE (overlay do topo com SCROLL) ========== */
.mh-backdrop{
  position:fixed; inset:0; z-index:900; background:rgba(0,0,0,.45);
  opacity:0; pointer-events:none; transition:.2s ease
}
.mh-backdrop.show{opacity:1; pointer-events:auto}

.mh-mpanel{
  position:fixed; left:0; right:0; top:var(--mh-header-h); z-index:950;
  height:calc(100vh - var(--mh-header-h));  /* ocupa toda área abaixo do header */
  background:linear-gradient(160deg,rgba(20,24,36,.98),rgba(20,24,36,.92));
  border-bottom:1px solid var(--mh-stroke);
  transform:translateY(-12px); opacity:0; pointer-events:none; transition:.22s ease;
  overflow:hidden; /* o scroll fica na .inner */
}
.mh-mpanel.open{transform:translateY(0); opacity:1; pointer-events:auto}

/* área rolável interna */
.mh-mpanel .inner{
  height:100%; overflow-y:auto; -webkit-overflow-scrolling:touch; padding:12px 12px 18px;
  scrollbar-width:thin; scrollbar-color: rgba(255,255,255,.3) transparent;
}
.mh-mpanel .inner::-webkit-scrollbar{ width:8px }
.mh-mpanel .inner::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.28); border-radius:10px }
.mh-mpanel .inner::-webkit-scrollbar-track{ background:transparent }

/* fades indicando que há mais conteúdo acima/abaixo */
.mh-mpanel::before,
.mh-mpanel::after{
  content:""; position:sticky; left:0; right:0; display:block; z-index:1; pointer-events:none; height:14px
}
.mh-mpanel::before{ top:0; background:linear-gradient(180deg,rgba(20,24,36,1),rgba(20,24,36,0)) }
.mh-mpanel::after{ bottom:0; margin-top:-14px; background:linear-gradient(0deg,rgba(20,24,36,1),rgba(20,24,36,0)) }

/* trava o scroll do fundo quando o menu abre */
body.mh-lock{ overflow:hidden; touch-action:none; overscroll-behavior:contain }

/* Esconde overlay em desktop */
@media (min-width:981px){.mh-backdrop,.mh-mpanel{display:none}}
</style>

<header class="mh-header">
  <div class="mh-left">
    <button class="mh-burger" id="mhBurger" aria-label="Abrir menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <div>
      <div class="mh-title">Mindhub</div>
      <div class="mh-sub">RH &amp; Treinamentos</div>
    </div>
  </div>

  <div class="mh-right">
    <?php if (!empty($companies)): ?>
      <div class="mh-org">
        <select id="mhSelectOrg" onchange="mhSwitchOrg(this)" aria-label="Selecionar organização">
          <?php foreach ($companies as $c): ?>
            <option
              title="<?= htmlspecialchars(($c['trade_name'] ?: $c['trade_name'])) ?>"
              value="<?= (int)$c['id'] ?>"
              <?= ($currentCompany && $c['id']==$currentCompany['id'])?'selected':'' ?>>
              <?= htmlspecialchars(($c['trade_name'] ?: $c['trade_name'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <svg class="chev" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5H7z"/></svg>
      </div>
    <?php endif; ?>

    <div class="mh-userwrap">
      <button class="mh-user" id="mhUserBtn" aria-haspopup="menu" aria-expanded="false" title="<?= htmlspecialchars($user['name'] ?? '') ?>">
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Perfil">
      </button>
      <nav class="mh-menu" id="mhUserMenu" role="menu" aria-label="Menu do usuário">
        <a href="<?= url_for('/pages/meus_dados.php') ?>" role="menuitem">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm-9 9a9 9 0 1118 0H3z"/></svg>
          Meu perfil
        </a>
        <a href="<?= url_for('/auth/logout.php') ?>" role="menuitem">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 13v-2H7V7l-5 5 5 5v-4h9zM20 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
          Sair
        </a>
      </nav>
    </div>
  </div>
</header>

<!-- overlay do menu mobile -->
<div class="mh-backdrop" id="mhBackdrop"></div>
<div class="mh-mpanel" id="mhMpanel">
  <div class="inner">
    <div class="logo"><img style="height: 60px;" src="<?= url_for('/assets/img/logo.png') ?>" alt="Mindhub"></div>
    <?php include __DIR__ . '/menu_items.php'; ?>
  </div>
</div>

<script>
async function mhPostJSON(url, payload){
  const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload||{})});
  const t = await r.text(); try{return JSON.parse(t)}catch(e){return {status:'error', message:t}}
}
async function mhSwitchOrg(sel){
  if(!sel?.value) return;
  const res = await mhPostJSON('<?= url_for("/auth/switch_company.php") ?>', {company_id: parseInt(sel.value,10)});
  if(res.status==='ok') location.reload(); else alert(res.message||'Não foi possível trocar a organização.');
}

/* ===== Menu mobile com scroll + bloqueio do fundo ===== */
(function(){
  const burger = document.getElementById('mhBurger');
  const panel  = document.getElementById('mhMpanel');
  const back   = document.getElementById('mhBackdrop');

  function open(){
    panel.classList.add('open');
    back.classList.add('show');
    burger?.setAttribute('aria-expanded','true');
    document.body.classList.add('mh-lock');   // bloqueia scroll do fundo
    // garante início do scroll no topo ao abrir
    const inner = panel.querySelector('.inner'); inner && (inner.scrollTop = 0);
  }
  function close(){
    panel.classList.remove('open');
    back.classList.remove('show');
    burger?.setAttribute('aria-expanded','false');
    document.body.classList.remove('mh-lock'); // libera scroll do fundo
  }

  burger?.addEventListener('click', ()=> panel.classList.contains('open') ? close() : open());
  back?.addEventListener('click', close);
  // evita arrastar o fundo ao mover o dedo no backdrop
  back?.addEventListener('touchmove', (e)=>{ e.preventDefault(); }, { passive:false });
  // fecha ao clicar em links dentro do menu
  panel?.addEventListener('click', e=>{ if(e.target.closest('a')) close(); });
})();

/* ===== Submenu do avatar ===== */
(function(){
  const btn = document.getElementById('mhUserBtn');
  const menu = document.getElementById('mhUserMenu');
  function toggle(){ const on = !menu.classList.contains('show'); menu.classList.toggle('show', on); btn.setAttribute('aria-expanded', on?'true':'false'); }
  function close(){ menu.classList.remove('show'); btn.setAttribute('aria-expanded','false'); }
  btn?.addEventListener('click', (e)=>{ e.stopPropagation(); toggle(); });
  document.addEventListener('click', (e)=>{ if(!e.target.closest('.mh-userwrap')) close(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });
})();
</script>
