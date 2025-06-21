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
    $where_conditions[] = "(client.nom LIKE ? OR livreur.nom LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($statut_filter)) {
    $where_conditions[] = "l.statut = ?";
    $params[] = $statut_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupération des livraisons
$stmt = $conn->prepare("SELECT l.*, 
                      client.nom as client_nom, 
                      livreur.nom as livreur_nom,
                      a.titre as annonce_titre
                      FROM livraisons l 
                      LEFT JOIN utilisateurs client ON l.id_client = client.id 
                      LEFT JOIN utilisateurs livreur ON l.id_livreur = livreur.id 
                      LEFT JOIN annonces a ON l.id_annonce = a.id
                      $where_clause
                      ORDER BY l.date_prise_en_charge DESC");
$stmt->execute($params);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $statut = $_POST['statut'] ?? '';
        $id_livreur = $_POST['id_livreur'] ?? null;
        $validation_client = isset($_POST['validation_client']) ? 1 : 0;
        $reception_confirmee = isset($_POST['reception_confirmee']) ? 1 : 0;
        
        // Validation
        $errors = [];
        if (empty($statut)) $errors[] = "Le statut est obligatoire";
        
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE livraisons SET 
                statut = ?, 
                id_livreur = ?, 
                validation_client = ?, 
                reception_confirmee = ? 
                WHERE id = ?");
            $stmt->execute([$statut, $id_livreur, $validation_client, $reception_confirmee, $id]);
            $_SESSION['message'] = "Livraison mise à jour avec succès";
            header('Location: livraisons.php');
            exit;
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM livraisons WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        
        // Supprimer également les segments liés
        $stmt = $conn->prepare("DELETE FROM segments WHERE id_livraison = ?");
        $stmt->execute([$_POST['id']]);
        
        $_SESSION['message'] = "Livraison supprimée avec succès";
        header('Location: livraisons.php');
        exit;
    }
}

// Récupération des livreurs pour le formulaire
$stmt = $conn->query("SELECT id, nom, email FROM utilisateurs WHERE role = 'livreur' ORDER BY nom");
$livreurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des segments pour chaque livraison
$segments = [];
foreach ($livraisons as $livraison) {
    $stmt = $conn->prepare("SELECT s.*, 
                           u.nom as livreur_nom, 
                           pd.nom as point_relais_depart_nom,
                           pa.nom as point_relais_arrivee_nom
                           FROM segments s 
                           LEFT JOIN utilisateurs u ON s.id_livreur = u.id
                           LEFT JOIN points_relais pd ON s.point_relais_depart = pd.id
                           LEFT JOIN points_relais pa ON s.point_relais_arrivee = pa.id
                           WHERE s.id_livraison = ?");
    $stmt->execute([$livraison['id']]);
    $segments[$livraison['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des livraisons - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        /* Style pour les segments */
        .segment-row {
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
        }
        
        .segment-table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .segment-table th {
            font-weight: 500;
            padding: 8px;
        }
        
        .segment-table td {
            padding: 8px;
        }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Livraisons</h1>
        </div>
        
        <div class="page-content">
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
                                <input type="text" class="form-control" placeholder="Rechercher par client ou livreur" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            </div>
                            <div class="search-button">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    Rechercher
                                </button>
                            </div>
                            <div class="search-button">
                                <a href="livraisons.php" class="btn btn-outline-secondary w-100" style="position: relative; z-index: 100; pointer-events: auto;">Réinitialiser</a>
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
                                    <th>Client</th>
                                    <th>Livreur</th>
                                    <th>Annonce</th>
                                    <th>Prise en charge</th>
                                    <th>Livraison</th>
                                    <th>Statut</th>
                                    <th>Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($livraisons)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune livraison trouvée</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($livraisons as $livraison): ?>
                                        <tr>
                                            <td><?php echo $livraison['id']; ?></td>
                                            <td><?php echo htmlspecialchars($livraison['client_nom'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($livraison['livreur_nom'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($livraison['annonce_titre'] ?? 'N/A'); ?></td>
                                            <td><?php echo $livraison['date_prise_en_charge'] ? date('d/m/Y H:i', strtotime($livraison['date_prise_en_charge'])) : 'N/A'; ?></td>
                                            <td><?php echo $livraison['date_livraison'] ? date('d/m/Y H:i', strtotime($livraison['date_livraison'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php
                                                    $statut = strtolower($livraison['statut']);
                                                    $couleur = match($statut) {
                                                        'livrée' => '🟢',
                                                        'en cours' => '🟠',
                                                        'en attente' => '🟡',
                                                        'annulée' => '🔴',
                                                        default => '⚪',
                                                    };
                                                    echo $couleur . ' ' . htmlspecialchars($livraison['statut']);
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo $livraison['validation_client'] ? '<span class="badge bg-success">Validé par client</span>' : ''; ?>
                                                <?php echo $livraison['reception_confirmee'] ? '<span class="badge bg-info">Réception confirmée</span>' : ''; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editLivraisonModal"
                                                        data-id="<?php echo $livraison['id']; ?>"
                                                        data-statut="<?php echo htmlspecialchars($livraison['statut']); ?>"
                                                        data-id_livreur="<?php echo $livraison['id_livreur']; ?>"
                                                        data-validation_client="<?php echo $livraison['validation_client']; ?>"
                                                        data-reception_confirmee="<?php echo $livraison['reception_confirmee']; ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; margin-bottom: 4px; width: 100%; padding: 6px 12px;">
                                                    Modifier
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-confirm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteLivraisonModal"
                                                        data-id="<?php echo $livraison['id']; ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; width: 100%; padding: 6px 12px;">
                                                    Supprimer
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm mt-2"
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#segments-<?php echo $livraison['id']; ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; width: 100%; padding: 6px 12px;">
                                                    <?php echo count($segments[$livraison['id']]) > 0 ? 'Voir segments ('.count($segments[$livraison['id']]).')' : 'Aucun segment'; ?>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Détails des segments -->
                                        <?php if (!empty($segments[$livraison['id']])): ?>
                                            <tr class="segment-row">
                                                <td colspan="9" class="p-0">
                                                    <div id="segments-<?php echo $livraison['id']; ?>" class="collapse">
                                                        <table class="table segment-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Livreur</th>
                                                                    <th>Départ</th>
                                                                    <th>Arrivée</th>
                                                                    <th>Point relais départ</th>
                                                                    <th>Point relais arrivée</th>
                                                                    <th>Statut</th>
                                                                    <th>Date début</th>
                                                                    <th>Date fin</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($segments[$livraison['id']] as $segment): ?>
                                                                    <tr>
                                                                        <td><?php echo $segment['id']; ?></td>
                                                                        <td><?php echo htmlspecialchars($segment['livreur_nom'] ?? 'N/A'); ?></td>
                                                                        <td><?php echo htmlspecialchars($segment['adresse_depart'] ?? 'N/A'); ?></td>
                                                                        <td><?php echo htmlspecialchars($segment['adresse_arrivee'] ?? 'N/A'); ?></td>
                                                                        <td><?php echo htmlspecialchars($segment['point_relais_depart_nom'] ?? 'N/A'); ?></td>
                                                                        <td><?php echo htmlspecialchars($segment['point_relais_arrivee_nom'] ?? 'N/A'); ?></td>
                                                                        <td>
                                                                            <span class="badge rounded-pill bg-<?php 
                                                                                echo match($segment['statut']) {
                                                                                    'en attente' => 'warning',
                                                                                    'en cours' => 'primary',
                                                                                    'en point relais' => 'info',
                                                                                    'livré' => 'success',
                                                                                    'annulé' => 'danger',
                                                                                    default => 'secondary'
                                                                                };
                                                                            ?>">
                                                                                <?php echo ucfirst($segment['statut']); ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?php echo $segment['date_debut'] ? date('d/m/Y H:i', strtotime($segment['date_debut'])) : 'N/A'; ?></td>
                                                                        <td><?php echo $segment['date_fin'] ? date('d/m/Y H:i', strtotime($segment['date_fin'])) : 'N/A'; ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification de livraison -->
    <div class="modal fade" id="editLivraisonModal" tabindex="-1" aria-labelledby="editLivraisonModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la livraison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="livraisons.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        
                        <div class="mb-3">
                            <label for="edit-statut" class="form-label">Statut</label>
                            <select class="form-select" id="edit-statut" name="statut">
                                <option value="en attente">En attente</option>
                                <option value="en cours">En cours</option>
                                <option value="livrée">Livrée</option>
                                <option value="annulée">Annulée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-id_livreur" class="form-label">Livreur</label>
                            <select class="form-select" id="edit-id_livreur" name="id_livreur">
                                <option value="">Sélectionner un livreur</option>
                                <?php foreach ($livreurs as $livreur): ?>
                                    <option value="<?php echo $livreur['id']; ?>"><?php echo htmlspecialchars($livreur['nom'] . ' (' . $livreur['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit-validation_client" name="validation_client">
                            <label class="form-check-label" for="edit-validation_client">Validé par le client</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit-reception_confirmee" name="reception_confirmee">
                            <label class="form-check-label" for="edit-reception_confirmee">Réception confirmée</label>
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

    <!-- Modal de suppression de livraison -->
    <div class="modal fade" id="deleteLivraisonModal" tabindex="-1" aria-labelledby="deleteLivraisonModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette livraison et tous ses segments associés ?</p>
                    <form action="livraisons.php" method="post">
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
            
            const editModal = document.getElementById('editLivraisonModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    const id = button.getAttribute('data-id');
                    const statut = button.getAttribute('data-statut');
                    const id_livreur = button.getAttribute('data-id_livreur');
                    const validation_client = button.getAttribute('data-validation_client') === '1';
                    const reception_confirmee = button.getAttribute('data-reception_confirmee') === '1';
                    
                    editModal.querySelector('#edit-id').value = id;
                    editModal.querySelector('#edit-statut').value = statut;
                    editModal.querySelector('#edit-id_livreur').value = id_livreur;
                    editModal.querySelector('#edit-validation_client').checked = validation_client;
                    editModal.querySelector('#edit-reception_confirmee').checked = reception_confirmee;
                });
            }
            
            const deleteModal = document.getElementById('deleteLivraisonModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    deleteModal.querySelector('#delete-id').value = id;
                });
            }
        });
    </script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html>
