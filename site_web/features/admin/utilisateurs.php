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

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Validation des données
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $role = $_POST['role'] ?? '';
    $adresse = htmlspecialchars($_POST['adresse'] ?? '');
    
    if (!$email) {
        $error = "L'adresse email est invalide.";
    } elseif (!in_array($role, ['client', 'livreur', 'commercant', 'prestataire', 'admin'])) {
        $error = "Le rôle sélectionné est invalide.";
    } else {
        if ($action === 'add') {
            // Vérification si l'email existe déjà
            $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé par un autre utilisateur.";
            } else {
                // Génération d'un mot de passe aléatoire
                $password = bin2hex(random_bytes(4)); // 8 caractères
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, adresse) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $email, $hashed_password, $role, $adresse]);
                
                $success = "Utilisateur ajouté avec succès. Mot de passe temporaire: $password";
            }
        } elseif ($action === 'edit' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Vérification si on modifie le mail pour un mail déjà existant
            $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé par un autre utilisateur.";
            } else {
                $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, email = ?, role = ?, adresse = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $role, $adresse, $id]);
                
                $success = "Utilisateur mis à jour avec succès.";
            }
        }
    }
}

// Traitement de la suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérification si ce n'est pas le dernier admin
    if ($id == $_SESSION['utilisateur']['id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $stmt = $conn->prepare("SELECT role FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['role'] === 'admin') {
            // Vérifier s'il reste d'autres admins
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'admin' AND id != ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                $error = "Impossible de supprimer le dernier administrateur.";
            }
        }
        
        if (!isset($error)) {
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Utilisateur supprimé avec succès.";
        }
    }
}

// Récupération de l'utilisateur à modifier
$userToEdit = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $userToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Recherche et filtrage
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nom LIKE ? OR email LIKE ? OR adresse LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Comptage total pour pagination
$count_sql = "SELECT COUNT(*) as total FROM utilisateurs $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);

// Récupération des utilisateurs
$limit = (int)$limit;
$offset = (int)$offset;
$sql = "SELECT * FROM utilisateurs $where_clause ORDER BY date_inscription DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Gestion des Utilisateurs - EcoDeli Admin</title>
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
    </style>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Gestion des Utilisateurs</h1>
        </div>
        
        <div class="page-content">
            <div class="add-button">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal" style="position: relative; z-index: 100; pointer-events: auto; border-radius: 4px; padding: 8px 16px; font-weight: 500;">
                    Ajouter un utilisateur
                </button>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filtres de recherche -->
            <div class="card search-card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="search-row">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Rechercher par nom, email ou adresse" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="search-button">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    Rechercher
                                </button>
                            </div>
                            <div class="search-button">
                                <a href="utilisateurs.php" class="btn btn-outline-secondary w-100" style="position: relative; z-index: 100; pointer-events: auto;">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tableau des utilisateurs -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Date d'inscription</th>
                                    <th>Adresse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun utilisateur trouvé</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <?php if ($user['photo_profil']): ?>
                                                    <img src="/site_web/uploads/<?php echo htmlspecialchars($user['photo_profil']); ?>" class="rounded-circle me-2" width="32" height="32" alt="Photo">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($user['nom']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge rounded-pill bg-<?php 
                                                    echo match($user['role']) {
                                                        'admin' => 'danger',
                                                        'client' => 'primary',
                                                        'livreur' => 'success',
                                                        'prestataire' => 'warning',
                                                        'commercant' => 'info',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                            <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($user['adresse'] ?? 'Non renseignée'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal-<?php echo $user['id']; ?>" 
                                                        style="position: relative; z-index: 100; pointer-events: auto; margin-bottom: 4px; width: 100%; padding: 6px 12px;">
                                                    Modifier
                                                </button>
                                                <a href="?delete=<?php echo $user['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');" 
                                                   style="position: relative; z-index: 100; pointer-events: auto; width: 100%; padding: 6px 12px;">
                                                    Supprimer
                                                </a>
                                                
                                                <!-- Modal d'édition pour chaque utilisateur -->
                                                <div class="modal fade" id="editUserModal-<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel-<?php echo $user['id']; ?>" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
                                                    <div class="modal-dialog" style="z-index: 10001;">
                                                        <div class="modal-content" style="pointer-events: auto;">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Modifier utilisateur</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="edit">
                                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="edit-nom-<?php echo $user['id']; ?>" class="form-label">Nom</label>
                                                                        <input type="text" class="form-control" id="edit-nom-<?php echo $user['id']; ?>" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="edit-email-<?php echo $user['id']; ?>" class="form-label">Email</label>
                                                                        <input type="email" class="form-control" id="edit-email-<?php echo $user['id']; ?>" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="edit-role-<?php echo $user['id']; ?>" class="form-label">Rôle</label>
                                                                        <select class="form-select" id="edit-role-<?php echo $user['id']; ?>" name="role" required>
                                                                            <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                                                            <option value="livreur" <?php echo $user['role'] === 'livreur' ? 'selected' : ''; ?>>Livreur</option>
                                                                            <option value="prestataire" <?php echo $user['role'] === 'prestataire' ? 'selected' : ''; ?>>Prestataire</option>
                                                                            <option value="commercant" <?php echo $user['role'] === 'commercant' ? 'selected' : ''; ?>>Commerçant</option>
                                                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="edit-adresse-<?php echo $user['id']; ?>" class="form-label">Adresse</label>
                                                                        <textarea class="form-control" id="edit-adresse-<?php echo $user['id']; ?>" name="adresse" rows="3"><?php echo htmlspecialchars($user['adresse'] ?? ''); ?></textarea>
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
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Pagination des utilisateurs">
                            <ul class="pagination justify-content-center mt-4">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>">Précédent</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>">Suivant</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal d'ajout d'utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10000;">
        <div class="modal-dialog" style="z-index: 10001;">
            <div class="modal-content" style="pointer-events: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="client">Client</option>
                                <option value="livreur">Livreur</option>
                                <option value="prestataire">Prestataire</option>
                                <option value="commercant">Commerçant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>Un mot de passe temporaire sera généré automatiquement et affiché après l'ajout.</small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/darkmode.js"></script>
    <script>
        // Réinitialiser Bootstrap et ses modales
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
        });
    </script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html> 