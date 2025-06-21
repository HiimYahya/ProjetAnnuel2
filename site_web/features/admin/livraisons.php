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
    <title>Gestion des livraisons - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/site_web/assets/js/color-modes.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/site_web/assets/dist/admin.css">
    <style>
        /* Style général */
        body { background-color: #f8f9fa; }
        .modal { z-index: 1055 !important; }
        .modal-backdrop { z-index: 1050 !important; }
        .page-title { text-align: center; margin: 40px 0 20px; }
        .page-title h1 { font-size: 1.75rem; font-weight: 600; color: #333; }
        .page-content { max-width: 1200px; margin: 0 auto; }
        .card { border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); margin-bottom: 20px; }
        .table th { background-color: #f8f9fa; font-weight: 500; }
        .segment-row { background-color: #f8f9fa; border-left: 3px solid #0d6efd; }
        .segment-table { margin-bottom: 0; font-size: 0.9rem; }
        .alert-container { position: fixed; top: 20px; right: 20px; z-index: 1100; min-width: 300px; }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Livraisons</h1>
        </div>
        
        <div class="page-content">
            <div id="alert-container"></div>
            
            <!-- Filtres de recherche -->
            <div class="card search-card mb-4">
                <div class="card-body">
                    <form id="search-form" class="row g-3 align-items-center">
                        <div class="col">
                            <input type="text" class="form-control" placeholder="Rechercher par client, livreur, titre..." name="search" id="search-input">
                        </div>
                        <div class="col-md-3">
                             <select class="form-select" id="statut-filter">
                                <option value="">Tous les statuts</option>
                                <option value="en attente">En attente</option>
                                <option value="en cours">En cours</option>
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
                                    <th>Client</th>
                                    <th>Livreur</th>
                                    <th>Annonce</th>
                                    <th>Prise en charge</th>
                                    <th>Livraison</th>
                                    <th>Statut</th>
                                    <th>Validation</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="livraisons-table-body">
                                <!-- Contenu chargé par JavaScript -->
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <nav aria-label="Pagination des livraisons">
                        <ul class="pagination justify-content-center mt-4" id="pagination-container"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification de livraison -->
    <div class="modal fade" id="editLivraisonModal" tabindex="-1" aria-labelledby="editLivraisonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la livraison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-livraison-form">
                <div class="modal-body">
                        <div id="edit-alert-container"></div>
                        <input type="hidden" name="id" id="edit-id">
                        
                        <div class="mb-3">
                            <label for="edit-statut" class="form-label">Statut</label>
                            <select class="form-select" id="edit-statut" name="statut" required>
                                <option value="en attente">En attente</option>
                                <option value="en cours">En cours</option>
                                <option value="livrée">Livrée</option>
                                <option value="annulée">Annulée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-id_livreur" class="form-label">Livreur</label>
                            <select class="form-select" id="edit-id_livreur" name="id_livreur">
                                <!-- Options chargées par JavaScript -->
                            </select>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="edit-validation_client" name="validation_client">
                            <label class="form-check-label" for="edit-validation_client">Validé par le client</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit-reception_confirmee" name="reception_confirmee">
                            <label class="form-check-label" for="edit-reception_confirmee">Réception confirmée</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/site_web/assets/js/darkmode.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // --- Constantes et variables ---
        const API_URL_LIVRAISONS = '/site_web/api/admin/livraisons/';
        const API_URL_USERS = '/site_web/api/admin/utilisateurs/';
        let currentPage = 1;
        let currentSearch = '';
        let currentStatut = '';

        // --- Éléments du DOM ---
        const tableBody = document.getElementById('livraisons-table-body');
        const paginationContainer = document.getElementById('pagination-container');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const statutFilter = document.getElementById('statut-filter');
        const resetSearchBtn = document.getElementById('reset-search');
        
        // Modale de modification
        const editModalEl = document.getElementById('editLivraisonModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('edit-livraison-form');
        const livreurSelect = document.getElementById('edit-id_livreur');
                    
        // --- Fonctions utilitaires ---
        const showAlert = (message, type = 'success', containerId = 'alert-container', duration = 5000) => {
            const container = document.getElementById(containerId);
            if (!container) return;
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            container.prepend(alert);
            setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), duration);
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
        };

        const getStatutInfo = (statut) => {
            const statutLower = statut.toLowerCase();
            const info = {
                'livrée': { icon: '🟢', text: 'Livrée' },
                'en cours': { icon: '🟠', text: 'En cours' },
                'en attente': { icon: '🟡', text: 'En attente' },
                'annulée': { icon: '🔴', text: 'Annulée' }
            };
            return info[statutLower] || { icon: '⚪', text: statut };
        };

        // --- Rendu ---
        const renderTable = (livraisons) => {
            tableBody.innerHTML = '';
            if (!livraisons || livraisons.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Aucune livraison trouvée.</td></tr>';
                return;
            }

            livraisons.forEach(livraison => {
                const statutInfo = getStatutInfo(livraison.statut);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${livraison.id}</td>
                    <td>${livraison.client_nom || 'N/A'}</td>
                    <td>${livraison.livreur_nom || 'N/A'}</td>
                    <td>${livraison.annonce_titre || 'N/A'}</td>
                    <td>${formatDate(livraison.date_prise_en_charge)}</td>
                    <td>${formatDate(livraison.date_livraison)}</td>
                    <td>${statutInfo.icon} ${statutInfo.text}</td>
                    <td>
                        ${livraison.validation_client == 1 ? '<span class="badge bg-success">Client</span>' : ''}
                        ${livraison.reception_confirmee == 1 ? '<span class="badge bg-info">Réception</span>' : ''}
                    </td>
                    <td class="text-end">
                        <button class="btn btn-primary btn-sm btn-edit" title="Modifier"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm btn-delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                        ${livraison.segments && livraison.segments.length > 0 ? `
                        <button class="btn btn-info btn-sm btn-toggle-segments" title="Voir les segments">
                            <i class="fas fa-route"></i> (${livraison.segments.length})
                        </button>` : ''}
                    </td>
                `;
                tr.querySelector('.btn-edit').dataset.livraison = JSON.stringify(livraison);
                tr.querySelector('.btn-delete').dataset.id = livraison.id;
                
                tableBody.appendChild(tr);

                if (livraison.segments && livraison.segments.length > 0) {
                    const segmentRow = document.createElement('tr');
                    segmentRow.classList.add('segment-row', 'collapse');
                    segmentRow.id = `segments-${livraison.id}`;
                    segmentRow.innerHTML = `<td colspan="9" class="p-0">${renderSegmentsTable(livraison.segments)}</td>`;
                    tableBody.appendChild(segmentRow);
                    tr.querySelector('.btn-toggle-segments').setAttribute('data-bs-toggle', 'collapse');
                    tr.querySelector('.btn-toggle-segments').setAttribute('data-bs-target', `#segments-${livraison.id}`);
                }
            });
        };
        
        const renderSegmentsTable = (segments) => {
            let tableHtml = `
                <table class="table segment-table">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>Livreur</th><th>Départ</th><th>Arrivée</th>
                            <th>Pt. Relais Départ</th><th>Pt. Relais Arrivée</th>
                            <th>Statut</th><th>Début</th><th>Fin</th>
                        </tr>
                    </thead>
                    <tbody>`;
            segments.forEach(s => {
                tableHtml += `
                    <tr>
                        <td>${s.id}</td>
                        <td>${s.livreur_nom || 'N/A'}</td>
                        <td>${s.adresse_depart || 'N/A'}</td>
                        <td>${s.adresse_arrivee || 'N/A'}</td>
                        <td>${s.point_relais_depart_nom || 'N/A'}</td>
                        <td>${s.point_relais_arrivee_nom || 'N/A'}</td>
                        <td><span class="badge rounded-pill bg-secondary">${s.statut}</span></td>
                        <td>${formatDate(s.date_debut)}</td>
                        <td>${formatDate(s.date_fin)}</td>
                    </tr>`;
            });
            tableHtml += '</tbody></table>';
            return tableHtml;
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
                a.innerHTML = text;
                a.dataset.page = p;
                li.appendChild(a);
                return li;
            };
            paginationContainer.append(createPageItem('&laquo;', page - 1, page <= 1));
            for (let i = 1; i <= totalPages; i++) {
                paginationContainer.append(createPageItem(i, i, false, i === page));
            }
            paginationContainer.append(createPageItem('&raquo;', page + 1, page >= totalPages));
        };
        
        // --- API ---
        const fetchLivraisons = async (page = 1, search = '', statut = '') => {
            currentPage = page;
            currentSearch = search;
            currentStatut = statut;
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Chargement...</td></tr>';
            try {
                const response = await fetch(`${API_URL_LIVRAISONS}get.php?page=${page}&search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`);
                if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
                const data = await response.json();
                renderTable(data.livraisons);
                renderPagination(data.totalPages, data.currentPage);
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Erreur lors du chargement: ${error.message}</td></tr>`;
                showAlert(error.message, 'danger');
            }
        };
        
        const populateLivreurSelect = async () => {
            try {
                const response = await fetch(`${API_URL_USERS}list.php?role=livreur`);
                if (!response.ok) throw new Error('Erreur de chargement des livreurs');
                const livreurs = await response.json();
                livreurSelect.innerHTML = '<option value="">Aucun livreur assigné</option>';
                livreurs.forEach(l => {
                    livreurSelect.innerHTML += `<option value="${l.id}">${l.nom} (${l.email})</option>`;
                });
            } catch (error) {
                showAlert(error.message, 'danger');
            }
        };

        // --- Event Listeners ---
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchLivraisons(1, searchInput.value.trim(), statutFilter.value);
        });

        resetSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            statutFilter.value = '';
            fetchLivraisons(1, '', '');
        });

        paginationContainer.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.tagName === 'A' && e.target.dataset.page) {
                const page = parseInt(e.target.dataset.page, 10);
                if (!isNaN(page)) fetchLivraisons(page, currentSearch, currentStatut);
            }
        });

        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit');
            const deleteBtn = e.target.closest('.btn-delete');

            if (editBtn) {
                const livraison = JSON.parse(editBtn.dataset.livraison);
                document.getElementById('edit-id').value = livraison.id;
                document.getElementById('edit-statut').value = livraison.statut;
                document.getElementById('edit-id_livreur').value = livraison.id_livreur || '';
                document.getElementById('edit-validation_client').checked = livraison.validation_client == 1;
                document.getElementById('edit-reception_confirmee').checked = livraison.reception_confirmee == 1;
                editModal.show();
            }
            
            if (deleteBtn) {
                const id = deleteBtn.dataset.id;
                if (confirm(`Êtes-vous sûr de vouloir supprimer la livraison N°${id} et ses segments ?`)) {
                    fetch(`${API_URL_LIVRAISONS}delete.php?id=${id}`, { method: 'DELETE' })
                    .then(response => response.json().then(data => ({ ok: response.ok, data })))
                    .then(({ok, data}) => {
                        if (!ok) throw new Error(data.error || 'Erreur inconnue');
                        showAlert(data.success, 'success');
                        fetchLivraisons(currentPage, currentSearch, currentStatut);
                    })
                    .catch(err => showAlert(err.message, 'danger'));
                }
            }
        });

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const data = {
                id: formData.get('id'),
                statut: formData.get('statut'),
                id_livreur: formData.get('id_livreur'),
                validation_client: formData.has('validation_client'),
                reception_confirmee: formData.has('reception_confirmee'),
            };

            try {
                const response = await fetch(`${API_URL_LIVRAISONS}put.php`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'Erreur serveur');
                editModal.hide();
                showAlert(result.success, 'success');
                fetchLivraisons(currentPage, currentSearch, currentStatut);
            } catch (err) {
                showAlert(err.message, 'danger', 'edit-alert-container');
            }
        });

        // --- Initialisation ---
        populateLivreurSelect();
        fetchLivraisons();
        });
    </script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html>
