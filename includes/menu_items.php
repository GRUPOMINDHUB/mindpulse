<?php // só a lista, sem wrappers — usada no sidebar (desktop) e no painel mobile ?>
<nav class="mh-nav">
  <div class="mh-section">
    <h6 class="mh-sec-title">Colaborador</h6>

    <a class="mh-item" href="<?= url_for('/pages/home.php') ?>">
  <span class="mh-ico">
    <!-- ícone home -->
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M12 3l9 8h-2v9h-6v-6H11v6H5v-9H3l9-8z"/>
    </svg>
  </span>
  <span class="label">Início</span>
</a>


    <a class="mh-item" href="<?= url_for('/pages/treinamentos.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
      <span class="label">Treinamentos</span>
    </a>
<a class="mh-item" href="<?= url_for('/pages/checklists.php') ?>">
  <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 11l3 3L22 4l-2-2-8 8-3-3-9 9 2 2 8-8z"/></svg></span>
  <span class="label">Checklists</span>
</a>

<a class="mh-item" href="<?= url_for('/pages/feedback.php') ?>">
  <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 15a4 4 0 0 1-4 4H8l-5 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z"/></svg></span>
  <span class="label">Feedback</span>
</a>

  </div>

  <?php if (canAccessAdmin()): ?>

  <div class="mh-section">
    <h6 class="mh-sec-title">Admin</h6>
    <a class="mh-item" href="<?= url_for('/pages/empresas.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 21V3h8v4h10v14H3Zm10-2h6V9h-6v10ZM5 19h6V5H5v14Z"/></svg></span>
      <span class="label">Empresas</span>
    </a>

    <a class="mh-item" href="<?= url_for('/pages/admin_dashboard.php') ?>">
      <span class="mh-ico">
        <!-- ícone dashboard -->
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/>
        </svg>
      </span>
      <span class="label">Dashboard</span>
    </a>


<a class="mh-item" href="<?= url_for('/pages/chamados.php') ?>">
  <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16v2H4zm0 6h16v2H4zm0 6h10v2H4z"/></svg></span>
  <span class="label">Chamados Internos</span>
</a>

    <a class="mh-item" href="<?= url_for('/pages/colaboradores.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg></span>
      <span class="label">Colaboradores</span>
    </a>
    <a class="mh-item" href="<?= url_for('/pages/training_new.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
      <span class="label">Novo Treinamento</span>
    </a>
    <a class="mh-item" href="<?= url_for('/pages/collaborator_new.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 8a4 4 0 1 1-4-4 4 4 0 0 1 4 4ZM4 20v-1c0-3.31 2.69-6 6-6h0c3.31 0 6 2.69 6 6v1H4Z"/></svg></span>
      <span class="label">Novo Colaborador</span>
    </a>
        <a class="mh-item" href="<?= url_for('/pages/checklist_new.php') ?>">
      <span class="mh-ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 8a4 4 0 1 1-4-4 4 4 0 0 1 4 4ZM4 20v-1c0-3.31 2.69-6 6-6h0c3.31 0 6 2.69 6 6v1H4Z"/></svg></span>
      <span class="label">Novo Checklist</span>
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
