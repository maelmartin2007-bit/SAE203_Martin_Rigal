<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requireRole('enseignant');

$user    = currentUser();
$message = '';
$action  = $_GET['action'] ?? '';
$edit_id = (int)($_GET['id'] ?? 0);

// Supprimer
if ($action === 'supprimer' && $edit_id) {
    $pdo->prepare("DELETE FROM offres WHERE id = ?")->execute([$edit_id]);
    $message = 'Offre supprimée.';
    $action  = '';
}

// Créer / Modifier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $titre       = trim($_POST['titre']       ?? '');
    $entreprise  = trim($_POST['entreprise']  ?? '');
    $lieu        = trim($_POST['lieu']        ?? '');
    $duree       = trim($_POST['duree']       ?? '');
    $categorie   = trim($_POST['categorie']   ?? 'Tech');
    $effectif    = trim($_POST['effectif']    ?? '');
    $site_web    = trim($_POST['site_web']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $places      = max(1, (int)($_POST['places_total'] ?? 1));

    if ($titre && $entreprise) {
        if ($id) {
            $pdo->prepare("UPDATE offres SET titre=?,entreprise=?,lieu=?,duree=?,categorie=?,effectif=?,site_web=?,description=?,places_total=?,places_disponibles=? WHERE id=?")
                ->execute([$titre,$entreprise,$lieu,$duree,$categorie,$effectif,$site_web,$description,$places,$places,$id]);
            $message = 'Offre modifiée avec succès.';
        } else {
            $pdo->prepare("INSERT INTO offres (titre,entreprise,lieu,duree,categorie,effectif,site_web,description,places_total,places_disponibles,statut,creee_par) VALUES (?,?,?,?,?,?,?,?,?,?,'active',?)")
                ->execute([$titre,$entreprise,$lieu,$duree,$categorie,$effectif,$site_web,$description,$places,$places,$user['id']]);
            $message = 'Offre ajoutée avec succès.';
        }
        $action  = '';
        $edit_id = 0;
    }
}

// Offre à éditer
$offre_edit = null;
if ($action === 'editer' && $edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM offres WHERE id = ?");
    $stmt->execute([$edit_id]);
    $offre_edit = $stmt->fetch();
}

// Recherche + filtre catégorie
$search = trim($_GET['q']   ?? '');
$filtre = trim($_GET['cat'] ?? 'tous');

$sql = "SELECT o.*, (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id=o.id) AS nb_candidatures FROM offres o WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (o.titre LIKE ? OR o.entreprise LIKE ? OR o.description LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s]);
}
if (!in_array($filtre, ['tous',''])) {
    $sql .= " AND o.categorie = ?";
    $params[] = $filtre;
}
$sql .= " ORDER BY o.categorie, o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres_db = $stmt->fetchAll();

// Stats globales
$nb_actives   = $pdo->query("SELECT COUNT(*) FROM offres WHERE statut='active'")->fetchColumn();
$nb_places    = $pdo->query("SELECT COALESCE(SUM(places_disponibles),0) FROM offres WHERE statut='active'")->fetchColumn();
$nb_urgentes  = $pdo->query("SELECT COUNT(*) FROM offres WHERE places_disponibles=1 AND statut='active'")->fetchColumn();
$nb_cands     = $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn();

// Données démo si vide
if (empty($offres_db)) {
    $offres_db = [
        ['id'=>1,'titre'=>'Développeur Web Frontend','entreprise'=>'TechCorp',      'lieu'=>'Paris',    'duree'=>'6 mois','description'=>'Stage React & TypeScript.','categorie'=>'Tech',         'effectif'=>'250–500','site_web'=>'techcorp.fr',      'places_total'=>3,'places_disponibles'=>2,'nb_candidatures'=>5,'statut'=>'active'],
        ['id'=>2,'titre'=>'Développeur Backend',     'entreprise'=>'CloudSolutions','lieu'=>'Remote',   'duree'=>'6 mois','description'=>'APIs Node.js & PostgreSQL.','categorie'=>'Tech',         'effectif'=>'100–250','site_web'=>'cloudsolutions.io','places_total'=>4,'places_disponibles'=>1,'nb_candidatures'=>8,'statut'=>'active'],
        ['id'=>3,'titre'=>'Data Analyst',            'entreprise'=>'DataInsight',   'lieu'=>'Lyon',     'duree'=>'4 mois','description'=>'Analyse Python & Pandas.',  'categorie'=>'Tech',         'effectif'=>'50–100', 'site_web'=>'datainsight.fr',   'places_total'=>2,'places_disponibles'=>0,'nb_candidatures'=>3,'statut'=>'active'],
        ['id'=>4,'titre'=>'Chargé(e) communication','entreprise'=>'MediaGroup',     'lieu'=>'Paris',    'duree'=>'5 mois','description'=>'Réseaux sociaux & presse.', 'categorie'=>'Communication','effectif'=>'500+',   'site_web'=>'mediagroup.fr',    'places_total'=>3,'places_disponibles'=>3,'nb_candidatures'=>2,'statut'=>'active'],
        ['id'=>5,'titre'=>'Community Manager',       'entreprise'=>'AgencePulse',   'lieu'=>'Bordeaux', 'duree'=>'3 mois','description'=>'Animation réseaux sociaux.','categorie'=>'Communication','effectif'=>'10–50',  'site_web'=>'agencepulse.fr',   'places_total'=>2,'places_disponibles'=>1,'nb_candidatures'=>4,'statut'=>'active'],
        ['id'=>6,'titre'=>'UX/UI Designer',          'entreprise'=>'PixelStudio',   'lieu'=>'Paris',    'duree'=>'3 mois','description'=>'Interfaces Figma.',          'categorie'=>'Design',       'effectif'=>'10–50',  'site_web'=>'pixelstudio.fr',   'places_total'=>1,'places_disponibles'=>0,'nb_candidatures'=>12,'statut'=>'active'],
        ['id'=>7,'titre'=>'Analyste financier jr',   'entreprise'=>'BNP Consulting','lieu'=>'Paris',    'duree'=>'6 mois','description'=>'Modélisation financière.',   'categorie'=>'Finance',      'effectif'=>'1000+',  'site_web'=>'bnpconsulting.fr', 'places_total'=>2,'places_disponibles'=>2,'nb_candidatures'=>6,'statut'=>'active'],
        ['id'=>8,'titre'=>'Contrôleur de gestion',   'entreprise'=>'GestionPro',    'lieu'=>'Lyon',     'duree'=>'4 mois','description'=>'Suivi budgétaire.',          'categorie'=>'Finance',      'effectif'=>'100–250','site_web'=>'gestionpro.fr',    'places_total'=>2,'places_disponibles'=>2,'nb_candidatures'=>1,'statut'=>'active'],
    ];
    $nb_actives = 8; $nb_places = 14; $nb_urgentes = 3; $nb_cands = 32;
}

// Grouper par catégorie
$par_cat = [];
foreach ($offres_db as $o) {
    $par_cat[$o['categorie'] ?? 'Autres'][] = $o;
}
$cat_labels = ['Tech'=>'Développement & Tech','Communication'=>'Communication & Marketing','Design'=>'Design','Finance'=>'Finance & Gestion','Autres'=>'Autres'];
$filtres = ['tous'=>'Toutes','Tech'=>'Tech','Communication'=>'Communication','Design'=>'Design','Finance'=>'Finance'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des offres — Enseignant</title>
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .stats-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:20px;}
    .stat-c{background:white;border:1px solid var(--gray-border);border-radius:10px;padding:14px 16px;}
    .stat-n{font-size:24px;font-weight:700;color:var(--text-main);line-height:1;}
    .stat-l{font-size:12px;color:var(--gray-text);margin-top:4px;}
    .stat-c.pink .stat-n{color:var(--pink-main);}
    .stat-c.orange .stat-n{color:var(--orange);}
    .stat-c.green .stat-n{color:var(--green);}

    .top-bar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
    .sw{position:relative;flex:1;max-width:300px;}
    .sw i,.sw .si{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--gray-text);font-size:15px;}
    .sw input{width:100%;padding:8px 12px 8px 34px;border:1px solid var(--gray-border);border-radius:8px;font-size:13px;background:white;color:var(--text-main);}
    .sw input:focus{outline:none;border-color:var(--blue-main);}
    .filtres{display:flex;gap:6px;flex-wrap:wrap;}
    .fb{padding:5px 13px;border:1.5px solid var(--gray-border);border-radius:20px;font-size:12px;color:var(--gray-text);background:white;cursor:pointer;text-decoration:none;transition:all .15s;}
    .fb:hover,.fb.on{border-color:var(--blue-main);color:var(--blue-main);background:var(--blue-light);}

    .cat-title{font-size:11px;font-weight:700;color:var(--gray-text);text-transform:uppercase;letter-spacing:.6px;margin:20px 0 10px;}
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
    .ktag-cat{background:var(--blue-light);color:var(--blue-main);border-color:var(--blue-border);}

    .kdesc{font-size:12px;color:var(--gray-text);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

    .cinfo{background:var(--gray-bg);border-radius:8px;padding:8px 10px;display:flex;flex-direction:column;gap:5px;border:0.5px solid var(--gray-border);}
    .crow{display:flex;justify-content:space-between;align-items:center;font-size:11px;}
    .crow-l{color:var(--gray-text);}
    .crow-r{color:var(--text-main);font-weight:600;}
    .crow-r a{color:var(--blue-main);}

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
    .kact{display:flex;align-items:center;gap:8px;}
    .badge-active{padding:2px 10px;background:var(--blue-light);color:var(--blue-main);border:1px solid var(--blue-border);border-radius:20px;font-size:11px;font-weight:600;}
    .badge-complet{padding:2px 10px;background:var(--gray-bg);color:var(--gray-text);border:1px solid var(--gray-border);border-radius:20px;font-size:11px;font-weight:600;}
    .ic-btn{background:none;border:none;cursor:pointer;font-size:16px;padding:3px;line-height:1;}
    .ic-edit{color:var(--blue-main);}
    .ic-del{color:var(--red);}

    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal-box{background:white;border-radius:14px;padding:28px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;}
    .modal-box h2{font-size:17px;font-weight:700;margin-bottom:20px;color:var(--text-main);}
    .fg-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .fg{margin-bottom:12px;}
    .fg label{display:block;font-size:12px;font-weight:600;color:var(--gray-text);margin-bottom:5px;}
    .fg input,.fg select,.fg textarea{width:100%;padding:9px 12px;border:1px solid var(--gray-border);border-radius:8px;font-size:13px;color:var(--text-main);font-family:inherit;background:white;}
    .fg input:focus,.fg select:focus,.fg textarea:focus{outline:none;border-color:var(--blue-main);}
    .fg textarea{height:80px;resize:vertical;}
    .modal-act{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
    .btn-cancel{padding:9px 18px;border:1px solid var(--gray-border);border-radius:8px;background:white;cursor:pointer;font-size:13px;color:var(--gray-text);}
    .btn-save{padding:9px 24px;background:var(--blue-main);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;}
    .btn-save:hover{background:var(--blue-dark);}
  </style>
</head>
<body>
<div class="app-layout">
  <?php sidebar('enseignant', 'offres'); ?>
  <main class="main-content">
    <h1 class="page-title">Gestion des offres de stage</h1>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= h($message) ?></div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-row">
      <div class="stat-c">
        <div class="stat-n"><?= (int)$nb_actives ?></div>
        <div class="stat-l">Offres actives</div>
      </div>
      <div class="stat-c pink">
        <div class="stat-n"><?= (int)$nb_places ?></div>
        <div class="stat-l">Places disponibles</div>
      </div>
      <div class="stat-c orange">
        <div class="stat-n"><?= (int)$nb_urgentes ?></div>
        <div class="stat-l">Dernières places</div>
      </div>
      <div class="stat-c green">
        <div class="stat-n"><?= (int)$nb_cands ?></div>
        <div class="stat-l">Candidatures reçues</div>
      </div>
    </div>

    <!-- Barre recherche + filtres + bouton -->
    <div class="top-bar">
      <form method="GET" style="display:contents">
        <div class="sw">
          <span class="si">🔍</span>
          <input type="text" name="q" placeholder="Rechercher une offre…"
                 value="<?= h($search) ?>" onchange="this.form.submit()">
        </div>
        <input type="hidden" name="cat" value="<?= h($filtre) ?>">
      </form>
      <div class="filtres">
        <?php foreach ($filtres as $val => $label):
          $qs = $search ? '&q='.urlencode($search) : '';
        ?>
          <a href="?cat=<?= urlencode($val) ?><?= $qs ?>"
             class="fb <?= $filtre===$val?'on':'' ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
      </div>
      <button class="btn-add" style="margin-left:auto" onclick="openModal()">
        + Ajouter une offre
      </button>
    </div>

    <!-- Cartes par catégorie -->
    <?php if (empty($offres_db)): ?>
      <div style="text-align:center;padding:60px 20px;color:var(--gray-text)">
        <div style="font-size:40px;margin-bottom:12px">🔍</div>
        <p>Aucune offre trouvée.</p>
      </div>
    <?php else: ?>
      <?php foreach ($par_cat as $cat => $offres): ?>
        <div class="cat-title"><?= h($cat_labels[$cat] ?? $cat) ?></div>
        <div class="offres-grid">
          <?php foreach ($offres as $offre):
            $dispo  = (int)($offre['places_disponibles'] ?? 1);
            $total  = (int)($offre['places_total']       ?? 1);
            $nb_c   = (int)($offre['nb_candidatures']    ?? 0);
            $complet= $dispo <= 0;
            $urgent = !$complet && $dispo === 1;
            $col    = $complet ? 'gr' : ($urgent ? 'or' : 'bl');
            $pct    = $total > 0 ? round((($total-$dispo)/$total)*100) : 100;
            $init   = mb_strtoupper(mb_substr($offre['entreprise'],0,1));
            $logo_col = match($cat) { 'Communication','Design'=>'gn', 'Finance'=>'am', default=>$col };
          ?>
          <div class="kard <?= $col ?>">
            <div class="kh">
              <div class="klogo <?= $logo_col ?>"><?= $init ?></div>
              <div class="ktb">
                <div class="ktit"><?= h($offre['titre']) ?></div>
                <div class="kco">🏢 <?= h($offre['entreprise']) ?></div>
              </div>
              <div class="pbadge <?= $col ?>">
                <span class="pnb"><?= $dispo ?></span>
                <span class="plb"><?= $complet?'Complet':($dispo>1?'places':'place') ?></span>
              </div>
            </div>

            <div class="ktags">
              <span class="ktag">📍 <?= h($offre['lieu']) ?></span>
              <span class="ktag">📅 <?= h($offre['duree']) ?></span>
              <span class="ktag ktag-cat"><?= h($offre['categorie'] ?? '') ?></span>
            </div>

            <div class="kdesc"><?= h($offre['description']) ?></div>

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
              <div class="crow">
                <span class="crow-l">🪑 Places</span>
                <span class="crow-r"><?= $total-$dispo ?> occupée(s) / <?= $total ?> total</span>
              </div>
            </div>

            <div class="pbar-w">
              <div class="pbar-l">
                <span>Places occupées</span>
                <span><?= $total-$dispo ?>/<?= $total ?></span>
              </div>
              <div class="pbar">
                <div class="pbar-f <?= $col ?>" style="width:<?= $pct ?>%"></div>
              </div>
            </div>

            <div class="kfoot">
              <span class="kcands">👤 <?= $nb_c ?> candidature(s)</span>
              <div class="kact">
                <?php if ($complet): ?>
                  <span class="badge-complet">Complet</span>
                <?php else: ?>
                  <span class="badge-active">Active</span>
                <?php endif; ?>
                <a href="?action=editer&id=<?= $offre['id'] ?>" class="ic-btn ic-edit" title="Modifier">✏️</a>
                <a href="?action=supprimer&id=<?= $offre['id'] ?>"
                   class="ic-btn ic-del" title="Supprimer"
                   onclick="return confirm('Supprimer cette offre ?')">🗑️</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<!-- Modal Ajouter / Modifier -->
<div class="modal-overlay <?= $offre_edit ? 'open' : '' ?>" id="modal">
  <div class="modal-box">
    <h2><?= $offre_edit ? '✏️ Modifier l\'offre' : '+ Ajouter une offre' ?></h2>
    <form method="POST">
      <input type="hidden" name="id" value="<?= (int)($offre_edit['id'] ?? 0) ?>">
      <div class="fg-row">
        <div class="fg">
          <label>Titre *</label>
          <input type="text" name="titre" required placeholder="Ex : Développeur Web Frontend"
                 value="<?= h($offre_edit['titre'] ?? '') ?>">
        </div>
        <div class="fg">
          <label>Entreprise *</label>
          <input type="text" name="entreprise" required placeholder="Ex : TechCorp"
                 value="<?= h($offre_edit['entreprise'] ?? '') ?>">
        </div>
      </div>
      <div class="fg-row">
        <div class="fg">
          <label>Lieu</label>
          <input type="text" name="lieu" placeholder="Ex : Paris"
                 value="<?= h($offre_edit['lieu'] ?? '') ?>">
        </div>
        <div class="fg">
          <label>Durée</label>
          <input type="text" name="duree" placeholder="Ex : 6 mois"
                 value="<?= h($offre_edit['duree'] ?? '') ?>">
        </div>
      </div>
      <div class="fg-row">
        <div class="fg">
          <label>Catégorie</label>
          <select name="categorie">
            <?php foreach (['Tech','Communication','Design','Finance','Autres'] as $c): ?>
              <option value="<?= $c ?>" <?= ($offre_edit['categorie'] ?? 'Tech')===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Nombre de places</label>
          <input type="number" name="places_total" min="1" max="99" placeholder="Ex : 3"
                 value="<?= h((string)($offre_edit['places_total'] ?? '1')) ?>">
        </div>
      </div>
      <div class="fg-row">
        <div class="fg">
          <label>Effectif entreprise</label>
          <input type="text" name="effectif" placeholder="Ex : 100–250"
                 value="<?= h($offre_edit['effectif'] ?? '') ?>">
        </div>
        <div class="fg">
          <label>Site web</label>
          <input type="text" name="site_web" placeholder="Ex : techcorp.fr"
                 value="<?= h($offre_edit['site_web'] ?? '') ?>">
        </div>
      </div>
      <div class="fg">
        <label>Description</label>
        <textarea name="description" placeholder="Décrivez le poste, les missions, les technologies…"><?= h($offre_edit['description'] ?? '') ?></textarea>
      </div>
      <div class="modal-act">
        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
        <button type="submit" class="btn-save">
          <?= $offre_edit ? 'Enregistrer les modifications' : 'Ajouter l\'offre' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal()  { document.getElementById('modal').classList.add('open'); }
function closeModal() { document.getElementById('modal').classList.remove('open'); }
</script>
</body>
</html>
