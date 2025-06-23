<?php
session_start();
include '../../fonctions/db.php'; 
include '../../fonctions/fonctions.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    header('Location: /site_web/features/public/login.php');
    exit;
}

$conn = getConnexion();

// Recherche et filtrage
$search = $_GET['search'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(titre LIKE ? OR ville_depart LIKE ? OR ville_arrivee LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($statut_filter)) {
    $where_conditions[] = "statut = ?";
    $params[] = $statut_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupération des annonces
$stmt = $conn->prepare("SELECT a.*, u.nom as auteur_nom FROM annonces a 
                      LEFT JOIN utilisateurs u ON a.id_client = u.id 
                      $where_clause
                      ORDER BY a.date_annonce DESC");
$stmt->execute($params);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $ville_depart = trim($_POST['ville_depart']);
        $ville_arrivee = trim($_POST['ville_arrivee']);
        $taille = $_POST['taille'] ?? 0;
        $prix = $_POST['prix'] ?? 0;
        $date_livraison = $_POST['date_livraison'] ?? null;
        $date_expiration = $_POST['date_expiration'] ?? null;
        $segmentation_possible = isset($_POST['segmentation_possible']) ? 1 : 0;
        $statut = trim($_POST['statut']);
        $id_client = $_POST['id_client'] ?? $_SESSION['utilisateur']['id'];
        
        // Validation
        $errors = [];
        if (empty($titre)) $errors[] = "Le titre est obligatoire";
        if (empty($description)) $errors[] = "La description est obligatoire";
        if (empty($ville_depart)) $errors[] = "L'adresse de départ est obligatoire";
        if (empty($ville_arrivee)) $errors[] = "L'adresse d'arrivée est obligatoire";
        
        if (empty($errors)) {
            if ($_POST['action'] === 'add') {
                $stmt = $conn->prepare("INSERT INTO annonces 
                    (id_client, titre, description, ville_depart, ville_arrivee, taille, prix, 
                     date_livraison_souhaitee, date_expiration, date_annonce, statut, segmentation_possible) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                $stmt->execute([$id_client, $titre, $description, $ville_depart, $ville_arrivee, 
                               $taille, $prix, $date_livraison, $date_expiration, $statut, $segmentation_possible]);
                $_SESSION['message'] = "Annonce ajoutée avec succès";
            } else {
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE annonces SET 
                    titre = ?, description = ?, ville_depart = ?, ville_arrivee = ?, 
                    taille = ?, prix = ?, date_livraison_souhaitee = ?, date_expiration = ?, 
                    statut = ?, segmentation_possible = ? 
                    WHERE id = ?");
                $stmt->execute([$titre, $description, $ville_depart, $ville_arrivee, $taille, $prix, 
                               $date_livraison, $date_expiration, $statut, $segmentation_possible, $id]);
                $_SESSION['message'] = "Annonce mise à jour avec succès";
            }
            header('Location: annonces.php');
            exit;
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM annonces WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $_SESSION['message'] = "Annonce supprimée avec succès";
        header('Location: annonces.php');
        exit;
    }
}

// Récupération des utilisateurs pour le formulaire
$stmt = $conn->query("SELECT id, nom, email FROM utilisateurs ORDER BY nom");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des annonces - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="../../assets/js/color-modes.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/admin.css">
    <style>
        /* Fix pour les modales */
        .modal {
            z-index: 10000 !important;
        }
        
        /* Style général */
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .container-fluid {
            padding: 0;
            max-width: 100%;
        }
        
        /* Style pour les boutons d'action */
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 4px;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 4px;
        }
        
        /* Layout de la page */
        .page-title {
            text-align: center;
            margin: 40px 0 20px;
        }
        
        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
        }
        
        .page-content {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }
        
        .add-button {
            position: absolute;
            top: -60px;
            right: 0;
        }
        
        /* Style pour les cartes */
        .card {
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            background-color: white;
            border: 1px solid rgba(0,0,0,.125);
        }
        
        /* Style pour la recherche */
        .search-card .card-body {
            padding: 15px;
        }
        
        .search-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            flex: 3;
        }
        
        .search-select {
            flex: 2;
        }
        
        .search-button {
            flex: 1;
        }
        
        /* Style pour le tableau */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 500;
            border-top: none;
            border-bottom: 1px solid #dee2e6;
            color: #333;
            padding: 12px 8px;
        }
        
        .table td {
            border: none;
            padding: 12px 8px;
            vertical-align: middle;
        }
        
        .table tbody tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Annonces</h1>
        </div>
        
        <div class="page-content">
            <div class="add-button">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAnnonceModal" style="position: relative; z-index: 100; pointer-events: auto; border-radius: 4px; padding: 8px 16px; font-weight: 500;">
                    Ajouter une annonce
                </button>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filtres de recherche -->
            <div class="card search-card mb-4">
                <div class="card-body">
                    <form method="get">
                        <div class="search-row">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Rechercher par titre ou adresse" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            </div>
                            <div class="search-button">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    Rechercher
                                </button>
                            </div>
                            <div class="search-button">
                                <a href="annonces.php" class="btn btn-outline-secondary w-100" style="position: relative; z-index: 100; pointer-events: auto;">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Départ</th>
                                    <th>Arrivée</th>
                                    <th>Prix</th>
                                    <th>Auteur</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($annonces)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune annonce trouvée</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($annonces as $annonce): ?>
                                        <tr>
                                            <td><?php echo $annonce['id']; ?></td>
                                            <td><?php echo htmlspecialchars($annonce['titre']); ?></td>
                                            <td><?php echo htmlspecialchars($annonce['ville_depart']); ?></td>
                                            <td><?php echo htmlspecialchars($annonce['ville_arrivee']); ?></td>
                                            <td><?php echo number_format($annonce['prix'], 2, ',', ' '); ?> €</td>
                                            <td><?php echo htmlspecialchars($annonce['auteur_nom'] ?? 'Inconnu'); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($annonce['date_annonce'])); ?></td>
                                            <td>
                                                <?php
                                                    $statut = strtolower($annonce['statut']);
                                                    $couleur = match($statut) {
                                                        'livrée' => '🟢',
                                                        'en attente', 'prise en charge' => '🟠',
                                                        'annulée' => '🔴',
                                                        default => '⚪',
                                                    };
                                                    echo $couleur . ' ' . htmlspecialchars($annonce['statut']);
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editAnnonceModal"
                                                        data-id="<?php echo $annonce['id']; ?>"
                                                        data-titre="<?php echo htmlspecialchars($annonce['titre']); ?>"
                                                        data-description="<?php echo htmlspecialchars($annonce['description']); ?>"
                                                        data-ville_depart="<?php echo htmlspecialchars($annonce['ville_depart']); ?>"
                                                        data-ville_arrivee="<?php echo htmlspecialchars($annonce['ville_arrivee']); ?>"
                                                        data-taille="<?php echo $annonce['taille']; ?>"
                                                        data-prix="<?php echo $annonce['prix']; ?>"
                                                        data-date_livraison="<?php echo $annonce['date_livraison_souhaitee']; ?>"
                                                        data-date_expiration="<?php echo $annonce['date_expiration']; ?>"
                                                        data-segmentation_possible="<?php echo $annonce['segmentation_possible']; ?>"
                                                        data-statut="<?php echo htmlspecialchars($annonce['statut']); ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; margin-bottom: 4px; width: 100%; padding: 6px 12px;">
                                                    Modifier
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-confirm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteAnnonceModal"
                                                        data-id="<?php echo $annonce['id']; ?>"
                                                        data-titre="<?php echo htmlspecialchars($annonce['titre']); ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; width: 100%; padding: 6px 12px;">
                                                    Supprimer
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout d'annonce -->
    <div class="modal fade" id="addAnnonceModal" tabindex="-1" aria-labelledby="addAnnonceModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog modal-lg" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une annonce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="annonces.php" method="post">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ville_depart" class="form-label">Ville de départ</label>
                                    <input type="text" class="form-control" id="ville_depart" name="ville_depart" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ville_arrivee" class="form-label">Ville d'arrivée</label>
                                    <input type="text" class="form-control" id="ville_arrivee" name="ville_arrivee" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="taille" class="form-label">Taille</label>
                                    <input type="number" class="form-control" id="taille" name="taille" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prix" class="form-label">Prix</label>
                                    <input type="number" class="form-control" id="prix" name="prix" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_livraison" class="form-label">Date de livraison souhaitée</label>
                                    <input type="date" class="form-control" id="date_livraison" name="date_livraison">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_expiration" class="form-label">Date d'expiration</label>
                                    <input type="date" class="form-control" id="date_expiration" name="date_expiration">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="segmentation_possible" class="form-label">Segmentation possible</label>
                            <input type="checkbox" class="form-check-input" id="segmentation_possible" name="segmentation_possible">
                        </div>
                        
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_client" class="form-label">Auteur</label>
                            <select class="form-select" id="id_client" name="id_client">
                                <?php foreach ($utilisateurs as $utilisateur): ?>
                                    <option value="<?php echo $utilisateur['id']; ?>"><?php echo htmlspecialchars($utilisateur['nom'] . ' (' . $utilisateur['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification d'annonce -->
    <div class="modal fade" id="editAnnonceModal" tabindex="-1" aria-labelledby="editAnnonceModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog modal-lg" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'annonce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="annonces.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        
                        <div class="mb-3">
                            <label for="edit-titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="edit-titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-ville_depart" class="form-label">Ville de départ</label>
                                    <input type="text" class="form-control" id="edit-ville_depart" name="ville_depart" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-ville_arrivee" class="form-label">Ville d'arrivée</label>
                                    <input type="text" class="form-control" id="edit-ville_arrivee" name="ville_arrivee" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-taille" class="form-label">Taille</label>
                                    <input type="number" class="form-control" id="edit-taille" name="taille" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-prix" class="form-label">Prix</label>
                                    <input type="number" class="form-control" id="edit-prix" name="prix" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-date_livraison" class="form-label">Date de livraison souhaitée</label>
                                    <input type="date" class="form-control" id="edit-date_livraison" name="date_livraison">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-date_expiration" class="form-label">Date d'expiration</label>
                                    <input type="date" class="form-control" id="edit-date_expiration" name="date_expiration">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-segmentation_possible" class="form-label">Segmentation possible</label>
                            <input type="checkbox" class="form-check-input" id="edit-segmentation_possible" name="segmentation_possible">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-statut" class="form-label">Statut</label>
                            <select class="form-select" id="edit-statut" name="statut">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression d'annonce -->
    <div class="modal fade" id="deleteAnnonceModal" tabindex="-1" aria-labelledby="deleteAnnonceModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'annonce "<span id="delete-titre"></span>" ?</p>
                    <form action="annonces.php" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-id">
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../../assets/dist/admin.js"></script>
    <script>
        // Gestion des modals pour éditer et supprimer
        document.addEventListener('DOMContentLoaded', function() {
            // Réinitialiser Bootstrap et ses modales
            if (typeof bootstrap !== 'undefined') {
                // Réinitialiser toutes les modales existantes
                document.querySelectorAll('.modal').forEach(modalEl => {
                    // Supprimer d'abord toute instance existante
                    const oldModal = bootstrap.Modal.getInstance(modalEl);
                    if (oldModal) oldModal.dispose();
                    
                    // Créer une nouvelle instance de modal
                    const newModal = new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false,
                        focus: true
                    });
                    
                    // Assurer que la modale est au premier plan quand elle s'ouvre
                    modalEl.addEventListener('shown.bs.modal', function() {
                        modalEl.style.zIndex = '10000';
                        document.querySelector('.modal-backdrop').style.zIndex = '9999';
                    });
                });
            }
            
            // Fix pour les modales
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.zIndex = '10000';
                modal.querySelectorAll('input, select, textarea, button').forEach(el => {
                    el.style.pointerEvents = 'auto';
                });
            });
            
            // Assurer que les boutons qui ouvrent les modales fonctionnent correctement
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
                btn.style.position = 'relative';
                btn.style.zIndex = '100';
                btn.style.pointerEvents = 'auto';
            });
            
            const editModal = document.getElementById('editAnnonceModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    const id = button.getAttribute('data-id');
                    const titre = button.getAttribute('data-titre');
                    const description = button.getAttribute('data-description');
                    const ville_depart = button.getAttribute('data-ville_depart');
                    const ville_arrivee = button.getAttribute('data-ville_arrivee');
                    const taille = button.getAttribute('data-taille');
                    const prix = button.getAttribute('data-prix');
                    const date_livraison = button.getAttribute('data-date_livraison');
                    const date_expiration = button.getAttribute('data-date_expiration');
                    const segmentation_possible = button.getAttribute('data-segmentation_possible');
                    const statut = button.getAttribute('data-statut');
                    
                    editModal.querySelector('#edit-id').value = id;
                    editModal.querySelector('#edit-titre').value = titre;
                    editModal.querySelector('#edit-description').value = description;
                    editModal.querySelector('#edit-ville_depart').value = ville_depart;
                    editModal.querySelector('#edit-ville_arrivee').value = ville_arrivee;
                    editModal.querySelector('#edit-taille').value = taille;
                    editModal.querySelector('#edit-prix').value = prix;
                    editModal.querySelector('#edit-date_livraison').value = date_livraison;
                    editModal.querySelector('#edit-date_expiration').value = date_expiration;
                    editModal.querySelector('#edit-segmentation_possible').checked = segmentation_possible === '1';
                    editModal.querySelector('#edit-statut').value = statut;
                });
            }
            
            const deleteModal = document.getElementById('deleteAnnonceModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    const id = button.getAttribute('data-id');
                    const titre = button.getAttribute('data-titre');
                    
                    deleteModal.querySelector('#delete-id').value = id;
                    deleteModal.querySelector('#delete-titre').textContent = titre;
                });
            }
        });
    </script>
</body>
</html> 