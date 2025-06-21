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
    <title>Gestion des paiements - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/site_web/assets/js/color-modes.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/site_web/assets/dist/admin.css">
    <style>
        body { background-color: #f8f9fa; }
        .modal { z-index: 1055 !important; }
        .modal-backdrop { z-index: 1050 !important; }
        .page-title { text-align: center; margin: 40px 0 20px; }
        .page-title h1 { font-size: 1.75rem; font-weight: 600; }
        .page-content { max-width: 1200px; margin: 0 auto; }
        .add-button-container { text-align: right; margin-bottom: 20px; }
        .card { border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
        .table th { background-color: #f8f9fa; }
        .alert-container { position: fixed; top: 20px; right: 20px; z-index: 1100; min-width: 300px; }
        .total-card { border-left: 3px solid #28a745; }
        .total-value { font-size: 1.2rem; font-weight: 600; color: #28a745; }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Paiements</h1>
        </div>
        
        <div class="page-content">
            <div id="alert-container"></div>
            
            <div class="add-button-container">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaiementModal">
                    <i class="fas fa-plus me-2"></i>Ajouter un paiement
                </button>
            </div>
            
            <div class="row">
                <!-- Filtres -->
                <div class="col-lg-8">
                    <div class="card search-card mb-4">
                        <div class="card-body">
                            <form id="search-form" class="row g-3 align-items-center">
                                <div class="col">
                                    <input type="text" class="form-control" placeholder="Rechercher par méthode..." name="search" id="search-input">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="statut-filter">
                                        <option value="">Tous les statuts</option>
                                        <option value="effectué">Effectué</option>
                                        <option value="en attente">En attente</option>
                                        <option value="échoué">Échoué</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-outline-primary w-100" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                                <div class="col-auto">
                                    <button type="button" id="reset-search" class="btn btn-outline-secondary w-100"><i class="fas fa-undo"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Total -->
                <div class="col-lg-4">
                     <div class="card total-card mb-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Effectué :</h5>
                            <div class="total-value" id="total-effectue">0,00 €</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Créancier</th>
                                    <th>Débiteur</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paiements-table-body">
                                <!-- Contenu chargé par JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center mt-4" id="pagination-container"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de paiement -->
    <div class="modal fade" id="addPaiementModal" tabindex="-1" aria-labelledby="addPaiementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-paiement-form">
                    <div class="modal-body">
                        <div id="add-alert-container"></div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add-id_creancier" class="form-label">Créancier</label>
                                <select class="form-select" id="add-id_creancier" name="id_creancier" required>
                                    <!-- Options chargées par JS -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add-id_debiteur" class="form-label">Débiteur</label>
                                <select class="form-select" id="add-id_debiteur" name="id_debiteur" required>
                                    <!-- Options chargées par JS -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="add-montant" class="form-label">Montant (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="add-montant" name="montant" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add-methode" class="form-label">Méthode de paiement</label>
                            <select class="form-select" id="add-methode" name="methode" required>
                                <option value="carte">Carte bancaire</option>
                                <option value="paypal">PayPal</option>
                                <option value="virement">Virement</option>
                                <option value="espece">Espèces</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add-statut" class="form-label">Statut</label>
                            <select class="form-select" id="add-statut" name="statut" required>
                                <option value="en attente" selected>En attente</option>
                                <option value="effectué">Effectué</option>
                                <option value="échoué">Échoué</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 

    <!-- Modal de modification de paiement -->
    <div class="modal fade" id="editPaiementModal" tabindex="-1" aria-labelledby="editPaiementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-paiement-form">
                    <div class="modal-body">
                        <div id="edit-alert-container"></div>
                        <input type="hidden" id="edit-id" name="id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-id_creancier" class="form-label">Créancier</label>
                                <select class="form-select" id="edit-id_creancier" name="id_creancier" required>
                                    <!-- Options chargées par JS -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-id_debiteur" class="form-label">Débiteur</label>
                                <select class="form-select" id="edit-id_debiteur" name="id_debiteur" required>
                                    <!-- Options chargées par JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-montant" class="form-label">Montant (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit-montant" name="montant" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-methode" class="form-label">Méthode de paiement</label>
                            <select class="form-select" id="edit-methode" name="methode" required>
                                <option value="carte">Carte bancaire</option>
                                <option value="paypal">PayPal</option>
                                <option value="virement">Virement</option>
                                <option value="espece">Espèces</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-statut" class="form-label">Statut</label>
                            <select class="form-select" id="edit-statut" name="statut" required>
                                <option value="en attente">En attente</option>
                                <option value="effectué">Effectué</option>
                                <option value="échoué">Échoué</option>
                            </select>
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
        document.addEventListener('DOMContentLoaded', function () {
            // --- Constantes et API ---
            const API_URL_PAIEMENTS = '/site_web/api/admin/paiements/';
            const API_URL_USERS = '/site_web/api/admin/utilisateurs/';

            // --- Éléments du DOM ---
            const tableBody = document.getElementById('paiements-table-body');
            const paginationContainer = document.getElementById('pagination-container');
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');
            const statutFilter = document.getElementById('statut-filter');
            const resetSearchBtn = document.getElementById('reset-search');
            const totalEffectueEl = document.getElementById('total-effectue');

            // --- Modales ---
            const addModalEl = document.getElementById('addPaiementModal');
            const addModal = new bootstrap.Modal(addModalEl);
            const addForm = document.getElementById('add-paiement-form');
            const addCreancierSelect = document.getElementById('add-id_creancier');
            const addDebiteurSelect = document.getElementById('add-id_debiteur');

            const editModalEl = document.getElementById('editPaiementModal');
            const editModal = new bootstrap.Modal(editModalEl);
            const editForm = document.getElementById('edit-paiement-form');
            const editCreancierSelect = document.getElementById('edit-id_creancier');
            const editDebiteurSelect = document.getElementById('edit-id_debiteur');

            let currentPage = 1;
            let currentSearch = '';
            let currentStatut = '';

            // --- Fonctions Utilitaires ---
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

            const formatCurrency = (value) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
            const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('fr-FR') : 'N/A';
            
            const getMethodeInfo = (methode) => {
                const icons = {
                    'carte': 'fa-credit-card',
                    'paypal': 'fab fa-paypal',
                    'virement': 'fa-exchange-alt',
                    'espece': 'fa-money-bill-wave'
                };
                const icon = icons[methode.toLowerCase()] || 'fa-question';
                return `<i class="fas ${icon}"></i> ${methode}`;
            };

            const getStatutInfo = (statut) => {
                const colors = { 'effectué': '🟢', 'en attente': '🟠', 'échoué': '🔴' };
                return `${colors[statut.toLowerCase()] || '⚪'} ${statut}`;
            };

            // --- Rendu ---
            const renderTable = (paiements) => {
                tableBody.innerHTML = '';
                if (!paiements || paiements.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun paiement trouvé.</td></tr>';
                    return;
                }
                paiements.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${p.id}</td>
                        <td>${p.creancier_nom || 'N/A'}</td>
                        <td>${p.debiteur_nom || 'N/A'}</td>
                        <td>${formatCurrency(p.montant)}</td>
                        <td>${getMethodeInfo(p.methode)}</td>
                        <td>${getStatutInfo(p.statut)}</td>
                        <td>${formatDate(p.date_creation)}</td>
                        <td class="text-end">
                            <button class="btn btn-primary btn-sm btn-edit" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm btn-delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                    tr.querySelector('.btn-edit').dataset.paiement = JSON.stringify(p);
                    tr.querySelector('.btn-delete').dataset.id = p.id;
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
            const fetchPaiements = async (page = 1, search = '', statut = '') => {
                currentPage = page;
                currentSearch = search;
                currentStatut = statut;
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Chargement...</td></tr>';
                try {
                    const response = await fetch(`${API_URL_PAIEMENTS}get.php?page=${page}&search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`);
                    if (!response.ok) throw new Error(`Erreur: ${response.statusText}`);
                    const data = await response.json();
                    renderTable(data.paiements);
                    renderPagination(data.totalPages, data.currentPage);
                    totalEffectueEl.textContent = formatCurrency(data.totalEffectue);
                } catch (error) {
                    showAlert(error.message, 'danger');
                }
            };
            
            const populateUserSelects = async () => {
                try {
                    const response = await fetch(`${API_URL_USERS}list.php`);
                    if (!response.ok) throw new Error('Erreur de chargement des utilisateurs');
                    const users = await response.json();
                    
                    const options = ['<option value="">Sélectionnez un utilisateur</option>', 
                        ...users.map(u => `<option value="${u.id}">${u.nom} (${u.email})</option>`)
                    ].join('');

                    addCreancierSelect.innerHTML = options;
                    addDebiteurSelect.innerHTML = options;
                    editCreancierSelect.innerHTML = options;
                    editDebiteurSelect.innerHTML = options;
                } catch (error) {
                    showAlert(error.message, 'danger');
                }
            };

            // --- Écouteurs d'événements ---
            searchForm.addEventListener('submit', e => {
                e.preventDefault();
                fetchPaiements(1, searchInput.value.trim(), statutFilter.value);
            });

            resetSearchBtn.addEventListener('click', () => {
                searchForm.reset();
                fetchPaiements(1, '', '');
            });

            paginationContainer.addEventListener('click', (e) => {
                e.preventDefault();
                if (e.target.tagName === 'A' && e.target.dataset.page) {
                    const page = parseInt(e.target.dataset.page, 10);
                    if (!isNaN(page)) fetchPaiements(page, currentSearch, currentStatut);
                }
            });

            addForm.addEventListener('submit', async e => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(addForm).entries());
                try {
                    const response = await fetch(`${API_URL_PAIEMENTS}post.php`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error);
                    showAlert(result.success, 'success');
                    addModal.hide();
                    fetchPaiements(currentPage, currentSearch, currentStatut);
                } catch (error) {
                    showAlert(error.message, 'danger', 'add-alert-container');
                }
            });

            tableBody.addEventListener('click', e => {
                const editBtn = e.target.closest('.btn-edit');
                const deleteBtn = e.target.closest('.btn-delete');

                if (editBtn) {
                    const paiement = JSON.parse(editBtn.dataset.paiement);
                    editForm.querySelector('#edit-id').value = paiement.id;
                    editForm.querySelector('#edit-id_creancier').value = paiement.id_creancier;
                    editForm.querySelector('#edit-id_debiteur').value = paiement.id_debiteur;
                    editForm.querySelector('#edit-montant').value = paiement.montant;
                    editForm.querySelector('#edit-methode').value = paiement.methode;
                    editForm.querySelector('#edit-statut').value = paiement.statut;
                    editModal.show();
                }

                if (deleteBtn) {
                    const id = deleteBtn.dataset.id;
                    if (confirm(`Supprimer le paiement N°${id} ?`)) {
                        fetch(`${API_URL_PAIEMENTS}delete.php?id=${id}`, { method: 'DELETE' })
                        .then(res => res.json().then(data => ({ok: res.ok, data})))
                        .then(({ok, data}) => {
                            if (!ok) throw new Error(data.error);
                            showAlert(data.success, 'success');
                            fetchPaiements(currentPage, currentSearch, currentStatut);
                        })
                        .catch(err => showAlert(err.message, 'danger'));
                    }
                }
            });
            
            editForm.addEventListener('submit', async e => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(editForm).entries());
                try {
                    const response = await fetch(`${API_URL_PAIEMENTS}put.php`, {
                        method: 'PUT',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error);
                    showAlert(result.success, 'success');
                    editModal.hide();
                    fetchPaiements(currentPage, currentSearch, currentStatut);
                } catch (error) {
                    showAlert(error.message, 'danger', 'edit-alert-container');
                }
            });

            addModalEl.addEventListener('hidden.bs.modal', () => addForm.reset());

            // --- Initialisation ---
            populateUserSelects();
            fetchPaiements();
        });
    </script>
    
</body>
</html>
