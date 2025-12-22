<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) { header('Location: '.url_for('/login.php').'?e=1'); exit; }

$user = findUserByEmail($pdo, $email);
if (!$user) { header('Location: '.url_for('/login.php').'?e=1'); exit; }

if (!password_verify($password, $user['password_hash'])) {
  header('Location: '.url_for('/login.php').'?e=1'); exit;
}

$companies = getUserCompanies($pdo, (int)$user['id']);
$roles = getUserRoles($pdo, (int)$user['id']);

$_SESSION['user'] = [
  'id'=>(int)$user['id'],
  'name'=>$user['name'],
  'email'=>$user['email'],
  'type'=>$user['type'],
  'avatar_url'=>$user['avatar_url'] ?: url_for('/assets/img/avatar.svg')
];
$_SESSION['roles'] = $roles;
$_SESSION['companies'] = $companies;
$_SESSION['current_company'] = $companies[0] ?? null;

header('Location: '.url_for('/pages/home.php')); exit;
