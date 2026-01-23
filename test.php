<?php
/**
 * test.php - Script de test pour vérifier l'installation du forum
 * 
 * Ce script vérifie :
 * - La connexion à la base de données
 * - L'existence des tables
 * - Les données de test
 * - La configuration PHP
 */

// Configuration de l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test d'installation - Forum</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-success { color: #28a745; }
        .test-error { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .test-item { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class='container mt-5'>
        <h1 class='mb-4'>🧪 Test d'Installation du Forum</h1>
";

// Test 1 : Version PHP
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>1. Configuration PHP</h5>
    </div>
    <div class='card-body'>";

$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<div class='test-item test-success'>✓ Version PHP : $phpVersion (OK)</div>";
} else {
    echo "<div class='test-item test-error'>✗ Version PHP : $phpVersion (Minimum requis : 7.4)</div>";
}

// Test extension PDO
if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
    echo "<div class='test-item test-success'>✓ Extension PDO MySQL chargée</div>";
} else {
    echo "<div class='test-item test-error'>✗ Extension PDO MySQL non disponible</div>";
}

// Test autres extensions
$extensions = ['json', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='test-item test-success'>✓ Extension $ext chargée</div>";
    } else {
        echo "<div class='test-item test-warning'>⚠ Extension $ext recommandée</div>";
    }
}

echo "</div></div>";

// Test 2 : Connexion à la base de données
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>2. Connexion à la Base de Données</h5>
    </div>
    <div class='card-body'>";

try {
    require_once 'config/database.php';
    echo "<div class='test-item test-success'>✓ Connexion à la base de données réussie</div>";
    echo "<div class='test-item'>
        <strong>Informations :</strong><br>
        - Hôte : " . DB_HOST . "<br>
        - Base : " . DB_NAME . "<br>
        - Utilisateur : " . DB_USER . "
    </div>";
} catch (Exception $e) {
    echo "<div class='test-item test-error'>✗ Erreur de connexion : " . $e->getMessage() . "</div>";
    echo "<div class='alert alert-danger mt-3'>
        <strong>Vérifiez :</strong><br>
        - Les identifiants dans config/database.php<br>
        - Que MySQL est démarré<br>
        - Que la base de données existe
    </div>";
    die("</div></div></div></body></html>");
}

echo "</div></div>";

// Test 3 : Tables de la base de données
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>3. Structure de la Base de Données</h5>
    </div>
    <div class='card-body'>";

$tables = ['categories', 'sujets', 'messages'];
$allTablesExist = true;

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<div class='test-item test-success'>✓ Table '$table' existe ($count ligne(s))</div>";
    } catch (Exception $e) {
        echo "<div class='test-item test-error'>✗ Table '$table' introuvable</div>";
        $allTablesExist = false;
    }
}

if (!$allTablesExist) {
    echo "<div class='alert alert-warning mt-3'>
        <strong>Action requise :</strong> Importez le fichier database.sql dans votre base de données
    </div>";
}

echo "</div></div>";

// Test 4 : API REST
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>4. Test de l'API REST</h5>
    </div>
    <div class='card-body'>";

$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/forum.php?action=categories';

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            echo "<div class='test-item test-success'>✓ API REST fonctionne correctement</div>";
            echo "<div class='test-item'>URL testée : <code>$apiUrl</code></div>";
            echo "<div class='test-item'>Nombre de catégories : " . count($data) . "</div>";
        } else {
            echo "<div class='test-item test-warning'>⚠ API accessible mais réponse invalide</div>";
        }
    } else {
        echo "<div class='test-item test-warning'>⚠ Impossible de tester l'API (normal si serveur PHP intégré)</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item test-warning'>⚠ Test API ignoré : " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 5 : Fichiers et permissions
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>5. Fichiers du Projet</h5>
    </div>
    <div class='card-body'>";

$files = [
    'config/database.php' => 'Configuration BDD',
    'api/forum.php' => 'API REST',
    'js/forum.js' => 'JavaScript AJAX',
    'index.php' => 'Page principale'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        $readable = is_readable($file) ? 'Lecture OK' : 'Pas de lecture';
        echo "<div class='test-item test-success'>✓ $desc ($file) - $readable</div>";
    } else {
        echo "<div class='test-item test-error'>✗ $desc ($file) introuvable</div>";
    }
}

echo "</div></div>";

// Test 6 : Données de test
echo "<div class='card mb-3'>
    <div class='card-header bg-primary text-white'>
        <h5>6. Données de Test</h5>
    </div>
    <div class='card-body'>";

try {
    // Catégories
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $catCount = $stmt->fetchColumn();
    
    // Sujets
    $stmt = $pdo->query("SELECT COUNT(*) FROM sujets");
    $sujetCount = $stmt->fetchColumn();
    
    // Messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $msgCount = $stmt->fetchColumn();
    
    echo "<div class='test-item test-success'>
        <strong>Contenu de la base :</strong><br>
        - Catégories : $catCount<br>
        - Sujets : $sujetCount<br>
        - Messages : $msgCount
    </div>";
    
    if ($catCount == 0) {
        echo "<div class='alert alert-info mt-3'>
            <strong>Info :</strong> Aucune donnée de test. Importez database.sql pour avoir des exemples.
        </div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-item test-error'>✗ Erreur lecture données : " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Résumé final
echo "<div class='card mb-3 border-success'>
    <div class='card-header bg-success text-white'>
        <h5>✓ Résumé</h5>
    </div>
    <div class='card-body'>";

if ($allTablesExist && extension_loaded('pdo_mysql')) {
    echo "<div class='alert alert-success'>
        <h6>🎉 Installation réussie !</h6>
        <p>Le forum est prêt à être utilisé.</p>
        <a href='index.php' class='btn btn-success'>Accéder au Forum</a>
    </div>";
} else {
    echo "<div class='alert alert-warning'>
        <h6>⚠️ Installation incomplète</h6>
        <p>Veuillez corriger les erreurs ci-dessus avant d'utiliser le forum.</p>
        <strong>Actions recommandées :</strong>
        <ol>
            <li>Vérifier config/database.php</li>
            <li>Importer database.sql</li>
            <li>Relancer ce test</li>
        </ol>
    </div>";
}

echo "</div></div>";

// Conseils
echo "<div class='card mb-3'>
    <div class='card-header bg-info text-white'>
        <h5>💡 Conseils</h5>
    </div>
    <div class='card-body'>
        <ul>
            <li>Pour la production, modifiez les paramètres de sécurité dans .htaccess</li>
            <li>Ajoutez un système d'authentification pour la modération</li>
            <li>Activez HTTPS en production</li>
            <li>Configurez des sauvegardes automatiques de la base de données</li>
            <li>Supprimez ce fichier test.php en production</li>
        </ul>
        
        <h6 class='mt-3'>Commandes utiles :</h6>
        <pre class='bg-light p-3 rounded'>
# Démarrer le serveur PHP intégré
php -S localhost:8000

# Importer la base de données
mysql -u root -p forum_discussion < database.sql

# Sauvegarder la base de données
mysqldump -u root -p forum_discussion > backup.sql
        </pre>
    </div>
</div>";

echo "
    <div class='text-center my-4'>
        <a href='index.php' class='btn btn-primary btn-lg'>
            🚀 Lancer le Forum
        </a>
        <a href='test.php' class='btn btn-secondary btn-lg ms-2'>
            🔄 Relancer les Tests
        </a>
    </div>
    
    <footer class='text-center text-muted mb-5'>
        <small>Forum de Discussion - Script de test v1.0</small>
    </footer>
    
    </div>
</body>
</html>";
?>