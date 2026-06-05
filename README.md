# SAE203_Martin_Rigal[README.md](https://github.com/user-attachments/files/28656158/README.md)
# Plateforme de Stages — Guide complet

## Structure
```
stages/
├── index.php               → Redirection auto
├── login.php               → Connexion
├── register.php            → Inscription
├── logout.php              → Déconnexion
├── css/style.css           → Styles globaux
├── includes/
│   ├── config.php          → BASE URL auto-détectée
│   ├── db.php              → Connexion PDO MySQL ← à configurer
│   ├── auth.php            → Session / sécurité
│   └── sidebar.php         → Barre de navigation
├── pages/
│   ├── etudiant/
│   │   ├── dashboard.php   → Tableau de bord
│   │   ├── offres.php      → Offres par catégorie + recherche + places
│   │   ├── convention.php  → Convention + upload PDF
│   │   ├── oral.php        → Soutenance (date, jury, consignes)
│   │   └── suivi.php       → Timeline + tâches
│   └── enseignant/
│       ├── dashboard.php   → Tableau de bord + stats
│       ├── offres.php      → Gestion CRUD offres (cartes + catégories)
│       ├── conventions.php → Validation conventions
│       ├── oraux.php       → Planification + notation
│       ├── bareme.php      → Barème de notation
│       └── suivi_etudiants.php → Suivi global
├── uploads/conventions/    → PDFs uploadés (créé automatiquement)
└── database/
    ├── schema.sql          → INSTALLATION FRAÎCHE ← utiliser en premier
    └── migration_v2.sql    → MISE À JOUR si déjà installé

```

---

## Installation

### 1. Copier dans htdocs
```
C:\xampp\htdocs\stages\
```

### 2. Configurer la base de données
Modifier `includes/db.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stages_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // vide sous XAMPP par défaut
```

### 3. Créer la base de données
**Option A — Installation fraîche** (recommandé) :
→ phpMyAdmin > onglet SQL > coller `database/schema.sql` > Exécuter

**Option B — Mise à jour d'une installation existante** :
→ phpMyAdmin > onglet SQL > coller `database/migration_v2.sql` > Exécuter

### 4. Créer vos comptes
→ http://localhost/stages/register.php
→ Créer un compte Étudiant et un compte Enseignant

### 5. Tester
→ http://localhost/stages/

---

## Fonctionnalités

### Étudiant
- Inscription / connexion avec choix de rôle
- Tableau de bord avec notifications
- **Offres de stage** : cartes par catégorie (Tech / Communication / Design / Finance), barre de recherche par mot-clé, filtres, places disponibles avec barre de progression, infos entreprise
- Convention de stage avec statut et upload PDF signé
- Soutenance orale (date, jury, consignes)
- Timeline de progression + tâches à réaliser

### Enseignant
- Tableau de bord avec statistiques (offres, conventions, étudiants)
- **Gestion offres** : cartes par catégorie, stats globales, formulaire enrichi (catégorie, effectif, site web, places), recherche et filtres
- Validation des conventions (approuver / refuser)
- Planification soutenances + saisie notes /20
- Barème de notation avec échelle
- Suivi global des étudiants avec statuts colorés

---

## Catégories d'offres disponibles
- **Tech** — Développement, Data, DevOps
- **Communication** — Com, Marketing, Social Media
- **Design** — UX/UI, Graphisme
- **Finance** — Analyse financière, Contrôle de gestion
- **Autres** — Toute autre catégorie

---

## Sécurité
- Mots de passe hashés bcrypt
- Requêtes préparées PDO (anti SQL injection)
- htmlspecialchars() sur toutes les sorties (anti XSS)
- Vérification de rôle sur chaque page
- Validation des fichiers uploadés (PDF, max 10MB)
