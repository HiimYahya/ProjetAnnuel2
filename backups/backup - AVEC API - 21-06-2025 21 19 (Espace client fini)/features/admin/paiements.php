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

// Récupération des paiements sans filtres
$stmt = $conn->prepare("SELECT * FROM paiements ORDER BY id DESC");
$stmt->execute();
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $montant = filter_var($_POST['montant'] ?? 0, FILTER_VALIDATE_FLOAT);
        $methode = $_POST['methode'] ?? '';
        $statut = $_POST['statut'] ?? '';
        
        // Validation
        $errors = [];
        if ($montant <= 0) $errors[] = "Le montant doit être supérieur à 0";
        if (empty($methode)) $errors[] = "La méthode de paiement est obligatoire";
        if (empty($statut)) $errors[] = "Le statut est obligatoire";
        
        if (empty($errors)) {
            if ($_POST['action'] === 'add') {
                $stmt = $conn->prepare("INSERT INTO paiements (montant, methode, statut) VALUES (?, ?, ?)");
                $stmt->execute([$montant, $methode, $statut]);
                $_SESSION['message'] = "Paiement ajouté avec succès";
            } else {
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE paiements SET montant = ?, methode = ?, statut = ? WHERE id = ?");
                $stmt->execute([$montant, $methode, $statut, $id]);
                $_SESSION['message'] = "Paiement mis à jour avec succès";
            }
            header('Location: paiements.php');
            exit;
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM paiements WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $_SESSION['message'] = "Paiement supprimé avec succès";
        header('Location: paiements.php');
        exit;
    }
}

// Calcul du montant total des paiements
$stmt = $conn->query("SELECT SUM(montant) as total FROM paiements WHERE statut = 'effectué'");
$total_paiements = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des paiements - EcoDeli</title>
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
        
        /* Style pour le total */
        .total-card {
            background-color: #f8f9fa;
            border-left: 3px solid #28a745;
        }
        
        .total-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #28a745;
        }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Paiements</h1>
        </div>
        
        <div class="page-content">
            <div class="add-button">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaiementModal" style="position: relative; z-index: 100; pointer-events: auto; border-radius: 4px; padding: 8px 16px; font-weight: 500;">
                    Ajouter un paiement
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
            
            <!-- Montant total -->
            <div class="card total-card mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total des paiements effectués :</h5>
                    <div class="total-value"><?php echo number_format($total_paiements, 2, ',', ' '); ?> €</div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paiements)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun paiement trouvé</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paiements as $paiement): ?>
                                        <tr>
                                            <td><?php echo $paiement['id']; ?></td>
                                            <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</td>
                                            <td>
                                                <?php
                                                    $methode = strtolower($paiement['methode']);
                                                    $icon = match($methode) {
                                                        'carte' => '<i class="fas fa-credit-card"></i>',
                                                        'paypal' => '<i class="fab fa-paypal"></i>',
                                                        'virement' => '<i class="fas fa-exchange-alt"></i>',
                                                        'espece' => '<i class="fas fa-money-bill-wave"></i>',
                                                        default => '<i class="fas fa-question"></i>',
                                                    };
                                                    echo $icon . ' ' . ucfirst($paiement['methode']);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $statut = strtolower($paiement['statut']);
                                                    $couleur = match($statut) {
                                                        'effectué' => '🟢',
                                                        'en attente' => '🟠',
                                                        'échoué' => '🔴',
                                                        default => '⚪',
                                                    };
                                                    echo $couleur . ' ' . ucfirst($paiement['statut']);
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPaiementModal"
                                                        data-id="<?php echo $paiement['id']; ?>"
                                                        data-montant="<?php echo $paiement['montant']; ?>"
                                                        data-methode="<?php echo htmlspecialchars($paiement['methode']); ?>"
                                                        data-statut="<?php echo htmlspecialchars($paiement['statut']); ?>"
                                                        style="position: relative; z-index: 100; pointer-events: auto; margin-bottom: 4px; width: 100%; padding: 6px 12px;">
                                                    Modifier
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-confirm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deletePaiementModal"
                                                        data-id="<?php echo $paiement['id']; ?>"
                                                        data-montant="<?php echo number_format($paiement['montant'], 2, ',', ' '); ?>"
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

    <!-- Modal d'ajout de paiement -->
    <div class="modal fade" id="addPaiementModal" tabindex="-1" aria-labelledby="addPaiementModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="paiements.php" method="post">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="montant" name="montant" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="methode" class="form-label">Méthode de paiement</label>
                            <select class="form-select" id="methode" name="methode" required>
                                <option value="carte">Carte bancaire</option>
                                <option value="paypal">PayPal</option>
                                <option value="virement">Virement</option>
                                <option value="espece">Espèces</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="en attente">En attente</option>
                                <option value="effectué">Effectué</option>
                                <option value="échoué">Échoué</option>
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

    <!-- Modal de modification de paiement -->
    <div class="modal fade" id="editPaiementModal" tabindex="-1" aria-labelledby="editPaiementModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="paiements.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        
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
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression de paiement -->
    <div class="modal fade" id="deletePaiementModal" tabindex="-1" aria-labelledby="deletePaiementModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le paiement d'un montant de <span id="delete-montant"></span> € ?</p>
                    <form action="paiements.php" method="post">
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
            
            const editModal = document.getElementById('editPaiementModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    const id = button.getAttribute('data-id');
                    const montant = button.getAttribute('data-montant');
                    const methode = button.getAttribute('data-methode');
                    const statut = button.getAttribute('data-statut');
                    
                    editModal.querySelector('#edit-id').value = id;
                    editModal.querySelector('#edit-montant').value = montant;
                    editModal.querySelector('#edit-methode').value = methode;
                    editModal.querySelector('#edit-statut').value = statut;
                });
            }
            
            const deleteModal = document.getElementById('deletePaiementModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    const id = button.getAttribute('data-id');
                    const montant = button.getAttribute('data-montant');
                    
                    deleteModal.querySelector('#delete-id').value = id;
                    deleteModal.querySelector('#delete-montant').textContent = montant;
                });
            }
        });
    </script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html>
