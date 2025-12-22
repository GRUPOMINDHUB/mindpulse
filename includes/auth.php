<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

function url_for(string $path){ return rtrim(BASE_URL,'/').$path; }

function requireLogin(){
  if (empty($_SESSION['user'])){ header('Location: '.url_for('/login.php')); exit; }
}
function isAdmin(){
  return isset($_SESSION['user']['type']) && $_SESSION['user']['type']==='Admin';
}
function canAccessAdmin(){ return isAdmin(); }
function currentCompanyId(){ return $_SESSION['current_company']['id'] ?? null; }
