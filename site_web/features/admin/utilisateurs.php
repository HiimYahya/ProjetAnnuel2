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
    <title>Gestion des Utilisateurs - EcoDeli Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/site_web/assets/dist/admin.css">
    <style>
        /* CSS Inchangé */
        .modal { z-index: 1055 !important; }
        .modal-backdrop { z-index: 1050 !important; }
        body { background-color: #f8f9fa; }
        .page-title { text-align: center; margin: 40px 0 20px; }
        .page-title h1 { font-size: 1.75rem; font-weight: 600; }
        .page-content { max-width: 1000px; margin: 0 auto; }
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
            <h1>Gestion des Utilisateurs</h1>
        </div>
        
        <div class="page-content">
            <div id="alert-container"></div>
            
            <div class="add-button-container">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Ajouter un utilisateur
                </button>
            </div>
            
            <!-- Filtres de recherche -->
            <div class="card search-card mb-4">
                <div class="card-body">
                    <form id="search-form" class="row g-3 align-items-center">
                        <div class="col">
                            <input type="text" class="form-control" placeholder="Rechercher par nom, email ou adresse" name="search" id="search-input">
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
            
            <!-- Tableau des utilisateurs -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Inscrit le</th>
                                    <th>Adresse</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- Le contenu sera chargé par JavaScript -->
                                <tr><td colspan="7" class="text-center">Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                        <nav aria-label="Pagination des utilisateurs">
                        <ul class="pagination justify-content-center mt-4" id="pagination-container">
                            <!-- La pagination sera chargée par JavaScript -->
                            </ul>
                        </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal d'ajout d'utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-user-form">
                    <div class="modal-body">
                        <div id="add-alert-container"></div>
                        <div class="mb-3">
                            <label for="add-nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="add-nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="add-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-role" class="form-label">Rôle</label>
                            <select class="form-select" id="add-role" name="role" required>
                                <option value="client" selected>Client</option>
                                <option value="livreur">Livreur</option>
                                <option value="prestataire">Prestataire</option>
                                <option value="commercant">Commerçant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add-adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="add-adresse" name="adresse" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <small>Un mot de passe temporaire sera généré automatiquement.</small>
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

    <!-- Modal de modification d'utilisateur -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-user-form">
                    <div class="modal-body">
                        <div id="edit-alert-container"></div>
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit-nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-role" class="form-label">Rôle</label>
                            <select class="form-select" id="edit-role" name="role" required>
                                <option value="client">Client</option>
                                <option value="livreur">Livreur</option>
                                <option value="prestataire">Prestataire</option>
                                <option value="commercant">Commerçant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="edit-adresse" name="adresse" rows="3"></textarea>
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
        const API_URL = '/site_web/api/admin/utilisateurs/';
        
        const tableBody = document.getElementById('users-table-body');
        const paginationContainer = document.getElementById('pagination-container');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const resetSearchBtn = document.getElementById('reset-search');
        
        const addUserModalEl = document.getElementById('addUserModal');
        const addUserForm = document.getElementById('add-user-form');
        const addUserModal = new bootstrap.Modal(addUserModalEl);

        const editUserModalEl = document.getElementById('editUserModal');
        const editUserForm = document.getElementById('edit-user-form');
        const editUserModal = new bootstrap.Modal(editUserModalEl);

        let currentPage = 1;
        let currentSearch = '';

        // --- Fonctions Utilitaires ---

        const showAlert = (message, type = 'success', container = 'alert-container', duration = 5000) => {
            const alertContainer = document.getElementById(container);
            if (!alertContainer) return;

            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            alertContainer.prepend(alert);

            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, duration);
        };

        const getRoleBadge = (role) => {
            const roles = {
                admin: 'danger', client: 'primary', livreur: 'success', 
                prestataire: 'warning', commercant: 'info', default: 'secondary'
            };
            const color = roles[role] || roles.default;
            return `<span class="badge rounded-pill bg-${color}">${role.charAt(0).toUpperCase() + role.slice(1)}</span>`;
        };
                    
        // --- Fonctions de Rendu ---

        const renderTable = (users) => {
            tableBody.innerHTML = '';
            if (!users || users.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Aucun utilisateur trouvé.</td></tr>';
                return;
            }

            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>
                        ${user.photo_profil ? `<img src="/site_web/uploads/${user.photo_profil}" class="rounded-circle me-2" width="32" height="32" alt="Photo">` : ''}
                        ${user.nom}
                    </td>
                    <td>${user.email}</td>
                    <td>${getRoleBadge(user.role)}</td>
                    <td>${new Date(user.date_inscription).toLocaleDateString('fr-FR')}</td>
                    <td class="text-truncate" style="max-width: 150px;">${user.adresse || 'Non renseignée'}</td>
                    <td class="text-end">
                        <button class="btn btn-primary btn-sm btn-edit" data-user='${JSON.stringify(user)}'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${user.id}">
                            <i class="fas fa-trash"></i>
                        </button>
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

        // --- Fonctions API ---

        const fetchUsers = async (page = 1, search = '') => {
            currentPage = page;
            currentSearch = search;
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Chargement...</td></tr>';
            
            try {
                const response = await fetch(`${API_URL}get.php?page=${page}&search=${encodeURIComponent(search)}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                
                renderTable(data.users);
                renderPagination(data.totalPages, data.currentPage);
            } catch (error) {
                console.error('Fetch error:', error);
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des données.</td></tr>';
                showAlert('Impossible de charger les utilisateurs.', 'danger');
            }
        };
        
        // --- Écouteurs d'événements ---
        
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchUsers(1, searchInput.value.trim());
        });

        resetSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            fetchUsers(1, '');
        });

        paginationContainer.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && e.target.dataset.page) {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page, 10);
                if (!isNaN(page)) {
                    fetchUsers(page, currentSearch);
                }
            }
        });
        
        // Ajouter un utilisateur
        addUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(API_URL + 'post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Une erreur est survenue.');
                }

                addUserModal.hide();
                showAlert(`Utilisateur ajouté avec succès. Mot de passe temporaire : <strong>${result.password}</strong>`);
                fetchUsers(currentPage, currentSearch);
                this.reset();
            } catch (error) {
                showAlert(error.message, 'danger', 'add-alert-container');
            }
            });
            
        // Clics sur les boutons Modifier/Supprimer (délégation d'événement)
        tableBody.addEventListener('click', async (e) => {
            const editButton = e.target.closest('.btn-edit');
            const deleteButton = e.target.closest('.btn-delete');

            if (editButton) {
                const user = JSON.parse(editButton.dataset.user);
                document.getElementById('edit-id').value = user.id;
                document.getElementById('edit-nom').value = user.nom;
                document.getElementById('edit-email').value = user.email;
                document.getElementById('edit-role').value = user.role;
                document.getElementById('edit-adresse').value = user.adresse || '';
                editUserModal.show();
            }

            if (deleteButton) {
                const id = deleteButton.dataset.id;
                if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                    try {
                        const response = await fetch(`${API_URL}delete.php?id=${id}`, { method: 'DELETE' });
                        const result = await response.json();

                        if (!response.ok) throw new Error(result.error);
                        
                        showAlert(result.success);
                        fetchUsers(currentPage, currentSearch);
                    } catch (error) {
                        showAlert(error.message, 'danger');
                    }
                }
            }
        });
        
        // Modifier un utilisateur
        editUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(API_URL + 'put.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.error);

                editUserModal.hide();
                showAlert(result.success);
                fetchUsers(currentPage, currentSearch);
            } catch (error) {
                showAlert(error.message, 'danger', 'edit-alert-container');
            }
        });
        
        // Vider les alertes des modales quand elles se ferment
        addUserModalEl.addEventListener('hidden.bs.modal', () => {
            document.getElementById('add-alert-container').innerHTML = '';
            addUserForm.reset();
        });
        editUserModalEl.addEventListener('hidden.bs.modal', () => {
            document.getElementById('edit-alert-container').innerHTML = '';
            });

        // Chargement initial
        fetchUsers();
        });
    </script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html> 