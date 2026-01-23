// js/forum.js - Gestion AJAX du forum avec navigation moderne

let currentCategorie = null;
let currentSujet = null;
let currentCategorieName = '';
let allCategories = [];

// ========== Initialisation ==========
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadSidebarStats();
    
    // Recherche en temps réel avec délai (debounce)
    setupSearch('searchInput');
    setupSearch('searchInputMobile');
    
    // Aperçu BBCode en temps réel
    const replyContenu = document.getElementById('replyContenu');
    if (replyContenu) {
        replyContenu.addEventListener('input', updateBBCodePreview);
    }
    
    // Fermer la sidebar sur clic externe (mobile)
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const toggler = e.target.closest('[onclick*="toggleSidebar"]');
        if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !toggler) {
            closeSidebar();
        }
    });
});

// ========== Setup Search ==========
function setupSearch(inputId) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        searchTimeout = setTimeout(() => {
            if (query.length >= 3) {
                searchForum(query);
            } else if (query.length === 0) {
                showCategories();
            }
        }, 400);
    });
    
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length >= 3) {
                searchForum(query);
            }
        }
    });
}

// ========== Sidebar Toggle ==========
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar.classList.contains('open')) {
        closeSidebar();
    } else {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

// ========== Mobile Search Toggle ==========
function toggleMobileSearch() {
    const searchBar = document.getElementById('mobileSearchBar');
    const searchInput = document.getElementById('searchInputMobile');
    
    searchBar.classList.toggle('show');
    if (searchBar.classList.contains('show')) {
        setTimeout(() => searchInput?.focus(), 100);
    }
}

// ========== Système de Notifications Toast ==========
function showToast(message, type = 'success', title = null) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    const titles = {
        success: 'Succès',
        error: 'Erreur',
        warning: 'Attention',
        info: 'Information'
    };
    
    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div class="toast toast-${type}" id="${toastId}" role="alert">
            <div class="toast-icon">
                <i class="bi ${icons[type]}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title || titles[type]}</div>
                <div class="toast-message">${escapeHtml(message)}</div>
            </div>
            <button class="toast-close" onclick="closeToast('${toastId}')" aria-label="Fermer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHTML);
    setTimeout(() => closeToast(toastId), 5000);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }
}

// ========== Fonction d'aide pour les appels API ==========
async function apiCall(action, params = {}, method = 'GET', body = null) {
    showLoading();
    try {
        const pathParts = window.location.pathname.split('/');
        pathParts.pop();
        const basePath = pathParts.join('/') + '/';
        const url = new URL(basePath + 'api/forum.php', window.location.origin);
        url.searchParams.append('action', action);
        
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });
        
        const options = { method };
        
        if (body) {
            options.headers = { 'Content-Type': 'application/json' };
            options.body = JSON.stringify(body);
        }
        
        const response = await fetch(url, options);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Une erreur est survenue');
        }
        
        return data;
    } catch (error) {
        console.error('Erreur API:', error);
        showToast(error.message, 'error');
        throw error;
    } finally {
        hideLoading();
    }
}

function showLoading() {
    document.getElementById('loading')?.classList.add('active');
}

function hideLoading() {
    document.getElementById('loading')?.classList.remove('active');
}

// ========== Sidebar Stats ==========
async function loadSidebarStats() {
    try {
        const stats = await apiCall('stats');
        document.getElementById('statTotalSujets').textContent = stats.totaux?.sujets || 0;
        document.getElementById('statTotalAuteurs').textContent = stats.totaux?.auteurs || 0;
    } catch (error) {
        console.error('Erreur chargement stats sidebar:', error);
    }
}

// ========== Update Navigation State ==========
function updateNavState(view) {
    // Desktop sidebar
    document.querySelectorAll('.sidebar-link').forEach(link => link.classList.remove('active'));
    document.getElementById(`navLink${view}`)?.classList.add('active');
    
    // Mobile bottom nav
    document.querySelectorAll('.mobile-bottom-nav .nav-item').forEach(item => item.classList.remove('active'));
    document.getElementById(`mobileNav${view}`)?.classList.add('active');
    
    // Close sidebar on mobile after navigation
    if (window.innerWidth < 992) {
        closeSidebar();
    }
}

// ========== Gestion des Catégories ==========
async function loadCategories() {
    try {
        const categories = await apiCall('categories');
        allCategories = categories;
        
        // Mettre à jour la liste principale
        const html = categories.length ? categories.map((cat, index) => `
            <div class="col-12 col-md-6">
                <div class="card card-interactive category-card" style="animation-delay: ${index * 0.05}s" onclick="showSujets(${cat.id}, '${escapeHtml(cat.nom).replace(/'/g, "\\'")}')">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-icon">
                                <i class="bi bi-folder-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">${escapeHtml(cat.nom)}</h5>
                                <p class="mb-0 text-muted small text-truncate">${escapeHtml(cat.description || 'Aucune description')}</p>
                            </div>
                            <div class="text-end">
                                <span class="stats-badge">
                                    <i class="bi bi-chat-dots"></i>
                                    ${cat.nb_sujets}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('') : `
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-folder-x"></i></div>
                    <h5 class="empty-state-title">Aucune catégorie</h5>
                    <p class="text-muted">Il n'y a pas encore de catégories dans ce forum.</p>
                </div>
            </div>
        `;
        
        document.getElementById('categoriesList').innerHTML = html;
        
        // Mettre à jour la sidebar
        const sidebarHtml = categories.map(cat => `
            <a href="#" class="sidebar-link" onclick="showSujets(${cat.id}, '${escapeHtml(cat.nom).replace(/'/g, "\\'")}'); return false;">
                <i class="bi bi-folder"></i>
                <span>${escapeHtml(cat.nom)}</span>
                <span class="ms-auto small text-muted">${cat.nb_sujets}</span>
            </a>
        `).join('');
        
        document.getElementById('sidebarCategories').innerHTML = sidebarHtml || '<p class="text-muted small px-3">Aucune catégorie</p>';
        
        // Mettre à jour le select dans le modal
        const selectHtml = categories.map(cat => `
            <option value="${cat.id}">${escapeHtml(cat.nom)}</option>
        `).join('');
        document.getElementById('newSujetCategorie').innerHTML = `<option value="">Sélectionnez une catégorie...</option>${selectHtml}`;
        
        switchView('categories');
        updateNavState('Home');
    } catch (error) {
        console.error('Erreur chargement catégories:', error);
    }
}

function showCategories() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchInputMobile').value = '';
    loadCategories();
}

// ========== Afficher tous les sujets ==========
async function showAllSujets(page = 1) {
    try {
        const data = await apiCall('sujets', { page });
        
        const html = data.sujets.length ? data.sujets.map((sujet, index) => {
            const classes = ['card', 'topic-card', 'mb-2'];
            if (sujet.epingle) classes.push('topic-pinned');
            if (sujet.verrouille) classes.push('topic-locked');
            
            return `
                <div class="${classes.join(' ')}" style="animation-delay: ${index * 0.03}s">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 d-flex align-items-center flex-wrap gap-2">
                                    ${sujet.epingle ? '<span class="badge bg-warning text-dark"><i class="bi bi-pin-fill"></i></span>' : ''}
                                    ${sujet.verrouille ? '<span class="badge bg-secondary"><i class="bi bi-lock-fill"></i></span>' : ''}
                                    <a href="#" onclick="showMessages(${sujet.id}); return false;" class="text-decoration-none">
                                        ${escapeHtml(sujet.titre)}
                                    </a>
                                </h6>
                                <div class="text-muted small">
                                    <i class="bi bi-person"></i> ${escapeHtml(sujet.auteur)}
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-clock"></i> ${formatDate(sujet.date_creation)}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="stats-badge"><i class="bi bi-chat-left-text"></i> ${sujet.nb_messages || 0}</span>
                                <span class="stats-badge"><i class="bi bi-eye"></i> ${sujet.vues}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('') : `
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-chat-square-text"></i></div>
                <h5 class="empty-state-title">Aucun sujet</h5>
                <p class="text-muted">Aucune discussion n'a encore été créée.</p>
            </div>
        `;
        
        document.getElementById('allSujetsList').innerHTML = html;
        
        const pagination = createPagination(data.page, data.total_pages, (p) => showAllSujets(p));
        document.getElementById('allSujetsPagination').innerHTML = pagination;
        
        switchView('allSujets');
        updateNavState('Sujets');
    } catch (error) {
        console.error('Erreur chargement tous les sujets:', error);
    }
}

// ========== Gestion des Sujets par Catégorie ==========
async function showSujets(categorieId, categorieNom, page = 1) {
    currentCategorie = categorieId;
    currentCategorieName = categorieNom;
    
    try {
        const data = await apiCall('sujets', { categorie_id: categorieId, page });
        
        document.getElementById('categorieTitle').innerHTML = `
            <i class="bi bi-folder-open-fill text-primary me-2"></i>${escapeHtml(categorieNom)}
        `;
        document.getElementById('breadcrumbCategorie').textContent = categorieNom;
        
        const html = data.sujets.length ? data.sujets.map((sujet, index) => {
            const classes = ['card', 'topic-card', 'mb-2'];
            if (sujet.epingle) classes.push('topic-pinned');
            if (sujet.verrouille) classes.push('topic-locked');
            
            return `
                <div class="${classes.join(' ')}" style="animation-delay: ${index * 0.03}s">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-8 mb-2 mb-md-0">
                                <h6 class="mb-1 d-flex align-items-center flex-wrap gap-2">
                                    ${sujet.epingle ? '<span class="badge bg-warning text-dark"><i class="bi bi-pin-fill"></i></span>' : ''}
                                    ${sujet.verrouille ? '<span class="badge bg-secondary"><i class="bi bi-lock-fill"></i></span>' : ''}
                                    <a href="#" onclick="showMessages(${sujet.id}); return false;" class="text-decoration-none">
                                        ${escapeHtml(sujet.titre)}
                                    </a>
                                </h6>
                                <div class="text-muted small">
                                    <i class="bi bi-person"></i> ${escapeHtml(sujet.auteur)}
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-clock"></i> ${formatDate(sujet.date_creation)}
                                </div>
                            </div>
                            <div class="col-12 col-md-4 text-md-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                                    <span class="stats-badge"><i class="bi bi-chat-left-text"></i> ${sujet.nb_messages || 0}</span>
                                    <span class="stats-badge"><i class="bi bi-eye"></i> ${sujet.vues}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('') : `
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-chat-square-text"></i></div>
                <h5 class="empty-state-title">Aucun sujet</h5>
                <p class="text-muted mb-3">Soyez le premier à lancer une discussion !</p>
                <button class="btn btn-primary" onclick="showNewSujetModal()">
                    <i class="bi bi-plus-lg me-1"></i> Créer un sujet
                </button>
            </div>
        `;
        
        document.getElementById('sujetsList').innerHTML = html;
        
        const pagination = createPagination(data.page, data.total_pages, (p) => showSujets(categorieId, categorieNom, p));
        document.getElementById('sujetsPagination').innerHTML = pagination;
        
        switchView('sujets');
    } catch (error) {
        console.error('Erreur chargement sujets:', error);
    }
}

function backToSujets() {
    if (currentCategorie && currentCategorieName) {
        showSujets(currentCategorie, currentCategorieName);
    } else {
        showCategories();
    }
}

// ========== Modal Nouveau Sujet ==========
function showNewSujetModal() {
    // Pré-sélectionner la catégorie actuelle si on est dans une catégorie
    const select = document.getElementById('newSujetCategorie');
    const wrapper = document.getElementById('categorySelectWrapper');
    
    if (currentCategorie) {
        select.value = currentCategorie;
        wrapper.style.display = 'none';
    } else {
        select.value = '';
        wrapper.style.display = 'block';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('newSujetModal'));
    modal.show();
}

function showNewSujetModalGlobal() {
    currentCategorie = null;
    const select = document.getElementById('newSujetCategorie');
    const wrapper = document.getElementById('categorySelectWrapper');
    
    select.value = '';
    wrapper.style.display = 'block';
    
    const modal = new bootstrap.Modal(document.getElementById('newSujetModal'));
    modal.show();
}

async function createSujet() {
    const categorieId = currentCategorie || document.getElementById('newSujetCategorie').value;
    const auteur = document.getElementById('newSujetAuteur').value.trim();
    const titre = document.getElementById('newSujetTitre').value.trim();
    const contenu = document.getElementById('newSujetContenu').value.trim();
    
    if (!categorieId) {
        showToast('Veuillez sélectionner une catégorie', 'warning');
        return;
    }
    
    if (!auteur || !titre || !contenu) {
        showToast('Veuillez remplir tous les champs', 'warning');
        return;
    }
    
    try {
        await apiCall('sujets', {}, 'POST', {
            categorie_id: parseInt(categorieId),
            auteur,
            titre,
            contenu
        });
        
        bootstrap.Modal.getInstance(document.getElementById('newSujetModal'))?.hide();
        
        document.getElementById('newSujetAuteur').value = '';
        document.getElementById('newSujetTitre').value = '';
        document.getElementById('newSujetContenu').value = '';
        
        // Trouver le nom de la catégorie
        const cat = allCategories.find(c => c.id == categorieId);
        if (cat) {
            showSujets(categorieId, cat.nom);
        } else {
            showCategories();
        }
        
        showToast('Votre sujet a été créé avec succès !', 'success');
    } catch (error) {
        console.error('Erreur création sujet:', error);
    }
}

// ========== Gestion des Messages ==========
async function showMessages(sujetId, page = 1) {
    currentSujet = sujetId;
    
    try {
        const [sujet, messagesData] = await Promise.all([
            apiCall('sujet', { id: sujetId }),
            apiCall('messages', { sujet_id: sujetId, page })
        ]);
        
        // Mettre à jour les breadcrumbs
        document.getElementById('breadcrumbCategorieMsg').textContent = sujet.categorie_nom || 'Catégorie';
        document.getElementById('breadcrumbSujet').textContent = sujet.titre;
        
        document.getElementById('sujetTitle').textContent = sujet.titre;
        document.getElementById('sujetInfo').innerHTML = `
            <span class="author-badge author-badge-light">
                <i class="bi bi-person-fill"></i> ${escapeHtml(sujet.auteur)}
            </span>
            <span class="stats-badge">
                <i class="bi bi-calendar3"></i> ${formatDate(sujet.date_creation)}
            </span>
            <span class="stats-badge">
                <i class="bi bi-eye-fill"></i> ${sujet.vues} vue${sujet.vues > 1 ? 's' : ''}
            </span>
        `;
        
        updateTopicButtons(sujet);
        
        const html = messagesData.messages.length ? messagesData.messages.map((msg, index) => `
            <div class="message-item" id="message-${msg.id}">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2 mb-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="author-badge">${escapeHtml(msg.auteur)}</span>
                        ${index === 0 ? '<span class="badge bg-success">Auteur du sujet</span>' : ''}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock"></i> ${formatDate(msg.date_creation)}
                        ${msg.modifie ? `<span class="badge bg-light text-muted ms-1"><i class="bi bi-pencil"></i> modifié</span>` : ''}
                    </div>
                </div>
                <div class="message-content mb-3">${parseBBCode(msg.contenu)}</div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="editMessage(${msg.id}, \`${escapeHtml(msg.contenu).replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-sm-inline ms-1">Modifier</span>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="showDeleteConfirm(${msg.id})">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-sm-inline ms-1">Supprimer</span>
                    </button>
                </div>
            </div>
        `).join('') : `
            <div class="empty-state py-4">
                <div class="empty-state-icon"><i class="bi bi-chat-left-dots"></i></div>
                <h5 class="empty-state-title">Aucun message</h5>
            </div>
        `;
        
        document.getElementById('messagesList').innerHTML = html;
        
        const pagination = createPagination(messagesData.page, messagesData.total_pages, (p) => showMessages(sujetId, p));
        document.getElementById('messagesPagination').innerHTML = pagination;
        
        document.getElementById('replyForm').style.display = sujet.verrouille ? 'none' : 'block';
        
        // Sauvegarder la catégorie pour le retour
        if (sujet.categorie_id) {
            currentCategorie = sujet.categorie_id;
            currentCategorieName = sujet.categorie_nom;
        }
        
        switchView('messages');
    } catch (error) {
        console.error('Erreur chargement messages:', error);
    }
}

function updateTopicButtons(sujet) {
    const btnPin = document.getElementById('btnPin');
    const btnLock = document.getElementById('btnLock');
    
    if (btnPin) {
        btnPin.innerHTML = sujet.epingle 
            ? '<i class="bi bi-pin-fill"></i><span class="d-none d-md-inline ms-1">Désépingler</span>'
            : '<i class="bi bi-pin-angle"></i><span class="d-none d-md-inline ms-1">Épingler</span>';
        btnPin.classList.toggle('btn-warning', sujet.epingle);
        btnPin.classList.toggle('btn-outline-secondary', !sujet.epingle);
    }
    
    if (btnLock) {
        btnLock.innerHTML = sujet.verrouille 
            ? '<i class="bi bi-unlock-fill"></i><span class="d-none d-md-inline ms-1">Déverrouiller</span>'
            : '<i class="bi bi-lock"></i><span class="d-none d-md-inline ms-1">Verrouiller</span>';
        btnLock.classList.toggle('btn-danger', sujet.verrouille);
        btnLock.classList.toggle('btn-outline-danger', !sujet.verrouille);
    }
}

async function submitReply() {
    const auteur = document.getElementById('replyAuteur').value.trim();
    const contenu = document.getElementById('replyContenu').value.trim();
    
    if (!auteur || !contenu) {
        showToast('Veuillez remplir tous les champs', 'warning');
        return;
    }
    
    try {
        await apiCall('messages', {}, 'POST', {
            sujet_id: currentSujet,
            auteur,
            contenu
        });
        
        document.getElementById('replyAuteur').value = '';
        document.getElementById('replyContenu').value = '';
        document.getElementById('bbcodePreview').innerHTML = '<span class="text-muted fst-italic">L\'aperçu s\'affichera ici en temps réel...</span>';
        
        showMessages(currentSujet);
        showToast('Votre réponse a été publiée !', 'success');
    } catch (error) {
        console.error('Erreur envoi message:', error);
    }
}

function editMessage(id, currentContent) {
    document.getElementById('editMessageId').value = id;
    document.getElementById('editMessageContenu').value = currentContent;
    const modal = new bootstrap.Modal(document.getElementById('editMessageModal'));
    modal.show();
}

async function saveMessageEdit() {
    const id = document.getElementById('editMessageId').value;
    const newContent = document.getElementById('editMessageContenu').value.trim();
    
    if (!newContent) {
        showToast('Le message ne peut pas être vide', 'warning');
        return;
    }
    
    try {
        await apiCall('message', {}, 'PUT', { id: parseInt(id), contenu: newContent });
        bootstrap.Modal.getInstance(document.getElementById('editMessageModal'))?.hide();
        showMessages(currentSujet);
        showToast('Message modifié avec succès !', 'success');
    } catch (error) {
        console.error('Erreur modification message:', error);
    }
}

function showDeleteConfirm(id) {
    document.getElementById('deleteMessageId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
}

async function confirmDeleteMessage() {
    const id = document.getElementById('deleteMessageId').value;
    
    try {
        await apiCall('message', {}, 'DELETE', { id: parseInt(id) });
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'))?.hide();
        showMessages(currentSujet);
        showToast('Message supprimé', 'success');
    } catch (error) {
        console.error('Erreur suppression message:', error);
    }
}

async function togglePin() {
    try {
        const sujet = await apiCall('sujet', { id: currentSujet });
        await apiCall('sujet', {}, 'PUT', { 
            id: currentSujet, 
            epingle: !sujet.epingle 
        });
        showMessages(currentSujet);
        showToast(sujet.epingle ? 'Sujet désépinglé' : 'Sujet épinglé', 'success');
    } catch (error) {
        console.error('Erreur toggle pin:', error);
    }
}

async function toggleLock() {
    try {
        const sujet = await apiCall('sujet', { id: currentSujet });
        await apiCall('sujet', {}, 'PUT', { 
            id: currentSujet, 
            verrouille: !sujet.verrouille 
        });
        showMessages(currentSujet);
        showToast(sujet.verrouille ? 'Sujet déverrouillé' : 'Sujet verrouillé', 'success');
    } catch (error) {
        console.error('Erreur toggle lock:', error);
    }
}

// ========== Gestion BBCode ==========
function insertBBCode(openTag, closeTag) {
    const textarea = document.getElementById('replyContenu');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selectedText = text.substring(start, end);
    
    const newText = text.substring(0, start) + openTag + selectedText + closeTag + text.substring(end);
    textarea.value = newText;
    
    const newPosition = start + openTag.length + selectedText.length + closeTag.length;
    textarea.focus();
    textarea.setSelectionRange(newPosition, newPosition);
    
    updateBBCodePreview();
}

function updateBBCodePreview() {
    const content = document.getElementById('replyContenu')?.value;
    const previewEl = document.getElementById('bbcodePreview');
    if (!previewEl) return;
    
    if (content && content.trim()) {
        previewEl.innerHTML = parseBBCode(content);
    } else {
        previewEl.innerHTML = '<span class="text-muted fst-italic">L\'aperçu s\'affichera ici en temps réel...</span>';
    }
}

function parseBBCode(text) {
    text = escapeHtml(text);
    
    text = text.replace(/\[b\](.*?)\[\/b\]/gi, '<strong>$1</strong>');
    text = text.replace(/\[i\](.*?)\[\/i\]/gi, '<em>$1</em>');
    text = text.replace(/\[u\](.*?)\[\/u\]/gi, '<u>$1</u>');
    text = text.replace(/\[code\](.*?)\[\/code\]/gi, '<code>$1</code>');
    text = text.replace(/\[url=(.*?)\](.*?)\[\/url\]/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>');
    text = text.replace(/\[quote\](.*?)\[\/quote\]/gi, '<blockquote class="border-start border-3 border-primary ps-3 py-2 my-2 bg-light rounded">$1</blockquote>');
    text = text.replace(/\n/g, '<br>');
    
    return text;
}

// ========== Recherche ==========
async function searchForum(query) {
    try {
        const results = await apiCall('search', { q: query });
        
        document.getElementById('searchResultsInfo').textContent = `${results.length} résultat${results.length > 1 ? 's' : ''} pour "${query}"`;
        
        const html = results.length ? results.map((r, index) => `
            <div class="col-12">
                <div class="card card-interactive" style="animation-delay: ${index * 0.03}s" onclick="showMessages(${r.id})">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="category-icon" style="background: var(--info-light); color: var(--info);">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">${escapeHtml(r.titre)}</h5>
                                <p class="mb-2 text-muted small text-truncate-2">${escapeHtml((r.contenu || '').substring(0, 150))}...</p>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                    <span><i class="bi bi-person-fill"></i> ${escapeHtml(r.message_auteur || r.auteur)}</span>
                                    <span class="text-muted">•</span>
                                    <span><i class="bi bi-clock"></i> ${formatDate(r.date_creation)}</span>
                                    ${r.categorie_nom ? `<span class="text-muted">•</span><span><i class="bi bi-folder"></i> ${escapeHtml(r.categorie_nom)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('') : `
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-search"></i></div>
                    <h5 class="empty-state-title">Aucun résultat</h5>
                    <p class="text-muted">Aucune discussion ne correspond à votre recherche.</p>
                    <button class="btn btn-primary" onclick="showCategories()">
                        Retour aux catégories
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('searchResultsList').innerHTML = html;
        switchView('searchResults');
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

// ========== Statistiques ==========
async function showStats() {
    try {
        const stats = await apiCall('stats');
        
        const populairesHtml = stats.populaires?.length ? stats.populaires.map((s, index) => `
            <a href="#" onclick="showMessages(${s.id}); bootstrap.Modal.getInstance(document.getElementById('statsModal')).hide(); return false;" class="list-group-item list-group-item-action border-0 ${index > 0 ? 'border-top' : ''}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold mb-1">${escapeHtml(s.titre)}</div>
                        <small class="text-muted">par ${escapeHtml(s.auteur)}</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">${s.vues} <i class="bi bi-eye-fill"></i></span>
                </div>
            </a>
        `).join('') : '<p class="text-muted text-center py-3 mb-0">Aucune donnée</p>';
        
        const actifsHtml = stats.actifs?.length ? stats.actifs.map((a, index) => `
            <div class="list-group-item border-0 ${index > 0 ? 'border-top' : ''}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.75rem;">
                            ${index + 1}
                        </div>
                        <span class="fw-medium">${escapeHtml(a.auteur)}</span>
                    </div>
                    <span class="badge bg-success rounded-pill">${a.nb_messages} <i class="bi bi-chat-fill"></i></span>
                </div>
            </div>
        `).join('') : '<p class="text-muted text-center py-3 mb-0">Aucune donnée</p>';
        
        document.getElementById('statsPopulaires').innerHTML = populairesHtml;
        document.getElementById('statsActifs').innerHTML = actifsHtml;
        
        const modal = new bootstrap.Modal(document.getElementById('statsModal'));
        modal.show();
    } catch (error) {
        console.error('Erreur stats:', error);
    }
}

// ========== Help Modal ==========
function showHelp() {
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
}

// ========== Fonctions utilitaires ==========
function switchView(view) {
    const views = ['categories', 'allSujets', 'sujets', 'messages', 'searchResults'];
    views.forEach(v => {
        const el = document.getElementById(v + 'View');
        if (el) el.style.display = v === view ? 'block' : 'none';
    });
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function createPagination(current, total, callback) {
    if (total <= 1) return '';
    
    let html = '<ul class="pagination justify-content-center mb-0">';
    
    html += `
        <li class="page-item ${current === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); ${current > 1 ? `(${callback})(${current - 1})` : 'return false'};">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 2 && i <= current + 2)) {
            html += `
                <li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); (${callback})(${i});">${i}</a>
                </li>
            `;
        } else if (i === current - 3 || i === current + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    html += `
        <li class="page-item ${current === total ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); ${current < total ? `(${callback})(${current + 1})` : 'return false'};">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    html += '</ul>';
    return html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'À l\'instant';
    if (diff < 3600000) return `Il y a ${Math.floor(diff / 60000)} min`;
    if (diff < 86400000) return `Il y a ${Math.floor(diff / 3600000)}h`;
    if (diff < 604800000) {
        const days = Math.floor(diff / 86400000);
        return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
    }
    
    return date.toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric'
    });
}
