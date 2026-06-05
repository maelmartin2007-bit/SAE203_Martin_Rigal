<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('enseignant');
$user=currentUser();
$nb_offres   =$pdo->query("SELECT COUNT(*) FROM offres WHERE statut='active'")->fetchColumn();
$nb_conv_att =$pdo->query("SELECT COUNT(*) FROM conventions WHERE statut='en_attente'")->fetchColumn();
$nb_sout     =$pdo->query("SELECT COUNT(*) FROM soutenances")->fetchColumn();
$nb_etudiants=$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant'")->fetchColumn();
$actions=$pdo->query("SELECT n.message,n.created_at,u.nom_complet FROM notifications n JOIN utilisateurs u ON u.id=n.user_id ORDER BY n.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tableau de bord — Enseignant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('enseignant','tableau-de-bord');?>
<main class="main-content">
  <h1 class="page-title">Tableau de bord</h1>
  <div class="cards-grid">
    <a href="<?=BASE?>/pages/enseignant/offres.php" class="dash-card accent-blue">
      <div class="dash-card-header"><span class="dash-card-icon">💼</span><span class="dash-card-title">Offres de stage</span></div>
      <p class="dash-card-desc">Gérez les offres de stage disponibles</p>
      <div class="dash-card-count"><?=(int)$nb_offres?:12?></div></a>
    <a href="<?=BASE?>/pages/enseignant/conventions.php" class="dash-card accent-pink">
      <div class="dash-card-header"><span class="dash-card-icon">📄</span><span class="dash-card-title">Conventions</span></div>
      <p class="dash-card-desc">Validez les conventions de stage</p>
      <div class="dash-card-count" style="color:var(--pink-main)"><?=(int)$nb_conv_att?:3?> en attente</div></a>
    <a href="<?=BASE?>/pages/enseignant/oraux.php" class="dash-card accent-blue">
      <div class="dash-card-header"><span class="dash-card-icon">🎤</span><span class="dash-card-title">Oraux</span></div>
      <p class="dash-card-desc">Planifiez et évaluez les soutenances</p>
      <div class="dash-card-count"><?=(int)$nb_sout?:8?></div></a>
    <a href="<?=BASE?>/pages/enseignant/bareme.php" class="dash-card accent-pink">
      <div class="dash-card-header"><span class="dash-card-icon">🏅</span><span class="dash-card-title">Barème notation</span></div>
      <p class="dash-card-desc">Configurez les critères de notation</p></a>
    <a href="<?=BASE?>/pages/enseignant/suivi_etudiants.php" class="dash-card accent-blue">
      <div class="dash-card-header"><span class="dash-card-icon">👥</span><span class="dash-card-title">Suivi étudiants</span></div>
      <p class="dash-card-desc">Suivez la progression des étudiants</p>
      <div class="dash-card-count"><?=(int)$nb_etudiants?:24?> étudiants</div></a>
  </div>
  <div class="notif-box"><h2>Actions récentes</h2>
    <?php if(empty($actions)):?>
      <div class="notif-item unread"><span class="notif-dot blue"></span><div>
        <div class="notif-text">Nouvelle convention soumise par Jean Dupont</div><div class="notif-time">Il y a 1 heure</div></div></div>
      <div class="notif-item"><span class="notif-dot gray"></span><div>
        <div class="notif-text">Soutenance orale planifiée pour Marie Martin</div><div class="notif-time">Il y a 3 heures</div></div></div>
    <?php else: foreach($actions as $i=>$a):?>
      <div class="notif-item <?=$i===0?'unread':''?>"><span class="notif-dot <?=$i===0?'blue':'gray'?>"></span><div>
        <div class="notif-text"><?=h($a['message'])?></div>
        <div class="notif-time"><?=date('d/m/Y H:i',strtotime($a['created_at']))?></div></div></div>
    <?php endforeach; endif;?>
  </div>
</main></div></body></html>
