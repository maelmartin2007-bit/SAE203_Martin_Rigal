<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role']==='enseignant' ? url('pages/enseignant/dashboard.php') : url('pages/etudiant/dashboard.php')));
} else {
    header('Location: ' . url('login.php'));
}
exit;
