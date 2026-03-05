-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 12:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maternia`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `created_at`, `user_id`, `event_id`) VALUES
(24, '2026-03-04 16:02:43', 1, 24),
(25, '2026-03-04 16:04:09', 6, 24);

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `date_commande` datetime NOT NULL,
  `statut` varchar(50) NOT NULL,
  `total` double NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(2) DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `shipping_carrier` varchar(60) DEFAULT NULL,
  `shipping_eta_days` int(11) DEFAULT NULL,
  `shipping_tracking` varchar(100) DEFAULT NULL,
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commande_produit`
--

CREATE TABLE `commande_produit` (
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consultation`
--

CREATE TABLE `consultation` (
  `id` int(11) NOT NULL,
  `categorie` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `pour` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `statut` tinyint(4) NOT NULL,
  `ordre_affichage` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation`
--

INSERT INTO `consultation` (`id`, `categorie`, `description`, `pour`, `image`, `icon`, `statut`, `ordre_affichage`, `created_at`, `updated_at`) VALUES
(3, 'Pédiatrie générale', 'Consultation complète pour le suivi de la croissance et de la santé des enfants de 0 à 12 ans.', 'BEBE', 'pediatrie-generale.jpg', 'fas fa-baby', 1, 1, '2026-02-12 19:28:06', '2026-02-12 19:28:06'),
(4, 'Nutrition infantile', 'Conseils spécialisés pour l\'alimentation de bébé : diversification alimentaire, allergies, intolérances et troubles du comportement alimentaire.', 'BEBE', 'nutrition-infantile.jpg', 'fas fa-apple-alt', 1, 2, '2026-02-12 19:29:53', '2026-02-12 19:29:53'),
(5, 'Dermatologie pédiatrique', 'Traitement des problèmes de peau chez l\'enfant : eczéma, érythème fessier, allergies cutanées.', 'BEBE', 'dermatologie.jpg', 'fas fa-allergies', 1, 3, '2026-02-12 19:31:52', '2026-02-12 19:31:52'),
(6, 'Psychologie périnatale', 'Accompagnement psychologique pour futures et jeunes mamans : baby-blues, dépression post-partum, anxiété.', 'MAMAN', 'psychologie.jpg', 'fas fa-brain', 1, 1, '2026-02-12 19:33:27', '2026-02-12 19:33:43'),
(7, 'Gynécologie', 'Consultations de routine, suivi gynécologique, contraception, dépistage et traitement des infections.', 'MAMAN', 'gynecologie.jpg', 'fas fa-female', 1, 2, '2026-02-12 19:34:51', '2026-02-12 19:34:51'),
(8, 'Préparation à l\'accouchement', 'Séances de préparation à la naissance : exercices, respiration, gestion de la douleur.', 'MAMAN', 'preparation.jpg', 'fas fa-dumbbell', 1, 3, '2026-02-12 19:36:13', '2026-02-12 19:36:13'),
(9, 'Consultation couple parental', 'Accompagnement des parents pour harmoniser l\'éducation et la communication familiale.', 'LES_DEUX', 'ouple-parental.jp', 'fas fa-users', 1, 1, '2026-02-12 19:37:05', '2026-02-12 19:37:05'),
(10, 'Allaitement maternel', 'Soutien et conseils pour l\'allaitement maternel : techniques, difficultés, sevrage et solutions aux problèmes courants.', 'LES_DEUX', 'fou.jpg', 'fas fa-child', 1, 2, '2026-02-12 19:38:14', '2026-02-12 19:38:14'),
(11, 'validation1', 'rui\'r', 'MAMAN', 'kjr', 'rfh', 1, 2, '2026-02-13 11:41:33', '2026-02-13 11:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_creneau`
--

CREATE TABLE `consultation_creneau` (
  `id` int(11) NOT NULL,
  `nom_medecin` varchar(100) NOT NULL,
  `photo_medecin` varchar(255) DEFAULT NULL,
  `description_medecin` longtext DEFAULT NULL,
  `specialite_medecin` varchar(100) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `jour` date DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `statut_reservation` varchar(20) NOT NULL,
  `duree_minutes` int(11) DEFAULT NULL,
  `nombre_places` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `consultation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation_creneau`
--

INSERT INTO `consultation_creneau` (`id`, `nom_medecin`, `photo_medecin`, `description_medecin`, `specialite_medecin`, `date_debut`, `date_fin`, `jour`, `heure_debut`, `heure_fin`, `statut_reservation`, `duree_minutes`, `nombre_places`, `created_at`, `updated_at`, `consultation_id`) VALUES
(5, 'Dr.Semi Trabelsi', 'tom.jpg', 'Pédiatre depuis 15 ans, ancien chef de clinique à l\'hôpital d\'enfants. Spécialisé en néonatalogie.', 'Gynécologue', '2026-02-05 11:00:00', '2026-02-05 11:30:00', '2026-02-05', '11:00:00', '11:30:00', 'DISPONIBLE', 30, 1, '2026-02-12 19:56:24', '2026-02-13 10:45:12', 3),
(6, 'Dr. Samira Toumi', 'images_698e231ca72d99.18964737.jpg', 'Spécialiste en croissance infantile et troubles du développement. Formatrice en puériculture.', 'Pédiatre', '2026-02-05 11:55:00', '2026-02-05 12:25:00', '2026-02-05', '11:55:00', '12:25:00', 'RESERVE', 30, 1, '2026-02-12 19:59:40', '2026-02-12 19:59:40', 3),
(7, 'Dr. Samira Toumi', 'images_698e231ca72d99.18964737.jpg', 'Spécialiste en croissance infantile et troubles du développement. Formatrice en puériculture.', 'Pédiatre', '2026-02-05 12:00:00', '2026-02-05 12:30:00', '2026-02-05', '12:00:00', '12:30:00', 'DISPONIBLE', 30, 1, '2026-02-12 19:59:40', '2026-02-12 19:59:40', 3),
(8, 'Dr. Samira Toumi', 'images_698e231ca72d99.18964737.jpg', 'Spécialiste en croissance infantile et troubles du développement. Formatrice en puériculture.', 'Pédiatre', '2026-02-05 13:00:00', '2026-02-05 13:30:00', '2026-02-05', '13:00:00', '13:30:00', 'DISPONIBLE', 30, 1, '2026-02-12 19:59:40', '2026-02-12 19:59:40', 3),
(10, 'Dr. Leila Mansouri', 'images.jpg', 'Chef du service maternité, spécialiste en grossesses à risque et échographies morphologiques. 20 ans d\'expérience.', 'Gynécologue-obstétricienne', '2026-02-06 12:00:00', '2026-02-06 12:30:00', '2026-02-06', '12:00:00', '12:30:00', 'DISPONIBLE', 30, 1, '2026-02-13 00:55:15', '2026-02-13 00:56:23', 7),
(11, 'rttr', 'images (1).jpg', 'zekjez', 'r\'lktr', '2026-02-06 12:00:00', '2026-02-06 12:30:00', '2026-02-06', '12:00:00', '12:30:00', 'DISPONIBLE', 30, 1, '2026-02-13 01:01:23', '2026-02-13 01:01:23', 10),
(12, 'dr mariem', 'images (1).jpg', 'dejkr', 'gynecologue', '2026-02-27 12:00:00', '2026-02-27 12:30:00', '2026-02-27', '12:00:00', '12:30:00', 'RESERVE', 30, 1, '2026-02-13 01:21:48', '2026-02-13 01:21:48', 6),
(13, 'erio', 'HoodieSweatshirtMockupTemplate-MadewithPosterMyWall_698de298270c52.13131185.jpg', 'delke', NULL, '2026-02-07 12:00:00', '2026-02-07 12:30:00', '2026-02-07', '12:00:00', '12:30:00', 'DISPONIBLE', 30, 1, '2026-02-13 10:36:11', '2026-02-13 10:36:48', 9),
(14, 'jbhub', 'Capturedcran2026-02-26231312_69a0e50d0f24e3.83663757.png', 'hbvjvhvjhv', 'hbhb', '2026-02-28 12:00:00', '2026-02-28 12:30:00', '2026-02-28', '12:00:00', '12:30:00', 'RESERVE', 30, 1, '2026-02-27 01:27:57', '2026-02-27 01:27:57', 10),
(15, 'zreqgrg', 'Capturedcran2026-02-26231312_69a0e5bb2c8b24.23898129.png', 'afqezgbse', 'GERGRG', '2026-02-28 12:00:00', '2026-02-28 12:30:00', '2026-02-28', '12:00:00', '12:30:00', 'RESERVE', 30, 1, '2026-02-27 01:30:51', '2026-02-27 01:30:51', 4),
(16, 'dr test infinies', NULL, 'tres bon', 'Pediatre', '2026-03-06 00:00:00', '2026-03-06 12:30:00', '2026-03-06', '00:00:00', '12:30:00', 'DISPONIBLE', 30, 7, '2026-03-04 16:17:40', '2026-03-04 16:22:47', 9),
(17, 'dr test infinies', NULL, 'tres bon', 'Pediatre', '2026-03-19 12:00:00', '2026-03-19 00:30:00', '2026-03-19', '12:00:00', '00:30:00', 'RESERVE', 30, 7, '2026-03-04 16:22:47', '2026-03-04 16:22:47', 9);

-- --------------------------------------------------------

--
-- Table structure for table `demande_baby_sitter`
--

CREATE TABLE `demande_baby_sitter` (
  `id` int(11) NOT NULL,
  `nom_parent` varchar(255) NOT NULL,
  `email_parent` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `date_demande` datetime NOT NULL,
  `statut` varchar(50) NOT NULL,
  `offre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260208184607', '2026-02-12 10:48:29', 73),
('DoctrineMigrations\\Version20260211170607', '2026-02-12 10:48:29', 10),
('DoctrineMigrations\\Version20260211170629', '2026-02-12 10:48:29', 12);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_weekly` tinyint(4) NOT NULL,
  `capacity` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_outdoor` tinyint(4) NOT NULL DEFAULT 0,
  `event_cat_id` int(11) NOT NULL,
  `creator_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id`, `title`, `description`, `start_at`, `end_at`, `location`, `image`, `is_weekly`, `capacity`, `day_of_week`, `start_time`, `end_time`, `is_outdoor`, `event_cat_id`, `creator_id`) VALUES
(1, 'sqdsqd', 'qsdsqdsqddfgdfgsdfgfsdg', '2026-02-05 12:00:00', '2026-02-10 12:00:00', 'Rue de Bassora, Hedi Chaker, Délégation Bab Bhar, Tunis, 1075, Tunisia', '10.jpg', 0, 19, NULL, NULL, NULL, 0, 5, 6),
(2, 'test event new', 'tes test etstetdstdgsydugtiufdfd', '2026-02-27 10:00:00', '2026-02-28 12:00:00', 'Sidi Saad, Délégation Mornag, Ben Arous, Tunisia', '3.webp', 0, 30, NULL, NULL, NULL, 1, 5, 6),
(24, 'sqdsqdfgfdg', 'dfgfdgdfgfdgdgdfgdgsgdf', '2026-02-26 12:00:00', '2026-03-05 12:00:00', 'Rue de Tozeur, Hedi Chaker, Délégation Bab Bhar, Tunis, 1075, Tunisia', '1.jpg', 0, 25, NULL, NULL, NULL, 1, 5, 1),
(25, 'gfdgdfghhfg', 'hfgdhdgfhfgdhgfdhgfdhfgdhgfh', '2026-03-12 12:00:00', '2026-03-13 12:00:00', 'Monoprix, Rue du Koweït, Lafayette, Hedi Chaker, Délégation Bab Bhar, Tunis, 1075, Tunisia', '2.jpg', 0, 23, NULL, NULL, NULL, 1, 5, 1),
(26, 'fdsgfdgfdsgfdsgfds', 'fdgfdsgfdgdgsddfgdgdfg', '2026-03-06 16:01:00', '2026-03-16 16:01:00', 'Franceville, El Omrane, Délégation El Omrane, Tunis, 1075, Tunisia', '1.jpg', 0, 28, NULL, NULL, NULL, 1, 9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `event_cat`
--

CREATE TABLE `event_cat` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_cat`
--

INSERT INTO `event_cat` (`id`, `name`, `description`) VALUES
(5, 'Ateliers bébé', 'Ateliers pour stimuler le développement du bébé'),
(6, 'Séances maternité', 'Activités et formations pour futures mamans'),
(7, 'Nutrition & Santé', 'Conférences et conseils sur l’alimentation et la santé'),
(8, 'Psychologie & Bien-être', 'Séances de soutien psychologique pour mamans et parents'),
(9, 'Activités familiales', 'Événements ludiques pour toute la famille'),
(10, 'Vaccinations & Consultations', 'Campagnes de vaccination et suivi médical'),
(11, 'Rencontre entre mamans', 'Événements et rencontres permettant aux mamans d’échanger, partager leurs expériences et créer des liens.'),
(13, 'Développement de l’enfant', NULL),
(14, 'Éducation & Parentalité', NULL),
(15, 'Urgences & Premiers secours', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_requirement`
--

CREATE TABLE `event_requirement` (
  `event_id` int(11) NOT NULL,
  `requirement_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_requirement`
--

INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(24, 1),
(24, 2),
(25, 2),
(25, 4),
(26, 2),
(26, 3),
(26, 6);

-- --------------------------------------------------------

--
-- Table structure for table `grosesse`
--

CREATE TABLE `grosesse` (
  `id` int(11) NOT NULL,
  `connait_ddr` tinyint(4) NOT NULL DEFAULT 0,
  `date_dernieres_regles` date DEFAULT NULL,
  `date_debut_grossesse` date DEFAULT NULL,
  `statut_grossesse` varchar(50) NOT NULL,
  `type_grossesse` varchar(50) NOT NULL,
  `poids_actuel` double DEFAULT NULL,
  `symptomes` longtext DEFAULT NULL,
  `nausee` tinyint(4) NOT NULL DEFAULT 0,
  `vomissement` tinyint(4) NOT NULL DEFAULT 0,
  `saignement` tinyint(4) NOT NULL DEFAULT 0,
  `fievre` tinyint(4) NOT NULL DEFAULT 0,
  `douleur_abdominale` tinyint(4) NOT NULL DEFAULT 0,
  `fatigue` tinyint(4) NOT NULL DEFAULT 0,
  `vertiges` tinyint(4) NOT NULL DEFAULT 0,
  `indice_risque` double DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL,
  `date_accouchement_reelle` date DEFAULT NULL,
  `nombre_bebes` int(11) DEFAULT NULL,
  `nom_bebe` varchar(200) DEFAULT NULL,
  `sexe_bebe` varchar(10) DEFAULT NULL,
  `poids_naissance` double DEFAULT NULL,
  `taille_naissance` double DEFAULT NULL,
  `etat_naissance` varchar(100) DEFAULT NULL,
  `commentaire_general` longtext DEFAULT NULL,
  `bebes` longtext DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `date_mise_ajour` datetime NOT NULL,
  `maman_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grosesse`
--

INSERT INTO `grosesse` (`id`, `connait_ddr`, `date_dernieres_regles`, `date_debut_grossesse`, `statut_grossesse`, `type_grossesse`, `poids_actuel`, `symptomes`, `nausee`, `vomissement`, `saignement`, `fievre`, `douleur_abdominale`, `fatigue`, `vertiges`, `indice_risque`, `risk_level`, `date_accouchement_reelle`, `nombre_bebes`, `nom_bebe`, `sexe_bebe`, `poids_naissance`, `taille_naissance`, `etat_naissance`, `commentaire_general`, `bebes`, `date_creation`, `date_mise_ajour`, `maman_id`) VALUES
(1, 1, '2026-02-04', NULL, 'enCours', 'simple', 88, NULL, 1, 0, 0, 0, 0, 0, 0, NULL, 'unknown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 01:02:28', '2026-02-27 01:02:28', 1);

-- --------------------------------------------------------

--
-- Table structure for table `maman`
--

CREATE TABLE `maman` (
  `id` int(11) NOT NULL,
  `numero_urgence` varchar(30) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `groupe_sanguin` varchar(20) NOT NULL,
  `allergies` longtext DEFAULT NULL,
  `antecedents_medicaux` longtext DEFAULT NULL,
  `poids` double NOT NULL,
  `taille` double NOT NULL,
  `maladies_chroniques` longtext DEFAULT NULL,
  `medicaments_actuels` longtext DEFAULT NULL,
  `fumeur` tinyint(4) NOT NULL,
  `consommation_alcool` tinyint(4) NOT NULL,
  `niveau_activite_physique` varchar(50) NOT NULL,
  `habitudes_alimentaires` varchar(100) NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_mise_ajour` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maman`
--

INSERT INTO `maman` (`id`, `numero_urgence`, `email`, `date_naissance`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `poids`, `taille`, `maladies_chroniques`, `medicaments_actuels`, `fumeur`, `consommation_alcool`, `niveau_activite_physique`, `habitudes_alimentaires`, `date_creation`, `date_mise_ajour`) VALUES
(1, '54423368', 'malekbensassi321@gmail.com', NULL, 'A+', NULL, NULL, 80, 170, NULL, NULL, 1, 1, 'Léger', 'tous', '2026-02-27 01:01:53', '2026-02-27 01:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:1245:\\\"<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <style>\n        body { font-family: \\\'Helvetica Neue\\\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }\n        h1 { color: #f06292; }\n        .footer { margin-top: 30px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }\n    </style>\n</head>\n<body>\n    <div class=\\\"header\\\" style=\\\"text-align: center; margin-bottom: 30px;\\\">\n        <img src=\\\"https://maternia.com/logo.png\\\" alt=\\\"Maternia\\\" style=\\\"height: 50px;\\\">\n    </div>\n    \n        <h1>Confirmation de participation</h1>\n    <p>Bonjour adembenayed10@gmail.com,</p>\n    <p>Nous vous confirmons votre participation à l\\\'événement suivant :</p>\n    <div style=\\\"background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;\\\">\n        <h2 style=\\\"margin-top: 0;\\\">test1</h2>\n        <p><strong>Date :</strong> \n                            Tous les Tuesday à 12:00\n                    </p>\n        <p><strong>Lieu :</strong> esprit</p>\n    </div>\n    <p>Nous avons hâte de vous y voir !</p>\n    <p>L\\\'équipe Maternia</p>\n\n    <div class=\\\"footer\\\">\n        <p>&copy; 2026 Maternia. Tous droits réservés.</p>\n    </div>\n</body>\n</html>\n\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:21:\\\"no-reply@maternia.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:23:\\\"adembenayed10@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:37:\\\"Confirmation de participation : test1\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-02-23 21:22:07', '2026-02-23 21:22:07', NULL),
(2, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:1216:\\\"<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <style>\n        body { font-family: \\\'Helvetica Neue\\\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }\n        h1 { color: #f06292; }\n        .footer { margin-top: 30px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }\n    </style>\n</head>\n<body>\n    <div class=\\\"header\\\" style=\\\"text-align: center; margin-bottom: 30px;\\\">\n        <img src=\\\"https://maternia.com/logo.png\\\" alt=\\\"Maternia\\\" style=\\\"height: 50px;\\\">\n    </div>\n    \n        <h1>Nouveau participant !</h1>\n    <p>Bonjour adem@gmail.com,</p>\n    <p>Une nouvelle personne s\\\'est inscrite à votre événement <strong>test1</strong>.</p>\n    <div style=\\\"background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;\\\">\n        <p><strong>Participant :</strong> adembenayed10@gmail.com</p>\n        <p><strong>Date d\\\'inscription :</strong> 23/02/2026 à 22:22</p>\n    </div>\n    <p>Vous pouvez consulter la liste des participants sur le tableau de bord.</p>\n    <p>L\\\'équipe Maternia</p>\n\n    <div class=\\\"footer\\\">\n        <p>&copy; 2026 Maternia. Tous droits réservés.</p>\n    </div>\n</body>\n</html>\n\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:21:\\\"no-reply@maternia.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:14:\\\"adem@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:50:\\\"Nouveau participant pour votre événement : test1\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-02-23 21:22:07', '2026-02-23 21:22:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `offre_baby_sitter`
--

CREATE TABLE `offre_baby_sitter` (
  `id` int(11) NOT NULL,
  `nom_babysitter` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `experience` int(11) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `tarif` double NOT NULL,
  `description` longtext NOT NULL,
  `disponibilite` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offre_baby_sitter`
--

INSERT INTO `offre_baby_sitter` (`id`, `nom_babysitter`, `telephone`, `experience`, `ville`, `tarif`, `description`, `disponibilite`) VALUES
(1, 'Salma souki', '55720820', 2, 'Mahdia', 30, 'Tres bien Excellent', 1),
(5, 'Salima moukted', '45679543', 2, 'Nabeul', 21, 'Je veux un travail avec serieusite', 1),
(7, 'Malek Ben Sassiiiii', '96510796', 22, 'Kairouan', 0.9, 'Une femme est très bien dans le domaine de babysitting.', 1),
(10, 'imen', '11111111', 10, 'Jendouba', 10, 'jjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjj', 1);

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `prix` double NOT NULL,
  `stock` int(11) NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `poids_kg` double DEFAULT NULL,
  `sku` varchar(64) DEFAULT NULL,
  `rating_average` double DEFAULT NULL,
  `rating_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produit`
--

INSERT INTO `produit` (`id`, `nom`, `description`, `prix`, `stock`, `categorie`, `image_name`, `poids_kg`, `sku`, `rating_average`, `rating_count`) VALUES
(2, 'AAA', 'BIEN', 58, 1, NULL, NULL, NULL, NULL, NULL, 0),
(4, 'Coque de grossesse', 'Confort et maintient pour le ventre en croissance', 29.9, 20, NULL, NULL, NULL, NULL, NULL, 0),
(5, 'Kit naissance', 'Essentiels pour les premiers jous de bébé', 49, 1, NULL, NULL, NULL, NULL, NULL, 0),
(6, 'Sucette', 'Bien', 5, 2, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `promo_code`
--

CREATE TABLE `promo_code` (
  `id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requirement`
--

CREATE TABLE `requirement` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirement`
--

INSERT INTO `requirement` (`id`, `name`) VALUES
(1, 'Tapis de yoga'),
(2, 'Bouteille d\'eau'),
(3, 'Tenue confortable'),
(4, 'Serviette'),
(5, 'Coussin'),
(6, 'Carnet de notes'),
(7, 'Biberon'),
(8, 'Poussette'),
(9, 'Lingettes'),
(10, 'Couches');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_client`
--

CREATE TABLE `reservation_client` (
  `id` int(11) NOT NULL,
  `nom_client` varchar(100) NOT NULL,
  `prenom_client` varchar(100) NOT NULL,
  `email_client` varchar(100) NOT NULL,
  `telephone_client` varchar(20) NOT NULL,
  `type_patient` varchar(20) NOT NULL,
  `mois_grossesse` int(11) DEFAULT NULL,
  `date_naissance_bebe` date DEFAULT NULL,
  `statut_reservation` varchar(50) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `date_reservation` datetime NOT NULL,
  `notes` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `consultation_creneau_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_client`
--

INSERT INTO `reservation_client` (`id`, `nom_client`, `prenom_client`, `email_client`, `telephone_client`, `type_patient`, `mois_grossesse`, `date_naissance_bebe`, `statut_reservation`, `reference`, `date_reservation`, `notes`, `created_at`, `updated_at`, `consultation_creneau_id`) VALUES
(4, 'sboui', 'montaha', 'sms@gc', '12345678', 'MAMAN', 5, NULL, 'CONFIRME', 'RDV-698EFF88BBC16', '2026-02-13 11:40:08', 'sjkdhjf', '2026-02-13 11:40:08', '2026-02-13 11:40:08', 6),
(5, 'ben ayed', 'adem', 'mohamedadem.benayed@esprit.tn', '97076060', 'MAMAN', 6, NULL, 'CONFIRME', 'RDV-69A0E120880C3', '2026-02-27 01:11:12', '🤖 BILAN IA DES SYMPTÔMES:\n📊 État Général Observé\nSur la base de l\'analyse faciale réalisée, le patient présente une expression neutre dominante. L\'indice de bien-être global est estimé à 8/10. L\'état général semble satisfaisant, ce qui est important à prendre en compte lors de la consultation.\n---\n🔍 Signes Détectés\n- 😴 Indice de fatigue: 1/10\n- 😰 Niveau de stress perçu: 2/10\n- 🌡️ Teint et expression: Expression neutre dominante (40%)\n- 💆 Tension visible: Faible\n---\n📈 Indicateurs de Bien-être\n| Indicateur | Score | Niveau |\n|---|---|---|\n| 😌 Bien-être général | 8/10 | ████░ |\n| 😰 Stress perçu | 2/10 | █░░░░ |\n| 😴 Fatigue | 1/10 | █░░░░ |\n---\n💡 Recommandations Personnalisées\n- 🧘 Pratiquer 10-15 min de relaxation ou respiration profonde quotidiennement\n- 💊 S\'assurer de prendre les suppléments prescrits (acide folique, fer, calcium)\n- 💧 Boire au moins 2L d\'eau par jour pour rester bien hydratée\n- 🚶 Marche légère de 20-30 min par jour si possible\n- 😴 Viser 8-9h de sommeil par nuit et des siestes si nécessaire\n---\n🩺 Points à Aborder avec le Médecin\n- 📊 Résultats des dernières analyses sanguines (6e mois de grossesse)\n- 🩸 Pression artérielle et surveillance de l\'œdème\n- 🤰 Suivi du développement fœtal au 6e mois\n- 💊 Ajustement des suppléments et médications\n---\n> ⚠️ Important : Ce bilan est généré par l\'IA Maternia à partir d\'indicateurs visuels. Il est indicatif uniquement et ne remplace en aucun cas un diagnostic médical professionnel. Veuillez discuter de ces observations avec votre médecin lors de la consultation.\n*Analyse générée le 27/02/2026 à 01:10:47 — Maternia AI*', '2026-02-27 01:11:12', '2026-02-27 01:11:12', 12),
(6, 'gqqrg', 'hig', 'mohamedadem.benayed@esprit.tn', '97076060', 'MAMAN', 6, NULL, 'CONFIRME', 'RDV-69A0E55A4CE68', '2026-02-27 01:29:14', 'gzfqrgzrg\n\n🤖 BILAN IA DES SYMPTÔMES:\n📊 État Général Observé\nSur la base de l\'analyse faciale réalisée, le patient présente une expression neutre dominante. L\'indice de bien-être global est estimé à 8/10. L\'état général semble satisfaisant, ce qui est important à prendre en compte lors de la consultation.\n---\n🔍 Signes Détectés\n- 😴 Indice de fatigue: 2/10\n- 😰 Niveau de stress perçu: 2/10\n- 🌡️ Teint et expression: Expression neutre dominante (40%)\n- 💆 Tension visible: Faible\n---\n📈 Indicateurs de Bien-être\n| Indicateur | Score | Niveau |\n|---|---|---|\n| 😌 Bien-être général | 8/10 | ████░ |\n| 😰 Stress perçu | 2/10 | █░░░░ |\n| 😴 Fatigue | 2/10 | █░░░░ |\n---\n💡 Recommandations Personnalisées\n- 🧘 Pratiquer 10-15 min de relaxation ou respiration profonde quotidiennement\n- 💊 S\'assurer de prendre les suppléments prescrits (acide folique, fer, calcium)\n- 💧 Boire au moins 2L d\'eau par jour pour rester bien hydratée\n- 🚶 Marche légère de 20-30 min par jour si possible\n- 😴 Viser 8-9h de sommeil par nuit et des siestes si nécessaire\n---\n🩺 Points à Aborder avec le Médecin\n- 📊 Résultats des dernières analyses sanguines (6e mois de grossesse)\n- 🩸 Pression artérielle et surveillance de l\'œdème\n- 🤰 Suivi du développement fœtal au 6e mois\n- 💊 Ajustement des suppléments et médications\n---\n> ⚠️ Important : Ce bilan est généré par l\'IA Maternia à partir d\'indicateurs visuels. Il est indicatif uniquement et ne remplace en aucun cas un diagnostic médical professionnel. Veuillez discuter de ces observations avec votre médecin lors de la consultation.\n*Analyse générée le 27/02/2026 à 01:29:06 — Maternia AI*', '2026-02-27 01:29:14', '2026-02-27 01:29:14', 14),
(7, 'afzqger', 'ZQEFGRSE', 'sboui.montaha@esprit.tn', '97076060', 'MAMAN', 5, NULL, 'CONFIRME', 'RDV-69A0E6116B7A7', '2026-02-27 01:32:17', 'zeqrsgt', '2026-02-27 01:32:17', '2026-02-27 01:32:17', 15),
(8, 'Ben Sassi', 'Malek', 'malekbensassi321@gmail.com', '96510796', 'MAMAN', 4, NULL, 'CONFIRME', 'RDV-69A84D51CC63A', '2026-03-04 16:18:41', 'testesttest', '2026-03-04 16:18:41', '2026-03-04 16:18:41', 16),
(9, 'Ben Sassi', 'Malek', 'malek.bensassi@esprit.tn', '96510796', 'MAMAN', 6, NULL, 'CONFIRME', 'RDV-69A84E7DA42CC', '2026-03-04 16:23:41', 'ttttttrertretretertertertertert', '2026-03-04 16:23:41', '2026-03-04 16:23:41', 17);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) DEFAULT NULL,
  `nom` varchar(35) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `facial_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `nom`, `prenom`, `type`, `facial_id`) VALUES
(1, 'admin@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Admin', 'Maternia', 'ADMIN', NULL),
(6, 'malekbensassi321@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$q/xVNxeD3gkzDdFwrMJeyec4HRqHavKtrLdQtLhclZq97SJsMIly6', 'malek', 'admin', 'ADMIN', NULL),
(7, 'plumungum@gmail.com', '[\"ROLE_MAMAN\"]', '$2y$13$NwfiAEjDzqynutPSGWbkr.g2QLK0x1VnEeLjGQOqNSMrQE3XbVhLK', 'malekmalek', 'bensassisassi', 'MAMAN', 'face_69a1644129e3d_6df52422');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_ATTENDANCE` (`user_id`,`event_id`),
  ADD KEY `IDX_6DE30D91A76ED395` (`user_id`),
  ADD KEY `IDX_6DE30D9171F7E88B` (`event_id`);

--
-- Indexes for table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commande_email` (`email`),
  ADD KEY `idx_commande_statut` (`statut`),
  ADD KEY `idx_commande_date` (`date_commande`);

--
-- Indexes for table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD PRIMARY KEY (`commande_id`,`produit_id`),
  ADD KEY `IDX_DF1E9E8782EA2E54` (`commande_id`),
  ADD KEY `IDX_DF1E9E87F347EFB` (`produit_id`);

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consultation_creneau`
--
ALTER TABLE `consultation_creneau`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BC657ABD62FF6CDF` (`consultation_id`);

--
-- Indexes for table `demande_baby_sitter`
--
ALTER TABLE `demande_baby_sitter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E09A060E4CC8505A` (`offre_id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3BAE0AA7D434DE11` (`event_cat_id`),
  ADD KEY `IDX_3BAE0AA761220EA6` (`creator_id`);

--
-- Indexes for table `event_cat`
--
ALTER TABLE `event_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_requirement`
--
ALTER TABLE `event_requirement`
  ADD PRIMARY KEY (`event_id`,`requirement_id`),
  ADD KEY `IDX_70B686D071F7E88B` (`event_id`),
  ADD KEY `IDX_70B686D07B576F77` (`requirement_id`);

--
-- Indexes for table `grosesse`
--
ALTER TABLE `grosesse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E0AEFE8351CCF339` (`maman_id`);

--
-- Indexes for table `maman`
--
ALTER TABLE `maman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maman_email` (`email`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `offre_baby_sitter`
--
ALTER TABLE `offre_baby_sitter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produit_categorie` (`categorie`),
  ADD KEY `idx_produit_sku` (`sku`);

--
-- Indexes for table `promo_code`
--
ALTER TABLE `promo_code`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_PROMO_CODE_CODE` (`code`);

--
-- Indexes for table `requirement`
--
ALTER TABLE `requirement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservation_client`
--
ALTER TABLE `reservation_client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8FB54DCE43CFAC64` (`consultation_creneau_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `consultation_creneau`
--
ALTER TABLE `consultation_creneau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `demande_baby_sitter`
--
ALTER TABLE `demande_baby_sitter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `event_cat`
--
ALTER TABLE `event_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `grosesse`
--
ALTER TABLE `grosesse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `maman`
--
ALTER TABLE `maman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offre_baby_sitter`
--
ALTER TABLE `offre_baby_sitter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `produit`
--
ALTER TABLE `produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promo_code`
--
ALTER TABLE `promo_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requirement`
--
ALTER TABLE `requirement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reservation_client`
--
ALTER TABLE `reservation_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `FK_6DE30D9171F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_6DE30D91A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD CONSTRAINT `FK_DF1E9E8782EA2E54` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_DF1E9E87F347EFB` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consultation_creneau`
--
ALTER TABLE `consultation_creneau`
  ADD CONSTRAINT `FK_BC657ABD62FF6CDF` FOREIGN KEY (`consultation_id`) REFERENCES `consultation` (`id`);

--
-- Constraints for table `demande_baby_sitter`
--
ALTER TABLE `demande_baby_sitter`
  ADD CONSTRAINT `FK_E09A060E4CC8505A` FOREIGN KEY (`offre_id`) REFERENCES `offre_baby_sitter` (`id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_3BAE0AA761220EA6` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_3BAE0AA7D434DE11` FOREIGN KEY (`event_cat_id`) REFERENCES `event_cat` (`id`);

--
-- Constraints for table `event_requirement`
--
ALTER TABLE `event_requirement`
  ADD CONSTRAINT `FK_70B686D071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_70B686D07B576F77` FOREIGN KEY (`requirement_id`) REFERENCES `requirement` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grosesse`
--
ALTER TABLE `grosesse`
  ADD CONSTRAINT `FK_E0AEFE8351CCF339` FOREIGN KEY (`maman_id`) REFERENCES `maman` (`id`);

--
-- Constraints for table `reservation_client`
--
ALTER TABLE `reservation_client`
  ADD CONSTRAINT `FK_8FB54DCE43CFAC64` FOREIGN KEY (`consultation_creneau_id`) REFERENCES `consultation_creneau` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
