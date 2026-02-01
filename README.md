DevForum - Forum Communautaire pour Développeurs
 Version 

 PHP 

 Bootstrap 

 License 
Un forum communautaire moderne et réactif pour développeurs, conçu pour faciliter le partage de connaissances, l'apprentissage et la collaboration.
📋 Table des matières
Aperçu
Fonctionnalités
Captures d'écran
Prérequis
Installation
Configuration
Structure du projet
API Documentation
Base de données
Utilisation
BBCode Support
Responsive Design
Sécurité
Contribution
Licence
Auteur
🎯 Aperçu
DevForum est une application web de forum développée en PHP avec une interface utilisateur moderne utilisant Bootstrap 5. Elle offre une expérience utilisateur fluide avec des fonctionnalités avancées comme la recherche en temps réel, le support BBCode, la pagination, et une interface entièrement responsive.
Points forts
🎨 Interface moderne et intuitive
⚡ Rechargement dynamique sans refresh (AJAX)
📱 Design 100% responsive (mobile, tablette, desktop)
🔍 Recherche en temps réel avec debounce
💬 Éditeur BBCode avec aperçu en direct
📊 Statistiques du forum en temps réel
🔔 Système de notifications toast
📡 Flux RSS intégré
✨ Fonctionnalités
Gestion des Catégories
Création et affichage de catégories
Compteur de sujets par catégorie
Navigation hiérarchique (breadcrumb)
Gestion des Sujets
Création de nouveaux sujets
Épinglage/désépinglage des sujets
Verrouillage/déverrouillage des sujets
Compteur de vues et de réponses
Pagination des sujets
Gestion des Messages
Publication de réponses
Modification des messages
Suppression des messages
Aperçu BBCode en temps réel
Badges "Auteur du sujet" et "Modifié"
Recherche
Recherche en temps réel (debounce 400ms)
Recherche par titre, contenu et auteur
Affichage des résultats avec mise en évidence
Statistiques
Sujets les plus populaires
Contributeurs les plus actifs
Nombre total de sujets et membres
Interface Utilisateur
Sidebar navigation avec statistiques
Navigation mobile optimisée (bottom nav)
Mode sombre/clair (préparation)
Toast notifications
Loading states
📸 Captures d'écran
Les captures d'écran seront ajoutées prochainement
🔧 Prérequis
Serveur
PHP >= 8.0
MySQL >= 5.7 ou MariaDB >= 10.3
Apache >= 2.4 ou Nginx >= 1.18
Composer (optionnel, pour l'autoloading)
Extensions PHP requises
pdo et pdo_mysql
json
mbstring
xml (pour le flux RSS)
Navigateurs supportés
Chrome >= 90
Firefox >= 88
Safari >= 14
Edge >= 90
🚀 Installation
1. Cloner le projet
bash
Copy
git clone https://github.com/votre-username/devforum.git
cd devforum
2. Configurer la base de données
bash
Copy
# Créer la base de données
mysql -u root -p -e "CREATE DATABASE devforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importer le schéma
mysql -u root -p devforum < database/schema.sql
3. Configuration
bash
Copy
# Copier le fichier de configuration
cp config/database.example.php config/database.php

# Éditer les paramètres de connexion
nano config/database.php
4. Permissions
bash
Copy
# Définir les permissions (Linux/Mac)
chmod 755 -R .
chmod 777 -R uploads/  # Si vous avez des uploads
5. Accéder au forum
Ouvrez votre navigateur et accédez à :
http://localhost/devforum/
⚙️ Configuration
Fichier config/database.php
php
Copy
<?php
return [
    'host'     => 'localhost',
    'database' => 'devforum',
    'username' => 'votre_username',
    'password' => 'votre_mot_de_passe',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
Configuration Apache (.htaccess)
apache
Copy
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
📁 Structure du projet
Copy
devforum/
├── api/
│   └── forum.php              # API REST principale
├── config/
│   ├── database.php           # Configuration BDD
│   └── database.example.php   # Exemple de configuration
├── css/
│   └── style.css              # Styles personnalisés
├── js/
│   └── forum.js               # JavaScript principal
├── database/
│   └── schema.sql             # Schéma de la base de données
├── uploads/                   # Dossier des uploads (si activé)
├── index.php                  # Point d'entrée principal
├── README.md                  # Ce fichier
└── .htaccess                  # Configuration Apache
📚 API Documentation
Endpoints
Table
Copy
Méthode	Endpoint	Description
GET	api/forum.php?action=categories	Liste toutes les catégories
GET	api/forum.php?action=sujets	Liste les sujets (avec filtres)
GET	api/forum.php?action=sujet&id={id}	Détails d'un sujet
POST	api/forum.php?action=sujets	Créer un nouveau sujet
PUT	api/forum.php?action=sujet	Modifier un sujet
GET	api/forum.php?action=messages	Liste les messages d'un sujet
POST	api/forum.php?action=messages	Ajouter un message
PUT	api/forum.php?action=message	Modifier un message
DELETE	api/forum.php?action=message	Supprimer un message
GET	api/forum.php?action=search&q={query}	Rechercher
GET	api/forum.php?action=stats	Statistiques du forum
GET	api/forum.php?action=rss	Flux RSS
Exemples de requêtes
Créer un sujet
JavaScript
Copy
fetch('api/forum.php?action=sujets', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        categorie_id: 1,
        auteur: 'JohnDoe',
        titre: 'Mon premier sujet',
        contenu: 'Contenu du message...'
    })
});
Rechercher
JavaScript
Copy
fetch('api/forum.php?action=search&q=php')
    .then(r => r.json())
    .then(data => console.log(data));
🗄️ Base de données
Schéma
sql
Copy
-- Table: categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    ordre INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: sujets
CREATE TABLE sujets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categorie_id INT NOT NULL,
    auteur VARCHAR(50) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    epingle BOOLEAN DEFAULT FALSE,
    verrouille BOOLEAN DEFAULT FALSE,
    vues INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table: messages
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sujet_id INT NOT NULL,
    auteur VARCHAR(50) NOT NULL,
    contenu TEXT NOT NULL,
    modifie BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sujet_id) REFERENCES sujets(id) ON DELETE CASCADE
);
Indexes recommandés
sql
Copy
CREATE INDEX idx_sujets_categorie ON sujets(categorie_id);
CREATE INDEX idx_sujets_epingle ON sujets(epingle);
CREATE INDEX idx_messages_sujet ON messages(sujet_id);
CREATE FULLTEXT INDEX idx_search ON sujets(titre), messages(contenu);
🎮 Utilisation
Navigation
Accueil - Liste des catégories
Tous les sujets - Vue globale de toutes les discussions
Catégorie - Sujets d'une catégorie spécifique
Sujet - Messages d'une discussion
Actions utilisateur
Table
Copy
Action	Comment
Créer un sujet	Cliquer sur "Nouveau sujet"
Répondre	Remplir le formulaire en bas du sujet
Modifier	Cliquer sur l'icône ✏️ sur son message
Supprimer	Cliquer sur l'icône 🗑️ sur son message
Épingler	Bouton "Épingler" (créateur/modérateur)
Verrouiller	Bouton "Verrouiller" (créateur/modérateur)
📝 BBCode Support
Le forum supporte les balises BBCode suivantes :
Table
Copy
Balise	Rendu	Exemple
[b]texte[/b]	Gras	[b]Important[/b]
[i]texte[/i]	Italique	[i]Citation[/i]
[u]texte[/u]	<u>	[u]Note[/u]
[code]code[/code]	Code	[code]echo "Hello";[/code]
[url=lien]texte[/url]	Lien	[url=https://example.com]Site[/url]
[quote]texte[/quote]	> Citation	[quote]Citation[/quote]
Barre d'outils BBCode
L'éditeur dispose d'une barre d'outils pour insérer facilement les balises :
B - Gras
I - Italique
<u> - Souligné
</> - Code
🔗 - Lien
❝ ❞ - Citation
📱 Responsive Design
Breakpoints
Table
Copy
Breakpoint	Largeur	Description
Mobile	< 576px	Navigation bottom, sidebar cachée
Tablette	576px - 991px	Sidebar collapsible
Desktop	>= 992px	Sidebar fixe, pleine largeur
Composants adaptatifs
Navbar : Compacte sur mobile avec recherche dépliable
Sidebar : Drawer sur mobile, fixe sur desktop
Bottom Nav : Navigation rapide sur mobile
Cards : Pleine largeur sur mobile, grille sur desktop
Formulaires : Empilés sur mobile, côte à côte sur desktop
🔒 Sécurité
Mesures implémentées
Table
Copy
Mesure	Description
XSS Protection	Échappement HTML avec escapeHtml()
SQL Injection	Requêtes préparées PDO
CSRF	Tokens (à implémenter selon besoin)
Validation	Validation côté client et serveur
Rate Limiting	Debounce sur la recherche
Bonnes pratiques
JavaScript
Copy
// Toujours échapper le contenu utilisateur
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
🤝 Contribution
Les contributions sont les bienvenues ! Voici comment participer :
Rapport de bugs
Vérifiez si le bug n'a pas déjà été signalé
Créez une issue avec :
Description détaillée
Étapes de reproduction
Comportement attendu vs réel
Screenshots si applicable
Pull Requests
Fork le projet
Créez une branche (git checkout -b feature/ma-fonctionnalite)
Committez vos changements (git commit -m 'Ajout de...')
Push sur la branche (git push origin feature/ma-fonctionnalite)
Ouvrez une Pull Request
Code Style
PHP : PSR-12
JavaScript : ESLint recommandé
CSS : BEM methodology
📄 Licence
Ce projet est sous licence MIT.
Copy
MIT License

Copyright (c) 2024 DevForum

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.


