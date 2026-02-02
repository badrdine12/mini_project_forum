# 🗨️ DevForum - Forum de Discussion Communautaire

Un forum de discussion moderne développé en PHP, JavaScript (AJAX) et MySQL avec une interface responsive Material Design.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

---

## 📋 Table des matières

- [Présentation](#-présentation)
- [Fonctionnalités](#-fonctionnalités)
- [Technologies utilisées](#-technologies-utilisées)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Structure du projet](#-structure-du-projet)
- [Architecture de l'application](#-architecture-de-lapplication)
- [API REST](#-api-rest)
- [Base de données](#-base-de-données)
- [Captures d'écran](#-captures-décran)
- [Tests](#-tests)
- [Auteurs](#-auteurs)
- [Licence](#-licence)

---

## 🎯 Présentation

### Objectif du projet

DevForum est une application web de forum communautaire permettant aux utilisateurs de :
- Créer et participer à des discussions organisées par catégories
- Échanger des messages avec mise en forme (BBCode)
- Rechercher du contenu en temps réel
- Suivre les tendances et statistiques du forum

### Contexte

Ce projet a été réalisé dans le cadre du module **Développement Web** pour démontrer la maîtrise des technologies web côté client et serveur.

### Public cible

- Communautés de développeurs
- Groupes d'étudiants
- Entreprises (support interne)
- Toute communauté souhaitant un espace de discussion structuré

---

## ✨ Fonctionnalités

### 📂 Gestion des Catégories
| Fonctionnalité | Description |
|----------------|-------------|
| Liste des catégories | Affichage avec nombre de sujets |
| Navigation | Accès direct aux sujets d'une catégorie |

### 💬 Gestion des Sujets
| Fonctionnalité | Description |
|----------------|-------------|
| Créer un sujet | Titre + message initial |
| Consulter | Affichage paginé des messages |
| Épingler | Maintenir un sujet en haut de liste |
| Verrouiller | Empêcher les nouvelles réponses |
| Compteur de vues | Statistiques de consultation |

### 📝 Gestion des Messages
| Fonctionnalité | Description |
|----------------|-------------|
| Répondre | Ajouter un message à un sujet |
| Modifier | Éditer son message |
| Supprimer | Retirer un message |
| BBCode | Mise en forme (gras, italique, code, liens, citations) |
| Prévisualisation | Aperçu en temps réel |

### 🔍 Recherche
| Fonctionnalité | Description |
|----------------|-------------|
| Recherche AJAX | Résultats en temps réel |
| Multi-critères | Titre, contenu, auteur |
| Résultats paginés | Navigation facilitée |

### 📊 Statistiques
| Fonctionnalité | Description |
|----------------|-------------|
| Sujets populaires | Top 10 par vues |
| Top contributeurs | Classement par messages |
| Compteurs globaux | Sujets, messages, membres |

### 📡 Flux RSS
| Fonctionnalité | Description |
|----------------|-------------|
| Derniers messages | 20 dernières publications |
| Format standard | Compatible tous lecteurs RSS |

### 🎨 Interface Utilisateur
| Fonctionnalité | Description |
|----------------|-------------|
| Design responsive | Mobile, tablette, desktop |
| Sidebar navigation | Menu latéral structuré |
| Bottom navigation | Barre mobile |
| Notifications Toast | Feedback visuel moderne |
| Animations fluides | Transitions CSS |

---

## 🛠 Technologies utilisées

### Backend
| Technologie | Version | Utilisation |
|-------------|---------|-------------|
| PHP | 8.0+ | API REST, logique métier |
| MySQL | 5.7+ | Base de données relationnelle |
| PDO | - | Accès base de données sécurisé |

### Frontend
| Technologie | Version | Utilisation |
|-------------|---------|-------------|
| HTML5 | - | Structure des pages |
| CSS3 | - | Styles et animations |
| JavaScript | ES6+ | Interactivité (AJAX) |
| Bootstrap | 5.3 | Framework CSS responsive |
| Bootstrap Icons | 1.11 | Bibliothèque d'icônes |
| Google Fonts | Inter | Typographie moderne |

### Architecture
| Concept | Implementation |
|---------|----------------|
| SPA | Single Page Application |
| REST API | Interface de communication |
| MVC | Séparation des responsabilités |
| AJAX | Requêtes asynchrones |
| JSON | Format d'échange de données |

---

## 📦 Prérequis

### Logiciels requis

- **Serveur web** : XAMPP, WAMP, MAMP ou serveur Apache/Nginx
- **PHP** : Version 8.0 ou supérieure
- **MySQL** : Version 5.7 ou supérieure
- **Navigateur** : Chrome, Firefox, Edge ou Safari (versions récentes)

### Extensions PHP requises

```
✅ pdo_mysql
✅ json
✅ mbstring
```

---

## 🚀 Installation

### Étape 1 : Télécharger le projet

**Option A - Cloner avec Git :**
```bash
git clone https://github.com/votre-username/mini_project_forum.git
```

**Option B - Télécharger le ZIP :**
1. Télécharger le fichier ZIP
2. Extraire le contenu

### Étape 2 : Placer dans le dossier web

| Serveur | Chemin |
|---------|--------|
| XAMPP Windows | `C:\xampp\htdocs\mini_project_forum` |
| XAMPP Mac | `/Applications/XAMPP/htdocs/mini_project_forum` |
| WAMP | `C:\wamp64\www\mini_project_forum` |
| Linux | `/var/www/html/mini_project_forum` |

### Étape 3 : Créer la base de données

1. Démarrer **Apache** et **MySQL** dans XAMPP
2. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
3. Créer une nouvelle base de données : `forum_discussion`
4. Importer le fichier SQL :
   - Aller dans l'onglet "Importer"
   - Sélectionner `database/forum_discussion.sql`
   - Cliquer sur "Exécuter"

### Étape 4 : Configurer la connexion

Modifier `config/database.php` si nécessaire :

```php
<?php
define('DB_HOST', 'localhost');     // Serveur MySQL
define('DB_NAME', 'forum_discussion'); // Nom de la base
define('DB_USER', 'root');          // Utilisateur MySQL
define('DB_PASS', '');              // Mot de passe (vide par défaut sur XAMPP)
```

### Étape 5 : Lancer l'application

Ouvrir dans le navigateur :
```
http://localhost/mini_project_forum/
```

---

## 📁 Structure du projet

```
mini_project_forum/
│
├── 📂 api/
│   └── forum.php              # API REST (toutes les actions CRUD)
│
├── 📂 config/
│   └── database.php           # Configuration connexion BDD
│
├── 📂 css/
│   └── style.css              # Styles personnalisés (1800+ lignes)
│
├── 📂 js/
│   └── forum.js               # JavaScript AJAX (780+ lignes)
│
├── 📂 includes/
│   ├── header.php             # En-tête réutilisable
│   └── footer.php             # Pied de page réutilisable
│
├── 📂 database/
│   └── forum_discussion.sql   # Script SQL (structure + données)
│
├── 📄 index.php               # Page principale (SPA)
├── 📄 README.md               # Documentation (ce fichier)
└── 📄 .htaccess               # Configuration Apache (optionnel)
```

---

## 🏗 Architecture de l'application

### Diagramme de l'architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        NAVIGATEUR                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │  index.php  │  │  style.css  │  │     forum.js        │  │
│  │   (HTML)    │  │   (CSS)     │  │  (JavaScript/AJAX)  │  │
│  └─────────────┘  └─────────────┘  └──────────┬──────────┘  │
└───────────────────────────────────────────────┼─────────────┘
                                                │
                          Requêtes AJAX (JSON)  │
                                                ▼
┌─────────────────────────────────────────────────────────────┐
│                      SERVEUR (Apache)                        │
│  ┌─────────────────────────────────────────────────────┐    │
│  │                   api/forum.php                      │    │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐   │    │
│  │  │  GET    │ │  POST   │ │  PUT    │ │ DELETE  │   │    │
│  │  │ (Lire)  │ │(Créer)  │ │(Modifier)│ │(Supprimer)│   │    │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘   │    │
│  └──────────────────────────┬──────────────────────────┘    │
└─────────────────────────────┼───────────────────────────────┘
                              │
                              │ PDO (requêtes SQL)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     BASE DE DONNÉES (MySQL)                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ categories  │  │   sujets    │  │      messages       │  │
│  │ (id, nom,   │  │ (id, titre, │  │ (id, contenu,       │  │
│  │ description)│  │ auteur...)  │  │  auteur, date...)   │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Flux de données

```
1. Utilisateur → Action (clic, saisie)
2. JavaScript → Requête AJAX vers API
3. API PHP → Traitement + Requête SQL
4. MySQL → Données
5. API PHP → Réponse JSON
6. JavaScript → Mise à jour du DOM
7. Utilisateur → Voit le résultat
```

---

## 🔌 API REST

### Base URL
```
http://localhost/mini_project_forum/api/forum.php?action=
```

### Endpoints

#### Catégories
| Méthode | Action | Description |
|---------|--------|-------------|
| GET | `categories` | Liste toutes les catégories |

**Exemple de réponse :**
```json
[
  {
    "id": 1,
    "nom": "Général",
    "description": "Discussions générales",
    "nb_sujets": 5
  }
]
```

#### Sujets
| Méthode | Action | Paramètres | Description |
|---------|--------|------------|-------------|
| GET | `sujets` | `categorie_id`, `page` | Liste des sujets |
| POST | `sujets` | Body JSON | Créer un sujet |
| GET | `sujet` | `id` | Détail d'un sujet |
| PUT | `sujet` | Body JSON | Modifier (épingler/verrouiller) |

#### Messages
| Méthode | Action | Paramètres | Description |
|---------|--------|------------|-------------|
| GET | `messages` | `sujet_id`, `page` | Messages d'un sujet |
| POST | `messages` | Body JSON | Ajouter un message |
| PUT | `message` | Body JSON | Modifier un message |
| DELETE | `message` | Body JSON | Supprimer un message |

#### Autres
| Méthode | Action | Description |
|---------|--------|-------------|
| GET | `search?q=terme` | Recherche |
| GET | `stats` | Statistiques |
| GET | `rss` | Flux RSS (XML) |

---

## 🗄 Base de données

### Schéma MCD (Modèle Conceptuel)

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│   CATEGORIES    │       │     SUJETS      │       │    MESSAGES     │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │       │ id (PK)         │
│ nom             │───1:N─│ categorie_id(FK)│───1:N─│ sujet_id (FK)   │
│ description     │       │ titre           │       │ auteur          │
│ ordre           │       │ auteur          │       │ contenu         │
└─────────────────┘       │ date_creation   │       │ date_creation   │
                          │ vues            │       │ date_modification│
                          │ epingle         │       │ modifie         │
                          │ verrouille      │       └─────────────────┘
                          └─────────────────┘
```

### Schéma MLD (Structure SQL)

```sql
-- Table des catégories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    ordre INT DEFAULT 0
);

-- Table des sujets
CREATE TABLE sujets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    vues INT DEFAULT 0,
    epingle TINYINT(1) DEFAULT 0,
    verrouille TINYINT(1) DEFAULT 0,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

-- Table des messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sujet_id INT NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME,
    modifie TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sujet_id) REFERENCES sujets(id) ON DELETE CASCADE
);
```



## 🧪 Tests

### Scénarios de test effectués

| # | Scénario | Résultat |
|---|----------|----------|
| 1 | Affichage des catégories | ✅ Passé |
| 2 | Navigation entre catégories | ✅ Passé |
| 3 | Création d'un nouveau sujet | ✅ Passé |
| 4 | Validation des champs (min caractères) | ✅ Passé |
| 5 | Ajout d'une réponse | ✅ Passé |
| 6 | Modification d'un message | ✅ Passé |
| 7 | Suppression d'un message | ✅ Passé |
| 8 | Protection anti-flood (30s) | ✅ Passé |
| 9 | Épingler un sujet | ✅ Passé |
| 10 | Verrouiller un sujet | ✅ Passé |
| 11 | Recherche avec 3+ caractères | ✅ Passé |
| 12 | Pagination des résultats | ✅ Passé |
| 13 | Flux RSS valide | ✅ Passé |
| 14 | Responsive mobile | ✅ Passé |
| 15 | BBCode (gras, italique, code) | ✅ Passé |

### Bugs identifiés et corrigés

| Bug | Solution |
|-----|----------|
| RSS retournait JSON | Séparation du traitement RSS avant les headers JSON |
| Recherche ne fonctionnait pas | Correction du format de retour de l'API |
| Sidebar ne se fermait pas sur mobile | Ajout de closeSidebar() après navigation |

---

## 👥 Auteurs

| Nom | Rôle | Contributions |
|-----|------|---------------|
| **[Votre Nom]** | Développeur Full Stack | API REST, Base de données, JavaScript |
| **[Membre 2]** | Développeur Frontend | Interface UI/UX, CSS, Responsive |
| **[Membre 3]** | Testeur / Documentaliste | Tests, Rapport, Documentation |

---

## 📄 Licence

Ce projet est sous licence **MIT**.

```
MIT License

Copyright (c) 2025 [Votre Nom]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software.
```

---

## 🔗 Liens utiles

| Ressource | Lien |
|-----------|------|
| 🌐 Démo en ligne |http://binoua.dwm.ma/


---



**Développé avec ❤️ pour le module Développement Web**

*© 2025 - Tous droits réservés*

</div>

