<?php
function sidebar(string $role, string $activePage): void {
    $user  = currentUser();
    $base  = BASE;
    $nav   = $role === 'enseignant' ? [
        ['id'=>'tableau-de-bord', 'icon'=>'⊞', 'label'=>'Tableau de bord',  'href'=>"$base/pages/enseignant/dashboard.php"],
        ['id'=>'offres',          'icon'=>'💼','label'=>'Offres de stage',   'href'=>"$base/pages/enseignant/offres.php"],
        ['id'=>'conventions',     'icon'=>'📄','label'=>'Conventions',        'href'=>"$base/pages/enseignant/conventions.php"],
        ['id'=>'oraux',           'icon'=>'🎤','label'=>'Oraux',              'href'=>"$base/pages/enseignant/oraux.php"],
        ['id'=>'bareme',          'icon'=>'🏅','label'=>'Barème notation',   'href'=>"$base/pages/enseignant/bareme.php"],
        ['id'=>'suivi-etudiants', 'icon'=>'👥','label'=>'Suivi étudiants',  'href'=>"$base/pages/enseignant/suivi_etudiants.php"],
    ] : [
        ['id'=>'tableau-de-bord', 'icon'=>'⊞', 'label'=>'Tableau de bord', 'href'=>"$base/pages/etudiant/dashboard.php"],
        ['id'=>'offres',          'icon'=>'💼','label'=>'Offres de stage',  'href'=>"$base/pages/etudiant/offres.php"],
        ['id'=>'convention',      'icon'=>'📄','label'=>'Convention',        'href'=>"$base/pages/etudiant/convention.php"],
        ['id'=>'oral',            'icon'=>'🎤','label'=>'Oral',              'href'=>"$base/pages/etudiant/oral.php"],
        ['id'=>'suivi',           'icon'=>'✅','label'=>'Suivi de stage',   'href'=>"$base/pages/etudiant/suivi.php"],
    ];
    $roleLabel = $role === 'enseignant' ? 'Enseignant' : 'Étudiant';
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🎓</div>
    <div>
      <div class="brand-name">Stages</div>
      <div class="brand-role"><?= h($roleLabel) ?></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <?php foreach ($nav as $item): ?>
      <a href="<?= $item['href'] ?>" class="nav-item <?= $activePage===$item['id']?'active':'' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <?= h($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">Connecté en tant que</div>
    <div class="sidebar-username"><?= h($user['nom']) ?></div>
    <a href="<?= $base ?>/logout.php" class="btn-logout">↪ Déconnexion</a>
  </div>
</aside>
<?php } ?>
