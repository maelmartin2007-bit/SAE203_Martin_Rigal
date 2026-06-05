<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: '.($_SESSION['role']==='enseignant'?url('pages/enseignant/dashboard.php'):url('pages/etudiant/dashboard.php'))); exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??''); $mdp = $_POST['mot_de_passe']??'';
    if ($email && $mdp) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email=?"); $stmt->execute([$email]); $user=$stmt->fetch();
        if ($user && password_verify($mdp,$user['mot_de_passe'])) {
            loginUser($user);
            header('Location: '.($user['role']==='enseignant'?url('pages/enseignant/dashboards.php'):url('pages/etudiant/dashboard.php'))); exit;
        } else { $error='Email ou mot de passe incorrect.'; }
    } else { $error='Veuillez remplir tous les champs.'; }
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion — Stages</title><link rel="stylesheet" href="css/style.css"></head>
<body><div class="auth-page"><div class="auth-card">
  <div class="auth-header"><div class="auth-logo">✦</div><div class="auth-icon-circle">🎓</div></div>
  <h1>Connexion</h1><p class="subtitle">Accédez à votre compte</p>
  <?php if($error):?><div class="alert alert-error"><?=h($error)?></div><?php endif;?>
  <form method="POST">
    <div class="form-group"><label>Email</label><div class="input-wrap"><span class="input-icon">✉</span>
      <input type="email" name="email" placeholder="exemple@email.com" value="<?=h($_POST['email']??'')?>" required></div></div>
    <div class="form-group"><label>Mot de passe</label><div class="input-wrap"><span class="input-icon">🔒</span>
      <input type="password" name="mot_de_passe" placeholder="••••••••" required></div></div>
    <button type="submit" class="btn-primary">Se connecter</button>
  </form>
  <p class="auth-footer">Pas encore de compte ? <a href="<?=url('register.php')?>">S'inscrire</a></p>
</div></div></body></html>
