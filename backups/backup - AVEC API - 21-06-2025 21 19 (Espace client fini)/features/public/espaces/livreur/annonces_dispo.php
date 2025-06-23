<?php
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

// Sécurité : vérifier que le livreur est connecté
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: /site_web/features/public/login.php');
    exit;
}

// Paramètres de filtrage
$filtre_ville = $_GET['ville'] ?? '';
$filtre_prix_min = $_GET['prix_min'] ?? '';
$filtre_prix_max = $_GET['prix_max'] ?? '';
$filtre_tri = $_GET['tri'] ?? 'recent';

// Construction de la requête SQL avec filtres
$where_clauses = ["a.statut = 'en attente'"];
$params = [];

if (!empty($filtre_ville)) {
    $where_clauses[] = "(a.ville_depart LIKE ? OR a.ville_arrivee LIKE ?)";
    $search_term = "%$filtre_ville%";
    $params = array_merge($params, [$search_term, $search_term]);
}

if (!empty($filtre_prix_min) && is_numeric($filtre_prix_min)) {
    $where_clauses[] = "a.prix >= ?";
    $params[] = $filtre_prix_min;
}

if (!empty($filtre_prix_max) && is_numeric($filtre_prix_max)) {
    $where_clauses[] = "a.prix <= ?";
    $params[] = $filtre_prix_max;
}

$where_clause = implode(' AND ', $where_clauses);

// Tri des résultats
$order_by = match($filtre_tri) {
    'prix_asc' => 'a.prix ASC',
    'prix_desc' => 'a.prix DESC',
    'date_livraison' => 'a.date_livraison_souhaitee ASC',
    default => 'a.date_annonce DESC' // Tri par défaut: plus récent d'abord
};

// Récupérer toutes les annonces en attente avec nom du client
$sql = "
    SELECT a.*, u.nom AS nom_client
    FROM annonces a
    JOIN utilisateurs u ON a.id_client = u.id
    WHERE $where_clause
    ORDER BY $order_by
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les points relais
$points_relais_stmt = $conn->prepare("SELECT id, nom, ville FROM points_relais ORDER BY ville");
$points_relais_stmt->execute();
$points_relais = $points_relais_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les villes uniques pour le filtre
$villes_stmt = $conn->prepare("
    SELECT DISTINCT ville_depart as ville FROM annonces WHERE ville_depart IS NOT NULL
    UNION
    SELECT DISTINCT ville_arrivee as ville FROM annonces WHERE ville_arrivee IS NOT NULL
    ORDER BY ville
");
$villes_stmt->execute();
$villes = $villes_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
  <meta charset="UTF-8">
  <title>Annonces disponibles - EcoDeli</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../../../../fonctions/header_livreur.php'; ?>

  <div class="container-fluid">
    <div class="page-title">
      <h1>Annonces disponibles</h1>
    </div>
    
    <div class="page-content">
      <!-- Card pour filtres de recherche -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <label for="ville" class="form-label">Ville</label>
              <select class="form-select" id="ville" name="ville">
                <option value="">Toutes les villes</option>
                <?php foreach($villes as $ville): ?>
                  <option value="<?= htmlspecialchars($ville) ?>" <?= $filtre_ville == $ville ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ville) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-2">
              <label for="prix_min" class="form-label">Prix min (€)</label>
              <input type="number" class="form-control" id="prix_min" name="prix_min" value="<?= htmlspecialchars($filtre_prix_min) ?>" min="0" step="0.01">
            </div>
            
            <div class="col-md-2">
              <label for="prix_max" class="form-label">Prix max (€)</label>
              <input type="number" class="form-control" id="prix_max" name="prix_max" value="<?= htmlspecialchars($filtre_prix_max) ?>" min="0" step="0.01">
            </div>
            
            <div class="col-md-2">
              <label for="tri" class="form-label">Trier par</label>
              <select class="form-select" id="tri" name="tri">
                <option value="recent" <?= $filtre_tri == 'recent' ? 'selected' : '' ?>>Plus récent</option>
                <option value="prix_asc" <?= $filtre_tri == 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $filtre_tri == 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="date_livraison" <?= $filtre_tri == 'date_livraison' ? 'selected' : '' ?>>Date de livraison</option>
              </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
              <div class="d-grid gap-2 w-100">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-2"></i>Filtrer
                </button>
                <a href="annonces_dispo.php" class="btn btn-outline-secondary">
                  <i class="fas fa-undo me-2"></i>Réinitialiser
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
      
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (empty($annonces)): ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i> Aucune annonce disponible correspondant à vos critères.
        </div>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <?php foreach ($annonces as $annonce): 
            $statut = strtolower($annonce['statut']);
            $dot = match($statut) {
                'livrée' => '<i class="fas fa-check-circle text-success"></i>',
                'prise en charge', 'en cours' => '<i class="fas fa-truck text-warning"></i>',
                'en attente' => '<i class="fas fa-clock text-info"></i>',
                'annulée' => '<i class="fas fa-times-circle text-danger"></i>',
                default => '<i class="fas fa-question-circle text-secondary"></i>'
            };
            
            // Calculer le temps restant jusqu'à la date de livraison souhaitée
            $date_livraison = !empty($annonce['date_livraison_souhaitee']) ? new DateTime($annonce['date_livraison_souhaitee']) : null;
            $aujourd_hui = new DateTime();
            $jours_restants = $date_livraison ? $aujourd_hui->diff($date_livraison)->days : null;
            $urgence = $jours_restants !== null ? ($jours_restants <= 3 ? 'text-danger fw-bold' : ($jours_restants <= 7 ? 'text-warning' : 'text-success')) : '';
          ?>
            <div class="col">
              <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                  <h5 class="card-title mb-0"><?= htmlspecialchars($annonce['titre']) ?></h5>
                  <span class="badge bg-primary"><?= $annonce['prix'] ? number_format($annonce['prix'], 2) . " €" : 'Prix non défini' ?></span>
                </div>
                
                <div class="card-body">
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <span class="me-2 fw-bold">Client :</span>
                      <span><?= htmlspecialchars($annonce['nom_client']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                      <div>
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <span class="fw-bold">De :</span>
                      </div>
                      <span class="text-truncate"><?= htmlspecialchars($annonce['ville_depart']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                      <div>
                        <i class="fas fa-map-marker-alt text-success me-1"></i>
                        <span class="fw-bold">À :</span>
                      </div>
                      <span class="text-truncate"><?= htmlspecialchars($annonce['ville_arrivee']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                      <div>
                        <i class="fas fa-calendar-alt me-1"></i>
                        <span class="fw-bold">Livraison souhaitée :</span>
                      </div>
                      <span class="<?= $urgence ?>">
                        <?= $annonce['date_livraison_souhaitee'] ? date('d/m/Y', strtotime($annonce['date_livraison_souhaitee'])) : 'Non précisée' ?>
                        <?= $jours_restants !== null ? "($jours_restants jours)" : "" ?>
                      </span>
                    </div>
                    
                    <div class="mb-3">
                      <strong>Description :</strong>
                      <p class="mb-0"><?= nl2br(htmlspecialchars(substr($annonce['description'], 0, 150)) . (strlen($annonce['description']) > 150 ? '...' : '')) ?></p>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-secondary me-2">Statut:</span>
                    <span><?= $dot ?> <?= htmlspecialchars($annonce['statut']) ?></span>
                  </div>

                  <!-- Boutons d'action -->
                  <div class="d-flex flex-column gap-2">
                    <!-- Bouton accepter -->
                    <form method="POST" action="accepter_annonce.php">
                      <input type="hidden" name="id_annonce" value="<?= $annonce['id'] ?>">
                      <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-1"></i> Accepter la livraison
                      </button>
                    </form>

                    <!-- Lien suivi -->
                    <a href="livraisons.php?id=<?= $annonce['id'] ?>" class="btn btn-outline-primary">
                      <i class="fas fa-eye me-1"></i> Détails
                    </a>
                  </div>

                  <?php if ($annonce['segmentation_possible'] == 1): ?>
                    <hr>
                    <h6 class="mb-3"><i class="fas fa-cut me-1"></i> Proposer un segment</h6>
                    
                    <form method="POST" action="proposer_segment.php">
                      <input type="hidden" name="id_annonce" value="<?= $annonce['id'] ?>">
                      <input type="hidden" name="point_relais_depart" value="origine">
                      <input type="hidden" name="segment_depart" value="<?= htmlspecialchars($annonce['ville_depart']) ?>">
                      <input type="hidden" name="segment_arrivee" value="<?= htmlspecialchars($annonce['ville_arrivee']) ?>">
                      
                      <div class="mb-3">
                        <label class="form-label">Point de départ</label>
                        <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($annonce['ville_depart']) ?>" disabled>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label">Point d'arrivée</label>
                        <select name="point_relais_arrivee" class="form-select form-select-sm" required>
                          <option value="destination">Adresse de destination: <?= htmlspecialchars($annonce['ville_arrivee']) ?></option>
                          <?php foreach ($points_relais as $point): ?>
                            <option value="<?= $point['id'] ?>"><?= htmlspecialchars($point['nom'] . ' - ' . $point['ville']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      
                      <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-cut me-1"></i> Proposer un segment
                      </button>
                    </form>
                  <?php else: ?>
                    <div class="alert alert-light mt-3 mb-0">
                      <i class="fas fa-info-circle me-1"></i> La segmentation n'est pas possible pour cette annonce.
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
