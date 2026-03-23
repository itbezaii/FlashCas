-- ============================================================
-- Script SQL — ESPACTU
-- Projet Final Backend — ESP Département Génie Informatique
-- ============================================================
 
-- Création de la base
CREATE DATABASE IF NOT EXISTS projet_news
    CHARACTER SET utf8
    COLLATE utf8_general_ci;
 
USE projet_news;
 
-- ============================================================
-- TABLE CATEGORIES
-- (créée avant articles car articles en dépend)
-- ============================================================
CREATE TABLE categories (
    id  INT          AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);
 
-- ============================================================
-- TABLE UTILISATEURS
-- ============================================================
CREATE TABLE utilisateurs (
    id           INT          AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(100) NOT NULL,
    prenom       VARCHAR(100) NOT NULL,
    login        VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role         ENUM('editeur', 'administrateur') NOT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
 
-- ============================================================
-- TABLE ARTICLES
-- ============================================================
CREATE TABLE articles (
    id               INT          AUTO_INCREMENT PRIMARY KEY,
    titre            VARCHAR(255) NOT NULL,
    contenu          TEXT         NOT NULL,
    date_publication DATETIME     DEFAULT CURRENT_TIMESTAMP,
    categorie_id     INT,
    auteur_id        INT,
 
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
 
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);
 
-- ============================================================
-- DONNÉES INITIALES — CATÉGORIES
-- ============================================================
INSERT INTO categories (nom) VALUES
('Technologie'),
('Sport'),
('Politique'),
('Education'),
('Culture');
 
-- ============================================================
-- DONNÉES INITIALES — UTILISATEURS
-- Mots de passe hashés avec password_hash($mdp, PASSWORD_DEFAULT)
--
-- admin        → mot de passe : admin123
-- editeur1     → mot de passe : editeur123
-- ============================================================
INSERT INTO utilisateurs (nom, prenom, login, mot_de_passe, role) VALUES
(
    'Diallo',
    'Mamadou',
    'admin',
    '$2y$10$TKh8H1.PfznU4E2GbxnkKuDp0M/ByHLPfpkfBR0sznbWQJzJ6rXJW',
    'administrateur'
),
(
    'Ndiaye',
    'Fatou',
    'editeur1',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'editeur'
);
 
-- ============================================================
-- DONNÉES INITIALES — ARTICLES
-- ============================================================
INSERT INTO articles (titre, contenu, date_publication, categorie_id, auteur_id) VALUES
(
    'Lancement du nouveau campus numérique de l\'ESP',
    'L\'École Supérieure Polytechnique a inauguré ce lundi son nouveau campus numérique entièrement équipé de salles connectées, de laboratoires de dernière génération et d\'un espace de coworking ouvert aux étudiants et aux startups. Ce projet, fruit d\'un partenariat public-privé, vise à renforcer la formation dans les métiers du numérique et à accompagner la transformation digitale du Sénégal.',
    '2026-03-15 09:00:00',
    1,
    2
),
(
    'Les Lions du Sénégal se préparent pour la CAN 2027',
    'La sélection nationale de football a entamé son stage de préparation à Saly en vue des prochaines qualifications pour la Coupe d\'Afrique des Nations 2027. Le sélectionneur a convoqué un groupe de 25 joueurs dont plusieurs nouvelles têtes évoluant en Europe. Les premières séances d\'entraînement se sont déroulées dans une bonne ambiance selon le staff technique.',
    '2026-03-16 10:30:00',
    2,
    2
),
(
    'Réforme du système éducatif : ce qui va changer',
    'Le ministère de l\'Éducation nationale a présenté les grandes lignes de la réforme du système éducatif sénégalais. Parmi les mesures phares : la généralisation de l\'enseignement du code informatique dès le primaire, la révision des programmes de mathématiques et de sciences, ainsi que la mise en place d\'un nouveau système d\'évaluation continue. La réforme entrera en vigueur à la rentrée prochaine.',
    '2026-03-17 08:00:00',
    4,
    2
),
(
    'Festival de Jazz de Dakar : une édition record',
    'La 15e édition du Festival International de Jazz de Dakar a accueilli plus de 50 000 spectateurs sur trois jours. Des artistes venus de 20 pays ont animé les scènes du Monument de la Renaissance et de la Place du Souvenir Africain. Le public a particulièrement applaudi la fusion jazz-sabar proposée par plusieurs groupes locaux, confirmant le rayonnement culturel de Dakar sur la scène internationale.',
    '2026-03-18 14:00:00',
    5,
    2
),
(
    'Sénégal : croissance économique de 8% prévue pour 2026',
    'Le Fonds Monétaire International a revu à la hausse ses prévisions de croissance pour le Sénégal, tablant désormais sur un taux de 8% pour l\'année 2026. Cette performance serait portée par le démarrage de la production pétrolière et gazière, ainsi que par les investissements massifs dans les infrastructures. Le gouvernement salue ces prévisions tout en appelant à une gestion rigoureuse des ressources.',
    '2026-03-19 11:00:00',
    3,
    2
);