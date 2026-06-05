<?php
// =============================================
// includes/auth.php
// =============================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

function requireAuth() {
    if (empty($_SESSION['user_id'])) { header('Location: ' . url('login.php')); exit; }
}
function requireRole(string $role) {
    requireAuth();
    if ($_SESSION['role'] !== $role) { header('Location: ' . url('login.php')); exit; }
}
function currentUser(): array {
    return ['id'=>$_SESSION['user_id']??null,'nom'=>$_SESSION['nom']??'','role'=>$_SESSION['role']??''];
}
function loginUser(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom']     = $user['nom_complet'];
    $_SESSION['role']    = $user['role'];
}
function logoutUser(): void {
    session_destroy();
    header('Location: ' . url('login.php')); exit;
}
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
