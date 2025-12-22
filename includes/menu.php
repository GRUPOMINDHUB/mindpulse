<?php require_once __DIR__ . '/auth.php'; ?>

<!-- Topbar visível só no mobile (hambúrguer) -->
<div class="mh-topbar">
  <button class="mh-burger" id="mhBurger" aria-label="Abrir menu" aria-expanded="false" aria-controls="mhSidebar">
    <span></span><span></span><span></span>
  </button>
  <img src="<?= url_for('/assets/img/logo.png') ?>" alt="Mindhub" class="mh-top-logo">
</div>

<!-- Backdrop para mobile -->
<div id="mhBackdrop" class="mh-backdrop" hidden></div>

<!-- Sidebar -->
<aside class="mh-sidebar" id="mhSidebar" aria-hidden="false">
  <div class="mh-brand">
    <img src="<?= url_for('/assets/img/logo.png') ?>" alt="Mindhub" class="mh-logo">
  </div>

  <nav class="mh-nav">
    <div class="mh-section">
      <h6 class="mh-sec-title">Colaborador</h6>
      <a class="mh-item" href="<?= url_for('/pages/treinamentos.php') ?>">
        <span class="mh-ico">
          <!-- play -->
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
        </span>
        <span class="label">Treinamentos</span>
      </a>
      <a class="mh-item" href="<?= url_for('/pages/meus_dados.php') ?>">
        <span class="mh-ico">
          <!-- user -->
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5C21 16.5 17 14 12 14Z"/></svg>
        </span>
        <span class="label">Meus dados</span>
      </a>
    </div>

    <?php if (canAccessAdmin()): ?>
    <div class="mh-section">
      <h6 class="mh-sec-title">Admin</h6>

      <a class="mh-item" href="<?= url_for('/pages/empresas.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 21V3h8v4h10v14H3Zm10-2h6V9h-6v10ZM5 19h6V5H5v14Z"/></svg></span>
        <span class="label">Empresas</span>
      </a>

      <a class="mh-item" href="<?= url_for('/pages/colaboradores.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg></span>
        <span class="label">Colaboradores</span>
      </a>

      <a class="mh-item" href="<?= url_for('/pages/config.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94a7.77 7.77 0 0 0 .05-.94 7.77 7.77 0 0 0-.05-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.61-.22l-2.39.96a7.42 7.42 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54a7.42 7.42 0 0 0-1.63.94l-2.39-.96a.5.5 0 0 0-.61.22L2.66 8.84a.5.5 0 0 0 .12.64L4.81 11.06c-.03.31-.05.63-.05.94s.02.63.05.94L2.78 14.52a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .61.22l2.39-.96c.5.4 1.05.72 1.63.94l.36 2.54a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.54c.58-.22 1.13-.54 1.63-.94l2.39.96a.5.5 0 0 0 .61-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5Z"/></svg></span>
        <span class="label">Configurações</span>
      </a>

      <a class="mh-item" href="<?= url_for('/pages/training_new.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
        <span class="label">Novo Treinamento</span>
      </a>

      <a class="mh-item" href="<?= url_for('/pages/collaborator_new.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 8a4 4 0 1 1-4-4 4 4 0 0 1 4 4ZM4 20v-1c0-3.31 2.69-6 6-6h0c3.31 0 6 2.69 6 6v1H4Z"/></svg></span>
        <span class="label">Novo Colaborador</span>
      </a>
    </div>
    <?php endif; ?>

    <div class="mh-section">
      <h6 class="mh-sec-title">Sessão</h6>
      <a class="mh-item" href="<?= url_for('/auth/logout.php') ?>">
        <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v3H3v4h7v3zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg></span>
        <span class="label">Sair</span>
      </a>
    </div>
  </nav>
</aside>

<style>
/* ====== Base ====== */
:root{
  --mh-bg: #0f1117;
  --mh-panel: #141824;
  --mh-stroke: rgba(255,255,255,.12);
  --mh-text: #e8edf7;
  --mh-muted: #9aa4b2;
  --mh-brand: #ff6a00;
  --mh-brand-2: #ff9153;
}

.mh-topbar{
  display:none;
  position:sticky; top:0; z-index:60;
  background:linear-gradient(180deg,rgba(15,17,23,.95),rgba(15,17,23,.85));
  border-bottom:1px solid var(--mh-stroke);
  padding:10px 12px;
  align-items:center; gap:10px;
}
.mh-top-logo{ height:34px; }

.mh-burger{
  width:40px; height:40px; border-radius:12px; border:1px solid var(--mh-stroke);
  background:rgba(255,255,255,.04); display:inline-flex; flex-direction:column;
  justify-content:center; align-items:center; gap:4px; cursor:pointer;
  transition:.15s ease;
}
.mh-burger:hover{ filter:brightness(1.05) }
.mh-burger span{ width:18px; height:2px; background:var(--mh-text); display:block; border-radius:2px }

/* Sidebar */
.mh-sidebar{
  position:sticky; top:0; height:100dvh; width:260px;
  background:linear-gradient(160deg, rgba(20,24,36,.95), rgba(20,24,36,.85));
  border-right:1px solid var(--mh-stroke);
  padding:14px 12px; z-index:50;
  backdrop-filter: blur(6px);
}
.mh-brand{ display:flex; align-items:center; justify-content:center; padding:8px 0 12px }
.mh-logo{ height:56px; }

.mh-nav{ display:flex; flex-direction:column; gap:18px; }
.mh-section{ display:flex; flex-direction:column; gap:6px; }
.mh-sec-title{ color:var(--mh-muted); letter-spacing:.08em; font-weight:800; text-transform:uppercase; font-size:.72rem; margin:6px 6px; }

.mh-item{
  display:flex; align-items:center; gap:10px;
  padding:10px 10px; border-radius:12px; color:var(--mh-text); text-decoration:none;
  border:1px solid transparent; transition:.18s ease; position:relative; overflow:hidden;
}
.mh-item:hover{
  background:linear-gradient(135deg, rgba(255,106,0,.12), rgba(255,106,0,.06));
  border-color:var(--mh-stroke);
  transform:translateY(-1px);
  box-shadow:0 10px 22px rgba(0,0,0,.16);
}
.mh-ico{
  width:28px; height:28px; border-radius:10px; display:grid; place-items:center;
  background:rgba(255,255,255,.06); border:1px solid var(--mh-stroke); color:var(--mh-text);
}
.mh-ico svg{ width:16px; height:16px }

/* Backdrop + Off-canvas (mobile) */
.mh-backdrop{
  position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:59;
  opacity:0; transition:opacity .2s ease;
}
.mh-backdrop.show{ opacity:1 }

@media (max-width: 980px){
  .mh-topbar{ display:flex; }
  .mh-sidebar{
    position:fixed; left:0; top:0; bottom:0; height:100dvh;
    transform: translateX(-100%); transition: transform .25s ease, box-shadow .25s ease;
    box-shadow: 0 20px 40px rgba(0,0,0,.35);
  }
  .mh-sidebar.open{ transform: translateX(0); }
}

/* Micro interações */
.mh-item:active{ transform:translateY(0) scale(.995); }
</style>

<script>
(function(){
  const sidebar  = document.getElementById('mhSidebar');
  const burger   = document.getElementById('mhBurger');
  const backdrop = document.getElementById('mhBackdrop');

  function openMenu(){
    sidebar.classList.add('open');
    backdrop.hidden = false;
    setTimeout(()=>backdrop.classList.add('show'), 10);
    burger.setAttribute('aria-expanded','true');
    sidebar.setAttribute('aria-hidden','false');
  }
  function closeMenu(){
    sidebar.classList.remove('open');
    backdrop.classList.remove('show');
    burger.setAttribute('aria-expanded','false');
    sidebar.setAttribute('aria-hidden','true');
    setTimeout(()=>backdrop.hidden = true, 200);
  }

  burger && burger.addEventListener('click', ()=>{
    if (sidebar.classList.contains('open')) closeMenu(); else openMenu();
  });
  backdrop && backdrop.addEventListener('click', closeMenu);

  // Fecha ao clicar em item (só no mobile)
  sidebar.addEventListener('click', e=>{
    if (window.matchMedia('(max-width: 980px)').matches && e.target.closest('.mh-item')) closeMenu();
  });

  // Fecha ao redimensionar para desktop
  window.addEventListener('resize', ()=>{
    if (!window.matchMedia('(max-width: 980px)').matches) {
      backdrop.hidden = true; backdrop.classList.remove('show');
      sidebar.classList.remove('open'); sidebar.setAttribute('aria-hidden','false');
      burger && burger.setAttribute('aria-expanded','false');
    }
  });
})();
</script>
