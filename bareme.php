<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('enseignant');
$rows=$pdo->query("SELECT * FROM bareme ORDER BY id")->fetchAll();
$categories=[];
foreach($rows as $r) { $categories[$r['categorie']]['pct']=$r['pourcentage']; $categories[$r['categorie']]['criteres'][]=['critere'=>$r['critere'],'points'=>$r['points']]; }
if (empty($categories)) $categories=[
    'Rapport écrit'=>['pct'=>40,'criteres'=>[['critere'=>'Qualité rédactionnelle','points'=>10],['critere'=>'Analyse technique','points'=>15],['critere'=>'Présentation générale','points'=>10],['critere'=>'Bibliographie','points'=>5]]],
    'Soutenance orale'=>['pct'=>40,'criteres'=>[['critere'=>'Qualité de la présentation','points'=>15],['critere'=>'Maîtrise du sujet','points'=>15],['critere'=>'Réponses aux questions','points'=>10]]],
    'Évaluation entreprise'=>['pct'=>20,'criteres'=>[['critere'=>'Travail réalisé','points'=>10],['critere'=>'Comportement professionnel','points'=>10]]],
];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Barème — Enseignant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('enseignant','bareme');?>
<main class="main-content">
  <h1 class="page-title">Barème de notation</h1>
  <div class="convention-section"><h2>Critères d'évaluation</h2>
    <p style="font-size:13px;color:var(--gray-text);margin-bottom:16px">Le stage est évalué sur 20 points, répartis selon les critères suivants :</p>
    <?php foreach($categories as $cat=>$data):?>
      <div class="bareme-category">
        <div class="bareme-header"><span><?=h($cat)?></span><span><?=$data['pct']?>%</span></div>
        <?php foreach($data['criteres'] as $c):?>
          <div class="bareme-row"><span><?=h($c['critere'])?></span><span class="bareme-pts"><?=$c['points']?> pts</span></div>
        <?php endforeach;?>
      </div>
    <?php endforeach;?>
  </div>
  <div class="convention-section"><h2>Échelle de notation</h2>
    <table class="data-table" style="margin-top:4px"><thead><tr><th>Mention</th><th>Note /20</th><th>Appréciation</th></tr></thead>
    <tbody>
      <tr><td>Très bien</td><td>16 – 20</td><td style="color:var(--green);font-weight:600">Excellent travail</td></tr>
      <tr><td>Bien</td><td>14 – 15</td><td>Bonne maîtrise du sujet</td></tr>
      <tr><td>Assez bien</td><td>12 – 13</td><td>Travail satisfaisant</td></tr>
      <tr><td>Passable</td><td>10 – 11</td><td>Résultats acceptables</td></tr>
      <tr><td>Insuffisant</td><td>< 10</td><td style="color:var(--red)">Ne valide pas le stage</td></tr>
    </tbody></table>
  </div>
</main></div></body></html>
