<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/sidebar.php';
requireRole('etudiant');
$user=currentUser(); $message='';
$upload_dir=__DIR__.'/../../uploads/conventions/';
if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_FILES['doc_signe'])) {
    $file=$_FILES['doc_signe'];
    if ($file['error']===UPLOAD_ERR_OK) {
        $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
        if ($ext==='pdf'&&$file['size']<=10*1024*1024) {
            if (!is_dir($upload_dir)) mkdir($upload_dir,0755,true);
            $fn='signe_'.$user['id'].'_'.time().'.pdf';
            move_uploaded_file($file['tmp_name'],$upload_dir.$fn);
            $pdo->prepare("UPDATE conventions SET fichier_signe=? WHERE etudiant_id=?")->execute([$fn,$user['id']]);
            $message='Document signé uploadé avec succès.';
        } else { $message='Fichier invalide. PDF requis, max 10 MB.'; }
    }
}
$stmt=$pdo->prepare("SELECT * FROM conventions WHERE etudiant_id=? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user['id']]); $conv=$stmt->fetch();
$demo=!$conv;
if ($demo) $conv=['entreprise'=>'TechCorp','lieu'=>'Paris','date_debut'=>'2026-06-01','date_fin'=>'2026-11-30','statut'=>'en_attente','fichier_pdf'=>'convention_demo.pdf','fichier_signe'=>null];
$sl=['en_attente'=>['label'=>'En attente de validation','dot'=>'orange'],'approuvee'=>['label'=>'Approuvée','dot'=>'green'],'refusee'=>['label'=>'Refusée','dot'=>'red']];
$s=$sl[$conv['statut']]??$sl['en_attente'];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Convention — Étudiant</title><link rel="stylesheet" href="../../css/style.css"></head>
<body><div class="app-layout">
<?php sidebar('etudiant','convention');?>
<main class="main-content">
  <h1 class="page-title">Convention de stage</h1>
  <?php if($message):?><div class="alert alert-success"><?=h($message)?></div><?php endif;?>
  <div class="convention-section"><h2>Statut de la convention</h2>
    <div class="statut-badge"><span class="statut-dot <?=$s['dot']?>"></span><?=h($s['label'])?></div>
    <?php if($conv['statut']==='en_attente'):?>
      <div class="alert alert-info" style="margin-top:14px">Votre convention a été soumise et est en cours de validation par l'enseignant responsable.</div>
    <?php endif;?></div>
  <div class="convention-section"><h2>Informations du stage</h2>
    <div class="info-grid">
      <div class="info-item"><label>Entreprise</label><span><?=h($conv['entreprise'])?></span></div>
      <div class="info-item"><label>Lieu</label><span><?=h($conv['lieu'])?></span></div>
      <div class="info-item"><label>Date de début</label><span><?=date('d/m/Y',strtotime($conv['date_debut']))?></span></div>
      <div class="info-item"><label>Date de fin</label><span><?=date('d/m/Y',strtotime($conv['date_fin']))?></span></div>
    </div></div>
  <div class="convention-section"><h2>Documents</h2>
    <?php if($conv['fichier_pdf']):?>
      <div class="doc-item">
        <div class="doc-item-left"><span>📄</span><span>Convention de stage.pdf</span></div>
        <?php if(!$demo):?><a href="<?=BASE?>/uploads/conventions/<?=h($conv['fichier_pdf'])?>" download class="btn-download">⬇</a><?php else:?><button class="btn-download">⬇</button><?php endif;?>
      </div>
    <?php endif;?>
    <?php if(!$demo):?>
      <form method="POST" enctype="multipart/form-data">
        <div class="upload-zone" onclick="document.getElementById('doc_signe').click()">
          <div class="upload-icon">⬆</div><div>Télécharger un document signé</div>
          <div style="font-size:11px;margin-top:4px">PDF, max 10MB</div>
          <input type="file" id="doc_signe" name="doc_signe" accept=".pdf" style="display:none" onchange="this.form.submit()">
        </div></form>
    <?php else:?>
      <div class="upload-zone"><div class="upload-icon">⬆</div><div>Télécharger un document signé</div><div style="font-size:11px;margin-top:4px">PDF, max 10MB</div></div>
    <?php endif;?>
  </div>
</main></div></body></html>
