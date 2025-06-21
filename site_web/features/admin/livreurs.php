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

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_livreur = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Valider un livreur
    if ($_POST['action'] === 'valider' && $id_livreur > 0) {
        $stmt = $conn->prepare("UPDATE utilisateurs SET validation_identite = 'validee' WHERE id = ? AND role = 'livreur'");
        $stmt->execute([$id_livreur]);
        $_SESSION['message'] = "Le compte livreur a été validé avec succès.";
    }
    
    // Refuser un livreur
    if ($_POST['action'] === 'refuser' && $id_livreur > 0) {
        $stmt = $conn->prepare("UPDATE utilisateurs SET validation_identite = 'refusee' WHERE id = ? AND role = 'livreur'");
        $stmt->execute([$id_livreur]);
        $_SESSION['message'] = "Le compte livreur a été refusé.";
    }
    
    header('Location: livreurs.php');
    exit;
}

// Récupération des livreurs
$stmt = $conn->prepare("SELECT id, nom, email, validation_identite, piece_identite, date_inscription FROM utilisateurs WHERE role = 'livreur' ORDER BY id DESC");
$stmt->execute();
$livreurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des livreurs - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/admin.css">
    <style>
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
        .btn-primary, .btn-success, .btn-danger {
            border-radius: 4px;
            position: relative;
            z-index: 100;
            pointer-events: auto;
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
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
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
        
        /* Badge personnalisé */
        .badge-validation {
            padding: 6px 12px;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 50px;
        }
        
        .badge-en-attente {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-validee {
            background-color: #28a745;
            color: white;
        }
        
        .badge-refusee {
            background-color: #dc3545;
            color: white;
        }
        
        /* Prévisualisation de document */
        .doc-preview {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        /* Modal pour visualiser le document */
        .modal-document img {
            max-width: 100%;
            max-height: 80vh;
        }
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Livreurs et Validation des Identités</h1>
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
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                    <th>Pièce d'identité</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($livreurs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun livreur trouvé</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($livreurs as $livreur): ?>
                                        <tr>
                                            <td><?php echo $livreur['id']; ?></td>
                                            <td><?php echo htmlspecialchars($livreur['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($livreur['email']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($livreur['date_inscription'])); ?></td>
                                            <td>
                                                <?php if ($livreur['piece_identite']): ?>
                                                    <?php 
                                                        $extension = pathinfo($livreur['piece_identite'], PATHINFO_EXTENSION);
                                                        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])): 
                                                    ?>
                                                        <img src="/site_web/uploads/<?php echo $livreur['piece_identite']; ?>" class="doc-preview" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="/site_web/uploads/<?php echo $livreur['piece_identite']; ?>">
                                                    <?php else: ?>
                                                        <a href="/site_web/uploads/<?php echo $livreur['piece_identite']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="position: relative; z-index: 100; pointer-events: auto;">
                                                            <i class="fas fa-file-pdf"></i> Voir
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Non fournie</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statut = $livreur['validation_identite'] ?? 'non fournie';
                                                    $badge_class = '';
                                                    
                                                    switch($statut) {
                                                        case 'en_attente':
                                                            $badge_class = 'badge-en-attente';
                                                            $statut_text = 'En attente';
                                                            break;
                                                        case 'validee':
                                                            $badge_class = 'badge-validee';
                                                            $statut_text = 'Validée';
                                                            break;
                                                        case 'refusee':
                                                            $badge_class = 'badge-refusee';
                                                            $statut_text = 'Refusée';
                                                            break;
                                                        default:
                                                            $badge_class = 'bg-secondary';
                                                            $statut_text = 'Non fournie';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $statut_text; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($livreur['piece_identite'] && $livreur['validation_identite'] !== 'validee'): ?>
                                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                                        <input type="hidden" name="action" value="valider">
                                                        <input type="hidden" name="id" value="<?php echo $livreur['id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" style="position: relative; z-index: 100; pointer-events: auto;">
                                                            <i class="fas fa-check"></i> Valider
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($livreur['piece_identite'] && $livreur['validation_identite'] !== 'refusee'): ?>
                                                    <form method="POST" style="display: inline-block;">
                                                        <input type="hidden" name="action" value="refuser">
                                                        <input type="hidden" name="id" value="<?php echo $livreur['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" style="position: relative; z-index: 100; pointer-events: auto;">
                                                            <i class="fas fa-times"></i> Refuser
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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

    <!-- Modal pour afficher le document -->
    <div class="modal fade modal-document" id="documentModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Pièce d'identité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../../assets/dist/modal-fix.js"></script>
    <script>
        // Gestion de l'affichage des documents dans la modal
        document.addEventListener('DOMContentLoaded', function() {
            var documentModal = document.getElementById('documentModal');
            if (documentModal) {
                documentModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var imageUrl = button.getAttribute('data-src');
                    var modalImage = document.getElementById('modalImage');
                    modalImage.src = imageUrl;
                });
            }
        });
    </script>
    
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html> 