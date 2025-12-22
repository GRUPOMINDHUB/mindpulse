<?php require_once __DIR__ . '/auth.php'; requireLogin();
require_once __DIR__ . '/db.php'; // <-- ADICIONE ESTA LINHA
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Mindhub — Painel</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url_for('/assets/css/styles.css') ?>"/>

<script>window.BASE_URL="<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>";</script>
<script src="<?= url_for('/assets/js/app.js') ?>" defer></script>

<style>
  :root{ --mh-header-h:64px; }

  /* Fundo + tipografia base */
  body{ background:#0f1117; color:#e8edf7; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif }

  /* 1) Sidebar é FIXED, então o shell não deve ter grid de 2 colunas */
  .app-shell{ min-height:100dvh; display:block !important; }

  /* 2) Área do conteúdo ocupa 100% da largura disponível */
  .mh-content{
    width:100%;
    padding:16px;
    padding-top:calc(var(--mh-header-h) + 12px); /* espaço do header fixo */
  }
  @media (min-width:981px){
    .mh-content{ padding-left:276px; } /* 260 sidebar + margem */
  }

  /* 3) Alguns temas antigos limitavam .content; aqui liberamos total */
  .content{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding:0 !important;
  }

  /* 4) Cards e formulários podem ocupar toda a faixa naturalmente */
  .card{ width:100%; }

  /* 5) Evita que algum CSS legado defina container estreito */
  [class*="container"], [class*="wrapper"]{
    max-width:none !important;
  }
</style>
</head>
<body>
<div class="app-shell">

  <?php include __DIR__ . '/header.php'; ?>
  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="mh-content">
    <div class="content">
