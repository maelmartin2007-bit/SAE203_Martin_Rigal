-- =============================================
-- migration_v2.sql — Mise à jour base existante
-- À exécuter si vous avez déjà installé le projet
-- =============================================

USE stages_db;

-- Ajout des colonnes manquantes dans offres
ALTER TABLE offres
  ADD COLUMN IF NOT EXISTS categorie          VARCHAR(50)  DEFAULT 'Tech'   AFTER description,
  ADD COLUMN IF NOT EXISTS effectif           VARCHAR(50)                    AFTER categorie,
  ADD COLUMN IF NOT EXISTS site_web           VARCHAR(100)                   AFTER effectif,
  ADD COLUMN IF NOT EXISTS places_total       INT          DEFAULT 1         AFTER site_web,
  ADD COLUMN IF NOT EXISTS places_disponibles INT          DEFAULT 1         AFTER places_total;

-- Mise à jour des offres existantes
UPDATE offres SET categorie='Tech', places_total=3, places_disponibles=2 WHERE id=1;
UPDATE offres SET categorie='Tech', places_total=4, places_disponibles=1 WHERE id=2;
UPDATE offres SET categorie='Tech', places_total=2, places_disponibles=2 WHERE id=3;

-- Nouvelles offres (Communication, Design, Finance)
INSERT IGNORE INTO offres (titre,entreprise,lieu,duree,description,categorie,effectif,site_web,places_total,places_disponibles,statut,creee_par)
SELECT 'Chargé(e) de communication','MediaGroup','Paris','5 mois','Rédaction de contenus, gestion réseaux sociaux.','Communication','500+','mediagroup.fr',3,3,'active',id
FROM utilisateurs WHERE role='enseignant' LIMIT 1;

INSERT IGNORE INTO offres (titre,entreprise,lieu,duree,description,categorie,effectif,site_web,places_total,places_disponibles,statut,creee_par)
SELECT 'Community Manager','AgencePulse','Bordeaux','3 mois','Animation des réseaux sociaux, création de contenus.','Communication','10–50','agencepulse.fr',2,1,'active',id
FROM utilisateurs WHERE role='enseignant' LIMIT 1;

INSERT IGNORE INTO offres (titre,entreprise,lieu,duree,description,categorie,effectif,site_web,places_total,places_disponibles,statut,creee_par)
SELECT 'UX/UI Designer','PixelStudio','Paris','3 mois','Conception d''interfaces Figma pour des clients.','Design','10–50','pixelstudio.fr',1,0,'active',id
FROM utilisateurs WHERE role='enseignant' LIMIT 1;

INSERT IGNORE INTO offres (titre,entreprise,lieu,duree,description,categorie,effectif,site_web,places_total,places_disponibles,statut,creee_par)
SELECT 'Analyste financier junior','BNP Consulting','Paris','6 mois','Modélisation financière et reporting mensuel.','Finance','1000+','bnpconsulting.fr',2,2,'active',id
FROM utilisateurs WHERE role='enseignant' LIMIT 1;

INSERT IGNORE INTO offres (titre,entreprise,lieu,duree,description,categorie,effectif,site_web,places_total,places_disponibles,statut,creee_par)
SELECT 'Contrôleur de gestion','GestionPro','Lyon','4 mois','Suivi budgétaire et tableaux de bord.','Finance','100–250','gestionpro.fr',2,2,'active',id
FROM utilisateurs WHERE role='enseignant' LIMIT 1;
