-- =============================================
-- database/schema.sql — Base de données complète
-- Plateforme de stages — Version finale
-- =============================================

CREATE DATABASE IF NOT EXISTS stages_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stages_db;

-- -----------------------------------------------
-- Utilisateurs
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom_complet   VARCHAR(120)  NOT NULL,
  email         VARCHAR(180)  NOT NULL UNIQUE,
  mot_de_passe  VARCHAR(255)  NOT NULL,
  role          ENUM('etudiant','enseignant') NOT NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Offres de stage
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS offres (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titre               VARCHAR(200)  NOT NULL,
  entreprise          VARCHAR(150)  NOT NULL,
  lieu                VARCHAR(100),
  duree               VARCHAR(50),
  description         TEXT,
  categorie           VARCHAR(50)   DEFAULT 'Tech',
  effectif            VARCHAR(50),
  site_web            VARCHAR(100),
  places_total        INT           DEFAULT 1,
  places_disponibles  INT           DEFAULT 1,
  statut              ENUM('active','inactive') DEFAULT 'active',
  creee_par           INT UNSIGNED,
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creee_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Candidatures
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS candidatures (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etudiant_id   INT UNSIGNED NOT NULL,
  offre_id      INT UNSIGNED NOT NULL,
  statut        ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (offre_id)    REFERENCES offres(id)       ON DELETE CASCADE,
  UNIQUE KEY uq_cand (etudiant_id, offre_id)
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Conventions de stage
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS conventions (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etudiant_id     INT UNSIGNED NOT NULL,
  enseignant_id   INT UNSIGNED,
  entreprise      VARCHAR(150) NOT NULL,
  lieu            VARCHAR(100),
  date_debut      DATE,
  date_fin        DATE,
  statut          ENUM('en_attente','approuvee','refusee') DEFAULT 'en_attente',
  fichier_pdf     VARCHAR(255),
  fichier_signe   VARCHAR(255),
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (etudiant_id)   REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Soutenances orales
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS soutenances (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etudiant_id     INT UNSIGNED NOT NULL,
  date_soutenance DATE,
  heure_debut     TIME,
  heure_fin       TIME,
  salle           VARCHAR(50),
  note            DECIMAL(4,2),
  jury            TEXT,
  consignes       TEXT,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Suivi de stage (étapes)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS suivi_etapes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etudiant_id   INT UNSIGNED NOT NULL,
  etape         ENUM('recherche','convention_signee','debut_stage','rapport_intermediaire','rapport_final','soutenance') NOT NULL,
  statut        ENUM('fait','en_cours','en_attente') DEFAULT 'en_attente',
  date_etape    DATE,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Notifications
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  message       TEXT NOT NULL,
  lue           TINYINT(1) DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Barème de notation
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS bareme (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categorie   VARCHAR(100) NOT NULL,
  critere     VARCHAR(200) NOT NULL,
  points      INT          NOT NULL,
  pourcentage INT          NOT NULL
) ENGINE=InnoDB;

-- =============================================
-- DONNÉES DE DÉMONSTRATION
-- =============================================

-- Comptes utilisateurs (mots de passe à recréer via /register.php)
INSERT IGNORE INTO utilisateurs (id, nom_complet, email, mot_de_passe, role) VALUES
(1, 'Mael Martin',   'etudiant@demo.fr',   '$2y$12$placeholder_etudiant_hash_000000000000000u', 'etudiant'),
(2, 'Prof. Martin',  'enseignant@demo.fr', '$2y$12$placeholder_enseignant_hash_00000000000000u', 'enseignant');

-- Offres de stage
INSERT IGNORE INTO offres (titre, entreprise, lieu, duree, description, categorie, effectif, site_web, places_total, places_disponibles, statut, creee_par) VALUES
('Développeur Web Frontend',   'TechCorp',       'Paris',    '6 mois', 'Stage React & TypeScript. Développement d''interfaces modernes et performantes.',       'Tech',          '250–500', 'techcorp.fr',       3, 2, 'active', 2),
('Développeur Backend',        'CloudSolutions', 'Remote',   '6 mois', 'APIs Node.js & PostgreSQL, environnement cloud-native moderne.',                         'Tech',          '100–250', 'cloudsolutions.io', 4, 1, 'active', 2),
('Data Analyst',               'DataInsight',    'Lyon',     '4 mois', 'Analyse et visualisation de données avec Python & Pandas.',                              'Tech',          '50–100',  'datainsight.fr',    2, 2, 'active', 2),
('Chargé(e) de communication', 'MediaGroup',     'Paris',    '5 mois', 'Rédaction de contenus, gestion réseaux sociaux et relations presse.',                    'Communication', '500+',    'mediagroup.fr',     3, 3, 'active', 2),
('Community Manager',          'AgencePulse',    'Bordeaux', '3 mois', 'Animation des réseaux sociaux, création de contenus visuels et reporting.',              'Communication', '10–50',   'agencepulse.fr',    2, 1, 'active', 2),
('UX/UI Designer',             'PixelStudio',    'Paris',    '3 mois', 'Conception d''interfaces Figma pour des clients dans le secteur digital.',               'Design',        '10–50',   'pixelstudio.fr',    1, 0, 'active', 2),
('Analyste financier junior',  'BNP Consulting', 'Paris',    '6 mois', 'Modélisation financière, analyse de portefeuilles et reporting mensuel.',                 'Finance',       '1000+',   'bnpconsulting.fr',  2, 2, 'active', 2),
('Contrôleur de gestion',      'GestionPro',     'Lyon',     '4 mois', 'Suivi budgétaire, tableaux de bord et analyse des écarts.',                              'Finance',       '100–250', 'gestionpro.fr',     2, 2, 'active', 2);

-- Barème
INSERT IGNORE INTO bareme (categorie, critere, points, pourcentage) VALUES
('Rapport écrit',       'Qualité rédactionnelle',    10, 40),
('Rapport écrit',       'Analyse technique',         15, 40),
('Rapport écrit',       'Présentation générale',     10, 40),
('Rapport écrit',       'Bibliographie',              5, 40),
('Soutenance orale',    'Qualité de la présentation',15, 40),
('Soutenance orale',    'Maîtrise du sujet',         15, 40),
('Soutenance orale',    'Réponses aux questions',    10, 40),
('Évaluation entreprise','Travail réalisé',          10, 20),
('Évaluation entreprise','Comportement professionnel',10,20);
