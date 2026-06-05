<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('enseignant');
$user=currentUser(); $message='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='noter'&&isset($_POST['soutenance_id'],$_POST['note'])) {
        $note=min(20,max(0,(float)str_replace(',','.',$_POST['note'])));
        $pdo->prepare("UPDATE soutenances SET note=? WHERE id=?")->execute([$note,(int)$_POST['soutenance_id']]);
        $message='Note enregistrée.';
    } elseif ($action==='planifier') {
        $eid=(int)($_POST['etudiant_id']??0);
        if ($eid) {
            $pdo->prepare("INSERT INTO soutenances (etudiant_id,date_soutenance,heure_debut,heure_fin,salle) VALUES (?,?,?,?,?)")
                ->execute([$eid,$_POST['date_soutenance']??'',$_POST['heure_debut']??'',$_POST['heure_fin']??'',$_POST['salle']??'']);
            $message='Soutenance planifiée.';
        }
    }
}
$soutenances=$pdo->query("SELECT s.*,u.nom_complet FROM soutenances s JOIN utilisateurs u ON u.id=s.etudiant_id ORDER BY s.date_soutenance,s.heure_debut")->fetchAll();
if (empty($soutenances)) $soutenances=[
    ['id'=>1,'nom_complet'=>'Jean Dupont',   'date_soutenance'=>'2026-12-15','heure_debut'=>'14:00','salle'=>'B204','note'=>null],
    ['id'=>2,'nom_complet'=>'Marie Martin',  'date_soutenance'=>'2026-12-15','heure_debut'=>'15:00','salle'=>'B204','note'=>null],
    ['id'=>3,'nom_complet'=>'Pierre Leblanc','date_soutenance'=>'2026-12-10','heure_debut'=>'10:00','salle'=>'A301','note'=>16.00],
];
$etudiants=$pdo->query("SELECT id,nom_complet FROM utilisateurs WHERE role='etudiant' ORDER BY nom_complet")->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Oraux — Enseignant</title><link rel="stylesheet" href="../../css/style.css">
<style>
.plan-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center;}
.plan-modal.open{display:flex;}
.plan-box{background:white;border-radius:14px;padding:28px;width:100%;max-width:480px;}
.plan-box h2{font-size:17px;font-weight:700;margin-bottom:18px;}
.plan-group{margin-bottom:12px;}
.plan-group label{display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:var(--gray-text);}
.plan-group input,.plan-group select{width:100%;padding:8px 12px;border:1px solid var(--gray-border);border-radius:8px;font-size:13px;}
.plan-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px;}
.btn-cancel{padding:9px 18px;border:1px solid var(--gray-border);border-radius:8px;background:white;cursor:pointer;}
</style></head>
<body><div class="app-layout">
<?php sidebar('enseignant','oraux');?>
<main class="main-content">
  <h1 class="page-title">Gestion des soutenances orales</h1>
  <?php if($message):?><div class="alert alert-success"><?=h($message)?></div><?php endif;?>
  <button class="btn-add" onclick="document.getElementById('planModal').classList.add('open')">+ Planifier une soutenance</button>
  <?php foreach($soutenances as $s):?>
    <div class="oral-card">
      <div class="oral-card-name"><?=h($s['nom_complet'])?></div>
      <div class="oral-card-meta">
        <span>📅 <?=date('d/m/Y',strtotime($s['date_soutenance']))?></span>
        <span>🕐 <?=substr($s['heure_debut'],0,5)?></span>
        <span>📍 Salle : <?=h($s['salle'])?></span>
      </div>
      <?php if($s['note']!==null):?>
        <div class="note-result">Note : <?=number_format($s['note'],0)?>/20</div>
      <?php else:?>
        <form method="POST" class="note-row">
          <input type="hidden" name="action" value="noter">
          <input type="hidden" name="soutenance_id" value="<?=$s['id']?>">
          <input type="number" name="note" class="note-input" placeholder="Note /20" min="0" max="20" step="0.5">
          <button type="submit" class="btn-valider">Valider la note</button>
        </form>
      <?php endif;?>
    </div>
  <?php endforeach;?>
</main></div>
<div class="plan-modal" id="planModal">
  <div class="plan-box">
    <h2>Planifier une soutenance</h2>
    <form method="POST"><input type="hidden" name="action" value="planifier">
      <div class="plan-group"><label>Étudiant</label>
        <select name="etudiant_id">
          <?php foreach($etudiants as $e):?><option value="<?=$e['id']?>"><?=h($e['nom_complet'])?></option><?php endforeach;?>
        </select></div>
      <div class="plan-group"><label>Date</label><input type="date" name="date_soutenance"></div>
      <div class="plan-group"><label>Heure de début</label><input type="time" name="heure_debut"></div>
      <div class="plan-group"><label>Heure de fin</label><input type="time" name="heure_fin"></div>
      <div class="plan-group"><label>Salle</label><input type="text" name="salle" placeholder="ex: B204"></div>
      <div class="plan-actions">
        <button type="button" class="btn-cancel" onclick="document.getElementById('planModal').classList.remove('open')">Annuler</button>
        <button type="submit" class="btn-primary" style="width:auto;padding:9px 22px">Planifier</button>
      </div>
    </form>
  </div>
</div>
</body></html>
