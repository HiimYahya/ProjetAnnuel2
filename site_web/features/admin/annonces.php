<?php
session_start();
include '../../fonctions/fonctions.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    header('Location: /site_web/features/public/login.php');
    exit;
}
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des annonces - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/site_web/assets/dist/admin.css">
    <style>
        .modal { z-index: 1055 !important; }
        .modal-backdrop { z-index: 1050 !important; }
        body { background-color: #f8f9fa; }
        .page-title { text-align: center; margin: 40px 0 20px; }
        .page-title h1 { font-size: 1.75rem; font-weight: 600; }
        .page-content { max-width: 1200px; margin: 0 auto; }
        .add-button-container { text-align: right; margin-bottom: 20px; }
        .card { border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
        .table th { background-color: #f8f9fa; }
        .alert-container { position: fixed; top: 20px; right: 20px; z-index: 1100; min-width: 300px; }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Annonces</h1>
        </div>
        
        <div class="page-content">
            <div id="alert-container"></div>
            
            <div class="add-button-container">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAnnonceModal">
                    <i class="fas fa-plus me-2"></i>Ajouter une annonce
                </button>
            </div>
            
            <!-- Filtres de recherche -->
            <div class="card search-card mb-4">
                <div class="card-body">
                    <form id="search-form" class="row g-3 align-items-center">
                        <div class="col">
                            <input type="text" class="form-control" placeholder="Rechercher par titre ou ville..." name="search" id="search-input">
                        </div>
                        <div class="col-md-3">
                             <select class="form-select" id="statut-filter">
                                <option value="">Tous les statuts</option>
                                <option value="disponible">Disponible</option>
                                <option value="prise en charge">Prise en charge</option>
                                <option value="livrée">Livrée</option>
                                <option value="annulée">Annulée</option>
                            </select>
                            </div>
                        <div class="col-auto">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        <div class="col-auto">
                            <button type="button" id="reset-search" class="btn btn-outline-secondary w-100">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Départ</th>
                                    <th>Arrivée</th>
                                    <th>Prix</th>
                                    <th>Auteur</th>
                                    <th>Publiée le</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="annonces-table-body">
                                <!-- Le contenu sera chargé par JavaScript -->
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <nav aria-label="Pagination des annonces">
                        <ul class="pagination justify-content-center mt-4" id="pagination-container"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal d'ajout d'annonce -->
    <div class="modal fade" id="addAnnonceModal" tabindex="-1" aria-labelledby="addAnnonceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une annonce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-annonce-form">
                <div class="modal-body">
                        <div id="add-alert-container"></div>
                        
                        <div class="mb-3">
                            <label for="add-titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="add-titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add-description" class="form-label">Description</label>
                            <textarea class="form-control" id="add-description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add-ville_depart" class="form-label">Ville de départ</label>
                                <input type="text" class="form-control" id="add-ville_depart" name="ville_depart" required>
                                </div>
                            <div class="col-md-6 mb-3">
                                <label for="add-ville_arrivee" class="form-label">Ville d'arrivée</label>
                                <input type="text" class="form-control" id="add-ville_arrivee" name="ville_arrivee" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add-taille" class="form-label">Taille (Volume m³)</label>
                                <input type="number" class="form-control" id="add-taille" name="taille" step="0.01" required>
                                </div>
                            <div class="col-md-6 mb-3">
                                <label for="add-prix" class="form-label">Prix (€)</label>
                                <input type="number" class="form-control" id="add-prix" name="prix" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add-date_livraison" class="form-label">Date de livraison souhaitée</label>
                                <input type="date" class="form-control" id="add-date_livraison" name="date_livraison">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add-date_expiration" class="form-label">Date d'expiration de l'annonce</label>
                                <input type="date" class="form-control" id="add-date_expiration" name="date_expiration">
                            </div>
                        </div>
                        
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="add-statut" class="form-label">Statut</label>
                                <select class="form-select" id="add-statut" name="statut" required>
                                    <option value="disponible" selected>Disponible</option>
                                    <option value="prise en charge">Prise en charge</option>
                                    <option value="livrée">Livrée</option>
                                    <option value="annulée">Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add-segmentation_possible" name="segmentation_possible" value="1">
                                    <label class="form-check-label" for="add-segmentation_possible">
                                        Segmentation possible
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add-id_client" class="form-label">Auteur de l'annonce</label>
                            <select class="form-select" id="add-id_client" name="id_client" required>
                                <!-- Les utilisateurs seront chargés ici par JS -->
                            </select>
                        </div>
                        
                        </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter l'annonce</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <!-- Modal de modification d'annonce -->
    <div class="modal fade" id="editAnnonceModal" tabindex="-1" aria-labelledby="editAnnonceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'annonce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-annonce-form">
                <div class="modal-body">
                        <div id="edit-alert-container"></div>
                        <input type="hidden" id="edit-id" name="id">
                        
                        <div class="mb-3">
                            <label for="edit-titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="edit-titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                    <label for="edit-ville_depart" class="form-label">Ville de départ</label>
                                    <input type="text" class="form-control" id="edit-ville_depart" name="ville_depart" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                    <label for="edit-ville_arrivee" class="form-label">Ville d'arrivée</label>
                                    <input type="text" class="form-control" id="edit-ville_arrivee" name="ville_arrivee" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-taille" class="form-label">Taille (Volume m³)</label>
                                <input type="number" class="form-control" id="edit-taille" name="taille" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-prix" class="form-label">Prix (€)</label>
                                <input type="number" class="form-control" id="edit-prix" name="prix" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-date_livraison_souhaitee" class="form-label">Date de livraison souhaitée</label>
                                <input type="date" class="form-control" id="edit-date_livraison_souhaitee" name="date_livraison_souhaitee">
                            </div>
                            <div class="col-md-6 mb-3">
                                    <label for="edit-date_expiration" class="form-label">Date d'expiration</label>
                                    <input type="date" class="form-control" id="edit-date_expiration" name="date_expiration">
                            </div>
                        </div>
                        
                         <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="edit-statut" class="form-label">Statut</label>
                                <select class="form-select" id="edit-statut" name="statut" required>
                                    <option value="disponible">Disponible</option>
                                    <option value="prise en charge">Prise en charge</option>
                                    <option value="livrée">Livrée</option>
                                    <option value="annulée">Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit-segmentation_possible" name="segmentation_possible" value="1">
                                    <label class="form-check-label" for="edit-segmentation_possible">
                                        Segmentation possible
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-id_client" class="form-label">Auteur de l'annonce</label>
                            <select class="form-select" id="edit-id_client" name="id_client" required>
                                <!-- Les utilisateurs seront chargés ici par JS -->
                            </select>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/site_web/assets/js/darkmode.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_URL_ANNONCES = '/site_web/api/admin/annonces/';
            const API_URL_USERS = '/site_web/api/admin/utilisateurs/';

            // --- Éléments du DOM ---
            const tableBody = document.getElementById('annonces-table-body');
            const paginationContainer = document.getElementById('pagination-container');
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');
            const statutFilter = document.getElementById('statut-filter');
            const resetSearchBtn = document.getElementById('reset-search');

            // Modale d'ajout
            const addAnnonceModalEl = document.getElementById('addAnnonceModal');
            const addAnnonceForm = document.getElementById('add-annonce-form');
            const addAnnonceModal = new bootstrap.Modal(addAnnonceModalEl);
            const userSelectAdd = document.getElementById('add-id_client');

            // Modale de modification
            const editAnnonceModalEl = document.getElementById('editAnnonceModal');
            const editAnnonceForm = document.getElementById('edit-annonce-form');
            const editAnnonceModal = new bootstrap.Modal(editAnnonceModalEl);
            const userSelectEdit = document.getElementById('edit-id_client');


            let currentPage = 1;
            let currentSearch = '';
            let currentStatut = '';

            // --- Fonctions Utilitaires ---
            const showAlert = (message, type = 'success', container = 'alert-container', duration = 5000) => {
                const alertContainer = document.getElementById(container);
                if (!alertContainer) return;
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.role = 'alert';
                alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                alertContainer.prepend(alert);
                setTimeout(() => new bootstrap.Alert(alert).close(), duration);
            };

            const getStatutBadge = (statut) => {
                const badges = {
                    'disponible': { bg: 'success', text: 'Disponible' },
                    'prise en charge': { bg: 'warning', text: 'Prise en charge' },
                    'livrée': { bg: 'primary', text: 'Livrée' },
                    'annulée': { bg: 'danger', text: 'Annulée' },
                };
                const badge = badges[statut] || { bg: 'secondary', text: 'Inconnu' };
                return `<span class="badge bg-${badge.bg}">${badge.text}</span>`;
            };

            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            // --- Fonctions de Rendu ---
            const renderTable = (annonces) => {
                tableBody.innerHTML = '';
                if (!annonces || annonces.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Aucune annonce trouvée.</td></tr>';
                    return;
                }
                annonces.forEach(annonce => {
                    const tr = document.createElement('tr');
                    tr.dataset.annonceId = annonce.id;
                    tr.innerHTML = `
                        <td>${annonce.id}</td>
                        <td>${annonce.titre}</td>
                        <td>${annonce.ville_depart}</td>
                        <td>${annonce.ville_arrivee}</td>
                        <td>${parseFloat(annonce.prix).toFixed(2)} €</td>
                        <td>${annonce.auteur_nom || 'N/A'}</td>
                        <td>${new Date(annonce.date_annonce).toLocaleDateString('fr-FR')}</td>
                        <td>${getStatutBadge(annonce.statut)}</td>
                        <td class="text-end">
                            <button class="btn btn-primary btn-sm btn-edit" data-annonce='${JSON.stringify(annonce)}'><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm btn-delete" data-id="${annonce.id}"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            };

            const renderPagination = (totalPages, page) => {
                paginationContainer.innerHTML = '';
                if (totalPages <= 1) return;
                const createPageItem = (text, p, isDisabled = false, isActive = false) => {
                    const li = document.createElement('li');
                    li.className = `page-item ${isDisabled ? 'disabled' : ''} ${isActive ? 'active' : ''}`;
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.textContent = text;
                    a.dataset.page = p;
                    li.appendChild(a);
                    return li;
                };
                paginationContainer.appendChild(createPageItem('Précédent', page - 1, page <= 1));
                for (let i = 1; i <= totalPages; i++) {
                    paginationContainer.appendChild(createPageItem(i, i, false, i === page));
                }
                paginationContainer.appendChild(createPageItem('Suivant', page + 1, page >= totalPages));
            };

            const populateUserSelects = async () => {
                try {
                    const response = await fetch(`${API_URL_USERS}list.php`);
                    if (!response.ok) throw new Error('Erreur lors de la récupération des utilisateurs');
                    const users = await response.json();
                    
                    userSelectAdd.innerHTML = '<option value="">Sélectionnez un auteur</option>';
                    userSelectEdit.innerHTML = '<option value="">Sélectionnez un auteur</option>';

                    users.forEach(user => {
                        const option = `<option value="${user.id}">${user.nom} (${user.email})</option>`;
                        userSelectAdd.innerHTML += option;
                        userSelectEdit.innerHTML += option;
                    });
                } catch (error) {
                    console.error(error);
                    showAlert('Impossible de charger la liste des utilisateurs.', 'danger');
                }
            };

            // --- Fonctions API ---
            const fetchAnnonces = async (page = 1, search = '', statut = '') => {
                currentPage = page;
                currentSearch = search;
                currentStatut = statut;
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Chargement...</td></tr>';
                
                try {
                    const response = await fetch(`${API_URL_ANNONCES}get.php?page=${page}&search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    
                    renderTable(data.annonces);
                    renderPagination(data.totalPages, data.currentPage);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Erreur lors du chargement des annonces.</td></tr>';
                    showAlert('Impossible de charger les annonces.', 'danger');
                }
            };

            // --- Écouteurs d'événements ---
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                fetchAnnonces(1, searchInput.value.trim(), statutFilter.value);
            });

            resetSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                statutFilter.value = '';
                fetchAnnonces(1, '', '');
            });

            paginationContainer.addEventListener('click', (e) => {
                e.preventDefault();
                if (e.target.tagName === 'A' && e.target.dataset.page) {
                    const page = parseInt(e.target.dataset.page, 10);
                    if (!isNaN(page)) fetchAnnonces(page, currentSearch, currentStatut);
                }
            });

            // Ajout
            addAnnonceForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                data.segmentation_possible = formData.has('segmentation_possible');

                try {
                    const response = await fetch(`${API_URL_ANNONCES}post.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error || 'Erreur inconnue.');

                    addAnnonceModal.hide();
                    showAlert(result.success, 'success');
                    fetchAnnonces(currentPage, currentSearch, currentStatut);
                } catch (error) {
                    showAlert(error.message, 'danger', 'add-alert-container');
                }
            });

            // Clics sur Modifier / Supprimer
            tableBody.addEventListener('click', (e) => {
                const editButton = e.target.closest('.btn-edit');
                const deleteButton = e.target.closest('.btn-delete');

                if (editButton) {
                    const annonce = JSON.parse(editButton.dataset.annonce);
                    
                    // Populate form
                    document.getElementById('edit-id').value = annonce.id;
                    document.getElementById('edit-titre').value = annonce.titre;
                    document.getElementById('edit-description').value = annonce.description;
                    document.getElementById('edit-ville_depart').value = annonce.ville_depart;
                    document.getElementById('edit-ville_arrivee').value = annonce.ville_arrivee;
                    document.getElementById('edit-taille').value = annonce.taille;
                    document.getElementById('edit-prix').value = annonce.prix;
                    document.getElementById('edit-date_livraison_souhaitee').value = formatDate(annonce.date_livraison_souhaitee);
                    document.getElementById('edit-date_expiration').value = formatDate(annonce.date_expiration);
                    document.getElementById('edit-statut').value = annonce.statut;
                    document.getElementById('edit-segmentation_possible').checked = annonce.segmentation_possible == 1;
                    document.getElementById('edit-id_client').value = annonce.id_client;
                    
                    editAnnonceModal.show();
                }

                if (deleteButton) {
                    const id = deleteButton.dataset.id;
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
                        fetch(`${API_URL_ANNONCES}delete.php?id=${id}`, { method: 'DELETE' })
                        .then(response => response.json().then(data => ({ ok: response.ok, data })))
                        .then(({ ok, data }) => {
                            if (!ok) throw new Error(data.error);
                            showAlert(data.success, 'success');
                            fetchAnnonces(currentPage, currentSearch, currentStatut);
                        })
                        .catch(error => showAlert(error.message, 'danger'));
                    }
                }
            });

            // Modification
            editAnnonceForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                data.segmentation_possible = formData.has('segmentation_possible');

                try {
                    const response = await fetch(`${API_URL_ANNONCES}put.php`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error || 'Erreur inconnue.');
                    
                    editAnnonceModal.hide();
                    showAlert(result.success, 'success');
                    fetchAnnonces(currentPage, currentSearch, currentStatut);
                } catch (error) {
                    showAlert(error.message, 'danger', 'edit-alert-container');
                }
            });
            
            // Nettoyage des modales
            addAnnonceModalEl.addEventListener('hidden.bs.modal', () => {
                document.getElementById('add-alert-container').innerHTML = '';
                addAnnonceForm.reset();
            });
            editAnnonceModalEl.addEventListener('hidden.bs.modal', () => {
                document.getElementById('edit-alert-container').innerHTML = '';
            });

            // --- Initialisation ---
            populateUserSelects();
            fetchAnnonces();
        });
    </script>
</body>
</html> 
