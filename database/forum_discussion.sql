-- ============================================
-- DevForum - Script de création de la base de données
-- Version: 1.0
-- Date: Janvier 2025
-- ============================================

-- Supprimer la base de données si elle existe
DROP DATABASE IF EXISTS forum_discussion;

-- Créer la base de données
CREATE DATABASE forum_discussion 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE forum_discussion;

-- ============================================
-- TABLE: categories
-- Description: Catégories du forum
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    ordre INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: sujets
-- Description: Sujets de discussion
-- ============================================
CREATE TABLE sujets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    vues INT DEFAULT 0,
    epingle TINYINT(1) DEFAULT 0,
    verrouille TINYINT(1) DEFAULT 0,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_categorie (categorie_id),
    INDEX idx_date (date_creation),
    INDEX idx_epingle (epingle),
    FULLTEXT INDEX idx_titre (titre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: messages
-- Description: Messages/réponses dans les sujets
-- ============================================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sujet_id INT NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL,
    modifie TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sujet_id) REFERENCES sujets(id) ON DELETE CASCADE,
    INDEX idx_sujet (sujet_id),
    INDEX idx_auteur (auteur),
    INDEX idx_date (date_creation),
    FULLTEXT INDEX idx_contenu (contenu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES DE TEST: Catégories
-- ============================================
INSERT INTO categories (nom, description, ordre) VALUES
('Général', 'Discussions générales sur tous les sujets', 1),
('Aide & Support', 'Posez vos questions techniques ici', 2),
('Présentations', 'Présentez-vous à la communauté', 3),
('Suggestions', 'Proposez vos idées pour améliorer le forum', 4),
('Hors-Sujet', 'Discussions libres et détente', 5);

-- ============================================
-- DONNÉES DE TEST: Sujets
-- ============================================
INSERT INTO sujets (categorie_id, titre, auteur, date_creation, vues, epingle) VALUES
-- Catégorie Général
(1, 'Bienvenue sur DevForum !', 'Admin', '2025-01-01 10:00:00', 150, 1),
(1, 'Règles du forum à lire', 'Admin', '2025-01-01 10:30:00', 89, 1),

-- Catégorie Aide & Support
(2, 'Comment réinitialiser mon mot de passe ?', 'Marie_User', '2025-01-05 14:00:00', 45, 0),
(2, 'Problème de connexion au serveur', 'Pierre_Dev', '2025-01-10 16:45:00', 67, 0),

-- Catégorie Présentations
(3, 'Présentez-vous ici', 'Admin', '2025-01-01 11:00:00', 234, 1),

-- Catégorie Suggestions
(4, 'Ajout d''un mode sombre', 'Lucas_Design', '2025-01-08 13:00:00', 78, 0),
(4, 'Application mobile ?', 'Sophie_Mobile', '2025-01-12 09:00:00', 56, 0),

-- Catégorie Hors-Sujet
(5, 'Café du matin ☕ - Discussion libre', 'Modérateur_Jean', '2025-01-06 08:00:00', 189, 0),
(5, '[TUTO] Configurer son profil étape par étape', 'Admin', '2025-01-03 14:00:00', 123, 1);

-- ============================================
-- DONNÉES DE TEST: Messages
-- ============================================
INSERT INTO messages (sujet_id, auteur, contenu, date_creation) VALUES
-- Sujet 1: Bienvenue
(1, 'Admin', 'Bienvenue sur DevForum, votre nouvelle communauté de développeurs !\n\nN''hésitez pas à vous présenter et à participer aux discussions.\n\nBonne navigation ! 🚀', '2025-01-01 10:00:00'),
(1, 'Pierre_Dev', 'Merci pour ce forum ! Hâte de découvrir la communauté.', '2025-01-02 09:00:00'),
(1, 'Marie_User', 'Super initiative ! Le design est vraiment moderne.', '2025-01-02 11:30:00'),

-- Sujet 2: Règles
(2, 'Admin', '[b]Règles du forum[/b]\n\n1. Respectez les autres membres\n2. Pas de spam ni de publicité\n3. Utilisez les bonnes catégories\n4. Faites des recherches avant de poster\n\nMerci de votre compréhension !', '2025-01-01 10:30:00'),

-- Sujet 3: Mot de passe
(3, 'Marie_User', 'Bonjour, j''ai oublié mon mot de passe. Comment puis-je le réinitialiser ?', '2025-01-05 14:00:00'),
(3, 'Admin', 'Bonjour Marie,\n\nPour le moment, contactez un administrateur qui pourra vous aider.\n\nUne fonction de récupération sera bientôt disponible.', '2025-01-05 15:00:00'),

-- Sujet 4: Problème connexion
(4, 'Pierre_Dev', 'Bonjour, depuis ce matin je n''arrive plus à me connecter au serveur. J''ai l''erreur "Connection timeout". Quelqu''un a le même problème ?', '2025-01-10 16:45:00'),
(4, 'Thomas_IT', 'As-tu vérifié ta connexion internet ? Essaie aussi de vider le cache.', '2025-01-10 17:00:00'),
(4, 'Pierre_Dev', 'Oui, j''ai essayé tout ça. Le problème persiste...', '2025-01-10 17:15:00'),
(4, 'Admin', 'Nous avons identifié un problème serveur. La correction est en cours. Merci de votre patience.', '2025-01-10 18:00:00'),
(4, 'Pierre_Dev', 'Ça remarche ! Merci pour la réactivité ! 🎉', '2025-01-10 19:30:00'),

-- Sujet 5: Présentations
(5, 'Admin', 'Bienvenue dans l''espace présentations !\n\nN''hésitez pas à vous présenter : qui êtes-vous, quels sont vos centres d''intérêt, vos compétences...', '2025-01-01 11:00:00'),
(5, 'Lucas_Design', 'Salut ! Je suis Lucas, designer UI/UX depuis 3 ans. Passionné par les interfaces modernes et le Material Design.', '2025-01-03 10:00:00'),
(5, 'Sophie_Mobile', 'Hello ! Sophie ici, développeuse mobile (Flutter/React Native). Ravie de rejoindre cette communauté !', '2025-01-04 14:00:00'),

-- Sujet 6: Mode sombre
(6, 'Lucas_Design', 'Ce serait génial d''avoir un mode sombre pour le forum. Beaucoup de sites le proposent maintenant et c''est plus agréable pour les yeux le soir.', '2025-01-08 13:00:00'),
(6, 'Emma_Community', '+1 pour cette suggestion ! J''utilise toujours le mode sombre quand c''est possible.', '2025-01-08 14:30:00'),
(6, 'Sophie_Mobile', 'Totalement d''accord, surtout sur mobile !', '2025-01-08 16:00:00'),
(6, 'Admin', 'Bonne idée ! Nous l''ajoutons à notre roadmap pour la prochaine mise à jour.', '2025-01-09 10:00:00'),
(6, 'Lucas_Design', 'Super nouvelle ! Merci d''écouter la communauté 😊', '2025-01-09 11:30:00'),

-- Sujet 7: Application mobile
(7, 'Sophie_Mobile', 'Est-ce qu''une application mobile est prévue ? Ce serait pratique pour suivre les discussions en déplacement.', '2025-01-12 09:00:00'),
(7, 'Admin', 'Pas pour le moment, mais le site est entièrement responsive. Vous pouvez l''ajouter à votre écran d''accueil comme une PWA.', '2025-01-12 10:30:00'),

-- Sujet 8: Café du matin
(8, 'Modérateur_Jean', 'Bonjour à tous ! ☕ Ce topic est dédié aux discussions libres. Passez dire bonjour !', '2025-01-06 08:00:00'),
(8, 'Julie_Fun', 'Bonjour ! Premier café de la journée, prête à attaquer la semaine !', '2025-01-06 08:15:00'),
(8, 'Alex_Music', 'Salut tout le monde ! Du thé pour moi 🍵', '2025-01-06 08:30:00'),
(8, 'Pierre_Dev', 'Hello ! Café + code = productivité maximale 💻', '2025-01-06 09:00:00'),
(8, 'Emma_Community', 'Bonne journée à tous ! ☀️', '2025-01-06 09:30:00'),

-- Sujet 9: Tuto profil
(9, 'Admin', '[b]Guide de configuration du profil[/b]\n\n[code]Étape 1[/code] : Accédez aux paramètres\n[code]Étape 2[/code] : Modifiez vos informations\n[code]Étape 3[/code] : Sauvegardez\n\nN''hésitez pas à poser vos questions !', '2025-01-03 14:00:00'),
(9, 'Marie_User', 'Merci pour ce guide clair ! J''ai pu configurer mon profil facilement.', '2025-01-03 15:00:00'),
(9, 'Antoine_Tech', 'Très utile pour les nouveaux membres 👍', '2025-01-04 10:00:00');

-- ============================================
-- VUES UTILES (optionnel)
-- ============================================

-- Vue: Statistiques par catégorie
CREATE OR REPLACE VIEW vue_stats_categories AS
SELECT 
    c.id,
    c.nom,
    COUNT(DISTINCT s.id) as nb_sujets,
    COUNT(m.id) as nb_messages
FROM categories c
LEFT JOIN sujets s ON c.id = s.categorie_id
LEFT JOIN messages m ON s.id = m.sujet_id
GROUP BY c.id;

-- Vue: Derniers sujets actifs
CREATE OR REPLACE VIEW vue_derniers_sujets AS
SELECT 
    s.*,
    c.nom as categorie_nom,
    (SELECT COUNT(*) FROM messages WHERE sujet_id = s.id) as nb_messages,
    (SELECT MAX(date_creation) FROM messages WHERE sujet_id = s.id) as dernier_message
FROM sujets s
JOIN categories c ON s.categorie_id = c.id
ORDER BY dernier_message DESC
LIMIT 10;

-- ============================================
-- PROCÉDURES STOCKÉES (optionnel)
-- ============================================

DELIMITER //

-- Procédure: Obtenir les statistiques globales
CREATE PROCEDURE sp_stats_globales()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM sujets) as total_sujets,
        (SELECT COUNT(*) FROM messages) as total_messages,
        (SELECT COUNT(DISTINCT auteur) FROM messages) as total_auteurs;
END //

DELIMITER ;

-- ============================================
-- INDEX SUPPLÉMENTAIRES POUR PERFORMANCES
-- ============================================
-- Les index principaux sont déjà créés dans les tables
-- Ces index supplémentaires peuvent améliorer certaines requêtes

-- Index pour la recherche combinée
-- ALTER TABLE sujets ADD FULLTEXT INDEX idx_recherche (titre, auteur);

-- ============================================
-- FIN DU SCRIPT
-- ============================================

-- Vérification finale
SELECT 'Base de données forum_discussion créée avec succès !' as message;
SELECT CONCAT(COUNT(*), ' catégories') as info FROM categories
UNION ALL
SELECT CONCAT(COUNT(*), ' sujets') FROM sujets
UNION ALL
SELECT CONCAT(COUNT(*), ' messages') FROM messages;
