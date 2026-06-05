<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('etudiant');
$user=currentUser();
$stmt=$pdo->prepare("SELECT * FROM suivi_etapes WHERE etudiant_id=? ORDER BY id"); $stmt->execute([$user['id']]); $db=$stmt->fetchAll();
if (empty($db)) {
    $etapes=[
        ['etape'=>'Recherche de stage','statut'=>'fait','date_etape'=>'2026-03-15'],
        ['etape'=>'Convention signée','statut'=>'fait','date_etape'=>'2026-04-22'],
        ['etape'=>'Début du stage','statut'=>'fait','date_etape'=>'2026-06-01'],
        ['etape'=>'Rapport intermédiaire','statut'=>'en_cours','date_etape'=>'2026-08-15'],
        ['etape'=>'Rapport final','statut'=>'en_attente','date_etape'=>'2026-11-15'],
        ['etape'=>'Soutenance orale','statut'=>'en_attente','date_etape'=>'2026-12-15'],
    ];
} else {
    $lbl=['recherche'=>'Recherche de stage','convention_signee'=>'Convention signée','debut_stage'=>'Début du stage','rapport_intermediaire'=>'Rapport intermédiaire','rapport_final'=>'Rapport final','soutenance'=>'Soutenance orale'];
    $etapes=array_map(fn($e)=>['etape'=>$lbl[$e['etape']]??$e['etape'],'statut'=>$e['statut'],'date_etape'=>$e['date_etape']],$db);
}
$taches=[
    ['titre'=>'Soumettre le rapport intermédiaire','echeance'=>'15 août 2026','urgente'=>true],
    ['titre'=>'Préparer la présentation orale','echeance'=>'10 décembre 2026','urgente'=>false],
];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Suivi de stage — Étudiant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('etudiant','suivi');?>
<main class="main-content">
  <h1 class="page-title">Suivi de stage</h1>
  <div class="convention-section"><h2>Progression du stage</h2>
    <div class="timeline">
      <?php foreach($etapes as $e):?>
        <div class="timeline-item">
          <div class="timeline-dot <?=$e['statut']==='fait'?'done':($e['statut']==='en_cours'?'current':'pending')?>"><?=$e['statut']==='fait'?'✓':''?></div>
          <div class="timeline-body">
            <div class="timeline-title"><?=h($e['etape'])?></div>
            <div class="timeline-date"><?=date('d/m/Y',strtotime($e['date_etape']))?></div>
          </div></div>
      <?php endforeach;?>
    </div></div>
  <div class="convention-section"><h2>Tâches à réaliser</h2>
    <?php foreach($taches as $t):?>
      <div class="task-item <?=$t['urgente']?'urgent':'normal'?>">
        <div class="task-title"><?=h($t['titre'])?></div>
        <div class="task-due <?=$t['urgente']?'urgent-text':'normal-text'?>">Échéance : <?=h($t['echeance'])?></div>
      </div>
    <?php endforeach;?>
  </div>
</main></div></body></html>
