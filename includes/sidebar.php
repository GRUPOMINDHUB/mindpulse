<?php require_once __DIR__ . '/auth.php'; ?>
<style>
:root{
  --mh-header-h:64px;
  --mh-stroke: rgba(255,255,255,.12);
}
/* Sidebar fixa only desktop */
.mh-sidebar{
  position:fixed; top:var(--mh-header-h); left:0; bottom:0; width:260px; z-index:500;
  background:linear-gradient(160deg,rgba(20,24,36,.95),rgba(20,24,36,.88));
  border-right:1px solid var(--mh-stroke);
  padding:12px 10px; overflow:auto;
}
/* Scrollbar elegante */
.mh-sidebar{ scrollbar-width:thin; scrollbar-color: rgba(255,255,255,.25) transparent; }
.mh-sidebar::-webkit-scrollbar{ width:8px }
.mh-sidebar::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.25); border-radius:10px }
.mh-sidebar::-webkit-scrollbar-track{ background:transparent }

@media (max-width:980px){ .mh-sidebar{ display:none } }

.mh-nav{ display:flex; flex-direction:column; gap:16px }
.mh-section{ display:flex; flex-direction:column; gap:6px }
.mh-sec-title{ color:#9aa4b2; letter-spacing:.08em; font-weight:800; text-transform:uppercase; font-size:.72rem; margin:6px 6px }
.mh-item{
  display:flex; align-items:center; gap:10px;
  padding:10px 10px; border-radius:12px; color:#e8edf7; text-decoration:none;
  border:1px solid transparent; transition:.18s ease; position:relative; overflow:hidden;
}
.mh-item:hover{ background:rgba(255,255,255,.06); border-color:var(--mh-stroke) }
.mh-ico{ width:28px;height:28px; display:grid; place-items:center; border-radius:10px; background:rgba(255,255,255,.06); border:1px solid var(--mh-stroke) }
.mh-ico svg{ width:16px;height:16px;color:#e8edf7 }
.mh-brand-side{display:flex; align-items:center; justify-content:center; padding:6px 0 10px}
.mh-brand-side img{height:48px}
</style>

<aside class="mh-sidebar" id="mhSidebar">
  <div class="mh-brand-side">
    <img src="<?= url_for('/assets/img/logo.png') ?>" alt="Mindhub">
  </div>
  <?php include __DIR__ . '/menu_items.php'; ?>
</aside>
