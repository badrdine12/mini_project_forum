<?php
// api/forum.php - API REST pour le forum (Version Optimisée)

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Traiter le RSS séparément car il a un Content-Type différent
if ($action === 'rss') {
    try {
        getRSS($pdo);
    } catch(Exception $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Erreur RSS: ' . $e->getMessage();
    }
    exit;
}

// Pour toutes les autres actions, définir les en-têtes JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    switch($action) {
        case 'categories':
            if($method === 'GET') {
                getCategories($pdo);
            }
            break;
            
        case 'sujets':
            if($method === 'GET') {
                getSujets($pdo);
            } elseif($method === 'POST') {
                createSujet($pdo);
            }
            break;
            
        case 'sujet':
            if($method === 'GET') {
                getSujet($pdo);
            } elseif($method === 'PUT') {
                updateSujet($pdo);
            }
            break;
            
        case 'messages':
            if($method === 'GET') {
                getMessages($pdo);
            } elseif($method === 'POST') {
                createMessage($pdo);
            }
            break;
            
        case 'message':
            if($method === 'PUT') {
                updateMessage($pdo);
            } elseif($method === 'DELETE') {
                deleteMessage($pdo);
            }
            break;
            
        case 'search':
            searchForum($pdo);
            break;
            
        case 'stats':
            getStats($pdo);
            break;
            
        default:
            throw new Exception('Action non valide');
    }
} catch(PDOException $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur de base de données']);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Récupère les catégories avec mise en cache
 */
function getCategories($pdo) {
    $cacheFile = sys_get_temp_dir() . '/forum_categories.json';
    $cacheTime = 300; // 5 minutes
    
    // Vérifier le cache fichier
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        echo file_get_contents($cacheFile);
        return;
    }
    
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(s.id) as nb_sujets,
               (SELECT COUNT(*) FROM messages m 
                JOIN sujets s2 ON m.sujet_id = s2.id 
                WHERE s2.categorie_id = c.id) as nb_messages
        FROM categories c 
        LEFT JOIN sujets s ON c.id = s.categorie_id 
        GROUP BY c.id 
        ORDER BY c.ordre
    ");
    
    $result = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Sauvegarder en cache
    file_put_contents($cacheFile, $result);
    
    echo $result;
}

/**
 * Récupère les sujets avec pagination optimisée
 */
function getSujets($pdo) {
    $categorie_id = isset($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : null;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $params = [];
    $where = "";
    
    if ($categorie_id) {
        $where = "WHERE s.categorie_id = :cat_id";
        $params[':cat_id'] = $categorie_id;
    }
    
    // Requête optimisée avec sous-requête pour le dernier message
    $sql = "
        SELECT s.*, 
               (SELECT COUNT(*) FROM messages WHERE sujet_id = s.id) as nb_messages,
               (SELECT MAX(date_creation) FROM messages WHERE sujet_id = s.id) as dernier_message,
               (SELECT auteur FROM messages WHERE sujet_id = s.id ORDER BY date_creation DESC LIMIT 1) as dernier_auteur
        FROM sujets s
        $where
        ORDER BY s.epingle DESC, dernier_message DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $sujets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Compter le total
    $countSql = "SELECT COUNT(*) FROM sujets s $where";
    $count_stmt = $pdo->prepare($countSql);
    if ($categorie_id) {
        $count_stmt->bindValue(':cat_id', $categorie_id, PDO::PARAM_INT);
    }
    $count_stmt->execute();
    $total = (int)$count_stmt->fetchColumn();
    
    echo json_encode([
        'sujets' => $sujets,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => (int)ceil($total / $per_page),
        'total' => $total
    ]);
}

/**
 * Récupère un sujet avec incrémentation des vues optimisée
 */
function getSujet($pdo) {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID invalide');
    }
    
    // Incrémenter les vues de manière asynchrone (ou différée)
    // Utiliser LOW_PRIORITY pour ne pas bloquer les lectures
    $pdo->prepare("UPDATE LOW_PRIORITY sujets SET vues = vues + 1 WHERE id = ?")->execute([$id]);
    
    $stmt = $pdo->prepare("
        SELECT s.*, c.nom as categorie_nom
        FROM sujets s
        LEFT JOIN categories c ON s.categorie_id = c.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $sujet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sujet) {
        throw new Exception('Sujet introuvable');
    }
    
    echo json_encode($sujet);
}

/**
 * Crée un nouveau sujet avec validation
 */
function createSujet($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation des données
    $titre = trim($data['titre'] ?? '');
    $auteur = trim($data['auteur'] ?? '');
    $contenu = trim($data['contenu'] ?? '');
    $categorie_id = (int)($data['categorie_id'] ?? 0);
    
    if (empty($titre) || strlen($titre) < 5) {
        throw new Exception('Le titre doit contenir au moins 5 caractères');
    }
    
    if (empty($auteur) || strlen($auteur) < 2) {
        throw new Exception('Le nom d\'auteur est requis');
    }
    
    if (empty($contenu) || strlen($contenu) < 10) {
        throw new Exception('Le contenu doit contenir au moins 10 caractères');
    }
    
    // Vérifier que la catégorie existe
    $catCheck = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $catCheck->execute([$categorie_id]);
    if (!$catCheck->fetch()) {
        throw new Exception('Catégorie invalide');
    }
    
    $pdo->beginTransaction();
    
    try {
        // Créer le sujet
        $stmt = $pdo->prepare("
            INSERT INTO sujets (categorie_id, titre, auteur, date_creation) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $categorie_id,
            htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8')
        ]);
        
        $sujet_id = (int)$pdo->lastInsertId();
        
        // Créer le premier message
        $stmt = $pdo->prepare("
            INSERT INTO messages (sujet_id, auteur, contenu, date_creation) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $sujet_id,
            htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')
        ]);
        
        $pdo->commit();
        
        // Invalider le cache des catégories
        @unlink(sys_get_temp_dir() . '/forum_categories.json');
        
        echo json_encode(['success' => true, 'id' => $sujet_id]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Met à jour un sujet (épinglé, verrouillé)
 */
function updateSujet($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID invalide');
    }
    
    $updates = [];
    $params = [];
    
    if (isset($data['epingle'])) {
        $updates[] = "epingle = ?";
        $params[] = (int)(bool)$data['epingle'];
    }
    
    if (isset($data['verrouille'])) {
        $updates[] = "verrouille = ?";
        $params[] = (int)(bool)$data['verrouille'];
    }
    
    if (isset($data['titre']) && strlen(trim($data['titre'])) >= 5) {
        $updates[] = "titre = ?";
        $params[] = htmlspecialchars(trim($data['titre']), ENT_QUOTES, 'UTF-8');
    }
    
    if (empty($updates)) {
        throw new Exception('Rien à mettre à jour');
    }
    
    $params[] = $id;
    $sql = "UPDATE sujets SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Sujet non trouvé');
    }
    
    echo json_encode(['success' => true]);
}

/**
 * Récupère les messages d'un sujet
 */
function getMessages($pdo) {
    $sujet_id = (int)($_GET['sujet_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 15;
    $offset = ($page - 1) * $per_page;
    
    if ($sujet_id <= 0) {
        throw new Exception('ID du sujet invalide');
    }
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
               (SELECT COUNT(*) FROM messages WHERE auteur = m.auteur) as total_messages_auteur
        FROM messages m
        WHERE m.sujet_id = ? 
        ORDER BY m.date_creation ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $sujet_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Compter le total
    $count = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sujet_id = ?");
    $count->execute([$sujet_id]);
    $total = (int)$count->fetchColumn();
    
    echo json_encode([
        'messages' => $messages,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => (int)ceil($total / $per_page),
        'total' => $total
    ]);
}

/**
 * Crée un nouveau message
 */
function createMessage($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sujet_id = (int)($data['sujet_id'] ?? 0);
    $auteur = trim($data['auteur'] ?? '');
    $contenu = trim($data['contenu'] ?? '');
    
    if ($sujet_id <= 0) {
        throw new Exception('ID du sujet invalide');
    }
    
    if (empty($auteur) || strlen($auteur) < 2) {
        throw new Exception('Le nom d\'auteur est requis');
    }
    
    if (empty($contenu) || strlen($contenu) < 2) {
        throw new Exception('Le contenu est requis');
    }
    
    // Vérifier si le sujet existe et n'est pas verrouillé
    $stmt = $pdo->prepare("SELECT id, verrouille FROM sujets WHERE id = ?");
    $stmt->execute([$sujet_id]);
    $sujet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sujet) {
        throw new Exception('Sujet introuvable');
    }
    
    if ($sujet['verrouille']) {
        throw new Exception('Ce sujet est verrouillé');
    }
    
    // Anti-flood : vérifier le dernier message de cet auteur
    $floodCheck = $pdo->prepare("
        SELECT date_creation FROM messages 
        WHERE auteur = ? AND sujet_id = ?
        ORDER BY date_creation DESC LIMIT 1
    ");
    $floodCheck->execute([$auteur, $sujet_id]);
    $lastMsg = $floodCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($lastMsg) {
        $timeDiff = time() - strtotime($lastMsg['date_creation']);
        if ($timeDiff < 30) { // 30 secondes minimum entre les messages
            throw new Exception('Veuillez attendre avant de poster un nouveau message');
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (sujet_id, auteur, contenu, date_creation) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([
        $sujet_id,
        htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')
    ]);
    
    echo json_encode([
        'success' => true, 
        'id' => (int)$pdo->lastInsertId()
    ]);
}

/**
 * Met à jour un message
 */
function updateMessage($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = (int)($data['id'] ?? 0);
    $contenu = trim($data['contenu'] ?? '');
    
    if ($id <= 0) {
        throw new Exception('ID invalide');
    }
    
    if (empty($contenu) || strlen($contenu) < 2) {
        throw new Exception('Le contenu est requis');
    }
    
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET contenu = ?, modifie = 1, date_modification = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([
        htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'), 
        $id
    ]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Message non trouvé');
    }
    
    echo json_encode(['success' => true]);
}

/**
 * Supprime un message
 */
function deleteMessage($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID invalide');
    }
    
    // Vérifier que le message existe
    $check = $pdo->prepare("SELECT sujet_id FROM messages WHERE id = ?");
    $check->execute([$id]);
    $msg = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$msg) {
        throw new Exception('Message non trouvé');
    }
    
    // Vérifier si c'est le seul message du sujet
    $countCheck = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sujet_id = ?");
    $countCheck->execute([$msg['sujet_id']]);
    
    if ((int)$countCheck->fetchColumn() === 1) {
        throw new Exception('Impossible de supprimer le seul message d\'un sujet');
    }
    
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
    
    echo json_encode(['success' => true]);
}

/**
 * Recherche dans le forum avec FULLTEXT (ou LIKE en fallback)
 */
function searchForum($pdo) {
    $q = trim($_GET['q'] ?? '');
    
    if (strlen($q) < 3) {
        throw new Exception('Recherche trop courte (minimum 3 caractères)');
    }
    
    // Fallback sur LIKE (plus fiable et compatible)
    $search = '%' . addcslashes($q, '%_') . '%';
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.titre, s.auteur, s.date_creation, s.vues,
               SUBSTRING(m.contenu, 1, 250) as contenu,
               m.auteur as message_auteur,
               m.date_creation as message_date,
               c.nom as categorie_nom
        FROM sujets s
        LEFT JOIN messages m ON m.sujet_id = s.id
        LEFT JOIN categories c ON s.categorie_id = c.id
        WHERE m.contenu LIKE ? OR s.titre LIKE ? OR s.auteur LIKE ?
        GROUP BY s.id
        ORDER BY s.date_creation DESC
        LIMIT 30
    ");
    $stmt->execute([$search, $search, $search]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
}

/**
 * Récupère les statistiques avec cache
 */
function getStats($pdo) {
    $cacheFile = sys_get_temp_dir() . '/forum_stats.json';
    $cacheTime = 60; // 1 minute
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        echo file_get_contents($cacheFile);
        return;
    }
    
    // Sujets populaires
    $populaires = $pdo->query("
        SELECT id, titre, auteur, vues, date_creation
        FROM sujets 
        ORDER BY vues DESC 
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Auteurs actifs
    $actifs = $pdo->query("
        SELECT auteur, COUNT(*) as nb_messages 
        FROM messages 
        GROUP BY auteur 
        ORDER BY nb_messages DESC 
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistiques globales
    $totalSujets = (int)$pdo->query("SELECT COUNT(*) FROM sujets")->fetchColumn();
    $totalMessages = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $totalAuteurs = (int)$pdo->query("SELECT COUNT(DISTINCT auteur) FROM messages")->fetchColumn();
    
    $result = json_encode([
        'populaires' => $populaires,
        'actifs' => $actifs,
        'totaux' => [
            'sujets' => $totalSujets,
            'messages' => $totalMessages,
            'auteurs' => $totalAuteurs
        ]
    ]);
    
    file_put_contents($cacheFile, $result);
    echo $result;
}

/**
 * Génère le flux RSS
 */
function getRSS($pdo) {
    header('Content-Type: application/rss+xml; charset=utf-8');
    
    $messages = $pdo->query("
        SELECT m.id, m.auteur, m.contenu, m.date_creation,
               s.id as sujet_id, s.titre as sujet_titre 
        FROM messages m 
        JOIN sujets s ON m.sujet_id = s.id 
        ORDER BY m.date_creation DESC 
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    
    $rss = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Forum de Discussion</title>
    <link>' . $baseUrl . '/forum</link>
    <description>Derniers messages du forum</description>
    <language>fr-FR</language>
    <lastBuildDate>' . date('r') . '</lastBuildDate>
    <atom:link href="' . $baseUrl . '/api/forum.php?action=rss" rel="self" type="application/rss+xml"/>';
    
    foreach ($messages as $msg) {
        $rss .= '
    <item>
      <title>' . htmlspecialchars($msg['sujet_titre'], ENT_XML1, 'UTF-8') . '</title>
      <link>' . $baseUrl . '/forum/sujet/' . $msg['sujet_id'] . '</link>
      <description><![CDATA[' . substr(strip_tags($msg['contenu']), 0, 300) . ']]></description>
      <author>' . htmlspecialchars($msg['auteur'], ENT_XML1, 'UTF-8') . '</author>
      <guid isPermaLink="false">msg-' . $msg['id'] . '</guid>
      <pubDate>' . date('r', strtotime($msg['date_creation'])) . '</pubDate>
    </item>';
    }
    
    $rss .= '
  </channel>
</rss>';
    
    echo $rss;
}
?>