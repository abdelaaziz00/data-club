-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 août 2025 à 20:20
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `data_club`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `ID_ADMIN` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL,
  `PROFILE_IMG` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`ID_ADMIN`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`, `PROFILE_IMG`) VALUES
(1, 'ttanani', 'mmouhsin', 'tanani@gmail.com', '$2y$10$dlZSo7TUMRwBJJNjPrAVmuUbCs/2NIercdyrQAg3qjr', NULL),
(2, 'Ahmed', 'Benali', 'admin@dataclub.ma', 'admin123', NULL),
(3, 'Fatima', 'Alaoui', 'fatima.admin@dataclub.ma', 'admin456', NULL),
(4, 'Karim', 'Tazi', 'karim.admin@dataclub.ma', 'admin789', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `club`
--

CREATE TABLE `club` (
  `ID_CLUB` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `LOGO` varchar(80) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `UNIVERSITY` varchar(50) DEFAULT NULL,
  `CITY` char(10) DEFAULT NULL,
  `INSTAGRAM_LINK` char(10) DEFAULT NULL,
  `LINKEDIN_LINK` char(10) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `CLUB_PHONE` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `club`
--

INSERT INTO `club` (`ID_CLUB`, `ID_MEMBER`, `NAME`, `LOGO`, `DESCRIPTION`, `UNIVERSITY`, `CITY`, `INSTAGRAM_LINK`, `LINKEDIN_LINK`, `EMAIL`, `CLUB_PHONE`) VALUES
(1, 1, 'Data Science Club Rabat', 'club_logo_1_1752349925.jpg', 'Leading data science community at Mohammed V University. We focus on machine learning, AI, and data analytics.', 'Mohammed V University', 'Rabat', '@dsc_rabat', 'dsc-rabat', 'dsc.rabat@um5.ac.ma', '0612345678'),
(2, 2, 'AI & ML Club Casablanca', NULL, 'Innovative AI and machine learning community at Hassan II University. We explore cutting-edge AI technologies.', 'Hassan II University', 'Casablanca', '@aiml_casa', 'aiml-casa', 'aiml.casa@uh2c.ac.ma', '0623456789'),
(3, 3, 'Tech Innovation Club Fes', NULL, 'Technology innovation hub at Sidi Mohamed Ben Abdellah University. We focus on emerging technologies.', 'Sidi Mohamed Ben Abdellah University', 'Fes', '@tech_fes', 'tech-fes', 'tech.fes@usmba.ac.ma', '0634567890'),
(4, 4, 'Digital Transformation Club Marrakech', NULL, 'Digital transformation and innovation community at Cadi Ayyad University.', 'Cadi Ayyad University', 'Marrakech', '@dtc_marra', 'dtc-marrak', 'dtc.marrakech@uca.ac.ma', '0645678901'),
(5, 5, 'Cyber Security Club Agadir', NULL, 'Cybersecurity and digital security community at Ibn Zohr University.', 'Ibn Zohr University', 'Agadir', '@cyber_aga', 'cyber-agad', 'cyber.agadir@uiz.ac.ma', '0656789012');

-- --------------------------------------------------------

--
-- Structure de la table `clubcreationrequest`
--

CREATE TABLE `clubcreationrequest` (
  `ID_REQUEST` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL,
  `ID_ADMIN` int(11) DEFAULT NULL,
  `CLUB_NAME` varchar(50) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `TEAM_MEMBERS` int(11) DEFAULT NULL,
  `STATUS` varchar(20) DEFAULT NULL,
  `UNIVERSITY` varchar(50) DEFAULT NULL,
  `CITY` text NOT NULL,
  `INSTAGRAM_LINK` text NOT NULL,
  `LINKEDIN_LINK` text NOT NULL,
  `EMAIL` text NOT NULL,
  `CLUB_PHONE` int(11) NOT NULL,
  `Motif` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clubcreationrequest`
--

INSERT INTO `clubcreationrequest` (`ID_REQUEST`, `ID_MEMBER`, `ID_ADMIN`, `CLUB_NAME`, `DESCRIPTION`, `TEAM_MEMBERS`, `STATUS`, `UNIVERSITY`, `CITY`, `INSTAGRAM_LINK`, `LINKEDIN_LINK`, `EMAIL`, `CLUB_PHONE`, `Motif`) VALUES
(1, 6, NULL, 'Quantum Computing Club', 'Exploring quantum computing and its applications in cryptography and optimization.', 25, 'pending', 'Mohammed VI Polytechnic University', 'Ben Guerir', '@quantum_club', 'quantum-club', 'quantum.club@um6p.ma', 612345678, 'We want to explore the cutting-edge field of quantum computing and prepare students for the future of technology.'),
(2, 7, NULL, 'Robotics and Automation Club', 'Building robots and exploring automation technologies for industrial and educational applications.', 30, 'pending', 'National School of Applied Sciences', 'Tangier', '@robotics_club', 'robotics-club', 'robotics.club@ensat.ma', 623456789, 'Our goal is to inspire students to explore robotics and automation, preparing them for Industry 4.0.'),
(3, 8, NULL, 'Digital Marketing Club', 'Learning modern digital marketing strategies, SEO, and social media management.', 20, 'pending', 'School of Information Sciences', 'Rabat', '@digital_marketing', 'digital-marketing', 'marketing.club@esi.ac.ma', 634567890, 'We aim to bridge the gap between technology and business through digital marketing education.'),
(4, 9, 1, 'Game Development Club', 'Creating video games and learning game design principles and development tools.', 35, 'approved', 'National School of Computer Science', 'Rabat', '@game_dev_club', 'game-dev-club', 'gamedev.club@ensias.ma', 645678901, 'We want to foster creativity and technical skills through game development.'),
(5, 10, 2, 'IoT and Smart Cities Club', 'Exploring Internet of Things technologies and smart city solutions.', 28, 'approved', 'Hassan II University', 'Casablanca', '@iot_club', 'iot-club', 'iot.club@uh2c.ac.ma', 656789012, 'Our mission is to develop IoT solutions for smart cities and sustainable development.'),
(6, 11, 3, 'Duplicate Club Request', 'This is a duplicate request that should be rejected.', 15, 'rejected', 'Test University', 'Test City', '@duplicate', 'duplicate-club', 'duplicate@test.ma', 667890123, 'This request should be rejected as it is a duplicate.');

-- --------------------------------------------------------

--
-- Structure de la table `contains`
--

CREATE TABLE `contains` (
  `ID_EVENT` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contains`
--

INSERT INTO `contains` (`ID_EVENT`, `TOPIC_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 3),
(2, 8),
(3, 5),
(3, 16),
(4, 4),
(4, 17),
(5, 2),
(5, 6),
(5, 10),
(6, 11),
(6, 18),
(7, 13),
(7, 15),
(8, 12),
(8, 16),
(9, 16),
(9, 17),
(10, 18),
(10, 20);

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `ID_EVENT` int(11) NOT NULL,
  `TITLE` varchar(70) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `DATE` date DEFAULT NULL,
  `STARTING_TIME` time DEFAULT NULL,
  `ENDING_TIME` time DEFAULT NULL,
  `LOCATION` varchar(100) DEFAULT NULL,
  `CITY` text NOT NULL,
  `EVENT_TYPE` varchar(50) DEFAULT NULL,
  `PRICE` float DEFAULT NULL,
  `CAPACITY` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evenement`
--

INSERT INTO `evenement` (`ID_EVENT`, `TITLE`, `DESCRIPTION`, `DATE`, `STARTING_TIME`, `ENDING_TIME`, `LOCATION`, `CITY`, `EVENT_TYPE`, `PRICE`, `CAPACITY`) VALUES
(1, 'Introduction to Machine Learning', 'Learn the fundamentals of machine learning algorithms and their applications in real-world scenarios.', '2025-02-15', '09:00:00', '17:00:00', 'Faculty of Sciences, Room A101', 'Rabat', 'Hackathon', 0, 50),
(2, 'AI Ethics and Responsible Development', 'Explore the ethical implications of AI development and best practices for responsible AI.', '2025-02-20', '14:00:00', '18:00:00', 'Engineering School, Auditorium', 'Casablanca', 'Seminar', 100, 100),
(3, 'Cybersecurity Fundamentals', 'Learn about cybersecurity threats, prevention strategies, and security best practices.', '2025-02-25', '10:00:00', '16:00:00', 'Computer Science Department', 'Agadir', 'Workshop', 50, 40),
(4, 'Web Development Bootcamp', 'Intensive 3-day bootcamp covering modern web development technologies and frameworks.', '2025-03-01', '09:00:00', '18:00:00', 'Innovation Center', 'Fes', 'Bootcamp', 200, 30),
(5, 'Data Science Hackathon', '24-hour hackathon focused on solving real-world problems using data science techniques.', '2025-03-10', '09:00:00', '09:00:00', 'University Campus', 'Marrakech', 'Hackathon', 0, 80),
(6, 'Cloud Computing Workshop', 'Hands-on workshop on cloud platforms and deployment strategies.', '2025-03-15', '13:00:00', '17:00:00', 'Tech Hub', 'Rabat', 'Workshop', 75, 60),
(7, 'Blockchain and Cryptocurrency', 'Introduction to blockchain technology and its applications beyond cryptocurrency.', '2025-03-20', '15:00:00', '19:00:00', 'Business School', 'Casablanca', 'Seminar', 150, 70),
(8, 'Mobile App Development', 'Learn to build native and cross-platform mobile applications.', '2025-03-25', '10:00:00', '16:00:00', 'Engineering Lab', 'Fes', 'Workshop', 100, 45),
(9, 'UI/UX Design Principles', 'Master the fundamentals of user interface and user experience design.', '2025-03-30', '14:00:00', '18:00:00', 'Design Studio', 'Marrakech', 'Workshop', 80, 35),
(10, 'DevOps and CI/CD', 'Learn about DevOps practices and continuous integration/deployment pipelines.', '2025-04-05', '09:00:00', '17:00:00', 'Computer Center', 'Agadir', 'Workshop', 120, 50);

-- --------------------------------------------------------

--
-- Structure de la table `event_session`
--

CREATE TABLE `event_session` (
  `SESSION_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL,
  `SESSION_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `event_session`
--

INSERT INTO `event_session` (`SESSION_ID`, `ID_EVENT`, `SESSION_NAME`) VALUES
(1, 1, '09:00 - 10:30 - Introduction to ML Concepts'),
(2, 1, '10:45 - 12:15 - Supervised Learning Algorithms'),
(3, 1, '14:00 - 15:30 - Unsupervised Learning'),
(4, 1, '15:45 - 17:00 - Hands-on Practice'),
(5, 2, '14:00 - 15:30 - AI Ethics Overview'),
(6, 2, '15:45 - 17:00 - Case Studies'),
(7, 2, '17:15 - 18:00 - Q&A Session'),
(8, 4, '09:00 - 10:30 - HTML/CSS Fundamentals'),
(9, 4, '10:45 - 12:15 - JavaScript Basics'),
(10, 4, '14:00 - 15:30 - React Framework'),
(11, 4, '15:45 - 17:00 - Backend Integration'),
(12, 4, '09:00 - 10:30 - Advanced Topics'),
(13, 4, '10:45 - 12:15 - Project Development'),
(14, 4, '14:00 - 17:00 - Final Project Presentation');

-- --------------------------------------------------------

--
-- Structure de la table `focuses`
--

CREATE TABLE `focuses` (
  `ID_CLUB` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `focuses`
--

INSERT INTO `focuses` (`ID_CLUB`, `TOPIC_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 6),
(2, 1),
(2, 3),
(2, 7),
(2, 8),
(3, 4),
(3, 11),
(3, 16),
(3, 17),
(4, 10),
(4, 17),
(4, 18),
(4, 20),
(5, 5),
(5, 15),
(5, 16),
(5, 19);

-- --------------------------------------------------------

--
-- Structure de la table `member`
--

CREATE TABLE `member` (
  `ID_MEMBER` int(11) NOT NULL,
  `ID_CLUB` int(11) DEFAULT NULL,
  `PROFILE_IMG` varchar(80) DEFAULT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `member`
--

INSERT INTO `member` (`ID_MEMBER`, `ID_CLUB`, `PROFILE_IMG`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`) VALUES
(1, 1, 'profile_picture_1_1752349726.jpg', 'Youssef', 'El Amrani', 'youssef.elamrani@student.ma', 'student123'),
(2, 2, NULL, 'Amina', 'Bennani', 'amina.bennani@student.ma', 'student456'),
(3, 3, NULL, 'Hassan', 'Tazi', 'hassan.tazi@student.ma', 'student789'),
(4, 4, NULL, 'Layla', 'Mourad', 'layla.mourad@student.ma', 'student101'),
(5, 5, NULL, 'Omar', 'Fassi', 'omar.fassi@student.ma', 'student202'),
(6, NULL, NULL, 'Nour', 'Alami', 'nour.alami@student.ma', 'student303'),
(7, NULL, NULL, 'Adam', 'Benjelloun', 'adam.benjelloun@student.ma', 'student404'),
(8, NULL, NULL, 'Zineb', 'Cherkaoui', 'zineb.cherkaoui@student.ma', 'student505'),
(9, NULL, NULL, 'Ilyas', 'Mekouar', 'ilyas.mekouar@student.ma', 'student606'),
(10, NULL, NULL, 'Sara', 'Bouazza', 'sara.bouazza@student.ma', 'student707'),
(11, NULL, NULL, 'Amine', 'Lahlou', 'amine.lahlou@student.ma', 'student808'),
(12, NULL, NULL, 'Hanae', 'Tahiri', 'hanae.tahiri@student.ma', 'student909'),
(13, NULL, NULL, 'Yassine', 'Boukhari', 'yassine.boukhari@student.ma', 'student110'),
(14, NULL, NULL, 'Aicha', 'Mansouri', 'aicha.mansouri@student.ma', 'student111'),
(15, NULL, NULL, 'Khalid', 'Rachidi', 'khalid.rachidi@student.ma', 'student112'),
(19, NULL, 'profile_picture_19_1752353233.png', 'kirigaya', 'kirigaya', 'kiiriigayakasuto@gmail.com', '$2y$10$2U0KF7KxbjR6CJy/xUWb0OxbwjoqdxeIMMu0N2uYvx/FIoXmW2b46'),
(20, NULL, NULL, 'omar', 'elamraoui', 'test@gmail.com', '$2y$10$N3IVfXIWrw/3dfO5do9u6uvtDfA5UaPx4v/xqNpn1tckJXh4o3OLi'),
(21, NULL, NULL, 'Mouhsin', 'TANANI', 'tananimouhsin@gmail.com', '$2y$10$p2gNbmIFLzQMwhvn3q1NV.Cud0Xuvpf2HYKX6Dmj.kyxLIN/ZZTuy');

-- --------------------------------------------------------

--
-- Structure de la table `organizes`
--

CREATE TABLE `organizes` (
  `ID_CLUB` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `organizes`
--

INSERT INTO `organizes` (`ID_CLUB`, `ID_EVENT`) VALUES
(1, 1),
(1, 6),
(1, 10),
(2, 2),
(2, 7),
(3, 4),
(3, 8),
(4, 5),
(4, 9),
(5, 3);

-- --------------------------------------------------------

--
-- Structure de la table `registre`
--

CREATE TABLE `registre` (
  `ID_EVENT` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `registre`
--

INSERT INTO `registre` (`ID_EVENT`, `ID_MEMBER`) VALUES
(1, 1),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(3, 6),
(3, 7),
(3, 15),
(4, 11),
(4, 12),
(4, 13),
(4, 14),
(5, 1),
(5, 6),
(5, 7),
(5, 13),
(5, 14),
(5, 15),
(6, 6),
(6, 7),
(6, 8),
(6, 9),
(6, 10);

-- --------------------------------------------------------

--
-- Structure de la table `requestjoin`
--

CREATE TABLE `requestjoin` (
  `ID_MEMBER` int(11) NOT NULL,
  `ID_CLUB` int(11) NOT NULL,
  `ACCEPTED` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `requestjoin`
--

INSERT INTO `requestjoin` (`ID_MEMBER`, `ID_CLUB`, `ACCEPTED`) VALUES
(1, 1, 0),
(1, 3, 0),
(6, 1, 1),
(6, 2, 0),
(7, 1, 1),
(7, 3, 0),
(8, 1, 1),
(8, 4, 0),
(9, 2, 1),
(9, 5, 0),
(10, 1, 0),
(10, 2, 1),
(11, 3, 1),
(12, 3, 1),
(13, 4, 1),
(14, 4, 1),
(15, 5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `speaker`
--

CREATE TABLE `speaker` (
  `SPEAKER_ID` int(11) NOT NULL,
  `SPEAKER_FULLNAME` text DEFAULT NULL,
  `SPEAKER_DISCRIPTION` text DEFAULT NULL,
  `SPEAKER_COMPANY` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `speaker`
--

INSERT INTO `speaker` (`SPEAKER_ID`, `SPEAKER_FULLNAME`, `SPEAKER_DISCRIPTION`, `SPEAKER_COMPANY`) VALUES
(1, 'Dr. Sarah Bennani', 'Senior Data Scientist', 'Microsoft Morocco'),
(2, 'Prof. Ahmed Tazi', 'AI Research Director', 'Google AI'),
(3, 'Fatima Alami', 'Machine Learning Engineer', 'Amazon Web Services'),
(4, 'Omar Cherkaoui', 'Cybersecurity Expert', 'Check Point Software'),
(5, 'Layla Bouazza', 'Data Analytics Lead', 'IBM Morocco'),
(6, 'Hassan Mekouar', 'Cloud Solutions Architect', 'Oracle'),
(7, 'Amina Rachidi', 'Blockchain Developer', 'ConsenSys'),
(8, 'Youssef Mansouri', 'DevOps Engineer', 'GitLab'),
(9, 'Nour Benjelloun', 'UI/UX Designer', 'Adobe'),
(10, 'Adam Tahiri', 'Mobile App Developer', 'Apple Developer');

-- --------------------------------------------------------

--
-- Structure de la table `speaks`
--

CREATE TABLE `speaks` (
  `SPEAKER_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `speaks`
--

INSERT INTO `speaks` (`SPEAKER_ID`, `ID_EVENT`) VALUES
(1, 1),
(2, 2),
(4, 3),
(5, 5),
(6, 4),
(6, 6),
(7, 7),
(8, 8),
(8, 10),
(9, 9);

-- --------------------------------------------------------

--
-- Structure de la table `topics`
--

CREATE TABLE `topics` (
  `TOPIC_ID` int(11) NOT NULL,
  `TOPIC_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `topics`
--

INSERT INTO `topics` (`TOPIC_ID`, `TOPIC_NAME`) VALUES
(1, 'Machine Learning'),
(2, 'Data Science'),
(3, 'Artificial Intelligence'),
(4, 'Web Development'),
(5, 'Cybersecurity'),
(6, 'Data Analytics'),
(7, 'Deep Learning'),
(8, 'Natural Language Processing'),
(9, 'Computer Vision'),
(10, 'Big Data'),
(11, 'Cloud Computing'),
(12, 'Mobile Development'),
(13, 'Blockchain'),
(14, 'IoT'),
(15, 'Robotics'),
(16, 'UI/UX Design'),
(17, 'DevOps'),
(18, 'Game Development'),
(19, 'AR/VR'),
(20, 'Data Engineering');

-- --------------------------------------------------------

--
-- Structure de la table `willfocus`
--

CREATE TABLE `willfocus` (
  `ID_REQUEST` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `willfocus`
--

INSERT INTO `willfocus` (`ID_REQUEST`, `TOPIC_ID`) VALUES
(1, 1),
(1, 3),
(1, 13),
(1, 15),
(2, 14),
(2, 15),
(2, 20),
(3, 16),
(3, 17),
(3, 18),
(4, 16),
(4, 17),
(4, 18),
(5, 10),
(5, 14),
(5, 20),
(6, 1),
(6, 2);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID_ADMIN`);

--
-- Index pour la table `club`
--
ALTER TABLE `club`
  ADD PRIMARY KEY (`ID_CLUB`);

--
-- Index pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD PRIMARY KEY (`ID_REQUEST`),
  ADD KEY `ID_MEMBER` (`ID_MEMBER`),
  ADD KEY `ID_ADMIN` (`ID_ADMIN`);

--
-- Index pour la table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`ID_EVENT`,`TOPIC_ID`),
  ADD KEY `TOPIC_ID` (`TOPIC_ID`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID_EVENT`);

--
-- Index pour la table `event_session`
--
ALTER TABLE `event_session`
  ADD PRIMARY KEY (`SESSION_ID`),
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD PRIMARY KEY (`ID_CLUB`,`TOPIC_ID`),
  ADD KEY `TOPIC_ID` (`TOPIC_ID`);

--
-- Index pour la table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`ID_MEMBER`),
  ADD KEY `ID_CLUB` (`ID_CLUB`);

--
-- Index pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD PRIMARY KEY (`ID_CLUB`,`ID_EVENT`),
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `registre`
--
ALTER TABLE `registre`
  ADD PRIMARY KEY (`ID_EVENT`,`ID_MEMBER`),
  ADD KEY `ID_MEMBER` (`ID_MEMBER`);

--
-- Index pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD PRIMARY KEY (`ID_MEMBER`,`ID_CLUB`),
  ADD KEY `ID_CLUB` (`ID_CLUB`);

--
-- Index pour la table `speaker`
--
ALTER TABLE `speaker`
  ADD PRIMARY KEY (`SPEAKER_ID`);

--
-- Index pour la table `speaks`
--
ALTER TABLE `speaks`
  ADD PRIMARY KEY (`SPEAKER_ID`,`ID_EVENT`),
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`TOPIC_ID`);

--
-- Index pour la table `willfocus`
--
ALTER TABLE `willfocus`
  ADD PRIMARY KEY (`ID_REQUEST`,`TOPIC_ID`),
  ADD KEY `ID_REQUEST` (`ID_REQUEST`),
  ADD KEY `ID_TOPIC` (`TOPIC_ID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID_ADMIN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `club`
--
ALTER TABLE `club`
  MODIFY `ID_CLUB` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  MODIFY `ID_REQUEST` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `ID_EVENT` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `event_session`
--
ALTER TABLE `event_session`
  MODIFY `SESSION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `member`
--
ALTER TABLE `member`
  MODIFY `ID_MEMBER` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `speaker`
--
ALTER TABLE `speaker`
  MODIFY `SPEAKER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `topics`
--
ALTER TABLE `topics`
  MODIFY `TOPIC_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD CONSTRAINT `clubcreationrequest_ibfk_1` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE,
  ADD CONSTRAINT `clubcreationrequest_ibfk_2` FOREIGN KEY (`ID_ADMIN`) REFERENCES `admin` (`ID_ADMIN`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `contains_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE,
  ADD CONSTRAINT `contains_ibfk_2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `event_session`
--
ALTER TABLE `event_session`
  ADD CONSTRAINT `event_session_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD CONSTRAINT `focuses_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE,
  ADD CONSTRAINT `focuses_ibfk_2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE;

--
-- Contraintes pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD CONSTRAINT `organizes_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE,
  ADD CONSTRAINT `organizes_ibfk_2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `registre`
--
ALTER TABLE `registre`
  ADD CONSTRAINT `registre_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE,
  ADD CONSTRAINT `registre_ibfk_2` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE;

--
-- Contraintes pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD CONSTRAINT `requestjoin_ibfk_1` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE,
  ADD CONSTRAINT `requestjoin_ibfk_2` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE;

--
-- Contraintes pour la table `speaks`
--
ALTER TABLE `speaks`
  ADD CONSTRAINT `speaks_ibfk_1` FOREIGN KEY (`SPEAKER_ID`) REFERENCES `speaker` (`SPEAKER_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `speaks_ibfk_2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
