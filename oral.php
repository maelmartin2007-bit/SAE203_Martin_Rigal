<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('etudiant');
$user=currentUser();
$stmt=$pdo->prepare("SELECT * FROM soutenances WHERE etudiant_id=? LIMIT 1"); $stmt->execute([$user['id']]); $s=$stmt->fetch();
if (!$s) $s=['date_soutenance'=>'2026-12-15','heure_debut'=>'14:00:00','heure_fin'=>'14:30:00','salle'=>'Salle B204',
    'jury'=>json_encode([['initiales'=>'PM','nom'=>'Prof. Martin','role'=>'Président du jury'],['initiales'=>'DR','nom'=>'Dr. Rousseau','role'=>'Examinateur']]),
    'consignes'=>json_encode(['Présentation PowerPoint de 15-20 minutes','Questions du jury : 10 minutes','Tenue professionnelle requise','Arriver 10 minutes en avance'])];
$jury=json_decode($s['jury'],true)??[]; $consignes=json_decode($s['consignes'],true)??[];
$mois=['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
$jours=['Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi'];
$d=new DateTime($s['date_soutenance']);
$date_label=$d->format('d').' '.$mois[$d->format('m')].' '.$d->format('Y');
$jour_label=$jours[$d->format('l')];
$debut=new DateTime($s['heure_debut']); $fin=new DateTime($s['heure_fin']);
$duree=$debut->diff($fin)->i+$debut->diff($fin)->h*60;
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Soutenance orale — Étudiant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('etudiant','oral');?>
<main class="main-content">
  <h1 class="page-title">Soutenance orale</h1>
  <div class="convention-section"><h2>Date et heure</h2>
    <div class="oral-item"><span class="oral-icon">📅</span><div>
      <div class="oral-main"><?=h($date_label)?></div><div class="oral-sub"><?=h($jour_label)?></div></div></div>
    <div class="oral-item"><span class="oral-icon">🕐</span><div>
      <div class="oral-main"><?=substr($s['heure_debut'],0,5)?> – <?=substr($s['heure_fin'],0,5)?></div>
      <div class="oral-sub">Durée : <?=$duree?> minutes</div></div></div>
    <div class="oral-item"><span class="oral-icon">📍</span><div>
      <div class="oral-main"><?=h($s['salle'])?></div><div class="oral-sub">Bâtiment principal</div></div></div>
  </div>
  <?php if(!empty($jury)):?>
  <div class="convention-section"><h2>Jury</h2>
    <?php foreach($jury as $m):?>
      <div class="jury-item"><div class="jury-avatar"><?=h($m['initiales'])?></div><div>
        <div class="jury-name"><?=h($m['nom'])?></div><div class="jury-role"><?=h($m['role'])?></div></div></div>
    <?php endforeach;?></div>
  <?php endif;?>
  <?php if(!empty($consignes)):?>
  <div class="convention-section"><h2>Consignes</h2>
    <ul class="consignes-list"><?php foreach($consignes as $c):?><li><?=h($c)?></li><?php endforeach;?></ul>
  </div><?php endif;?>
</main></div></body></html>
