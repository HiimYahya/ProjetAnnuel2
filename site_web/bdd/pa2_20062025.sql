-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 20 juin 2025 à 13:56
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pa2`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

DROP TABLE IF EXISTS `annonces`;
CREATE TABLE IF NOT EXISTS `annonces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `ville_depart` varchar(100) DEFAULT NULL,
  `ville_arrivee` varchar(100) DEFAULT NULL,
  `date_annonce` date DEFAULT NULL,
  `statut` enum('en attente','prise en charge','livrée','annulée') NOT NULL DEFAULT 'en attente',
  `taille` float DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `date_livraison_souhaitee` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `segmentation_possible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id`, `id_client`, `titre`, `description`, `ville_depart`, `ville_arrivee`, `date_annonce`, `statut`, `taille`, `prix`, `date_livraison_souhaitee`, `date_expiration`, `segmentation_possible`) VALUES
(4, 13, 'test2', 'test2', '38 Avenue Millies Lacroix, Élancourt', '37 boulevard Amiral Courbet, Orvault', '2025-04-10', '', 56, 25.00, '2025-04-21', '2025-04-21', 1),
(6, 13, 'test', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ', '38 Avenue Millies Lacroix, Élancourt', '37 boulevard Amiral Courbet, Orvault	', '2025-04-10', '', 56, 15.00, '2025-04-29', '2025-04-29', 1),
(7, 29, 'Caca de kamil', 'Le caca de kamil comme dit dans le titre ^^', '7 avenue Joffre, 94160 Saint-Mandé', '7 allée soufflot, 92600 Asnières-sur-Seine', '2025-04-17', '', 69, 0.69, '2025-05-02', '2025-04-30', 1),
(8, 26, 'Livraison Test', 'Cadeau d\'anniversaire', '7 allée soufflot, 92600 Asnières-sur-Seine', '69 Place Napoléon, 56600 Lanester', '2025-04-18', '', 52, 20.00, '2025-08-25', '2025-08-25', 1),
(17, 103, 'test isma', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ', '7 allée soufflot, 92600 Asnières-sur-Seine', '8 rue des bleuets, 92250 La Garenne-Colombes', '2025-05-13', 'prise en charge', 12, 13.00, '2025-09-30', '2025-09-30', 1),
(18, 103, 'Chaise', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', '7 allée soufflot, 92600 Asnières-sur-Seine', '224 avenue Georges Clemenceau, 92000 Nanterre', '2025-06-20', '', 198, 15555.00, '2025-08-31', '2025-08-31', 1),
(16, 103, 'test segment 2', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', '7 allée soufflot, 92600 Asnières-sur-Seine', '224 avenue Georges Clemenceau, 92000 Nanterre', '2025-04-25', '', 52, 11.00, '2025-04-30', '2025-04-30', 1);

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_commercant` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commercant` (`id_commercant`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `id_commercant`, `date_debut`, `date_fin`) VALUES
(1, 1, '2025-03-04', '2028-03-15');

-- --------------------------------------------------------

--
-- Structure de la table `livraisons`
--

DROP TABLE IF EXISTS `livraisons`;
CREATE TABLE IF NOT EXISTS `livraisons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_livreur` int NOT NULL,
  `id_annonce` int NOT NULL,
  `date_prise_en_charge` datetime DEFAULT NULL,
  `date_livraison` datetime DEFAULT NULL,
  `statut` enum('en attente','en cours','livrée','annulée') NOT NULL,
  `segment_depart` varchar(255) DEFAULT NULL,
  `segment_arrivee` varchar(255) DEFAULT NULL,
  `segmentation_possible` tinyint(1) NOT NULL DEFAULT '1',
  `validation_client` tinyint(1) NOT NULL DEFAULT '0',
  `reception_confirmee` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = réception confirmée par le client, 0 = non confirmée',
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`),
  KEY `id_livreur` (`id_livreur`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `livraisons`
--

INSERT INTO `livraisons` (`id`, `id_client`, `id_livreur`, `id_annonce`, `date_prise_en_charge`, `date_livraison`, `statut`, `segment_depart`, `segment_arrivee`, `segmentation_possible`, `validation_client`, `reception_confirmee`) VALUES
(12, 103, 104, 11, '2025-04-18 11:34:08', '2025-04-24 11:54:27', 'en cours', NULL, NULL, 1, 0, 0),
(13, 103, 104, 9, '2025-04-18 11:54:15', '2025-04-24 11:54:41', 'en cours', NULL, NULL, 1, 0, 0),
(14, 0, 104, 13, NULL, NULL, '', '7 allée soufflot, 92600 Asnières-sur-Seine', '242 Rue du Faubourg Saint-Antoine, 75012 Paris', 1, 0, 0),
(15, 103, 104, 14, '2025-04-25 12:11:51', NULL, 'en cours', NULL, NULL, 1, 1, 0),
(16, 103, 104, 15, '2025-04-25 12:38:11', NULL, 'en cours', NULL, NULL, 1, 1, 0),
(17, 103, 104, 16, '2025-04-25 12:47:30', NULL, 'en cours', NULL, NULL, 1, 1, 0),
(18, 103, 104, 17, '2025-05-13 17:27:30', NULL, 'en cours', NULL, NULL, 1, 1, 1),
(19, 103, 104, 18, '2025-06-20 11:31:36', NULL, 'en attente', NULL, NULL, 1, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `livraison_segments`
--

DROP TABLE IF EXISTS `livraison_segments`;
CREATE TABLE IF NOT EXISTS `livraison_segments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_livraison` int DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `ville_depart` varchar(255) DEFAULT NULL,
  `ville_arrivee` varchar(255) DEFAULT NULL,
  `id_livreur` int DEFAULT NULL,
  `date_prise_en_charge` datetime DEFAULT NULL,
  `date_livraison` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_livraison` (`id_livraison`),
  KEY `id_livreur` (`id_livreur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,
  `id_utilisateur` int NOT NULL,
  `details` text NOT NULL,
  `date_action` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `logs`
--

INSERT INTO `logs` (`id`, `action`, `id_utilisateur`, `details`, `date_action`) VALUES
(1, 'livraison_point_relais', 104, 'Segment #3 (test segment 2) livré au point relais Relay Point Nantes à Nantes', '2025-05-13 19:54:54'),
(2, 'recuperation_segment', 104, 'Segment #3 (test segment 2) récupéré du point relais Relay Point Nantes (Nantes)', '2025-05-13 19:55:04'),
(3, 'livraison_point_relais', 104, 'Segment #3 (test segment 2) livré au point relais Relay Point Nantes à Nantes', '2025-05-13 19:55:13'),
(4, 'recuperation_segment', 104, 'Segment #3 (test segment 2) récupéré du point relais Relay Point Nantes (Nantes)', '2025-05-22 18:53:04'),
(5, 'livraison_point_relais', 104, 'Segment #3 (test segment 2) livré au point relais Relay Point Nantes à Nantes', '2025-05-22 18:53:11'),
(6, 'livraison_point_relais', 104, 'Segment #4 (Chaise) livré au point relais Relay Point Paris à Paris', '2025-06-20 11:31:58');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `date_creation` datetime NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `id_utilisateur`, `type`, `message`, `date_creation`, `lu`) VALUES
(1, 103, 'segment_point_relais', 'Votre colis \"test segment 2\" a été déposé au point relais Relay Point Nantes à Nantes. Il est maintenant disponible pour être récupéré par un autre livreur qui se chargera de la suite de la livraison.', '2025-05-13 19:54:54', 0),
(2, 103, 'segment_point_relais', 'Votre colis \"test segment 2\" a été déposé au point relais Relay Point Nantes à Nantes. Il est maintenant disponible pour être récupéré par un autre livreur qui se chargera de la suite de la livraison.', '2025-05-13 19:55:13', 0),
(3, 103, 'segment_point_relais', 'Votre colis \"test segment 2\" a été déposé au point relais Relay Point Nantes à Nantes. Il est maintenant disponible pour être récupéré par un autre livreur qui se chargera de la suite de la livraison.', '2025-05-22 18:53:11', 0),
(4, 103, 'segment_point_relais', 'Votre colis \"Chaise\" a été déposé au point relais Relay Point Paris à Paris. Il est maintenant disponible pour être récupéré par un autre livreur qui se chargera de la suite de la livraison.', '2025-06-20 11:31:58', 0);

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(10,2) NOT NULL,
  `methode` enum('carte','paypal','virement','espece') NOT NULL,
  `statut` enum('en attente','effectué','échoué') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `montant`, `methode`, `statut`) VALUES
(7, 1000.00, 'carte', 'effectué'),
(6, 199.20, 'carte', 'effectué'),
(8, 100.00, 'carte', 'effectué'),
(9, 51.00, 'carte', 'effectué');

-- --------------------------------------------------------

--
-- Structure de la table `points_relais`
--

DROP TABLE IF EXISTS `points_relais`;
CREATE TABLE IF NOT EXISTS `points_relais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `coordonnees` varchar(50) NOT NULL COMMENT 'Format: latitude,longitude',
  `horaires` text,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `points_relais`
--

INSERT INTO `points_relais` (`id`, `nom`, `adresse`, `code_postal`, `ville`, `coordonnees`, `horaires`, `date_creation`) VALUES
(1, 'Relay Point Paris', '23 Rue de Rivoli', '75001', 'Paris', '48.8566,2.3522', 'Lun-Ven: 9h-19h, Sam: 10h-18h', '2025-04-25 09:39:56'),
(2, 'Relay Point Lyon', '15 Rue de la République', '69002', 'Lyon', '45.7578,4.8320', 'Lun-Ven: 8h30-19h30, Sam: 9h-18h', '2025-04-25 09:39:56'),
(3, 'Relay Point Marseille', '88 La Canebière', '13001', 'Marseille', '43.2965,5.3698', 'Lun-Ven: 9h-19h, Sam: 9h30-18h30', '2025-04-25 09:39:56'),
(4, 'Relay Point Bordeaux', '45 Rue Sainte-Catherine', '33000', 'Bordeaux', '44.8378,0.5792', 'Lun-Ven: 9h-19h, Sam: 10h-19h', '2025-04-25 09:39:56'),
(5, 'Relay Point Lille', '32 Rue de Béthune', '59000', 'Lille', '50.6292,3.0573', 'Lun-Ven: 8h-19h, Sam: 9h-18h', '2025-04-25 09:39:56'),
(6, 'Relay Point Strasbourg', '18 Place Kléber', '67000', 'Strasbourg', '48.5734,7.7521', 'Lun-Ven: 9h-19h, Sam: 10h-18h', '2025-04-25 09:39:56'),
(7, 'Relay Point Nice', '54 Av. Jean Médecin', '06000', 'Nice', '43.7032,7.2661', 'Lun-Sam: 9h-19h', '2025-04-25 09:39:56'),
(8, 'Relay Point Toulouse', '29 Rue d\'Alsace-Lorraine', '31000', 'Toulouse', '43.6047,1.4442', 'Lun-Ven: 9h-19h, Sam: 10h-18h', '2025-04-25 09:39:56'),
(9, 'Relay Point Nantes', '12 Rue d\'Orléans', '44000', 'Nantes', '47.2173,1.5534', 'Lun-Ven: 9h-19h, Sam: 9h30-18h30', '2025-04-25 09:39:56'),
(10, 'Relay Point Montpellier', '25 Grand Rue Jean Moulin', '34000', 'Montpellier', '43.6108,3.8767', 'Lun-Sam: 9h-19h', '2025-04-25 09:39:56');

-- --------------------------------------------------------

--
-- Structure de la table `prestations`
--

DROP TABLE IF EXISTS `prestations`;
CREATE TABLE IF NOT EXISTS `prestations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `statut` enum('en attente','en cours','terminée','annulée') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `segments`
--

DROP TABLE IF EXISTS `segments`;
CREATE TABLE IF NOT EXISTS `segments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_livraison` int NOT NULL,
  `id_annonce` int NOT NULL,
  `id_livreur` int NOT NULL,
  `adresse_depart` varchar(255) NOT NULL,
  `adresse_arrivee` varchar(255) NOT NULL,
  `point_relais_depart` int DEFAULT NULL COMMENT 'ID du point relais de départ',
  `point_relais_arrivee` int DEFAULT NULL COMMENT 'ID du point relais d''arrivée',
  `statut` enum('en attente','en cours','en point relais','livré','annulé') NOT NULL DEFAULT 'en attente',
  `date_debut` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_annonce` (`id_annonce`),
  KEY `id_livreur` (`id_livreur`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `segments`
--

INSERT INTO `segments` (`id`, `id_livraison`, `id_annonce`, `id_livreur`, `adresse_depart`, `adresse_arrivee`, `point_relais_depart`, `point_relais_arrivee`, `statut`, `date_debut`, `date_fin`) VALUES
(1, 0, 13, 103, 'Paris', 'Lyon', NULL, NULL, 'en cours', '2025-04-18 16:21:51', NULL),
(2, 16, 15, 104, '7 allée soufflot, 92600 Asnières-sur-Seine', '23 Rue de Rivoli, 75001 Paris', NULL, 1, 'en point relais', '2025-04-25 12:38:11', '2025-04-25 12:39:55'),
(3, 17, 16, 0, '12 Rue d\'Orléans, 44000 Nantes', '12 Rue d\'Orléans, 44000 Nantes', 9, 9, 'en point relais', '2025-05-22 18:53:04', '2025-05-22 18:53:11'),
(4, 19, 18, 0, '7 allée soufflot, 92600 Asnières-sur-Seine', '23 Rue de Rivoli, 75001 Paris', NULL, 1, 'en point relais', '2025-06-20 11:31:36', '2025-06-20 11:31:58');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('client','livreur','commercant','prestataire','admin') NOT NULL,
  `validation_identite` enum('en_attente','validee','refusee') DEFAULT NULL,
  `piece_identite` varchar(255) DEFAULT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `photo_profil` varchar(255) DEFAULT NULL,
  `adresse` text,
  `statut` enum('disponible','en livraison','indisponible') DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `role`, `validation_identite`, `piece_identite`, `date_inscription`, `photo_profil`, `adresse`, `statut`, `type`, `service`) VALUES
(105, 'admin@admin.com', 'admin@admin.com', '$2y$10$23ApYFbm1ob2DaNOm2mDLuHzj3inCtka94yzbmuVsZ1buneeqYMqq', 'admin', NULL, NULL, '2025-04-24 16:17:07', NULL, NULL, NULL, NULL, NULL),
(103, 'client@client.com', 'client@client.com', '$2y$10$twg6Rz9/2LKYMTYJ2Kb/8OpgG.Ezf4DmwfP.QH0lvA15z6zXIY6sW', 'client', NULL, NULL, '2025-04-18 11:07:52', 'photo_6855286e9f8ee.png', '69 rue du zizi', NULL, NULL, NULL),
(104, 'livreur@livreur.com', 'livreur@livreur.com', '$2y$10$RBOhnJjblW9XxdR3IajW9eB5T9fArI/3q6BQyMSKhwb9Gp/kRyGG6', 'livreur', 'validee', 'identite_104_6823817b79fd4.jpg', '2025-04-18 11:16:13', NULL, NULL, NULL, NULL, NULL),
(106, 'livreurtest@livreurtest.com', 'livreurtest@livreurtest.com', '$2y$10$vKjgtOwK95FdoYyYqKMikujj6tv05e26oRXu34xXvt4ZQWQl/ZLG.', 'livreur', 'validee', 'identite_106_68237cd5cff04.jpg', '2025-05-13 19:09:30', NULL, NULL, NULL, NULL, NULL),
(107, 'testlivreur@testlivreur.com', 'testlivreur@testlivreur.com', '$2y$10$V6bhki.NTR1HGE1V1sdDR.3QwHJPt4xQKd9hMLBIaoIjyatacI.cS', 'livreur', 'validee', 'identite_107_68237d5fd7019.jpg', '2025-05-13 19:11:45', NULL, NULL, NULL, NULL, NULL),
(108, 'MOCHMI API', 'mochmix@mochmix.com', '$2y$10$0yGdVV4S4bI3uLRdyIZiiuBLJa5c88h7cSL.k9KJs18Cq3F7d9W7u', 'livreur', 'validee', 'identite_108_685526caf3eb3.jpg', '2025-06-20 11:15:32', NULL, '', NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
