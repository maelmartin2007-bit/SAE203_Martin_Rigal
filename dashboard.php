<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('etudiant');
$user=currentUser();
$stmt=$pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user['id']]); $notifications=$stmt->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tableau de bord — Étudiant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('etudiant','tableau-de-bord');?>
<main class="main-content">
  <h1 class="page-title">Tableau de bord</h1>
  <div class="cards-grid">
    <a href="<?=BASE?>/pages/etudiant/offres.php" class="dash-card accent-blue">
      <div class="dash-card-header"><span class="dash-card-icon">💼</span><span class="dash-card-title">Offres de stage</span></div>
      <p class="dash-card-desc">Consultez et postulez aux offres de stage disponibles</p></a>
    <a href="<?=BASE?>/pages/etudiant/convention.php" class="dash-card accent-pink">
      <div class="dash-card-header"><span class="dash-card-icon">📄</span><span class="dash-card-title">Convention</span></div>
      <p class="dash-card-desc">Gérez votre convention de stage</p></a>
    <a href="<?=BASE?>/pages/etudiant/oral.php" class="dash-card accent-blue">
      <div class="dash-card-header"><span class="dash-card-icon">🎤</span><span class="dash-card-title">Oral</span></div>
      <p class="dash-card-desc">Informations sur la soutenance orale</p></a>
    <a href="<?=BASE?>/pages/etudiant/suivi.php" class="dash-card accent-pink">
      <div class="dash-card-header"><span class="dash-card-icon">✅</span><span class="dash-card-title">Suivi de stage</span></div>
      <p class="dash-card-desc">Suivez l'avancement de votre stage</p></a>
  </div>
  <div class="notif-box"><h2>Notifications récentes</h2>
    <?php if(empty($notifications)):?>
      <div class="notif-item unread"><span class="notif-dot blue"></span><div>
        <div class="notif-text">Nouvelle offre de stage disponible</div><div class="notif-time">Il y a 2 heures</div></div></div>
      <div class="notif-item"><span class="notif-dot gray"></span><div>
        <div class="notif-text">Convention approuvée par l'enseignant</div><div class="notif-time">Hier</div></div></div>
    <?php else: foreach($notifications as $n):?>
      <div class="notif-item <?=!$n['lue']?'unread':''?>"><span class="notif-dot <?=!$n['lue']?'blue':'gray'?>"></span><div>
        <div class="notif-text"><?=h($n['message'])?></div>
        <div class="notif-time"><?=date('d/m/Y H:i',strtotime($n['created_at']))?></div></div></div>
    <?php endforeach; endif;?>
  </div>
</main></div></body></html>
