-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 31, 2025 at 11:06 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_stagiaires`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id_document` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int NOT NULL,
  `type_document` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `date_ajout` datetime NOT NULL,
  PRIMARY KEY (`id_document`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id_document`, `id_etudiant`, `type_document`, `nom_fichier`, `date_ajout`) VALUES
(53, 79, 'Fiche Etudiant', 'fiche_79_20250718_003717.pdf', '2025-07-18 00:00:00'),
(55, 81, 'Fiche Etudiant', 'fiche_81_20250718_014829.pdf', '2025-07-18 00:00:00'),
(52, 78, 'Fiche Etudiant', 'fiche_78_20250718_003625.pdf', '2025-07-18 00:00:00'),
(51, 77, 'Fiche Etudiant', 'fiche_77_20250718_003048.pdf', '2025-07-18 00:00:00'),
(49, 75, 'Fiche Etudiant', 'fiche_75_20250718_002903.pdf', '2025-07-18 00:00:00'),
(50, 76, 'Fiche Etudiant', 'fiche_76_20250718_002946.pdf', '2025-07-18 00:00:00'),
(48, 74, 'Fiche Etudiant', 'fiche_74_20250718_002757.pdf', '2025-07-18 00:00:00'),
(47, 73, 'Fiche Etudiant', 'fiche_73_20250718_002452.pdf', '2025-07-18 00:00:00'),
(45, 71, 'Fiche Etudiant', 'fiche_71_20250718_001506.pdf', '2025-07-18 00:00:00'),
(46, 72, 'Fiche Etudiant', 'fiche_72_20250718_002127.pdf', '2025-07-18 00:00:00'),
(54, 80, 'Fiche Etudiant', 'fiche_80_20250718_014013.pdf', '2025-07-18 00:00:00'),
(43, 68, 'Fiche Etudiant', 'fiche_68_20250717_230550.pdf', '2025-07-17 00:00:00'),
(56, 83, 'Fiche Etudiant', 'fiche_83_20250718_015245.pdf', '2025-07-18 00:00:00'),
(57, 84, 'Fiche Etudiant', 'fiche_84_20250718_020628.pdf', '2025-07-18 00:00:00'),
(58, 85, 'Fiche Etudiant', 'fiche_85_20250718_020711.pdf', '2025-07-18 00:00:00'),
(59, 86, 'Fiche Etudiant', 'fiche_86_20250718_020754.pdf', '2025-07-18 00:00:00'),
(60, 87, 'Fiche Etudiant', 'fiche_87_20250718_020831.pdf', '2025-07-18 00:00:00'),
(61, 88, 'Fiche Etudiant', 'fiche_88_20250718_020924.pdf', '2025-07-18 00:00:00'),
(62, 89, 'Fiche Etudiant', 'fiche_89_20250718_021019.pdf', '2025-07-18 00:00:00'),
(64, 90, 'Fiche Etudiant', 'fiche_90_20250719_112520.pdf', '2025-07-19 00:00:00'),
(65, 91, 'Fiche Etudiant', 'fiche_91_20250719_114132.pdf', '2025-07-19 00:00:00'),
(67, 89, 'Lettre d\'affectation', '687b97aa21433.png', '2025-07-19 13:03:38'),
(68, 92, 'Fiche Etudiant', 'fiche_92_20250720_105906.pdf', '2025-07-20 00:00:00'),
(69, 94, 'Fiche Etudiant', 'fiche_94_20250720_185445.pdf', '2025-07-20 00:00:00'),
(70, 95, 'Fiche Etudiant', 'fiche_95_20250720_194100.pdf', '2025-07-20 00:00:00'),
(71, 89, 'Attestation de stage', '687f88a176ec8.doc', '2025-07-22 12:48:33'),
(72, 96, 'Fiche Etudiant', 'fiche_96_20250722_133313.pdf', '2025-07-22 13:33:12'),
(73, 97, 'Fiche Etudiant', 'fiche_97_20250723_231819.pdf', '2025-07-23 23:18:18'),
(74, 97, 'Copie de CIN', '68820d605a97d.jpg', '2025-07-24 10:39:28'),
(75, 97, 'Rapport de stage', '68820dae299ab.docx', '2025-07-24 10:40:46'),
(76, 97, 'Convention de stage', '68820ef0663d6.jpg', '0000-00-00 00:00:00'),
(77, 98, 'Fiche Etudiant', 'fiche_98_20250728_220002.pdf', '2025-07-28 22:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id_etudiant` int NOT NULL AUTO_INCREMENT,
  `cin` varchar(20) DEFAULT NULL,
  `delivre_cin` date DEFAULT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `telephone_pere` varchar(20) DEFAULT NULL,
  `adress` text,
  `date_naissance` date DEFAULT NULL,
  `situation_familiale` enum('Célibataire','Marié(e)') DEFAULT 'Célibataire',
  `nationalite` varchar(50) DEFAULT NULL,
  `contact_urgence` varchar(100) DEFAULT NULL,
  `etablissement_scolaire` varchar(100) DEFAULT NULL,
  `niveau_scolaire` varchar(50) DEFAULT NULL,
  `duree_scolaire` varchar(20) DEFAULT NULL,
  `diplome` varchar(100) DEFAULT NULL,
  `date_diplome` date DEFAULT NULL,
  PRIMARY KEY (`id_etudiant`),
  UNIQUE KEY `cin` (`cin`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `etudiants`
--

INSERT INTO `etudiants` (`id_etudiant`, `cin`, `delivre_cin`, `nom`, `prenom`, `email`, `telephone`, `telephone_pere`, `adress`, `date_naissance`, `situation_familiale`, `nationalite`, `contact_urgence`, `etablissement_scolaire`, `niveau_scolaire`, `duree_scolaire`, `diplome`, `date_diplome`) VALUES
(25, 'CD234567', '0000-00-00', 'Benjelloun', 'Fatima', 'fatima.benjelloun@email.com', '0623456789', '0522234567', '45 Avenue Mohammed V, Casablanca', '1999-03-22', 'Célibataire', 'Marocaine', '0634567890', 'Lycée Lyautey', 'Bac+3', '3 ans', 'Licence', '2021-07-15'),
(26, 'EF345678', '0000-00-00', 'Cherkaoui', 'Karim', 'karim.cherkaoui@email.com', '0634567890', '0537345678', '78 Rue Oued Zem, Rabat', '1997-11-10', 'Célibataire', 'Marocaine', '0645678901', 'Lycée Moulay Youssef', 'Bac+4', '4 ans', 'Master 1', '2022-06-20'),
(27, 'GH456789', '0000-00-00', 'Daoudi', 'Amina', 'amina.daoudi@email.com', '0645678901', '0524456789', '32 Rue de la Koutoubia, Marrakech', '2000-07-30', 'Célibataire', 'Marocaine', '0656789012', 'Lycée Victor Hugo', 'Bac+5', '5 ans', 'Master 2', '2023-07-10'),
(28, 'IJ567890', '0000-00-00', 'El Fassi', 'Youssef', 'youssef.elfassi@email.com', '0656789012', '0539567890', '10 Boulevard Pasteur, Tanger', '1998-09-05', 'Célibataire', 'Marocaine', '0667890123', 'Lycée Regnault', 'Bac+1', '1 an', 'Bac', '2019-06-25'),
(29, 'KL678901', '0000-00-00', 'Gharbi', 'Leila', 'leila.gharbi@email.com', '0667890123', '0535678901', '56 Avenue des FAR, Fès', '1999-12-18', 'Célibataire', 'Marocaine', '0678901234', 'Lycée Moulay Idriss', 'Bac+2', '2 ans', 'DEUG', '2020-07-05'),
(30, 'MN789012', '0000-00-00', 'Hassani', 'Omar', 'omar.hassani@email.com', '0678901234', '0535789012', '22 Rue Sidi Bouzekri, Meknès', '1997-04-25', 'Célibataire', 'Marocaine', '0689012345', 'Lycée Paul Valéry', 'Bac+3', '3 ans', 'Licence', '2021-06-30'),
(31, 'OP890123', '0000-00-00', 'Idrissi', 'Samira', 'samira.idrissi@email.com', '0689012345', '0536890123', '14 Rue Al Mansour, Oujda', '2000-08-12', 'Célibataire', 'Marocaine', '0690123456', 'Lycée Omar Ibn Abdelaziz', 'Bac+4', '4 ans', 'Master 1', '2022-07-15'),
(32, 'QR901234', '0000-00-00', 'Jabri', 'Adil', 'ilyesrejeb12@gmail.com', '0690123456', '0537901234', '8 Rue des Orangers, Sal', '1998-02-28', 'Célibataire', 'Marocaine', '0611234567', 'Lycée Imam Malik / chennini', 'Primaire + Secondaire', '5 ans / 5', 'Master 2', '2023-07-20'),
(33, 'ST012345', '0000-00-00', 'Khaldi', 'Nadia', 'nadia.khaldi@email.com', '0611234567', '0538012345', '5 Avenue Mohammed VI, Tétouan', '1999-06-08', 'Célibataire', 'Marocaine', '0622345678', 'Lycée Moulay Hassan', 'Bac+1', '1 an', 'Bac', '2019-06-15'),
(34, 'UV123456', '0000-00-00', 'Lamrani', 'Hicham', 'hicham.lamrani@email.com', '0622345678', '0539123456', '17 Rue Ibn Khaldoun, Kenitra', '1997-10-15', 'Célibataire', 'Marocaine', '0633456789', 'Lycée Descartes', 'Bac+2', '2 ans', 'DEUG', '2020-06-28'),
(35, 'WX234567', '0000-00-00', 'Mouline', 'Sanaa', 'sanaa.mouline@email.com', '0633456789', '0520234567', '9 Avenue Moulay Rachid, Safi', '2000-01-20', 'Célibataire', 'Marocaine', '0644567890', 'Lycée Colbert', 'Bac+3', '3 ans', 'Licence', '2021-07-10'),
(36, 'YZ345678', '0000-00-00', 'Naciri', 'Rachid', 'rachid.naciri@email.com', '0644567890', '0521345678', '3 Rue Ibn Batouta, El Jadida', '1998-07-03', 'Célibataire', 'Marocaine', '0655678901', 'Lycée Mohammed V', 'Bac+4', '4 ans', 'Master 1', '2022-06-25'),
(37, 'AB456789', '0000-00-00', 'Ouazzani', 'Fatima Zahra', 'fatimazahra.ouazzani@email.com', '0655678901', '0522456789', '12 Avenue Hassan II, Béni Mellal', '1999-09-14', 'Célibataire', 'Marocaine', '0666789012', 'Lycée Ibn Toumart', 'Bac+5', '5 ans', 'Master 2', '2023-07-05'),
(38, 'CD567890', '0000-00-00', 'Pacha', 'Mehdi', 'mehdi.pacha@email.com', '0666789012', '0523567890', '7 Rue des Mines, Khouribga', '1997-12-05', 'Célibataire', 'Marocaine', '0677890123', 'Lycée Al Khansaa', 'Bac+1', '1 an', 'Bac', '2019-06-20'),
(39, 'EF678901', '0000-00-00', 'Qasmi', 'Yasmina', 'yasmina.qasmi@email.com', '0677890123', '0524678901', '21 Avenue Mohammed V, Nador', '2000-04-18', 'Célibataire', 'Marocaine', '0688901234', 'Lycée Al Qods', 'Bac+2', '2 ans', 'DEUG', '2020-07-12'),
(40, 'GH789012', '0000-00-00', 'Rahmouni', 'Khalid', 'khalid.rahmouni@email.com', '0688901234', '0525789012', '4 Rue Sidi Ahmed, Taza', '1998-08-22', 'Célibataire', 'Marocaine', '0699012345', 'Lycée Ibn Al Khatib', 'Bac+3', '3 ans', 'Licence', '2021-06-15'),
(41, 'IJ890123', '0000-00-00', 'Saidi', 'Zineb', 'zineb.saidi@email.com', '0699012345', '0526890123', '15 Avenue Moulay Ismail, Settat', '1999-02-10', 'Célibataire', 'Marocaine', '0610123456', 'Lycée Al Khawarizmi', 'Bac+4', '4 ans', 'Master 1', '2022-07-01'),
(42, 'KL901234', '0000-00-00', 'Tazi', 'Anas', 'anas.tazi@email.com', '0610123456', '0527901234', '28 Rue Ibn Rochd, Berrechid', '1997-05-30', 'Célibataire', 'Marocaine', '0621234567', 'Lycée Al Jabr', 'Bac+5', '5 ans', 'Master 2', '2023-07-18'),
(43, 'MN012345', '0000-00-00', 'Ulmari', 'Hafsa', 'hafsa.ulmari@email.com', '0621234567', '0528012345', '6 Rue de la Plage, Larache', '2000-11-25', 'Célibataire', 'Marocaine', '0632345678', 'Lycée Al Amal', 'Bac+1', '1 an', 'Bac', '2019-06-10'),
(44, 'OP123456', '0000-00-00', 'Vazir', 'Amine', 'amine.vazir@email.com', '0632345678', '0529123456', '19 Avenue Hassan I, Ksar El Kebir', '1998-03-08', 'Célibataire', 'Marocaine', '0643456789', 'Lycée Ibn Zaydoun', 'Bac+2', '2 ans', 'DEUG', '2020-06-22'),
(45, 'QR234567', '0000-00-00', 'Wahabi', 'Souad', 'souad.wahabi@email.com', '0643456789', '0520234567', '11 Rue Ibn Tachfine, Guelmim', '1999-07-19', 'Célibataire', 'Marocaine', '0654567890', 'Lycée Al Massira', 'Bac+3', '3 ans', 'Licence', '2021-07-05'),
(46, 'ST345678', '0000-00-00', 'Xalil', 'Yassin', 'yassin.xalil@email.com', '0654567890', '0521345678', '2 Avenue Mohammed VI, Dakhla', '1997-01-12', 'Célibataire', 'Marocaine', '0665678901', 'Lycée Al Wahda', 'Bac+4', '4 ans', 'Master 1', '2022-06-18'),
(47, 'UV456789', '0000-00-00', 'Yousfi', 'Naima', 'naima.yousfi@email.com', '0665678901', '0522456789', '8 Rue Al Moukawama, Laâyoune', '2000-10-05', 'Célibataire', 'Marocaine', '0676789012', 'Lycée Al Qods', 'Bac+5', '5 ans', 'Master 2', '2023-07-12'),
(48, 'WX567890', '0000-00-00', 'Zeroual', 'Bilal', 'bilal.zeroual@email.com', '0676789012', '0523567890', '13 Avenue Moulay Ali Cherif, Errachidia', '1998-04-30', 'Célibataire', 'Marocaine', '0687890123', 'Lycée Al Massira', 'Bac+1', '1 an', 'Bac', '2019-06-28'),
(49, 'YZ678901', '0000-00-00', 'Ait', 'Khadija', 'rejeb424@gmail.com', '0687890123', '0524678901', '25 Rue Ibn Khaldoun, Taroudant', '1999-09-15', 'Célibataire', 'Marocaine', '0698901234', 'Lycée Al Amal / ', 'Primaire + Secondaire', '2 ans / ', 'DEUG', '2020-07-08'),
(51, 'CD890123', '0000-00-00', 'Chafik', 'Meryem', 'meryem.chafik@email.com', '0619012345', '0526890123', '14 Rue des Cèdres, Azrou', '2000-02-14', 'Célibataire', 'Marocaine', '0620123456', 'Lycée Tarik Ibn Ziad', 'Bac+4', '4 ans', 'Master 1', '2022-07-08'),
(52, 'EF901234', '0000-00-00', 'Dahmani', 'Othmane', 'othmane.dahmani@email.com', '0620123456', '0527901234', '3 Avenue Mohammed V, Midelt', '1998-06-07', 'Célibataire', 'Marocaine', '0631234567', 'Lycée Al Wahda', 'Bac+5', '5 ans', 'Master 2', '2023-07-25'),
(53, 'GH012345', '0000-00-00', 'El Amrani', 'Houda', 'houda.elamrani@email.com', '0631234567', '0528012345', '9 Rue Ibn Sina, Sidi Kacem', '1999-11-20', 'Célibataire', 'Marocaine', '0642345678', 'Lycée Al Qods', 'Bac+1', '1 an', 'Bac', '2019-06-15'),
(54, 'IJ123456', '0000-00-00', 'Fassi', 'Adnan', 'adnan.fassi@email.com', '0642345678', '0529123456', '6 Avenue Moulay Hassan, Sidi Slimane', '1997-03-25', 'Célibataire', 'Marocaine', '0653456789', 'Lycée Ibn Rochd', 'Bac+2', '2 ans', 'DEUG', '2020-07-01'),
(55, 'KL234567', '0000-00-00', 'Ghanmi', 'Samira', 'samira.ghanmi@email.com', '0653456789', '0520234567', '11 Rue Al Andalous, Ouazzane', '2000-08-08', 'Célibataire', 'Marocaine', '0664567890', 'Lycée Al Massira', 'Bac+3', '3 ans', 'Licence', '2021-06-28'),
(56, 'MN345678', '0000-00-00', 'Haddadi', 'Tarik', 'tarik.haddadi@email.com', '0664567890', '0521345678', '4 Avenue des FAR, Sefrou', '1998-01-17', 'Célibataire', 'Marocaine', '0675678901', 'Lycée Al Amal', 'Bac+4', '4 ans', 'Master 1', '2022-07-12'),
(57, 'OP456789', '0000-00-00', 'Idrissi', 'Nadia', 'nadia.idrissi@email.com', '0675678901', '0522456789', '17 Rue Mohammed V, Berkane', '1999-05-22', 'Célibataire', 'Marocaine', '0686789012', 'Lycée Al Khawarizmi', 'Bac+5', '5 ans', 'Master 2', '2023-07-30'),
(58, 'QR567890', '0000-00-00', 'Jabri', 'Mehdi', 'mehdi.jabri@email.com', '0686789012', '0523567890', '8 Avenue Hassan II, Taourirt', '1997-10-10', 'Célibataire', 'Marocaine', '0697890123', 'Lycée Tarik Ibn Ziad', 'Bac+1', '1 an', 'Bac', '2019-06-25'),
(59, 'ST678901', '0000-00-00', 'Khalil', 'Fatima', 'fatima.khalil@email.com', '0697890123', '0524678901', '2 Rue Al Qods, Figuig', '2000-12-03', 'Célibataire', 'Marocaine', '0618901234', 'Lycée Al Wahda', 'Bac+2', '2 ans', 'DEUG', '2020-07-15'),
(60, 'UV789012', '0000-00-00', 'Lahbabi', 'Karim', 'karim.lahbabi@email.com', '0618901234', '0525789012', '5 Avenue Mohammed VI, Boulemane', '1998-04-19', 'Célibataire', 'Marocaine', '0629012345', 'Lycée Al Qods', 'Bac+3', '3 ans', 'Licence', '2021-07-08'),
(61, 'WX890123', '0000-00-00', 'Moujahid', 'Amina', 'amina.moujahid@email.com', '0629012345', '0526890123', '13 Rue des Cèdres, Ifrane', '1999-09-28', 'Célibataire', 'Marocaine', '0630123456', 'Lycée Al Massira', 'Bac+4', '4 ans', 'Master 1', '2022-06-30'),
(62, 'YZ901234', '0000-00-00', 'Naji', 'Younes', 'younes.naji@email.com', '0630123456', '0527901234', '7 Rue Hassan I, Chefchaouen', '1997-02-14', 'Célibataire', 'Marocaine', '0641234567', 'Lycée Al Amal', 'Bac+5', '5 ans', 'Master 2', '2023-07-22'),
(63, 'AB012345', '0000-00-00', 'Ouali', 'Sara', 'sara.ouali@email.com', '0641234567', '0528012345', '9 Avenue Mohammed V, Tetouan', '2000-07-07', 'Célibataire', 'Marocaine', '0652345678', 'Lycée Al Khawarizmi', 'Bac+1', '1 an', 'Bac', '2019-06-18'),
(64, 'CD123456', '0000-00-00', 'Qadiri', 'Hamza', 'hamza.qadiri@email.com', '0652345678', '0529123456', '12 Rue Ibn Batouta, Larache', '1998-11-30', 'Célibataire', 'Marocaine', '0663456789', 'Lycée Tarik Ibn Ziad', 'Bac+2', '2 ans', 'DEUG', '2020-07-22'),
(65, 'EF234567', '0000-00-00', 'Rahali', 'Kawtar', 'kawtar.rahali@email.com', '0663456789', '0520234567', '3 Avenue de la Plage, Asilah', '1999-03-12', 'Célibataire', 'Marocaine', '0674567890', 'Lycée Al Wahda', 'Bac+3', '3 ans', 'Licence', '2021-06-15'),
(66, 'GH345678', '0000-00-00', 'Sefrioui', 'Adil', 'adil.sefrioui@email.com', '0674567890', '0521345678', '18 Rue de la Liberté, Tangier', '1997-08-25', 'Célibataire', 'Marocaine', '0685678901', 'Lycée Al Qods', 'Bac+4', '4 ans', 'Master 1', '2022-07-05'),
(67, 'IJ456789', '0000-00-00', 'Toumi', 'Nabila', 'nabila.toumi@email.com', '0685678901', '0522456789', '6 Avenue Moulay Hassan, Tetouan', '2000-01-08', 'Célibataire', 'Marocaine', '0696789012', 'Lycée Al Massira', 'Bac+5', '5 ans', 'Master 2', '2023-07-15'),
(91, '03369665', '2025-07-16', 'Ahmed', 'ALi', 'rejeb424@gmail.com', '27704669', '69321658', 'rue farhet', '2025-07-16', 'Célibataire', 'tunisienne', 'kamel', 'chenini / chennini', 'Primaire + Secondaire', '3 / 5', 'Baccalaureat Informatique', '2025-07-16'),
(94, '85214796', '2025-07-12', 'amine', 'rejeb', 'test7578586@gmail.com', '69321658', '69321658', 'lac2 hay bastyin', '2025-07-08', 'Célibataire', 'tunisienne', 'kamel', 'chenini / chennini', 'Primaire + Secondaire', '3 / 5', 'Baccalaureat Informatique', '2025-07-09'),
(95, '03369781', '2025-07-24', 'mohmd', 'alowi', 'rejeb424@gmail.com', '69321658', '69321658', 'lac2 hay bastyin', '2025-07-09', 'Célibataire', 'tunisienne', 'kamel', 'chenini / chennini', 'Primaire + Secondaire', '3 / 5', 'Baccalaureat Informatique', '2025-07-25'),
(97, '11223344', '2024-02-14', 'Rejeb', 'Ilyes', 'ilyesrejeb12@gmail.com', '11111111', '22222222', 'chenini gabes ', '2004-12-07', 'Célibataire', 'tunisienne', 'kamel', 'chenini / chennini', 'Primaire + Secondaire', '3 / 5', 'Baccalaureat Informatique', '2024-06-23'),
(98, '12345677', '2025-07-22', 'test', 'test', 'ilyesrejeb12@gmail.com', '27704669', '69321658', 'Rue Nooman', '2025-07-01', 'Célibataire', 'tunisienne', 'kamel', 'chenini / gabes', 'Primaire + Secondaire', '3 / 4', 'Baccalaureat Informatique', '2025-07-09');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id_eval` int NOT NULL AUTO_INCREMENT,
  `id_stage` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text,
  PRIMARY KEY (`id_eval`),
  KEY `id_stage` (`id_stage`)
) ;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id_eval`, `id_stage`, `date`, `note`, `commentaire`) VALUES
(1, 54, '2025-07-18', 3, 'bien'),
(2, 57, '2025-07-18', 20, 'bien'),
(6, 61, '2025-07-22', 20, 'Bien'),
(4, 51, '2025-07-19', 10, 'bien'),
(5, 52, '2025-07-20', 20, ''),
(7, 63, '2025-07-28', 20, '............');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `action` text,
  `date_action` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=491 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id_log`, `id_user`, `action`, `date_action`) VALUES
(140, 2, 'Connexion au système', '2025-07-14 11:50:12'),
(139, 2, 'Ajoute un Stage', '2025-07-14 11:38:24'),
(138, 2, 'Modfie le Donne de Stagiér ilyes rejeb', '2025-07-14 11:37:16'),
(137, 2, 'Connexion au système', '2025-07-14 11:36:31'),
(136, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:35:54'),
(135, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:35:12'),
(134, 2, 'Modfie le Donne de Stagiér ilyes rejeb', '2025-07-14 11:34:57'),
(133, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:29:31'),
(132, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:27:27'),
(131, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:09:04'),
(130, 2, 'Connexion au système', '2025-07-14 11:08:53'),
(129, 2, 'Modfie le presences de Stagiér rejeb _ ilyes ', '2025-07-14 11:03:50'),
(128, 2, 'Modfie le presences de Stagiér rejeb _ ilyes ', '2025-07-14 11:03:19'),
(127, 2, 'Connexion au système', '2025-07-14 11:00:28'),
(126, 3, 'Modfie le presences de Stagiér rejeb _ ilyes ', '2025-07-14 10:59:56'),
(125, 3, 'Modfie le presences de Stagiér rejeb _ ilyes ', '2025-07-14 10:59:00'),
(124, 3, 'Connexion au système', '2025-07-14 10:58:19'),
(123, 3, 'Connexion au système', '2025-07-14 10:37:36'),
(122, 3, 'Connexion au système', '2025-07-14 10:15:04'),
(121, 1, 'Téléchargement du Copie de CIN ', '2025-07-14 09:52:56'),
(120, 1, 'Téléchargement du Copie de CIN ', '2025-07-14 09:51:01'),
(119, 1, 'Téléchargement du Lettre d\'affectation ', '2025-07-14 09:49:24'),
(118, 1, 'Ajoute le presences a Stagiér ahmed ', '2025-07-14 09:47:16'),
(117, 1, 'Ajoute un Stage', '2025-07-14 09:47:10'),
(116, 1, 'Modfie le presences de Stagiér Omar _ Karray ', '2025-07-14 09:46:38'),
(115, 1, 'Modfie le Donne de Stagiér ahmed ahmed', '2025-07-14 09:36:07'),
(114, 1, 'Ajoute un Stagéier', '2025-07-14 00:00:00'),
(113, 1, 'Supprime de Stagiér Martin Sophie', '2025-07-14 09:34:12'),
(112, 1, 'Supprime de Stagiér Dupont Jean', '2025-07-14 09:34:10'),
(111, 1, 'Connexion au système', '2025-07-14 09:24:37'),
(110, 1, 'Téléchargement du Attestation de stage ', '2025-07-14 09:05:39'),
(109, 1, 'Ajoute le presences a Stagiér ilyes ', '2025-07-14 09:01:50'),
(108, 1, 'Ajoute un Stage', '2025-07-14 09:01:37'),
(106, 2, 'Connexion au système', '2025-07-11 10:52:20'),
(107, 1, 'Connexion au système', '2025-07-14 08:27:32'),
(105, 2, 'Ajoute un Stage', '2025-07-10 10:49:30'),
(104, 2, 'Modfie le Donne de Stagiér Dupont Jean', '2025-07-10 10:44:50'),
(102, 2, 'Téléchargement du Lettre d\'affectation ', '2025-07-10 09:44:31'),
(103, 2, 'Modfie le stage', '2025-07-10 09:49:31'),
(101, 2, 'Modfie le presences de Stagiér rejeb _ ilyes ', '2025-07-10 09:42:47'),
(100, 2, 'Ajoute le presences a Stagiér Martin ', '2025-07-10 09:42:19'),
(99, 2, 'Modfie le Donne de Stagiér ilyes rejeb', '2025-07-10 09:28:56'),
(98, 2, 'Connexion au système', '2025-07-10 09:28:02'),
(97, 2, 'Connexion au système', '2025-07-10 09:27:35'),
(96, 1, 'Connexion au système', '2025-07-10 09:27:20'),
(141, 2, 'Modifie la présence de Stagiaire rejeb ilyes', '2025-07-14 11:50:33'),
(142, 2, 'Connexion au système', '2025-07-14 11:51:56'),
(143, 2, 'Modfie le Donne de Stagiér ilyes rejeb', '2025-07-14 11:52:13'),
(144, 2, 'Ajout d\'un nouveau stage', '2025-07-14 12:05:26'),
(145, 2, 'Ajout d\'un nouveau stage', '2025-07-14 12:06:11'),
(146, 2, 'Ajout d\'un nouveau stage', '2025-07-14 12:07:12'),
(147, 2, 'Ajout d\'un nouveau stage', '2025-07-14 12:07:33'),
(148, 2, 'Connexion au système', '2025-07-14 12:08:34'),
(149, 2, 'Ajout d\'un nouveau stage', '2025-07-14 12:08:53'),
(150, 1, 'Connexion au système', '2025-07-14 20:15:16'),
(151, 1, 'Connexion au système', '2025-07-14 21:18:35'),
(152, 1, 'Connexion au système', '2025-07-14 21:19:15'),
(153, 1, 'Connexion au système', '2025-07-14 21:24:10'),
(154, 1, 'Ajoute la présence de Stagiaire ahmed ahmed', '2025-07-14 21:37:15'),
(155, 1, 'Ajoute la présence de Stagiaire ahmed ahmed', '2025-07-14 21:37:22'),
(156, 1, 'Ajoute la présence de Stagiaire ahmed ahmed', '2025-07-14 21:37:29'),
(157, 1, 'Ajoute la présence de Stagiaire rejeb ilyes', '2025-07-14 21:38:06'),
(158, 1, 'Modfie le Donne de Stagiér ahmed ahmed', '2025-07-14 21:46:52'),
(159, 1, 'Connexion au système', '2025-07-14 22:16:04'),
(160, 1, 'Connexion au système', '2025-07-14 22:46:20'),
(161, 1, 'Connexion au système', '2025-07-14 22:46:49'),
(162, 1, 'Ajout d\'un nouveau stage', '2025-07-14 22:49:55'),
(163, 1, 'Ajout d\'un nouveau stage', '2025-07-14 22:50:31'),
(164, 1, 'Connexion au système', '2025-07-15 11:25:34'),
(165, 1, 'Connexion au système', '2025-07-15 11:25:37'),
(166, 1, 'Connexion au système', '2025-07-15 11:25:40'),
(167, 1, 'Connexion au système', '2025-07-15 11:25:44'),
(168, 7, 'Connexion au système', '2025-07-15 11:27:52'),
(169, 1, 'Connexion au système', '2025-07-15 12:09:21'),
(170, 1, 'Connexion au système', '2025-07-15 21:09:29'),
(171, 1, 'Ajoute un Stagiaire', '2025-07-15 00:00:00'),
(172, 1, 'Ajout d\'un nouveau stage', '2025-07-15 21:58:11'),
(173, 1, 'Supprime de Stagiér rejeb ilyes', '2025-07-15 22:08:41'),
(174, 1, 'Ajoute un Stagiaire', '2025-07-15 00:00:00'),
(175, 1, 'Ajout d\'un stagiaire', '2025-07-15 00:00:00'),
(176, 1, 'Connexion au système', '2025-07-15 22:51:33'),
(177, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(178, 1, 'Connexion au système', '2025-07-15 23:00:27'),
(179, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(180, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(181, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(182, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(183, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(184, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(185, 1, 'Ajoute la présence de Stagiaire ahlem ahlem', '2025-07-15 23:25:04'),
(186, 1, 'Ajout étudiant', '2025-07-15 00:00:00'),
(187, 1, 'Supprime de Stagiér test pdf', '2025-07-15 23:46:34'),
(188, 1, 'Connexion au système', '2025-07-15 23:49:52'),
(189, 1, 'Modification des données de l\'étudiant sebie ahmed', '2025-07-16 00:05:22'),
(190, 1, 'Modification des données de l\'étudiant ahlem ahlem', '2025-07-16 00:06:46'),
(191, 1, 'Modification des données de l\'étudiant test ahlem', '2025-07-16 00:08:48'),
(192, 1, 'Connexion au système', '2025-07-16 20:11:36'),
(193, 1, 'Supprime de Stagiér ahmed ahmed', '2025-07-16 20:13:49'),
(194, 1, 'Supprime de Stagiér jih jihed', '2025-07-16 20:13:51'),
(195, 1, 'Supprime de Stagiér jihed jihed', '2025-07-16 20:13:51'),
(196, 1, 'Supprime de Stagiér rejeb ilyes', '2025-07-16 20:13:52'),
(197, 1, 'Supprime de Stagiér sebie ahmed', '2025-07-16 20:13:52'),
(198, 1, 'Supprime de Stagiér test ahlem', '2025-07-16 20:13:53'),
(199, 1, 'Ajout étudiant', '2025-07-16 00:00:00'),
(200, 1, 'Supprime de Stagiér rejeb ahmed', '2025-07-16 20:17:47'),
(201, 1, 'Ajout étudiant', '2025-07-16 00:00:00'),
(202, 1, 'Ajout d\'un nouveau stage', '2025-07-16 20:20:19'),
(203, 2, 'Connexion au système', '2025-07-17 21:50:21'),
(204, 2, 'Ajout étudiant', '2025-07-17 00:00:00'),
(205, 2, 'Connexion au système', '2025-07-17 22:06:12'),
(206, 2, 'Ajout étudiant', '2025-07-17 00:00:00'),
(207, 2, 'Ajout d\'un nouveau stage', '2025-07-17 22:21:36'),
(208, 2, 'Modfie le stage', '2025-07-17 22:21:46'),
(209, 2, 'Ajoute la présence de Stagiaire Ilyes Rejeb', '2025-07-17 22:21:51'),
(210, 2, 'Modfie le stage', '2025-07-17 22:22:25'),
(211, 2, 'Ajout d\'un nouveau stage', '2025-07-17 22:29:19'),
(212, 2, 'Ajout d\'un nouveau stage', '2025-07-17 22:29:38'),
(213, 2, 'Ajout d\'un nouveau stage', '2025-07-17 22:29:57'),
(214, 2, 'Ajout d\'un nouveau stage', '2025-07-17 22:30:25'),
(215, 2, 'Ajoute la présence de Stagiaire Khadija Ait', '2025-07-17 22:30:51'),
(216, 2, 'Ajout étudiant', '2025-07-17 00:00:00'),
(217, 2, 'Ajout étudiant', '2025-07-17 00:00:00'),
(218, 1, 'Connexion au système', '2025-07-17 23:45:23'),
(219, 1, 'Connexion au système', '2025-07-17 23:57:32'),
(220, 1, 'Connexion au système', '2025-07-18 00:05:35'),
(221, 1, 'Connexion au système', '2025-07-18 00:06:17'),
(222, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(223, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(224, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(225, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(226, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(227, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(228, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(229, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(230, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(231, 1, 'Connexion au système', '2025-07-18 00:41:39'),
(232, 1, 'Modification des données de l\'étudiant Ahmed ALi', '2025-07-18 01:17:10'),
(233, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(234, 1, 'Supprime de Stagiér Rejeb Ilyes', '2025-07-18 01:44:02'),
(235, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(236, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(237, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:06:03'),
(238, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(239, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:07:05'),
(240, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(241, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:07:48'),
(242, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(243, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:08:24'),
(244, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(245, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:09:15'),
(246, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(247, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-18 02:10:05'),
(248, 1, 'Ajout étudiant', '2025-07-18 00:00:00'),
(249, 1, 'Téléchargement du Fiche Etudiant rejeb ilyes', '2025-07-18 02:12:53'),
(250, 1, 'Ajout d\'un nouveau stage', '2025-07-18 02:15:05'),
(251, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:47'),
(252, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:48'),
(253, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:49'),
(254, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:50'),
(255, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:51'),
(256, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:52'),
(257, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:52'),
(258, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-18 02:15:54'),
(259, 1, 'Ajout d\'un nouveau stage', '2025-07-18 02:19:09'),
(260, 1, 'Connexion au système', '2025-07-18 02:23:34'),
(261, 1, 'Connexion au système', '2025-07-18 10:30:08'),
(262, 1, 'Connexion au système', '2025-07-18 10:35:27'),
(263, 1, 'Connexion au système', '2025-07-18 10:35:38'),
(264, 1, 'Connexion au système', '2025-07-18 10:35:51'),
(265, 1, 'Connexion au système', '2025-07-18 10:38:03'),
(266, 1, 'Connexion au système', '2025-07-18 10:42:04'),
(267, 1, 'Connexion au système', '2025-07-18 12:02:09'),
(268, 1, 'Connexion au système', '2025-07-18 18:44:37'),
(269, 1, 'Connexion au système', '2025-07-18 18:51:52'),
(270, 1, 'Ajout d\'un nouveau stage', '2025-07-18 19:47:24'),
(271, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-18 19:52:31'),
(272, 1, 'Modfie le stage', '2025-07-18 19:54:02'),
(273, 1, 'Connexion au système', '2025-07-18 20:18:25'),
(274, 1, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-18 20:25:32'),
(275, 1, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-18 20:25:58'),
(276, 1, 'Connexion au système', '2025-07-18 20:54:46'),
(277, 2, 'Connexion au système', '2025-07-18 20:54:59'),
(278, 2, 'Ajoute la présence de Stagiaire Reda Bouanani', '2025-07-18 20:57:21'),
(279, 3, 'Connexion au système', '2025-07-18 21:03:43'),
(280, 1, 'Connexion au système', '2025-07-18 21:05:28'),
(281, 2, 'Connexion au système', '2025-07-18 21:05:55'),
(282, 3, 'Connexion au système', '2025-07-18 21:06:08'),
(283, 1, 'Connexion au système', '2025-07-18 21:06:53'),
(284, 1, 'Ajout de l\'utilisateur ac', '2025-07-18 21:12:46'),
(285, 1, 'Suppression de l\'utilisateur ID 8', '2025-07-18 21:12:51'),
(286, 1, 'Suppression de l\'utilisateur ID 8', '2025-07-18 21:12:56'),
(287, 1, 'Suppression de l\'utilisateur ID 8', '2025-07-18 21:18:11'),
(288, 1, 'Suppression de l\'utilisateur ID 8', '2025-07-18 21:18:12'),
(289, 1, 'Ajout de l\'utilisateur admin2', '2025-07-18 21:18:52'),
(290, 1, 'Suppression de l\'utilisateur ID 8', '2025-07-18 21:18:52'),
(291, 1, 'Suppression de l\'utilisateur ID 9', '2025-07-18 21:19:02'),
(292, 1, 'Suppression de l\'utilisateur ID 9', '2025-07-18 21:22:22'),
(293, 1, 'Modification de l\'utilisateur ID 4', '2025-07-18 21:22:55'),
(294, 1, 'Modification de l\'utilisateur ID 3', '2025-07-18 21:23:16'),
(295, 1, 'Modification de l\'utilisateur ID 1', '2025-07-18 21:23:23'),
(296, 1, 'Modification de l\'utilisateur ID 3', '2025-07-18 21:23:35'),
(297, 3, 'Connexion au système', '2025-07-18 21:25:31'),
(298, 3, 'Connexion au système', '2025-07-18 21:29:15'),
(299, 1, 'Connexion au système', '2025-07-18 21:29:29'),
(300, 1, 'Connexion au système', '2025-07-18 21:32:46'),
(301, 1, 'Ajout d\'un nouveau stage', '2025-07-18 21:33:48'),
(302, 1, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-18 21:35:25'),
(303, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-18 21:35:43'),
(304, 1, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-18 21:38:10'),
(305, 1, 'Ajoute la présence de Stagiaire rejeb ilyes', '2025-07-18 21:53:01'),
(306, 1, 'Modfie le stage', '2025-07-18 21:57:22'),
(307, 1, 'Connexion au système', '2025-07-19 11:01:47'),
(308, 1, 'Connexion au système', '2025-07-19 11:14:48'),
(309, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-19 11:16:58'),
(310, 1, 'Ajout étudiant', '2025-07-19 00:00:00'),
(311, 1, 'Supprime de Stagiér Ahmed ALi', '2025-07-19 11:39:57'),
(312, 1, 'Ajout étudiant', '2025-07-19 00:00:00'),
(313, 1, 'Modification de l\'utilisateur ID 3', '2025-07-19 11:50:32'),
(314, 3, 'Connexion au système', '2025-07-19 11:51:19'),
(315, 1, 'Connexion au système', '2025-07-19 12:04:52'),
(316, 2, 'Connexion au système', '2025-07-19 12:05:12'),
(317, 2, 'Téléchargement du Attestation de stage rejeb ilyes', '2025-07-19 12:28:22'),
(318, 3, 'Connexion au système', '2025-07-19 12:38:57'),
(319, 3, 'Modification de l\'utilisateur ID 3', '2025-07-19 12:39:16'),
(320, 3, 'Connexion au système', '2025-07-19 12:40:28'),
(321, 3, 'Modification des données de l\'étudiant Ait Khadija', '2025-07-19 12:41:37'),
(322, 3, 'Ajoute la présence de Stagiaire Khadija Ait', '2025-07-19 12:41:47'),
(323, 3, 'Modifie la présence de Stagiaire Khadija Ait', '2025-07-19 12:42:58'),
(324, 3, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-19 12:43:59'),
(325, 3, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-19 12:44:07'),
(326, 3, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-19 12:44:09'),
(327, 3, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-19 12:44:11'),
(328, 3, 'Connexion au système', '2025-07-19 12:52:30'),
(329, 3, 'Connexion au système', '2025-07-19 12:55:33'),
(330, 3, 'Téléchargement du Lettre d\'affectation rejeb ilyes', '2025-07-19 13:03:38'),
(331, 3, 'Connexion au système', '2025-07-19 13:09:49'),
(332, 1, 'Connexion au système', '2025-07-19 13:19:43'),
(333, 1, 'Ajout de l\'utilisateur rh2', '2025-07-19 13:20:59'),
(334, 1, 'Suppression de l\'utilisateur ID 10', '2025-07-19 13:21:14'),
(335, 1, 'Modification de l\'utilisateur ID 4', '2025-07-19 13:21:32'),
(336, 1, 'Connexion au système', '2025-07-19 15:11:46'),
(337, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-20 10:45:00'),
(338, 1, 'Ajoute la présence de Stagiaire Mohamed Alaoui', '2025-07-20 10:45:31'),
(339, 1, 'Modfie le stage', '2025-07-20 10:46:27'),
(340, 2, 'Connexion au système', '2025-07-20 10:54:43'),
(341, 2, 'Ajout étudiant', '2025-07-20 00:00:00'),
(342, 2, 'Ajout d\'un nouveau stage', '2025-07-20 10:59:27'),
(343, 2, 'Connexion au système', '2025-07-20 11:19:17'),
(344, 2, 'Connexion au système', '2025-07-20 11:24:05'),
(345, 1, 'Connexion au système', '2025-07-20 11:29:49'),
(346, 3, 'Connexion au système', '2025-07-20 11:30:26'),
(347, 3, 'Connexion au système', '2025-07-20 11:33:50'),
(348, 2, 'Connexion au système', '2025-07-20 11:34:09'),
(349, 2, 'Connexion au système', '2025-07-20 11:38:04'),
(350, 1, 'Connexion au système', '2025-07-20 11:38:21'),
(351, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 11:51:05'),
(352, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 11:55:42'),
(353, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 11:57:13'),
(354, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 12:10:32'),
(355, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 12:19:07'),
(356, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 12:22:52'),
(357, 2, 'Connexion au système', '2025-07-20 16:03:31'),
(358, 1, 'Connexion au système', '2025-07-20 16:04:19'),
(359, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 16:14:31'),
(360, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 16:16:35'),
(361, 1, 'Ajout de l\'utilisateur bh', '2025-07-20 16:17:46'),
(362, 1, 'Suppression de l\'utilisateur ID 11', '2025-07-20 16:18:22'),
(363, 1, 'Modification de l\'utilisateur ID 4', '2025-07-20 16:20:40'),
(364, 1, 'Modification de l\'utilisateur ID 4', '2025-07-20 16:28:26'),
(365, 1, 'Modification de l\'utilisateur ID 4', '2025-07-20 16:39:04'),
(366, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 16:42:11'),
(367, 3, 'Connexion au système', '2025-07-20 16:42:25'),
(368, 1, 'Connexion au système', '2025-07-20 16:44:51'),
(369, 1, 'Modification de l\'utilisateur ID 4', '2025-07-20 16:51:08'),
(370, 3, 'Connexion au système', '2025-07-20 17:01:09'),
(371, 2, 'Connexion au système', '2025-07-20 17:01:57'),
(372, 3, 'Connexion au système', '2025-07-20 17:02:13'),
(373, 1, 'Connexion au système', '2025-07-20 17:05:22'),
(374, 2, 'Connexion au système', '2025-07-20 17:05:39'),
(375, 1, 'Connexion au système', '2025-07-20 17:05:53'),
(376, 1, 'Modification de l\'utilisateur ID 3', '2025-07-20 17:06:13'),
(377, 1, 'Ajout de l\'utilisateur info2', '2025-07-20 17:06:42'),
(378, 12, 'Connexion au système', '2025-07-20 17:07:02'),
(379, 3, 'Connexion au système', '2025-07-20 17:07:23'),
(380, 12, 'Connexion au système', '2025-07-20 17:11:39'),
(381, 1, 'Connexion au système', '2025-07-20 17:12:38'),
(382, 1, 'Connexion au système', '2025-07-20 17:15:05'),
(383, 2, 'Connexion au système', '2025-07-20 17:15:31'),
(384, 1, 'Connexion au système', '2025-07-20 17:20:28'),
(385, 1, 'Connexion au système', '2025-07-20 17:21:58'),
(386, 12, 'Connexion au système', '2025-07-20 17:23:20'),
(387, 2, 'Connexion au système', '2025-07-20 17:33:11'),
(388, 12, 'Connexion au système', '2025-07-20 17:33:42'),
(389, 5, 'Connexion au système', '2025-07-20 17:34:14'),
(390, 5, 'Ajoute la présence de Stagiaire Adnan Fassi', '2025-07-20 17:44:21'),
(391, 5, 'Connexion au système', '2025-07-20 17:45:17'),
(392, 5, 'Connexion au système', '2025-07-20 17:45:29'),
(393, 12, 'Connexion au système', '2025-07-20 18:11:03'),
(394, 1, 'Connexion au système', '2025-07-20 18:11:49'),
(395, 1, 'Ajout étudiant', '2025-07-20 00:00:00'),
(396, 1, 'Ajout d\'un nouveau stage', '2025-07-20 18:55:29'),
(397, 1, 'Connexion au système', '2025-07-20 19:08:55'),
(398, 12, 'Connexion au système', '2025-07-20 19:10:14'),
(399, 1, 'Connexion au système', '2025-07-20 19:11:00'),
(400, 2, 'Connexion au système', '2025-07-20 19:15:29'),
(401, 12, 'Connexion au système', '2025-07-20 19:20:29'),
(402, 2, 'Connexion au système', '2025-07-20 19:20:56'),
(403, 3, 'Connexion au système', '2025-07-20 19:21:04'),
(404, 12, 'Connexion au système', '2025-07-20 19:23:46'),
(405, 1, 'Connexion au système', '2025-07-20 19:25:42'),
(406, 1, 'Modification de l\'utilisateur ID 4', '2025-07-20 19:26:11'),
(407, 1, 'Connexion au système', '2025-07-20 19:28:24'),
(408, 1, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-20 19:29:55'),
(409, 12, 'Connexion au système', '2025-07-20 19:30:35'),
(410, 3, 'Connexion au système', '2025-07-20 19:30:46'),
(411, 1, 'Connexion au système', '2025-07-20 19:37:17'),
(412, 1, 'Supprime de Stagiér Alaoui Mohamed', '2025-07-20 19:39:42'),
(413, 1, 'Ajout étudiant', '2025-07-20 00:00:00'),
(414, 1, 'Connexion au système', '2025-07-20 20:01:23'),
(415, 1, 'Connexion au système', '2025-07-20 20:04:38'),
(416, 1, 'Connexion au système', '2025-07-20 20:18:29'),
(417, 1, 'Connexion au système', '2025-07-20 20:19:05'),
(418, 12, 'Connexion au système', '2025-07-20 20:30:06'),
(419, 12, 'Connexion au système', '2025-07-20 20:30:27'),
(420, 12, 'Connexion au système', '2025-07-20 20:31:11'),
(421, 1, 'Connexion au système', '2025-07-20 20:31:54'),
(422, 3, 'Connexion au système', '2025-07-20 20:37:48'),
(423, 1, 'Connexion au système', '2025-07-20 20:38:48'),
(424, 1, 'Connexion au système', '2025-07-20 20:50:26'),
(425, 1, 'Modification des données de l\'étudiant Jabri Adil', '2025-07-20 21:03:12'),
(426, 3, 'Connexion au système', '2025-07-21 12:16:27'),
(427, 1, 'Connexion au système', '2025-07-21 12:17:17'),
(428, 1, 'Connexion au système', '2025-07-21 12:42:54'),
(429, 1, 'Connexion au système', '2025-07-21 13:21:07'),
(430, 1, 'Supprime de Stagiér Bouanani Reda', '2025-07-21 13:22:53'),
(431, 6, 'Connexion au système', '2025-07-21 13:24:27'),
(432, 2, 'Connexion au système', '2025-07-21 13:24:39'),
(433, 3, 'Connexion au système', '2025-07-21 13:29:14'),
(434, 3, 'Ajoute la présence de Stagiaire rejeb amine', '2025-07-21 13:29:41'),
(435, 6, 'Connexion au système', '2025-07-21 13:39:54'),
(436, 6, 'Connexion au système', '2025-07-22 09:12:51'),
(437, 6, 'Connexion au système', '2025-07-22 09:12:57'),
(438, 6, 'Connexion au système', '2025-07-22 09:13:02'),
(439, 1, 'Connexion au système', '2025-07-22 12:14:56'),
(440, 1, 'Ajoute la présence de Stagiaire rejeb amine', '2025-07-22 12:17:53'),
(441, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-22 12:18:13'),
(442, 1, 'Ajoute la présence de Stagiaire Fatima Benjelloun', '2025-07-22 12:18:27'),
(443, 1, 'Téléchargement du Attestation de stage rejeb ilyes', '2025-07-22 12:48:33'),
(444, 1, 'Connexion au système', '2025-07-22 12:54:55'),
(445, 1, 'Ajoute la présence de Stagiaire Adnan Fassi', '2025-07-22 13:22:25'),
(446, 1, 'Modifie la présence de Stagiaire Adnan Fassi', '2025-07-22 13:22:53'),
(447, 1, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-22 13:25:03'),
(448, 1, 'Supprime de Stagiér ilyes rejeb', '2025-07-22 13:30:25'),
(449, 1, 'Ajout étudiant', '2025-07-22 13:33:12'),
(450, 1, 'Connexion au système', '2025-07-22 13:45:00'),
(451, 1, 'Modifie la présence de Stagiaire rejeb amine', '2025-07-22 13:46:16'),
(452, 1, 'Connexion au système', '2025-07-22 13:48:21'),
(453, 6, 'Connexion au système', '2025-07-22 13:48:49'),
(454, 3, 'Connexion au système', '2025-07-22 13:49:14'),
(455, 1, 'Connexion au système', '2025-07-22 13:49:53'),
(456, 1, 'Ajout d\'un nouveau stage', '2025-07-22 13:50:38'),
(457, 1, 'Connexion au système', '2025-07-22 13:52:16'),
(458, 1, 'Connexion au système', '2025-07-23 23:14:30'),
(459, 1, 'Ajout étudiant', '2025-07-23 23:18:18'),
(460, 1, 'Ajout d\'un nouveau stage', '2025-07-24 10:21:06'),
(461, 1, 'Ajoute la présence de Stagiaire Fatima Benjelloun', '2025-07-24 10:21:13'),
(462, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-24 10:21:32'),
(463, 1, 'Ajoute la présence de Stagiaire Adnan Fassi', '2025-07-24 10:24:10'),
(464, 1, 'Téléchargement du Copie de CIN Ilyes Rejeb', '2025-07-24 10:39:28'),
(465, 1, 'Téléchargement du Rapport de stage Ilyes Rejeb', '2025-07-24 10:40:46'),
(466, 1, 'Téléchargement du Convention de stage Ilyes Rejeb', '2025-07-24 10:46:08'),
(467, 1, 'Connexion au système', '2025-07-27 20:52:41'),
(468, 1, 'Connexion au système', '2025-07-28 10:16:01'),
(469, 1, 'Ajoute la présence de Stagiaire Adil Jabri', '2025-07-28 10:20:11'),
(470, 1, 'Ajoute la présence de Stagiaire rejeb amine', '2025-07-28 10:20:20'),
(471, 1, 'Connexion au système', '2025-07-28 21:44:56'),
(472, 1, 'Connexion au système', '2025-07-28 21:54:56'),
(473, 3, 'Connexion au système', '2025-07-28 21:55:52'),
(474, 1, 'Connexion au système', '2025-07-28 21:56:12'),
(475, 1, 'Modifie la présence de Stagiaire rejeb amine', '2025-07-28 21:57:07'),
(476, 1, 'Ajout d\'un nouveau stage', '2025-07-28 21:57:44'),
(477, 1, 'Ajoute la présence de Stagiaire rejeb amine', '2025-07-28 21:57:52'),
(478, 1, 'Modfie le stage', '2025-07-28 21:58:20'),
(479, 1, 'Ajout étudiant', '2025-07-28 22:00:01'),
(480, 1, 'Ajout d\'un nouveau stage', '2025-07-28 22:00:22'),
(481, 1, 'Modification des données de l\'étudiant test test', '2025-07-28 22:01:46'),
(482, 1, 'Modification de l\'utilisateur ID 7', '2025-07-28 22:03:32'),
(483, 6, 'Connexion au système', '2025-07-28 22:03:51'),
(484, 3, 'Connexion au système', '2025-07-28 22:04:39'),
(485, 3, 'Modifie la présence de Stagiaire Adil Jabri', '2025-07-28 22:04:57'),
(486, 3, 'Ajoute la présence de Stagiaire Khadija Ait', '2025-07-28 22:05:11'),
(487, 3, 'Connexion au système', '2025-07-30 02:18:11'),
(488, 3, 'Connexion au système', '2025-07-30 02:19:26'),
(489, 1, 'Connexion au système', '2025-07-30 02:19:33'),
(490, 3, 'Connexion au système', '2025-07-31 11:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `presences`
--

DROP TABLE IF EXISTS `presences`;
CREATE TABLE IF NOT EXISTS `presences` (
  `id_presence` int NOT NULL AUTO_INCREMENT,
  `id_stage` int DEFAULT NULL,
  `date` date NOT NULL,
  `etat` enum('Présent','Absent','Justifié') NOT NULL,
  PRIMARY KEY (`id_presence`),
  UNIQUE KEY `id_stage` (`id_stage`,`date`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `presences`
--

INSERT INTO `presences` (`id_presence`, `id_stage`, `date`, `etat`) VALUES
(82, 56, '2025-07-24', 'Présent'),
(81, 61, '2025-07-24', 'Présent'),
(80, 62, '2025-07-24', 'Présent'),
(79, 56, '2025-07-22', 'Présent'),
(78, 53, '2025-07-22', 'Absent'),
(77, 58, '2025-07-22', 'Justifié'),
(76, 60, '2025-07-22', 'Présent'),
(75, 60, '2025-07-21', 'Présent'),
(74, 56, '2025-07-20', 'Présent'),
(73, 52, '2025-07-20', 'Présent'),
(72, 58, '2025-07-20', 'Présent'),
(71, 51, '2025-07-19', 'Absent'),
(70, 58, '2025-07-19', 'Absent'),
(69, 55, '2025-07-18', 'Présent'),
(68, 58, '2025-07-18', 'Absent'),
(67, 54, '2025-07-18', 'Absent'),
(66, 57, '2025-07-18', 'Présent'),
(65, 51, '2025-07-17', 'Présent'),
(64, 50, '2025-07-17', 'Présent'),
(63, 35, '2025-07-15', 'Présent'),
(62, 33, '2025-07-14', 'Absent'),
(61, 42, '2025-07-14', 'Présent'),
(60, 43, '2025-07-14', 'Absent'),
(54, 35, '2025-07-12', 'Présent'),
(53, 34, '2025-07-11', 'Absent'),
(52, 38, '2025-07-13', 'Présent'),
(51, 37, '2025-07-13', 'Présent'),
(50, 36, '2025-07-12', 'Justifié'),
(49, 35, '2025-07-10', 'Absent'),
(48, 34, '2025-07-10', 'Présent'),
(59, 44, '2025-07-14', 'Justifié'),
(46, 20, '2025-07-10', 'Justifié'),
(45, 22, '2025-07-10', 'Présent'),
(44, 21, '2025-07-10', 'Présent'),
(43, 21, '2025-07-09', 'Absent'),
(42, 20, '2025-07-09', 'Présent'),
(83, 61, '2025-07-28', 'Absent'),
(84, 60, '2025-07-28', 'Absent'),
(85, 63, '2025-07-28', 'Présent'),
(86, 51, '2025-07-28', 'Présent');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(100) NOT NULL,
  PRIMARY KEY (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id_service`, `nom_service`) VALUES
(1, 'Ressources Humaines'),
(2, 'Informatique'),
(3, 'Comptabilité'),
(4, 'Marketing'),
(5, 'Direction Générale');

-- --------------------------------------------------------

--
-- Table structure for table `stages`
--

DROP TABLE IF EXISTS `stages`;
CREATE TABLE IF NOT EXISTS `stages` (
  `id_stage` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `etat` enum('En cours','Terminé') DEFAULT 'En cours',
  PRIMARY KEY (`id_stage`),
  KEY `id_etudiant` (`id_etudiant`),
  KEY `id_service` (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stages`
--

INSERT INTO `stages` (`id_stage`, `id_etudiant`, `id_service`, `date_debut`, `date_fin`, `etat`) VALUES
(60, 94, 2, '2025-07-20', '2025-07-20', 'En cours'),
(64, 98, 2, '2025-07-28', '2025-08-10', 'En cours'),
(58, 32, 2, '2025-07-22', '2025-07-31', 'En cours'),
(57, 32, 2, '2025-07-23', '2025-08-02', 'Terminé'),
(56, 54, 4, '2025-07-01', '2025-07-31', 'En cours'),
(63, 94, 4, '2025-07-29', '2025-07-31', 'En cours'),
(61, 32, 4, '2025-08-06', '2025-08-30', 'En cours'),
(53, 25, 2, '2025-08-01', '2025-09-04', 'En cours'),
(62, 25, 4, '2025-07-29', '2025-08-08', 'En cours'),
(51, 49, 2, '2025-07-24', '2025-07-30', 'En cours');

-- --------------------------------------------------------

--
-- Table structure for table `universites`
--

DROP TABLE IF EXISTS `universites`;
CREATE TABLE IF NOT EXISTS `universites` (
  `id_universite` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int NOT NULL,
  `etablissement` varchar(100) NOT NULL,
  `specialite` varchar(100) NOT NULL,
  `duree` varchar(20) NOT NULL,
  PRIMARY KEY (`id_universite`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `universites`
--

INSERT INTO `universites` (`id_universite`, `id_etudiant`, `etablissement`, `specialite`, `duree`) VALUES
(38, 91, 'ISIMG', 'LSIM1', '1'),
(39, 91, 'ISIMG', 'LSIM2', '1'),
(41, 94, 'ISIMG', 'LSIM1', '1'),
(42, 95, 'ISIMG', 'LSIM1', '1'),
(44, 97, 'ISIMG', 'LSIM1', '1'),
(45, 98, 'ISIMG', 'LSIM1', '1');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` enum('admin','superviseur','admin_super') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'superviseur',
  `id_service` int DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  KEY `id_service` (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_user`, `username`, `password`, `email`, `role`, `id_service`) VALUES
(1, 'admin', 'admin123', 'ilyesrejeb12@gmail.com', 'admin', NULL),
(2, 'rh1', 'test', 'ilyesrejeb12@gmail.com', 'superviseur', 1),
(3, 'info1', 'test', 'ilyesrejeb30@gmail.com', 'superviseur', 2),
(4, 'compta1', 'ilyes047851', 'ilyesrejeb2@gmail.com', 'superviseur', 3),
(5, 'marketing1', 'test', '', 'superviseur', 4),
(7, 'dh1', 'test', 'test1@gmail.com', 'admin_super', 5),
(6, 'info2', 'test', 'rejeb424@gmail.com', 'admin_super', 2);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `universites`
--
ALTER TABLE `universites`
  ADD CONSTRAINT `universites_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
