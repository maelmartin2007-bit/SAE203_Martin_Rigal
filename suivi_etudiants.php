<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('enseignant');
$search=trim($_GET['q']??'');
$sql="SELECT u.id,u.nom_complet,
  (SELECT c.entreprise FROM conventions c WHERE c.etudiant_id=u.id LIMIT 1) AS entreprise,
  (SELECT c.statut FROM conventions c WHERE c.etudiant_id=u.id LIMIT 1) AS conv_statut,
  (SELECT se.statut FROM suivi_etapes se WHERE se.etudiant_id=u.id AND se.etape='rapport_intermediaire' LIMIT 1) AS ri_statut,
  (SELECT se.statut FROM suivi_etapes se WHERE se.etudiant_id=u.id AND se.etape='rapport_final' LIMIT 1) AS rf_statut,
  (SELECT so.date_soutenance FROM soutenances so WHERE so.etudiant_id=u.id LIMIT 1) AS sout_date
FROM utilisateurs u WHERE u.role='etudiant'";
if ($search) { $sql.=" AND u.nom_complet LIKE ?"; $stmt=$pdo->prepare($sql." ORDER BY u.nom_complet"); $stmt->execute(["%$search%"]); }
else { $stmt=$pdo->prepare($sql." ORDER BY u.nom_complet"); $stmt->execute(); }
$etudiants=$stmt->fetchAll();
if (empty($etudiants)) $etudiants=[
    ['id'=>1,'nom_complet'=>'Jean Dupont',   'entreprise'=>'TechCorp',      'conv_statut'=>'approuvee','ri_statut'=>'fait',      'rf_statut'=>'en_attente','sout_date'=>'2026-12-15'],
    ['id'=>2,'nom_complet'=>'Marie Martin',  'entreprise'=>'DataInsight',   'conv_statut'=>'approuvee','ri_statut'=>'fait',      'rf_statut'=>'en_attente','sout_date'=>'2026-12-15'],
    ['id'=>3,'nom_complet'=>'Pierre Leblanc','entreprise'=>'CloudSolutions','conv_statut'=>'en_attente','ri_statut'=>'en_attente','rf_statut'=>'en_attente','sout_date'=>null],
];
function statusCell($statut,$date=null){
    if ($statut==='approuvee'||$statut==='fait') return '<span class="cell-status ok">✔ '.($statut==='approuvee'?'Approuvée':'Soumis').'</span>';
    if ($date) return '<span class="cell-status ok">✔ Planifiée</span>';
    if ($statut==='en_cours') return '<span class="cell-status wait">⏳ En cours</span>';
    return '<span class="cell-status clock">⏱ En attente</span>';
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Suivi étudiants — Enseignant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('enseignant','suivi-etudiants');?>
<main class="main-content">
  <h1 class="page-title">Suivi des étudiants</h1>
  <form method="GET" class="search-bar">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" placeholder="Rechercher un étudiant..." value="<?=h($search)?>">
  </form>
  <div style="background:white;border-radius:12px;border:1px solid var(--gray-border);overflow:hidden">
    <table class="data-table"><thead><tr>
      <th>Étudiant</th><th>Entreprise</th><th>Convention</th><th>Rapport int.</th><th>Rapport final</th><th>Soutenance</th>
    </tr></thead><tbody>
    <?php foreach($etudiants as $e):?>
      <tr>
        <td style="font-weight:600"><?=h($e['nom_complet'])?></td>
        <td><?=h($e['entreprise']??'—')?></td>
        <td><?=statusCell($e['conv_statut'])?></td>
        <td><?=statusCell($e['ri_statut'])?></td>
        <td><?=statusCell($e['rf_statut'])?></td>
        <td><?=statusCell(null,$e['sout_date'])?></td>
      </tr>
    <?php endforeach;?>
    <?php if(empty($etudiants)):?>
      <tr><td colspan="6" style="text-align:center;color:var(--gray-text);padding:30px">Aucun étudiant trouvé.</td></tr>
    <?php endif;?>
    </tbody></table>
  </div>
</main></div></body></html>
