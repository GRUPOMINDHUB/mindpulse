<?php
// Mindhub RH — Config (BASE_URL auto-detect para subpastas: ex. /mindhub)
if (!defined('APP_BOOTSTRAPPED')) {
  define('APP_BOOTSTRAPPED', true);

  $doc = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
  $appDir = str_replace('\\','/', realpath(__DIR__ . '/..'));
  $baseUrl = rtrim(str_replace($doc, '', $appDir), '/');
  if ($baseUrl === '') $baseUrl = ''; // root
  define('BASE_URL', $baseUrl);

  // Expor no JS global
  echo "<script>window.BASE_URL='".htmlspecialchars(BASE_URL, ENT_QUOTES)."';</script>";
}

define('DB_HOST', 'rhtrain.mysql.uhserver.com');
define('DB_NAME', 'rhtrain');
define('DB_USER', 'rhtrain');
define('DB_PASS', 'TPMBS3cr3t@');
define('APP_NAME', 'Mindhub');
define('APP_BRAND_COLOR', '#ff6a00');
