<?php require_once 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <meta name="description" content="Forum communautaire pour développeurs - Partagez, apprenez et collaborez">
    <title>DevForum - Communauté de développeurs</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- ========== Navbar ========== -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-3 px-lg-4">
            <!-- Mobile Menu Toggle -->
            <button class="btn btn-light btn-icon d-lg-none me-2" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            
            <!-- Logo -->
            <a class="navbar-brand" href="#" onclick="showCategories(); return false;">
                <i class="bi bi-chat-square-text-fill"></i>
                <span>DevForum</span>
            </a>
            
            <!-- Search Bar (Desktop) -->
            <div class="search-wrapper d-none d-md-flex mx-auto" style="max-width: 500px; flex: 1;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher des discussions, auteurs, sujets..." aria-label="Rechercher">
            </div>
            
            <!-- Desktop Actions -->
            <div class="d-none d-lg-flex align-items-center gap-2">
                <button class="btn btn-primary" onclick="showNewSujetModalGlobal()">
                    <i class="bi bi-plus-lg me-1"></i>
                    Nouveau sujet
                </button>
            </div>
            
            <!-- Mobile Search Toggle -->
            <button class="btn btn-light btn-icon d-md-none" onclick="toggleMobileSearch()">
                <i class="bi bi-search"></i>
            </button>
        </div>
        
        <!-- Mobile Search Bar -->
        <div id="mobileSearchBar" class="mobile-search-bar">
            <div class="container-fluid px-3">
                <div class="search-wrapper w-100">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInputMobile" class="form-control" placeholder="Rechercher..." aria-label="Rechercher">
                </div>
            </div>
        </div>
    </nav>

    <!-- ========== Sidebar + Main Content ========== -->
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-content">
                <!-- User Section -->
                <div class="sidebar-section">
                    <div class="user-card">
                        <div class="user-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-greeting">Bienvenue !</span>
                            <span class="user-status">Visiteur</span>
                        </div>
                    </div>
                </div>
                
                <!-- Main Navigation -->
                <div class="sidebar-section">
                    <div class="sidebar-label">Navigation</div>
                    <nav class="sidebar-nav">
                        <a href="#" class="sidebar-link active" onclick="showCategories(); return false;" id="navLinkHome">
                            <i class="bi bi-house-door-fill"></i>
                            <span>Accueil</span>
                        </a>
                        <a href="#" class="sidebar-link" onclick="showAllSujets(); return false;" id="navLinkSujets">
                            <i class="bi bi-chat-left-text-fill"></i>
                            <span>Tous les sujets</span>
                        </a>
                        <a href="#" class="sidebar-link" onclick="showStats(); return false;" id="navLinkStats">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Tendances</span>
                            <span class="sidebar-badge">Hot</span>
                        </a>
                    </nav>
                </div>
                
                <!-- Categories Section -->
                <div class="sidebar-section">
                    <div class="sidebar-label">Catégories</div>
                    <nav class="sidebar-nav" id="sidebarCategories">
                        <!-- Les catégories seront chargées dynamiquement -->
                        <div class="sidebar-loading">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span>Chargement...</span>
                        </div>
                    </nav>
                </div>
                
                <!-- Resources Section -->
                <div class="sidebar-section">
                    <div class="sidebar-label">Ressources</div>
                    <nav class="sidebar-nav">
                        <a href="api/forum.php?action=rss" target="_blank" class="sidebar-link" id="navLinkRSS">
                            <i class="bi bi-rss-fill text-warning"></i>
                            <span>Flux RSS</span>
                            <i class="bi bi-box-arrow-up-right ms-auto small"></i>
                        </a>
                        <a href="#" class="sidebar-link" onclick="showHelp(); return false;">
                            <i class="bi bi-question-circle-fill"></i>
                            <span>Aide & FAQ</span>
                        </a>
                    </nav>
                </div>
                
                <!-- Stats Footer -->
                <div class="sidebar-footer">
                    <div class="sidebar-stats" id="sidebarStats">
                        <div class="stat-item">
                            <i class="bi bi-chat-dots"></i>
                            <span id="statTotalSujets">--</span> sujets
                        </div>
                        <div class="stat-item">
                            <i class="bi bi-people"></i>
                            <span id="statTotalAuteurs">--</span> membres
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- ========== Main Content ========== -->
        <main class="main-content">
            <div class="content-wrapper">
                
                <!-- Categories View -->
                <div id="categoriesView">
                    <header class="content-header">
                        <div>
                            <h1 class="mb-2">
                                <i class="bi bi-grid-fill text-primary me-2"></i>
                                Explorer les catégories
                            </h1>
                            <p class="text-muted mb-0">Rejoignez les conversations qui vous passionnent</p>
                        </div>
                    </header>
                    <div id="categoriesList" class="row g-3"></div>
                </div>

                <!-- All Topics View -->
                <div id="allSujetsView" style="display:none;">
                    <header class="content-header">
                        <div>
                            <h1 class="mb-2">
                                <i class="bi bi-chat-left-text-fill text-primary me-2"></i>
                                Tous les sujets
                            </h1>
                            <p class="text-muted mb-0">Parcourez toutes les discussions du forum</p>
                        </div>
                        <button class="btn btn-primary" onclick="showNewSujetModalGlobal()">
                            <i class="bi bi-plus-lg me-1"></i>
                            <span class="d-none d-sm-inline">Nouveau sujet</span>
                        </button>
                    </header>
                    <div id="allSujetsList"></div>
                    <nav id="allSujetsPagination" class="mt-4 d-flex justify-content-center"></nav>
                </div>

                <!-- Topics View -->
                <div id="sujetsView" style="display:none;">
                    <header class="content-header">
                        <div>
                            <nav class="breadcrumb-nav mb-2">
                                <i class="bi bi-chevron-right"></i>
                                <span id="breadcrumbCategorie">Catégorie</span>
                            </nav>
                            <h2 id="categorieTitle" class="mb-0"></h2>
                        </div>
                        <button class="btn btn-primary" onclick="showNewSujetModal()">
                            <i class="bi bi-plus-lg me-1"></i>
                            <span class="d-none d-sm-inline">Nouveau sujet</span>
                        </button>
                    </header>
                    <div id="sujetsList"></div>
                    <nav id="sujetsPagination" class="mt-4 d-flex justify-content-center"></nav>
                </div>

                <!-- Messages View -->
                <div id="messagesView" style="display:none;">
                    <header class="content-header flex-column align-items-start">
                        <nav class="breadcrumb-nav mb-3">
                            <a href="#" onclick="showCategories(); return false;">Accueil</a>
                            <i class="bi bi-chevron-right"></i>
                            <a href="#" onclick="backToSujets(); return false;" id="breadcrumbCategorieMsg">Catégorie</a>
                            <i class="bi bi-chevron-right"></i>
                            <span id="breadcrumbSujet">Sujet</span>
                        </nav>
                        
                        <!-- Topic Header Card -->
                        <div class="card w-100">
                            <div class="card-body">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                                    <div class="flex-grow-1">
                                        <h1 id="sujetTitle" class="h3 mb-2"></h1>
                                        <div id="sujetInfo" class="d-flex flex-wrap align-items-center gap-2 gap-md-3 text-muted small"></div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="togglePin()" id="btnPin" title="Épingler/Désépingler">
                                            <i class="bi bi-pin-angle"></i>
                                            <span class="d-none d-md-inline ms-1">Épingler</span>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="toggleLock()" id="btnLock" title="Verrouiller/Déverrouiller">
                                            <i class="bi bi-lock"></i>
                                            <span class="d-none d-md-inline ms-1">Verrouiller</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    
                    <!-- Messages List -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div id="messagesList"></div>
                        </div>
                    </div>
                    
                    <nav id="messagesPagination" class="mb-4 d-flex justify-content-center"></nav>
                    
                    <!-- Reply Form -->
                    <div class="card" id="replyForm" style="display:none;">
                        <div class="card-header">
                            <h5 class="mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-reply-fill text-primary"></i>
                                Ajouter une réponse
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="replyAuteur" class="form-label fw-medium">Votre pseudo</label>
                                <input type="text" id="replyAuteur" class="form-control" placeholder="Entrez votre pseudo...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-medium">Votre message</label>
                                <div class="bbcode-toolbar">
                                    <button type="button" class="btn" onclick="insertBBCode('[b]', '[/b]')" title="Gras">
                                        <strong>B</strong>
                                    </button>
                                    <button type="button" class="btn" onclick="insertBBCode('[i]', '[/i]')" title="Italique">
                                        <em>I</em>
                                    </button>
                                    <button type="button" class="btn" onclick="insertBBCode('[u]', '[/u]')" title="Souligné">
                                        <u>U</u>
                                    </button>
                                    <div class="vr mx-1"></div>
                                    <button type="button" class="btn" onclick="insertBBCode('[code]', '[/code]')" title="Code">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                    <button type="button" class="btn" onclick="insertBBCode('[url=]', '[/url]')" title="Lien">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                    <button type="button" class="btn" onclick="insertBBCode('[quote]', '[/quote]')" title="Citation">
                                        <i class="bi bi-quote"></i>
                                    </button>
                                </div>
                                <textarea id="replyContenu" class="form-control" rows="5" placeholder="Partagez votre avis..."></textarea>
                            </div>
                            
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    BBCode supporté: [b], [i], [u], [code], [url], [quote]
                                </small>
                                <button class="btn btn-primary px-4" onclick="submitReply()">
                                    <i class="bi bi-send-fill me-1"></i>
                                    Envoyer
                                </button>
                            </div>
                            
                            <div class="mt-3">
                                <label class="form-label small text-muted">Aperçu</label>
                                <div id="bbcodePreview" class="bbcode-preview">
                                    <span class="text-muted fst-italic">L'aperçu s'affichera ici en temps réel...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Results View -->
                <div id="searchResultsView" style="display:none;">
                    <header class="content-header">
                        <div>
                            <h1 class="mb-2">
                                <i class="bi bi-search text-primary me-2"></i>
                                Résultats de recherche
                            </h1>
                            <p class="text-muted mb-0" id="searchResultsInfo">--</p>
                        </div>
                        <button class="btn btn-outline-secondary" onclick="showCategories()">
                            <i class="bi bi-x-lg me-1"></i>
                            Effacer
                        </button>
                    </header>
                    <div id="searchResultsList" class="row g-3"></div>
                </div>

                <!-- Loading Overlay -->
                <div class="loading" id="loading">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">Chargement en cours...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- ========== Mobile Bottom Navigation ========== -->
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <a href="#" class="nav-item active" onclick="showCategories(); return false;" id="mobileNavHome">
                <i class="bi bi-house-fill"></i>
                <span>Accueil</span>
            </a>
            <a href="#" class="nav-item" onclick="showAllSujets(); return false;" id="mobileNavSujets">
                <i class="bi bi-chat-left-text"></i>
                <span>Sujets</span>
            </a>
            <a href="#" class="nav-item nav-item-fab" onclick="showNewSujetModalGlobal(); return false;">
                <i class="bi bi-plus-lg"></i>
            </a>
            <a href="#" class="nav-item" onclick="showStats(); return false;" id="mobileNavStats">
                <i class="bi bi-graph-up"></i>
                <span>Tendances</span>
            </a>
            <a href="#" class="nav-item" onclick="toggleSidebar(); return false;">
                <i class="bi bi-grid"></i>
                <span>Menu</span>
            </a>
        </div>
    </nav>

    <!-- ========== Toast Container ========== -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- ========== New Topic Modal ========== -->
    <div class="modal fade" id="newSujetModal" tabindex="-1" aria-labelledby="newSujetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSujetModalLabel">
                        <i class="bi bi-plus-circle text-primary me-2"></i>
                        Créer une nouvelle discussion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="categorySelectWrapper">
                        <label for="newSujetCategorie" class="form-label fw-medium">Catégorie</label>
                        <select id="newSujetCategorie" class="form-select">
                            <option value="">Sélectionnez une catégorie...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newSujetAuteur" class="form-label fw-medium">Votre nom</label>
                        <input type="text" id="newSujetAuteur" class="form-control" placeholder="Entrez votre pseudo...">
                    </div>
                    <div class="mb-3">
                        <label for="newSujetTitre" class="form-label fw-medium">Titre du sujet</label>
                        <input type="text" id="newSujetTitre" class="form-control" placeholder="Un titre accrocheur pour votre sujet...">
                    </div>
                    <div class="mb-0">
                        <label for="newSujetContenu" class="form-label fw-medium">Message initial</label>
                        <textarea id="newSujetContenu" class="form-control" rows="6" placeholder="Décrivez votre sujet en détail..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary px-4" onclick="createSujet()">
                        <i class="bi bi-send-fill me-1"></i>
                        Publier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== Stats Modal ========== -->
    <div class="modal fade" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statsModalLabel">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                        Tendances du forum
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-6 border-end">
                            <div class="p-4">
                                <h6 class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger">🔥 HOT</span>
                                    Sujets les plus populaires
                                </h6>
                                <div id="statsPopulaires" class="list-group list-group-flush"></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-4">
                                <h6 class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-success">🏆 TOP</span>
                                    Contributeurs les plus actifs
                                </h6>
                                <div id="statsActifs" class="list-group list-group-flush"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== Help Modal ========== -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">
                        <i class="bi bi-question-circle-fill text-primary me-2"></i>
                        Aide & FAQ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="helpAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help1">
                                    Comment créer un nouveau sujet ?
                                </button>
                            </h2>
                            <div id="help1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    Cliquez sur le bouton <strong>"Nouveau sujet"</strong> dans la barre de navigation ou dans une catégorie. Remplissez le formulaire avec votre pseudo, un titre et votre message.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help2">
                                    Comment utiliser le BBCode ?
                                </button>
                            </h2>
                            <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <p>Utilisez les balises suivantes :</p>
                                    <ul class="mb-0">
                                        <li><code>[b]texte[/b]</code> - <strong>Gras</strong></li>
                                        <li><code>[i]texte[/i]</code> - <em>Italique</em></li>
                                        <li><code>[u]texte[/u]</code> - <u>Souligné</u></li>
                                        <li><code>[code]code[/code]</code> - <code>Code</code></li>
                                        <li><code>[url=lien]texte[/url]</code> - Lien</li>
                                        <li><code>[quote]citation[/quote]</code> - Citation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help3">
                                    Qu'est-ce que le flux RSS ?
                                </button>
                            </h2>
                            <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    Le flux RSS vous permet de suivre les dernières discussions du forum avec votre lecteur RSS préféré. Cliquez sur "Flux RSS" dans le menu pour y accéder.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== Edit Message Modal ========== -->
    <div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMessageModalLabel">
                        <i class="bi bi-pencil-fill text-primary me-2"></i>
                        Modifier le message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editMessageId">
                    <div class="mb-0">
                        <label for="editMessageContenu" class="form-label fw-medium">Contenu du message</label>
                        <textarea id="editMessageContenu" class="form-control" rows="6" placeholder="Modifiez votre message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary px-4" onclick="saveMessageEdit()">
                        <i class="bi bi-check-lg me-1"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== Confirm Delete Modal ========== -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        Confirmation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce message ?</p>
                    <p class="text-muted small mb-0">Cette action est irréversible.</p>
                    <input type="hidden" id="deleteMessageId">
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteMessage()">
                        <i class="bi bi-trash me-1"></i>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
