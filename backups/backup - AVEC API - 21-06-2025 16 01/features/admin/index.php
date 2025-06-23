<?php
session_start();
include '../../fonctions/fonctions.php';
// Suppression de toute connexion ou requête SQL directe
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Tableau de bord Admin - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="../../assets/js/color-modes.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/admin.css">
    <style>
        /* Fix pour les modales */
        .modal { z-index: 10000 !important; }
        .modal-backdrop { z-index: 9999 !important; }
        .modal-dialog { z-index: 10001 !important; }
        .modal-content { pointer-events: auto !important; }
        .modal input, .modal select, .modal button, .modal .form-check-label {
            pointer-events: auto !important;
            position: relative;
            z-index: 10002 !important;
        }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tableau de bord</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-download fa-sm mr-2"></i> Exporter
            </button>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="row mt-2">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label text-primary">Utilisateurs</div>
                                <div class="stat-value" id="stat-users">...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users stat-icon text-primary"></i>
                            </div>
                        </div>
                        <a href="utilisateurs.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label text-success">Annonces</div>
                                <div class="stat-value" id="stat-annonces">...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bullhorn stat-icon text-success"></i>
                            </div>
                        </div>
                        <a href="annonces.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label text-info">Livraisons</div>
                                <div class="stat-value" id="stat-livraisons">...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck stat-icon text-info"></i>
                            </div>
                        </div>
                        <a href="livraisons.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label text-warning">Revenus</div>
                                <div class="stat-value" id="stat-revenus">...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-euro-sign stat-icon text-warning"></i>
                            </div>
                        </div>
                        <a href="paiements.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Livraisons récentes -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Livraisons récentes</h6>
                        <div class="voir-tout-btn" onclick="window.location.href='/site_web/features/admin/livraisons.php'" style="cursor: pointer; background-color: #4e73df; color: white; padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 0.2rem; position: relative; z-index: 1000; pointer-events: auto; display: inline-block;">
                            Voir tout
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-recent">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Livreur</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" class="text-center">Chargement...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derniers utilisateurs -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Dernières inscriptions</h6>
                        <div class="voir-tout-btn" onclick="window.location.href='/site_web/features/admin/utilisateurs.php'" style="cursor: pointer; background-color: #4e73df; color: white; padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 0.2rem; position: relative; z-index: 1000; pointer-events: auto; display: inline-block;">
                            Voir tout
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-recent">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" class="text-center">Chargement...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'exportation -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true" style="z-index: 10000 !important;">
        <div class="modal-dialog" style="z-index: 10001 !important;">
            <div class="modal-content" style="pointer-events: auto !important;">
                <div class="modal-header">
                    <h5 class="modal-title">Exporter les données</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="export.php" method="post" id="exportForm">
                        <div class="mb-3">
                            <label class="form-label">Sélectionnez les données à exporter</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="export[]" value="utilisateurs" id="exportUsers" checked style="pointer-events: auto !important; position: relative; z-index: 10002;">
                                <label class="form-check-label" for="exportUsers" style="pointer-events: auto !important; position: relative; z-index: 10002;">Utilisateurs</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="export[]" value="annonces" id="exportAnnonces" checked style="pointer-events: auto !important; position: relative; z-index: 10002;">
                                <label class="form-check-label" for="exportAnnonces" style="pointer-events: auto !important; position: relative; z-index: 10002;">Annonces</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="export[]" value="livraisons" id="exportLivraisons" checked style="pointer-events: auto !important; position: relative; z-index: 10002;">
                                <label class="form-check-label" for="exportLivraisons" style="pointer-events: auto !important; position: relative; z-index: 10002;">Livraisons</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="export[]" value="paiements" id="exportPaiements" checked style="pointer-events: auto !important; position: relative; z-index: 10002;">
                                <label class="form-check-label" for="exportPaiements" style="pointer-events: auto !important; position: relative; z-index: 10002;">Paiements</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format" style="pointer-events: auto !important; position: relative; z-index: 10002;">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="pointer-events: auto !important; position: relative; z-index: 10002;">Annuler</button>
                    <button type="submit" form="exportForm" class="btn btn-primary" style="pointer-events: auto !important; position: relative; z-index: 10002;">Exporter</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../../assets/dist/modal-fix.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../api/admin/stats/get.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-users').textContent = data.utilisateurs;
                document.getElementById('stat-annonces').textContent = data.annonces;
                document.getElementById('stat-livraisons').textContent = data.livraisons;
                document.getElementById('stat-revenus').textContent = Number(data.montant_total).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';

                // Livraisons récentes
                const livTable = document.querySelector('table.table-recent tbody');
                if (livTable) {
                    livTable.innerHTML = '';
                    if (data.livraisons_recentes.length === 0) {
                        livTable.innerHTML = '<tr><td colspan="4" class="text-center">Aucune livraison récente</td></tr>';
                    } else {
                        data.livraisons_recentes.forEach(liv => {
                            livTable.innerHTML += `<tr>
                                <td>${liv.id}</td>
                                <td>${liv.client_nom ?? 'N/A'}</td>
                                <td>${liv.livreur_nom ?? 'N/A'}</td>
                                <td><span class="badge rounded-pill bg-${liv.statut === 'en attente' ? 'warning' : liv.statut === 'en cours' ? 'info' : liv.statut === 'livrée' ? 'success' : liv.statut === 'annulée' ? 'danger' : 'secondary'}">${liv.statut.charAt(0).toUpperCase() + liv.statut.slice(1)}</span></td>
                            </tr>`;
                        });
                    }
                }

                // Dernières inscriptions
                const userTable = document.querySelectorAll('table.table-recent tbody')[1];
                if (userTable) {
                    userTable.innerHTML = '';
                    if (data.nouveaux_utilisateurs.length === 0) {
                        userTable.innerHTML = '<tr><td colspan="4" class="text-center">Aucun nouvel utilisateur</td></tr>';
                    } else {
                        data.nouveaux_utilisateurs.forEach(user => {
                            userTable.innerHTML += `<tr>
                                <td>${user.photo_profil ? `<img src="/site_web/uploads/${user.photo_profil}" class="rounded-circle me-2" width="32" height="32" alt="Photo">` : ''}${user.nom}</td>
                                <td>${user.email}</td>
                                <td><span class="badge rounded-pill bg-${user.role === 'admin' ? 'danger' : user.role === 'client' ? 'primary' : user.role === 'livreur' ? 'success' : user.role === 'prestataire' ? 'warning' : user.role === 'commercant' ? 'info' : 'secondary'}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                                <td>${new Date(user.date_inscription).toLocaleDateString('fr-FR')}</td>
                            </tr>`;
                        });
                    }
                }
            });
    });
    </script>
    
    <?php include '../../fonctions/footer.php'; ?>
    
</body>
</html> 