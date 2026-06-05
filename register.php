<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
if (!empty($_SESSION['user_id'])) { header('Location:'.url('login.php')); exit; }
$error=$success=''; $role=$_POST['role']??'etudiant';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nom=trim($_POST['nom_complet']??''); $email=trim($_POST['email']??'');
    $mdp=$_POST['mot_de_passe']??''; $mdp2=$_POST['confirmer_mdp']??'';
    $role=in_array($_POST['role']??'',['etudiant','enseignant'])?$_POST['role']:'etudiant';
    if (!$nom||!$email||!$mdp||!$mdp2) { $error='Veuillez remplir tous les champs.'; }
    elseif ($mdp!==$mdp2) { $error='Les mots de passe ne correspondent pas.'; }
    elseif (strlen($mdp)<8) { $error='Le mot de passe doit faire au moins 8 caractères.'; }
    else {
        $stmt=$pdo->prepare("SELECT id FROM utilisateurs WHERE email=?"); $stmt->execute([$email]);
        if ($stmt->fetch()) { $error='Cet email est déjà utilisé.'; }
        else {
            $pdo->prepare("INSERT INTO utilisateurs (nom_complet,email,mot_de_passe,role) VALUES (?,?,?,?)")
                ->execute([$nom,$email,password_hash($mdp,PASSWORD_BCRYPT),$role]);
            $success='Inscription réussie ! <a href="'.url('login.php').'">Se connecter</a>';
        }
    }
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inscription — Stages</title><link rel="stylesheet" href="css/style.css"></head>
<body><div class="auth-page"><div class="auth-card">
  <div class="auth-header"><div class="auth-logo">✦</div><div class="auth-icon-circle">🎓</div></div>
  <h1>Inscription</h1><p class="subtitle">Créez votre compte pour accéder à la plateforme</p>
  <?php if($error):?><div class="alert alert-error"><?=h($error)?></div><?php endif;?>
  <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
  <form method="POST">
    <p class="role-label">Je suis :</p>
    <div class="role-selector">
      <button type="button" class="role-btn <?=$role==='etudiant'?'active-etudiant':''?>" onclick="setRole('etudiant')">
        <span class="role-icon">👤</span> Étudiant</button>
      <button type="button" class="role-btn <?=$role==='enseignant'?'active-enseignant':''?>" onclick="setRole('enseignant')">
        <span class="role-icon">🎓</span> Enseignant</button>
    </div>
    <input type="hidden" id="role" name="role" value="<?=h($role)?>">
    <div class="form-group"><label>Nom complet</label><div class="input-wrap"><span class="input-icon">👤</span>
      <input type="text" name="nom_complet" placeholder="Jean Dupont" value="<?=h($_POST['nom_complet']??'')?>" required></div></div>
    <div class="form-group"><label>Email</label><div class="input-wrap"><span class="input-icon">✉</span>
      <input type="email" name="email" placeholder="exemple@email.com" value="<?=h($_POST['email']??'')?>" required></div></div>
    <div class="form-group"><label>Mot de passe</label><div class="input-wrap"><span class="input-icon">🔒</span>
      <input type="password" name="mot_de_passe" placeholder="••••••••" required></div></div>
    <div class="form-group"><label>Confirmer le mot de passe</label><div class="input-wrap"><span class="input-icon">🔒</span>
      <input type="password" name="confirmer_mdp" placeholder="••••••••" required></div></div>
    <button type="submit" class="btn-primary">S'inscrire</button>
  </form>
  <p class="auth-footer">Vous avez déjà un compte ? <a href="<?=url('login.php')?>">Se connecter</a></p>
</div></div>
<script>
function setRole(r){document.getElementById('role').value=r;
  document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active-etudiant','active-enseignant'));
  document.querySelectorAll('.role-btn')[r==='etudiant'?0:1].classList.add('active-'+r);}
</script></body></html>
