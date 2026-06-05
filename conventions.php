<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('enseignant');
$message=''; $action=$_GET['action']??''; $id=(int)($_GET['id']??0);
if ($action&&$id&&in_array($action,['approuver','refuser'])) {
    $statut=$action==='approuver'?'approuvee':'refusee';
    $pdo->prepare("UPDATE conventions SET statut=? WHERE id=?")->execute([$statut,$id]);
    $message=$action==='approuver'?'Convention approuvée.':'Convention refusée.';
}
$convs=$pdo->query("SELECT c.*,u.nom_complet AS etudiant_nom FROM conventions c JOIN utilisateurs u ON u.id=c.etudiant_id ORDER BY c.created_at DESC")->fetchAll();
if (empty($convs)) $convs=[
    ['id'=>1,'etudiant_nom'=>'Jean Dupont',   'entreprise'=>'TechCorp',      'date_debut'=>'2026-06-01','date_fin'=>'2026-11-30','statut'=>'en_attente','fichier_pdf'=>null],
    ['id'=>2,'etudiant_nom'=>'Marie Martin',  'entreprise'=>'DataInsight',   'date_debut'=>'2026-06-15','date_fin'=>'2026-10-15','statut'=>'en_attente','fichier_pdf'=>null],
    ['id'=>3,'etudiant_nom'=>'Pierre Leblanc','entreprise'=>'CloudSolutions','date_debut'=>'2026-07-01','date_fin'=>'2026-12-31','statut'=>'approuvee', 'fichier_pdf'=>null],
];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Conventions — Enseignant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('enseignant','conventions');?>
<main class="main-content">
  <h1 class="page-title">Gestion des conventions</h1>
  <?php if($message):?><div class="alert alert-success"><?=h($message)?></div><?php endif;?>
  <div style="background:white;border-radius:12px;border:1px solid var(--gray-border);overflow:hidden">
    <table class="data-table"><thead><tr>
      <th>Étudiant</th><th>Entreprise</th><th>Période</th><th>Statut</th><th>Actions</th>
    </tr></thead><tbody>
    <?php foreach($convs as $c):?>
      <tr>
        <td><?=h($c['etudiant_nom'])?></td>
        <td><?=h($c['entreprise'])?></td>
        <td><?=date('d/m/Y',strtotime($c['date_debut']))?> – <?=date('d/m/Y',strtotime($c['date_fin']))?></td>
        <td>
          <?php if($c['statut']==='approuvee'):?><span class="badge badge-approuve">Approuvée</span>
          <?php elseif($c['statut']==='refusee'):?><span class="badge" style="background:#fee2e2;color:#b91c1c">Refusée</span>
          <?php else:?><span class="badge badge-attente">En attente</span><?php endif;?>
        </td>
        <td><div class="action-icons">
          <?php if($c['fichier_pdf']):?>
            <a href="<?=BASE?>/uploads/conventions/<?=h($c['fichier_pdf'])?>" class="icon-btn view" target="_blank">👁</a>
          <?php else:?><button class="icon-btn view" disabled>👁</button><?php endif;?>
          <?php if($c['statut']==='en_attente'):?>
            <a href="?action=approuver&id=<?=$c['id']?>" class="icon-btn ok" onclick="return confirm('Approuver ?')">✔</a>
            <a href="?action=refuser&id=<?=$c['id']?>" class="icon-btn del" onclick="return confirm('Refuser ?')">✖</a>
          <?php endif;?>
        </div></td>
      </tr>
    <?php endforeach;?>
    </tbody></table>
  </div>
</main></div></body></html>
