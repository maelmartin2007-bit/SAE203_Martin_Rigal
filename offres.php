<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requireRole('etudiant');

$user     = currentUser();
$message  = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offre_id'])) {
    $offre_id = (int)$_POST['offre_id'];
    $stmt = $pdo->prepare("SELECT places_disponibles FROM offres WHERE id = ?");
    $stmt->execute([$offre_id]);
    $row = $stmt->fetch();
    $places = isset($row['places_disponibles']) ? (int)$row['places_disponibles'] : 1;

    if ($places <= 0) {
        $message = "Plus de places disponibles pour cette offre.";
        $msg_type = 'error';
    } else {
        try {
            $pdo->prepare("INSERT IGNORE INTO candidatures (etudiant_id, offre_id) VALUES (?, ?)")
                ->execute([$user['id'], $offre_id]);
            $pdo->prepare("UPDATE offres SET places_disponibles = places_disponibles - 1 WHERE id = ? AND places_disponibles > 0")
                ->execute([$offre_id]);
            $message = 'Candidature envoyée avec succès !';
        } catch (PDOException $e) {
            $message = 'Erreur lors de la candidature.';
            $msg_type = 'error';
        }
    }
}

$search  = trim($_GET['q']   ?? '');
$filtre  = trim($_GET['cat'] ?? 'tous');

$sql = "SELECT o.*,
        (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) AS nb_candidatures
        FROM offres o WHERE o.statut = 'active'";
$params = [];

if ($search) {
    $sql .= " AND (o.titre LIKE ? OR o.description LIKE ? OR o.entreprise LIKE ? OR o.categorie LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s]);
}
if ($filtre === 'dispo') {
    $sql .= " AND o.places_disponibles > 0";
} elseif ($filtre === 'remote') {
    $sql .= " AND LOWER(o.lieu) LIKE '%remote%'";
} elseif (!in_array($filtre, ['tous','dispo','remote',''])) {
    $sql .= " AND o.categorie = ?";
    $params[] = $filtre;
}
$sql .= " ORDER BY o.categorie, o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres_db = $stmt->fetchAll();

$stmt2 = $pdo->prepare("SELECT offre_id FROM candidatures WHERE etudiant_id = ?");
$stmt2->execute([$user['id']]);
$deja_postule = array_column($stmt2->fetchAll(), 'offre_id');

// Démo si vide
if (empty($offres_db)) {
    $offres_db = [
        ['id'=>1,'titre'=>'Développeur Web Frontend','entreprise'=>'TechCorp','lieu'=>'Paris','duree'=>'6 mois','description'=>'Stage React & TypeScript. Développement d\'interfaces modernes et performantes.','places_total'=>3,'places_disponibles'=>2,'nb_candidatures'=>5,'statut'=>'active','categorie'=>'Tech','effectif'=>'250–500','site_web'=>'techcorp.fr'],
        ['id'=>2,'titre'=>'Développeur Backend','entreprise'=>'CloudSolutions','lieu'=>'Remote','duree'=>'6 mois','description'=>'APIs Node.js & PostgreSQL, environnement cloud-native moderne.','places_total'=>4,'places_disponibles'=>1,'nb_candidatures'=>8,'statut'=>'active','categorie'=>'Tech','effectif'=>'100–250','site_web'=>'cloudsolutions.io'],
        ['id'=>3,'titre'=>'Data Analyst','entreprise'=>'DataInsight','lieu'=>'Lyon','duree'=>'4 mois','description'=>'Analyse et visualisation de données avec Python & Pandas.','places_total'=>2,'places_disponibles'=>2,'nb_candidatures'=>3,'statut'=>'active','categorie'=>'Tech','effectif'=>'50–100','site_web'=>'datainsight.fr'],
        ['id'=>4,'titre'=>'Chargé(e) de communication','entreprise'=>'MediaGroup','lieu'=>'Paris','duree'=>'5 mois','description'=>'Rédaction de contenus, gestion réseaux sociaux et relations presse.','places_total'=>3,'places_disponibles'=>3,'nb_candidatures'=>2,'statut'=>'active','categorie'=>'Communication','effectif'=>'500+','site_web'=>'mediagroup.fr'],
        ['id'=>5,'titre'=>'Community Manager','entreprise'=>'AgencePulse','lieu'=>'Bordeaux','duree'=>'3 mois','description'=>'Animation des réseaux sociaux, création de contenus visuels et reporting.','places_total'=>2,'places_disponibles'=>1,'nb_candidatures'=>4,'statut'=>'active','categorie'=>'Communication','effectif'=>'10–50','site_web'=>'agencepulse.fr'],
        ['id'=>6,'titre'=>'UX/UI Designer','entreprise'=>'PixelStudio','lieu'=>'Paris','duree'=>'3 mois','description'=>'Conception d\'interfaces Figma pour des clients dans le secteur digital.','places_total'=>1,'places_disponibles'=>0,'nb_candidatures'=>12,'statut'=>'active','categorie'=>'Design','effectif'=>'10–50','site_web'=>'pixelstudio.fr'],
        ['id'=>7,'titre'=>'Analyste financier junior','entreprise'=>'BNP Consulting','lieu'=>'Paris','duree'=>'6 mois','description'=>'Modélisation financière, analyse de portefeuilles et reporting mensuel.','places_total'=>2,'places_disponibles'=>2,'nb_candidatures'=>6,'statut'=>'active','categorie'=>'Finance','effectif'=>'1000+','site_web'=>'bnpconsulting.fr'],
        ['id'=>8,'titre'=>'Contrôleur de gestion','entreprise'=>'GestionPro','lieu'=>'Lyon','duree'=>'4 mois','description'=>'Suivi budgétaire, tableaux de bord et analyse des écarts.','places_total'=>2,'places_disponibles'=>2,'nb_candidatures'=>1,'statut'=>'active','categorie'=>'Finance','effectif'=>'100–250','site_web'=>'gestionpro.fr'],
    ];
    // Appliquer filtres démo
    if ($search) {
        $s = strtolower($search);
        $offres_db = array_filter($offres_db, fn($o) =>
            str_contains(strtolower($o['titre']), $s) ||
            str_contains(strtolower($o['description']), $s) ||
            str_contains(strtolower($o['entreprise']), $s) ||
            str_contains(strtolower($o['categorie']), $s)
        );
    }
    if ($filtre === 'dispo')  $offres_db = array_filter($offres_db, fn($o) => $o['places_disponibles'] > 0);
    if ($filtre === 'remote') $offres_db = array_filter($offres_db, fn($o) => stripos($o['lieu'],'remote') !== false);
    if (!in_array($filtre, ['tous','dispo','remote',''])) {
        $offres_db = array_filter($offres_db, fn($o) => $o['categorie'] === $filtre);
    }
}

// Grouper par catégorie
$par_categorie = [];
foreach ($offres_db as $o) {
    $cat = $o['categorie'] ?? 'Autres';
    $par_categorie[$cat][] = $o;
}

$categories_label = [
    'Tech'          => 'Développement & Tech',
    'Communication' => 'Communication & Marketing',
    'Design'        => 'Design',
    'Finance'       => 'Finance & Gestion',
    'Autres'        => 'Autres offres',
];

$filtres = [
    'tous'          => 'Toutes',
    'dispo'         => 'Places dispo',
    'remote'        => 'Remote',
    'Tech'          => 'Tech',
    'Communication' => 'Communication',
    'Design'        => 'Design',
    'Finance'       => 'Finance',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Offres de stage</title>
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .top-bar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
    .search-wrap{position:relative;flex:1;max-width:340px;}
    .search-wrap .si{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;}
    .search-wrap input{width:100%;padding:9px 12px 9px 34px;border:1px solid var(--gray-border);border-radius:8px;font-size:13px;background:white;color:var(--text-main);outline:none;}
    .search-wrap input:focus{border-color:var(--blue-main);}
    .vsep{width:1px;height:28px;background:var(--gray-border);}
    .filtres-bar{display:flex;gap:6px;flex-wrap:wrap;}
    .fb{padding:5px 13px;border:1.5px solid var(--gray-border);border-radius:20px;font-size:12px;color:var(--gray-text);background:white;cursor:pointer;text-decoration:none;transition:all .15s;}
    .fb:hover,.fb.on{border-color:var(--blue-main);color:var(--blue-main);background:var(--blue-light);}
    .nb-offres{font-size:12px;color:var(--gray-text);margin-left:auto;white-space:nowrap;}

    .cat-title{font-size:11px;font-weight:700;color:var(--gray-text);text-transform:uppercase;letter-spacing:.6px;margin:20px 0 10px;}
    .cat-title:first-of-type{margin-top:0;}
    .offres-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;}

    .kard{background:white;border:1px solid var(--gray-border);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:10px;position:relative;overflow:hidden;transition:box-shadow .2s;}
    .kard:hover{box-shadow:0 6px 20px rgba(45,91,227,.09);}
    .kard::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:12px 12px 0 0;}
    .kard.bl::before{background:var(--blue-main);}
    .kard.or::before{background:var(--orange);}
    .kard.gr::before{background:var(--gray-text);}

    .kh{display:flex;align-items:flex-start;gap:10px;}
    .klogo{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0;}
    .klogo.bl{background:var(--blue-light);color:var(--blue-main);}
    .klogo.or{background:#fff7ed;color:var(--orange);}
    .klogo.gr{background:var(--gray-bg);color:var(--gray-text);}
    .klogo.gn{background:#f0fdf4;color:#15803d;}
    .klogo.am{background:#fefce8;color:#854f0b;}
    .ktb{flex:1;min-width:0;}
    .ktit{font-size:13px;font-weight:700;color:var(--text-main);line-height:1.3;margin-bottom:2px;}
    .kco{font-size:11px;color:var(--gray-text);}

    .pbadge{display:flex;flex-direction:column;align-items:center;border-radius:8px;padding:5px 9px;min-width:46px;flex-shrink:0;}
    .pbadge.bl{background:var(--blue-light);border:1px solid var(--blue-border);}
    .pbadge.or{background:#fff7ed;border:1px solid #fed7aa;}
    .pbadge.gr{background:var(--gray-bg);border:1px solid var(--gray-border);}
    .pnb{font-size:17px;font-weight:700;line-height:1;}
    .pbadge.bl .pnb{color:var(--blue-main);}
    .pbadge.or .pnb{color:var(--orange);}
    .pbadge.gr .pnb{color:var(--gray-text);}
    .plb{font-size:9px;font-weight:600;text-align:center;margin-top:2px;}
    .pbadge.bl .plb{color:var(--blue-main);}
    .pbadge.or .plb{color:var(--orange);}
    .pbadge.gr .plb{color:var(--gray-text);}

    .ktags{display:flex;flex-wrap:wrap;gap:5px;}
    .ktag{padding:2px 9px;background:var(--gray-bg);border-radius:20px;font-size:11px;color:var(--gray-text);border:0.5px solid var(--gray-border);}

    .kdesc{font-size:12px;color:var(--gray-text);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

    .cinfo{background:var(--gray-bg);border-radius:8px;padding:8px 10px;display:flex;flex-direction:column;gap:5px;border:0.5px solid var(--gray-border);}
    .crow{display:flex;justify-content:space-between;align-items:center;font-size:11px;}
    .crow-l{color:var(--gray-text);}
    .crow-r{color:var(--text-main);font-weight:600;}
    .crow-r a{color:var(--blue-main);font-size:11px;}

    .pbar-w{display:flex;flex-direction:column;gap:4px;}
    .pbar-l{display:flex;justify-content:space-between;font-size:11px;color:var(--gray-text);}
    .pbar-l span:last-child{font-weight:600;color:var(--text-main);}
    .pbar{height:5px;background:var(--gray-border);border-radius:99px;overflow:hidden;}
    .pbar-f{height:100%;border-radius:99px;}
    .pbar-f.bl{background:var(--blue-main);}
    .pbar-f.or{background:var(--orange);}
    .pbar-f.gr{background:var(--gray-text);}

    .kfoot{display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid var(--gray-border);}
    .kcands{font-size:11px;color:var(--gray-text);}

    .btn-k-pos{padding:7px 16px;background:var(--blue-main);color:white;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;}
    .btn-k-pos.or{background:var(--orange);}
    .btn-k-pos:hover{opacity:.9;}
    .btn-k-post{padding:7px 14px;background:#dcfce7;color:#15803d;border:1px solid #86efac;border-radius:8px;font-size:12px;font-weight:600;}
    .btn-k-comp{padding:7px 14px;background:var(--gray-bg);color:var(--gray-text);border:1px solid var(--gray-border);border-radius:8px;font-size:12px;}

    .no-result{text-align:center;padding:60px 20px;color:var(--gray-text);}
    .no-result .nr-icon{font-size:40px;margin-bottom:12px;}
    .no-result p{font-size:14px;}
  </style>
</head>
<body>
<div class="app-layout">
  <?php sidebar('etudiant', 'offres'); ?>
  <main class="main-content">
    <h1 class="page-title">Offres de stage</h1>

    <?php if ($message): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= h($message) ?></div>
    <?php endif; ?>

    <!-- Barre de recherche + filtres -->
    <div class="top-bar">
      <form method="GET" class="search-wrap" style="display:contents">
        <div class="search-wrap">
          <span class="si">🔍</span>
          <input type="text" name="q" placeholder="Rechercher : développement web, communication…"
                 value="<?= h($search) ?>" onchange="this.form.submit()">
        </div>
        <input type="hidden" name="cat" value="<?= h($filtre) ?>">
      </form>
      <div class="vsep"></div>
      <div class="filtres-bar">
        <?php foreach ($filtres as $val => $label):
          $qs = $search ? '&q=' . urlencode($search) : '';
          $active = $filtre === $val ? 'on' : '';
        ?>
          <a href="?cat=<?= urlencode($val) ?><?= $qs ?>" class="fb <?= $active ?>">
            <?= h($label) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <span class="nb-offres"><?= count($offres_db) ?> offre(s)</span>
    </div>

    <?php if (empty($offres_db)): ?>
      <div class="no-result">
        <div class="nr-icon">🔍</div>
        <p>Aucune offre ne correspond à votre recherche.</p>
      </div>
    <?php else: ?>
      <?php foreach ($par_categorie as $cat => $offres): ?>
        <div class="cat-title">
          <?= h($categories_label[$cat] ?? $cat) ?>
        </div>
        <div class="offres-grid">
          <?php foreach ($offres as $offre):
            $dispo   = (int)($offre['places_disponibles'] ?? 1);
            $total   = (int)($offre['places_total']       ?? 1);
            $nb_cand = (int)($offre['nb_candidatures']    ?? 0);
            $complet = $dispo <= 0;
            $urgent  = !$complet && $dispo === 1;
            $postule = in_array($offre['id'], $deja_postule);
            $col     = $complet ? 'gr' : ($urgent ? 'or' : 'bl');
            $pct     = $total > 0 ? round((($total - $dispo) / $total) * 100) : 100;
            $initiale= mb_strtoupper(mb_substr($offre['entreprise'], 0, 1));
            // Couleur logo selon catégorie
            $logo_col = match($offre['categorie'] ?? '') {
              'Communication','Design' => 'gn',
              'Finance'                => 'am',
              default                  => $col,
            };
          ?>
          <div class="kard <?= $col ?>">
            <div class="kh">
              <div class="klogo <?= $logo_col ?>"><?= $initiale ?></div>
              <div class="ktb">
                <div class="ktit"><?= h($offre['titre']) ?></div>
                <div class="kco">🏢 <?= h($offre['entreprise']) ?></div>
              </div>
              <div class="pbadge <?= $col ?>">
                <span class="pnb"><?= $dispo ?></span>
                <span class="plb"><?= $complet ? 'Complet' : 'place' . ($dispo > 1 ? 's' : '') ?></span>
              </div>
            </div>

            <div class="ktags">
              <span class="ktag">📍 <?= h($offre['lieu']) ?></span>
              <span class="ktag">📅 <?= h($offre['duree']) ?></span>
              <?php if (!empty($offre['categorie'])): ?>
                <span class="ktag"><?= h($offre['categorie']) ?></span>
              <?php endif; ?>
            </div>

            <div class="kdesc"><?= h($offre['description']) ?></div>

            <!-- Infos entreprise -->
            <div class="cinfo">
              <?php if (!empty($offre['effectif'])): ?>
                <div class="crow">
                  <span class="crow-l">👥 Effectif</span>
                  <span class="crow-r"><?= h($offre['effectif']) ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($offre['site_web'])): ?>
                <div class="crow">
                  <span class="crow-l">🌐 Site web</span>
                  <span class="crow-r"><a href="https://<?= h($offre['site_web']) ?>" target="_blank"><?= h($offre['site_web']) ?></a></span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Barre de progression -->
            <div class="pbar-w">
              <div class="pbar-l">
                <span>Places occupées</span>
                <span><?= $total - $dispo ?>/<?= $total ?></span>
              </div>
              <div class="pbar">
                <div class="pbar-f <?= $col ?>" style="width:<?= $pct ?>%"></div>
              </div>
            </div>

            <!-- Footer -->
            <div class="kfoot">
              <span class="kcands">👤 <?= $nb_cand ?> candidature(s)</span>
              <?php if ($postule): ?>
                <span class="btn-k-post">✓ Postulé</span>
              <?php elseif ($complet): ?>
                <span class="btn-k-comp">Complet</span>
              <?php else: ?>
                <form method="POST">
                  <input type="hidden" name="offre_id" value="<?= $offre['id'] ?>">
                  <button type="submit" class="btn-k-pos <?= $urgent ? 'or' : '' ?>">
                    Postuler →
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
