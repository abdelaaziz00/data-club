-- DataClub Database Population Script
-- This script fills the database with realistic sample data for the DataClub project

-- Clear existing data (optional - uncomment if you want to start fresh)
-- DELETE FROM registre;
-- DELETE FROM speaks;
-- DELETE FROM speaker;
-- DELETE FROM event_session;
-- DELETE FROM contains;
-- DELETE FROM organizes;
-- DELETE FROM evenement;
-- DELETE FROM requestjoin;
-- DELETE FROM focuses;
-- DELETE FROM willfocus;
-- DELETE FROM clubcreationrequest;
-- DELETE FROM club;
-- DELETE FROM member;
-- DELETE FROM admin;
-- DELETE FROM topics;

-- Reset auto-increment counters
-- ALTER TABLE admin AUTO_INCREMENT = 1;
-- ALTER TABLE member AUTO_INCREMENT = 1;
-- ALTER TABLE club AUTO_INCREMENT = 1;
-- ALTER TABLE evenement AUTO_INCREMENT = 1;
-- ALTER TABLE speaker AUTO_INCREMENT = 1;
-- ALTER TABLE event_session AUTO_INCREMENT = 1;
-- ALTER TABLE clubcreationrequest AUTO_INCREMENT = 1;
-- ALTER TABLE topics AUTO_INCREMENT = 1;

-- =====================================================
-- 1. INSERT ADMIN USERS
-- =====================================================

INSERT INTO `admin` (`FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`, `PROFILE_IMG`) VALUES
('Ahmed', 'Benali', 'admin@dataclub.ma', 'admin123', NULL),
('Fatima', 'Alaoui', 'fatima.admin@dataclub.ma', 'admin456', NULL),
('Karim', 'Tazi', 'karim.admin@dataclub.ma', 'admin789', NULL);

-- =====================================================
-- 2. INSERT TOPICS (Focus Areas)
-- =====================================================

INSERT INTO `topics` (`TOPIC_NAME`) VALUES
('Machine Learning'),
('Data Science'),
('Artificial Intelligence'),
('Web Development'),
('Cybersecurity'),
('Data Analytics'),
('Deep Learning'),
('Natural Language Processing'),
('Computer Vision'),
('Big Data'),
('Cloud Computing'),
('Mobile Development'),
('Blockchain'),
('IoT'),
('Robotics'),
('UI/UX Design'),
('DevOps'),
('Game Development'),
('AR/VR'),
('Data Engineering');

-- =====================================================
-- 3. INSERT MEMBERS (Students)
-- =====================================================

INSERT INTO `member` (`FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`, `PROFILE_IMG`) VALUES
('Youssef', 'El Amrani', 'youssef.elamrani@student.ma', 'student123', NULL),
('Amina', 'Bennani', 'amina.bennani@student.ma', 'student456', NULL),
('Hassan', 'Tazi', 'hassan.tazi@student.ma', 'student789', NULL),
('Layla', 'Mourad', 'layla.mourad@student.ma', 'student101', NULL),
('Omar', 'Fassi', 'omar.fassi@student.ma', 'student202', NULL),
('Nour', 'Alami', 'nour.alami@student.ma', 'student303', NULL),
('Adam', 'Benjelloun', 'adam.benjelloun@student.ma', 'student404', NULL),
('Zineb', 'Cherkaoui', 'zineb.cherkaoui@student.ma', 'student505', NULL),
('Ilyas', 'Mekouar', 'ilyas.mekouar@student.ma', 'student606', NULL),
('Sara', 'Bouazza', 'sara.bouazza@student.ma', 'student707', NULL),
('Amine', 'Lahlou', 'amine.lahlou@student.ma', 'student808', NULL),
('Hanae', 'Tahiri', 'hanae.tahiri@student.ma', 'student909', NULL),
('Yassine', 'Boukhari', 'yassine.boukhari@student.ma', 'student110', NULL),
('Aicha', 'Mansouri', 'aicha.mansouri@student.ma', 'student111', NULL),
('Khalid', 'Rachidi', 'khalid.rachidi@student.ma', 'student112', NULL);

-- =====================================================
-- 4. INSERT CLUBS
-- =====================================================

INSERT INTO `club` (`ID_MEMBER`, `NAME`, `DESCRIPTION`, `UNIVERSITY`, `CITY`, `INSTAGRAM_LINK`, `LINKEDIN_LINK`, `EMAIL`, `CLUB_PHONE`) VALUES
(1, 'Data Science Club Rabat', 'Leading data science community at Mohammed V University. We focus on machine learning, AI, and data analytics.', 'Mohammed V University', 'Rabat', '@dsc_rabat', 'dsc-rabat', 'dsc.rabat@um5.ac.ma', '0612345678'),
(2, 'AI & ML Club Casablanca', 'Innovative AI and machine learning community at Hassan II University. We explore cutting-edge AI technologies.', 'Hassan II University', 'Casablanca', '@aiml_casa', 'aiml-casa', 'aiml.casa@uh2c.ac.ma', '0623456789'),
(3, 'Tech Innovation Club Fes', 'Technology innovation hub at Sidi Mohamed Ben Abdellah University. We focus on emerging technologies.', 'Sidi Mohamed Ben Abdellah University', 'Fes', '@tech_fes', 'tech-fes', 'tech.fes@usmba.ac.ma', '0634567890'),
(4, 'Digital Transformation Club Marrakech', 'Digital transformation and innovation community at Cadi Ayyad University.', 'Cadi Ayyad University', 'Marrakech', '@dtc_marrakech', 'dtc-marrakech', 'dtc.marrakech@uca.ac.ma', '0645678901'),
(5, 'Cyber Security Club Agadir', 'Cybersecurity and digital security community at Ibn Zohr University.', 'Ibn Zohr University', 'Agadir', '@cyber_agadir', 'cyber-agadir', 'cyber.agadir@uiz.ac.ma', '0656789012');

-- =====================================================
-- 5. UPDATE MEMBERS WITH CLUB ASSOCIATIONS
-- =====================================================

UPDATE `member` SET `ID_CLUB` = 1 WHERE `ID_MEMBER` = 1;
UPDATE `member` SET `ID_CLUB` = 2 WHERE `ID_MEMBER` = 2;
UPDATE `member` SET `ID_CLUB` = 3 WHERE `ID_MEMBER` = 3;
UPDATE `member` SET `ID_CLUB` = 4 WHERE `ID_MEMBER` = 4;
UPDATE `member` SET `ID_CLUB` = 5 WHERE `ID_MEMBER` = 5;

-- =====================================================
-- 6. INSERT CLUB FOCUS AREAS
-- =====================================================

INSERT INTO `focuses` (`ID_CLUB`, `TOPIC_ID`) VALUES
-- Data Science Club Rabat
(1, 1), (1, 2), (1, 3), (1, 6),
-- AI & ML Club Casablanca
(2, 1), (2, 3), (2, 7), (2, 8),
-- Tech Innovation Club Fes
(3, 4), (3, 11), (3, 16), (3, 17),
-- Digital Transformation Club Marrakech
(4, 10), (4, 17), (4, 18), (4, 20),
-- Cyber Security Club Agadir
(5, 5), (5, 15), (5, 16), (5, 19);

-- =====================================================
-- 7. INSERT SPEAKERS
-- =====================================================

INSERT INTO `speaker` (`SPEAKER_FULLNAME`, `SPEAKER_DISCRIPTION`, `SPEAKER_COMPANY`) VALUES
('Dr. Sarah Bennani', 'Senior Data Scientist', 'Microsoft Morocco'),
('Prof. Ahmed Tazi', 'AI Research Director', 'Google AI'),
('Fatima Alami', 'Machine Learning Engineer', 'Amazon Web Services'),
('Omar Cherkaoui', 'Cybersecurity Expert', 'Check Point Software'),
('Layla Bouazza', 'Data Analytics Lead', 'IBM Morocco'),
('Hassan Mekouar', 'Cloud Solutions Architect', 'Oracle'),
('Amina Rachidi', 'Blockchain Developer', 'ConsenSys'),
('Youssef Mansouri', 'DevOps Engineer', 'GitLab'),
('Nour Benjelloun', 'UI/UX Designer', 'Adobe'),
('Adam Tahiri', 'Mobile App Developer', 'Apple Developer');

-- =====================================================
-- 8. INSERT EVENTS
-- =====================================================

INSERT INTO `evenement` (`TITLE`, `DESCRIPTION`, `DATE`, `STARTING_TIME`, `ENDING_TIME`, `LOCATION`, `CITY`, `EVENT_TYPE`, `PRICE`, `CAPACITY`) VALUES
('Introduction to Machine Learning', 'Learn the fundamentals of machine learning algorithms and their applications in real-world scenarios.', '2025-02-15', '09:00:00', '17:00:00', 'Faculty of Sciences, Room A101', 'Rabat', 'Workshop', 0, 50),
('AI Ethics and Responsible Development', 'Explore the ethical implications of AI development and best practices for responsible AI.', '2025-02-20', '14:00:00', '18:00:00', 'Engineering School, Auditorium', 'Casablanca', 'Seminar', 100, 100),
('Cybersecurity Fundamentals', 'Learn about cybersecurity threats, prevention strategies, and security best practices.', '2025-02-25', '10:00:00', '16:00:00', 'Computer Science Department', 'Agadir', 'Workshop', 50, 40),
('Web Development Bootcamp', 'Intensive 3-day bootcamp covering modern web development technologies and frameworks.', '2025-03-01', '09:00:00', '18:00:00', 'Innovation Center', 'Fes', 'Bootcamp', 200, 30),
('Data Science Hackathon', '24-hour hackathon focused on solving real-world problems using data science techniques.', '2025-03-10', '09:00:00', '09:00:00', 'University Campus', 'Marrakech', 'Hackathon', 0, 80),
('Cloud Computing Workshop', 'Hands-on workshop on cloud platforms and deployment strategies.', '2025-03-15', '13:00:00', '17:00:00', 'Tech Hub', 'Rabat', 'Workshop', 75, 60),
('Blockchain and Cryptocurrency', 'Introduction to blockchain technology and its applications beyond cryptocurrency.', '2025-03-20', '15:00:00', '19:00:00', 'Business School', 'Casablanca', 'Seminar', 150, 70),
('Mobile App Development', 'Learn to build native and cross-platform mobile applications.', '2025-03-25', '10:00:00', '16:00:00', 'Engineering Lab', 'Fes', 'Workshop', 100, 45),
('UI/UX Design Principles', 'Master the fundamentals of user interface and user experience design.', '2025-03-30', '14:00:00', '18:00:00', 'Design Studio', 'Marrakech', 'Workshop', 80, 35),
('DevOps and CI/CD', 'Learn about DevOps practices and continuous integration/deployment pipelines.', '2025-04-05', '09:00:00', '17:00:00', 'Computer Center', 'Agadir', 'Workshop', 120, 50);

-- =====================================================
-- 9. LINK EVENTS TO CLUBS (ORGANIZES)
-- =====================================================

INSERT INTO `organizes` (`ID_CLUB`, `ID_EVENT`) VALUES
(1, 1), (1, 6), -- Data Science Club Rabat
(2, 2), (2, 7), -- AI & ML Club Casablanca
(5, 3), -- Cyber Security Club Agadir
(3, 4), (3, 8), -- Tech Innovation Club Fes
(4, 5), (4, 9), -- Digital Transformation Club Marrakech
(1, 10); -- Data Science Club Rabat

-- =====================================================
-- 10. LINK EVENTS TO TOPICS (CONTAINS)
-- =====================================================

INSERT INTO `contains` (`ID_EVENT`, `TOPIC_ID`) VALUES
-- Machine Learning Workshop
(1, 1), (1, 2), (1, 3),
-- AI Ethics Seminar
(2, 3), (2, 8),
-- Cybersecurity Workshop
(3, 5), (3, 16),
-- Web Development Bootcamp
(4, 4), (4, 17),
-- Data Science Hackathon
(5, 2), (5, 6), (5, 10),
-- Cloud Computing Workshop
(6, 11), (6, 18),
-- Blockchain Seminar
(7, 13), (7, 15),
-- Mobile Development Workshop
(8, 12), (8, 16),
-- UI/UX Workshop
(9, 16), (9, 17),
-- DevOps Workshop
(10, 18), (10, 20);

-- =====================================================
-- 11. LINK SPEAKERS TO EVENTS (SPEAKS)
-- =====================================================

INSERT INTO `speaks` (`SPEAKER_ID`, `ID_EVENT`) VALUES
(1, 1), -- Dr. Sarah Bennani at ML Workshop
(2, 2), -- Prof. Ahmed Tazi at AI Ethics
(4, 3), -- Omar Cherkaoui at Cybersecurity
(6, 4), -- Hassan Mekouar at Web Development
(5, 5), -- Layla Bouazza at Data Science Hackathon
(6, 6), -- Hassan Mekouar at Cloud Computing
(7, 7), -- Amina Rachidi at Blockchain
(8, 8), -- Youssef Mansouri at Mobile Development
(9, 9), -- Nour Benjelloun at UI/UX
(8, 10); -- Youssef Mansouri at DevOps

-- =====================================================
-- 12. INSERT EVENT SESSIONS
-- =====================================================

INSERT INTO `event_session` (`ID_EVENT`, `SESSION_NAME`) VALUES
(1, '09:00 - 10:30 - Introduction to ML Concepts'),
(1, '10:45 - 12:15 - Supervised Learning Algorithms'),
(1, '14:00 - 15:30 - Unsupervised Learning'),
(1, '15:45 - 17:00 - Hands-on Practice'),
(2, '14:00 - 15:30 - AI Ethics Overview'),
(2, '15:45 - 17:00 - Case Studies'),
(2, '17:15 - 18:00 - Q&A Session'),
(4, '09:00 - 10:30 - HTML/CSS Fundamentals'),
(4, '10:45 - 12:15 - JavaScript Basics'),
(4, '14:00 - 15:30 - React Framework'),
(4, '15:45 - 17:00 - Backend Integration'),
(4, '09:00 - 10:30 - Advanced Topics'),
(4, '10:45 - 12:15 - Project Development'),
(4, '14:00 - 17:00 - Final Project Presentation');

-- =====================================================
-- 13. INSERT CLUB JOIN REQUESTS
-- =====================================================

INSERT INTO `requestjoin` (`ID_MEMBER`, `ID_CLUB`, `ACCEPTED`) VALUES
-- Accepted requests
(6, 1, 1), (7, 1, 1), (8, 1, 1), -- Data Science Club Rabat
(9, 2, 1), (10, 2, 1), -- AI & ML Club Casablanca
(11, 3, 1), (12, 3, 1), -- Tech Innovation Club Fes
(13, 4, 1), (14, 4, 1), -- Digital Transformation Club Marrakech
(15, 5, 1), -- Cyber Security Club Agadir
-- Pending requests
(6, 2, 0), (7, 3, 0), (8, 4, 0), (9, 5, 0), (10, 1, 0);

-- =====================================================
-- 14. INSERT EVENT REGISTRATIONS
-- =====================================================

INSERT INTO `registre` (`ID_EVENT`, `ID_MEMBER`) VALUES
-- Machine Learning Workshop registrations
(1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
-- AI Ethics Seminar registrations
(2, 9), (2, 10), (2, 11), (2, 12),
-- Cybersecurity Workshop registrations
(3, 15), (3, 6), (3, 7),
-- Web Development Bootcamp registrations
(4, 11), (4, 12), (4, 13), (4, 14),
-- Data Science Hackathon registrations
(5, 13), (5, 14), (5, 15), (5, 6), (5, 7),
-- Cloud Computing Workshop registrations
(6, 6), (6, 7), (6, 8), (6, 9), (6, 10);

-- =====================================================
-- 15. INSERT CLUB CREATION REQUESTS
-- =====================================================

INSERT INTO `clubcreationrequest` (`ID_MEMBER`, `CLUB_NAME`, `DESCRIPTION`, `TEAM_MEMBERS`, `STATUS`, `UNIVERSITY`, `CITY`, `INSTAGRAM_LINK`, `LINKEDIN_LINK`, `EMAIL`, `CLUB_PHONE`, `Motif`) VALUES
-- Pending requests
(6, 'Quantum Computing Club', 'Exploring quantum computing and its applications in cryptography and optimization.', 25, 'pending', 'Mohammed VI Polytechnic University', 'Ben Guerir', '@quantum_club', 'quantum-club', 'quantum.club@um6p.ma', '0612345678', 'We want to explore the cutting-edge field of quantum computing and prepare students for the future of technology.'),
(7, 'Robotics and Automation Club', 'Building robots and exploring automation technologies for industrial and educational applications.', 30, 'pending', 'National School of Applied Sciences', 'Tangier', '@robotics_club', 'robotics-club', 'robotics.club@ensat.ma', '0623456789', 'Our goal is to inspire students to explore robotics and automation, preparing them for Industry 4.0.'),
(8, 'Digital Marketing Club', 'Learning modern digital marketing strategies, SEO, and social media management.', 20, 'pending', 'School of Information Sciences', 'Rabat', '@digital_marketing', 'digital-marketing', 'marketing.club@esi.ac.ma', '0634567890', 'We aim to bridge the gap between technology and business through digital marketing education.'),
-- Approved requests (with admin tracking)
(9, 'Game Development Club', 'Creating video games and learning game design principles and development tools.', 35, 'approved', 'National School of Computer Science', 'Rabat', '@game_dev_club', 'game-dev-club', 'gamedev.club@ensias.ma', '0645678901', 'We want to foster creativity and technical skills through game development.'),
(10, 'IoT and Smart Cities Club', 'Exploring Internet of Things technologies and smart city solutions.', 28, 'approved', 'Hassan II University', 'Casablanca', '@iot_club', 'iot-club', 'iot.club@uh2c.ac.ma', '0656789012', 'Our mission is to develop IoT solutions for smart cities and sustainable development.'),
-- Rejected requests
(11, 'Duplicate Club Request', 'This is a duplicate request that should be rejected.', 15, 'rejected', 'Test University', 'Test City', '@duplicate', 'duplicate-club', 'duplicate@test.ma', '0667890123', 'This request should be rejected as it is a duplicate.');

-- =====================================================
-- 16. INSERT WILLFOCUS (Focus areas for pending requests)
-- =====================================================

INSERT INTO `willfocus` (`ID_REQUEST`, `TOPIC_ID`) VALUES
-- Quantum Computing Club focus areas
(1, 1), (1, 3), (1, 13), (1, 15),
-- Robotics and Automation Club focus areas
(2, 15), (2, 14), (2, 20),
-- Digital Marketing Club focus areas
(3, 16), (3, 17), (3, 18),
-- Game Development Club focus areas
(4, 18), (4, 16), (4, 17),
-- IoT and Smart Cities Club focus areas
(5, 14), (5, 10), (5, 20),
-- Duplicate Club focus areas
(6, 1), (6, 2);

-- =====================================================
-- 17. UPDATE APPROVED REQUESTS WITH ADMIN TRACKING
-- =====================================================

UPDATE `clubcreationrequest` SET `ID_ADMIN` = 1 WHERE `ID_REQUEST` = 4; -- Game Development Club approved by Ahmed
UPDATE `clubcreationrequest` SET `ID_ADMIN` = 2 WHERE `ID_REQUEST` = 5; -- IoT Club approved by Fatima
UPDATE `clubcreationrequest` SET `ID_ADMIN` = 3 WHERE `ID_REQUEST` = 6; -- Duplicate Club rejected by Karim

-- =====================================================
-- SCRIPT COMPLETION MESSAGE
-- =====================================================

-- The database has been populated with realistic sample data including:
-- - 3 Admin users
-- - 20 Topics (focus areas)
-- - 15 Members (students)
-- - 5 Active Clubs
-- - 10 Events with speakers and sessions
-- - Club join requests (accepted and pending)
-- - Event registrations
-- - Club creation requests (pending, approved, and rejected)
-- - Focus areas for all clubs and requests

-- All relationships are properly established:
-- - Clubs have focus areas
-- - Events are linked to clubs and topics
-- - Speakers are assigned to events
-- - Members are registered for events
-- - Join requests are tracked
-- - Admin tracking is implemented

SELECT 'Database population completed successfully!' as Status; 