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
$filtre_statut = $_GET['statut'] ?? '';
$filtre_distance = $_GET['distance'] ?? '';
$filtre_tri = $_GET['tri'] ?? 'recent';

// Construction de la requête SQL avec filtres
$where_clauses = ["(s.statut = 'en point relais' OR (s.statut = 'en attente' AND s.id_livreur IS NULL))", "l.validation_client = 1"];
$params = [];

if (!empty($filtre_ville)) {
    $where_clauses[] = "(pr_arrivee.ville LIKE ? OR pr_depart.ville LIKE ? OR s.adresse_arrivee LIKE ? OR s.adresse_depart LIKE ?)";
    $search_term = "%$filtre_ville%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if (!empty($filtre_statut) && in_array($filtre_statut, ['en_attente', 'en_point_relais'])) {
    $statut_map = [
        'en_attente' => 'en attente',
        'en_point_relais' => 'en point relais'
    ];
    $where_clauses[] = "s.statut = ?";
    $params[] = $statut_map[$filtre_statut];
}

$where_clause = implode(' AND ', $where_clauses);

// Tri des résultats
$order_by = match($filtre_tri) {
    'prix_asc' => 'a.prix ASC',
    'prix_desc' => 'a.prix DESC',
    'distance_asc' => 's.id ASC', // Remplacer par une vraie distance quand implémentée
    'distance_desc' => 's.id DESC', // Remplacer par une vraie distance quand implémentée
    default => 's.id DESC' // Tri par défaut: plus récent d'abord
};

// Récupérer tous les segments disponibles en points relais ou en attente sans livreur
$sql = "
    SELECT s.*, a.titre, a.description, a.prix, 
           u_client.nom AS nom_client,
           pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville,
           pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville,
           a.date_livraison_souhaitee
    FROM segments s
    JOIN livraisons l ON s.id_livraison = l.id
    JOIN annonces a ON s.id_annonce = a.id
    JOIN utilisateurs u_client ON a.id_client = u_client.id
    LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id
    LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id
    WHERE $where_clause
    ORDER BY $order_by
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$segments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des villes pour le filtre
$villes_stmt = $conn->prepare("
    SELECT DISTINCT ville FROM points_relais
    UNION
    SELECT DISTINCT SUBSTRING_INDEX(adresse_depart, ',', -1) as ville FROM segments
    UNION 
    SELECT DISTINCT SUBSTRING_INDEX(adresse_arrivee, ',', -1) as ville FROM segments
    ORDER BY ville
");
$villes_stmt->execute();
$villes = $villes_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
  <meta charset="UTF-8">
  <title>Segments disponibles - EcoDeli</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<?php include '../../../../fonctions/header_livreur.php'; ?>

<body class="d-flex flex-column min-vh-100">
  <div class="container-fluid">
    <div class="page-title">
      <h1>Segments de livraison disponibles</h1>
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
            
            <div class="col-md-3">
              <label for="statut" class="form-label">Statut</label>
              <select class="form-select" id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <option value="en_attente" <?= $filtre_statut == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="en_point_relais" <?= $filtre_statut == 'en_point_relais' ? 'selected' : '' ?>>En point relais</option>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="tri" class="form-label">Trier par</label>
              <select class="form-select" id="tri" name="tri">
                <option value="recent" <?= $filtre_tri == 'recent' ? 'selected' : '' ?>>Plus récent</option>
                <option value="prix_asc" <?= $filtre_tri == 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $filtre_tri == 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
              </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
              <div class="d-grid gap-2 w-100">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-2"></i>Filtrer
                </button>
                <a href="segments_disponibles.php" class="btn btn-outline-secondary">
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
      
      <?php if (empty($segments)): ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>Aucun segment de livraison disponible correspondant à vos critères.
        </div>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <?php foreach ($segments as $segment): 
            $statut = strtolower($segment['statut']);
            $dot = match($statut) {
                'livré' => '<i class="fas fa-check-circle text-success"></i>',
                'en cours' => '<i class="fas fa-truck text-warning"></i>',
                'en attente' => '<i class="fas fa-clock text-info"></i>',
                'en point relais' => '<i class="fas fa-store text-primary"></i>',
                'annulé' => '<i class="fas fa-times-circle text-danger"></i>',
                default => '<i class="fas fa-question-circle text-secondary"></i>'
            };

            // Déterminer le lieu de prise en charge
            $lieu_depart = $segment['point_relais_depart_nom'] 
                ? "{$segment['point_relais_depart_nom']} - {$segment['point_relais_depart_ville']}" 
                : $segment['adresse_depart'];

            // Déterminer le lieu de dépôt
            $lieu_arrivee = $segment['point_relais_arrivee_nom'] 
                ? "{$segment['point_relais_arrivee_nom']} - {$segment['point_relais_arrivee_ville']}" 
                : $segment['adresse_arrivee'];
                
            // Calculer le temps restant jusqu'à la date de livraison souhaitée
            $date_livraison = new DateTime($segment['date_livraison_souhaitee']);
            $aujourd_hui = new DateTime();
            $jours_restants = $aujourd_hui->diff($date_livraison)->days;
            $urgence = $jours_restants <= 3 ? 'text-danger fw-bold' : ($jours_restants <= 7 ? 'text-warning' : 'text-success');
          ?>
            <div class="col">
              <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                  <h5 class="card-title mb-0"><?= htmlspecialchars($segment['titre']) ?></h5>
                  <span class="badge bg-primary"><?= $segment['prix'] ? number_format($segment['prix'], 2) . " €" : 'Prix non défini' ?></span>
                </div>
                
                <div class="card-body">
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <span class="me-2 fw-bold">Client :</span>
                      <span><?= htmlspecialchars($segment['nom_client']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                      <div>
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <span class="fw-bold">De :</span>
                      </div>
                      <span class="text-truncate"><?= htmlspecialchars($lieu_depart) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                      <div>
                        <i class="fas fa-map-marker-alt text-success me-1"></i>
                        <span class="fw-bold">À :</span>
                      </div>
                      <span class="text-truncate"><?= htmlspecialchars($lieu_arrivee) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                      <div>
                        <i class="fas fa-calendar-alt me-1"></i>
                        <span class="fw-bold">Livraison souhaitée :</span>
                      </div>
                      <span class="<?= $urgence ?>">
                        <?= $segment['date_livraison_souhaitee'] ? date('d/m/Y', strtotime($segment['date_livraison_souhaitee'])) : 'Non précisée' ?>
                        <?= $jours_restants > 0 ? "($jours_restants jours)" : "(aujourd'hui)" ?>
                      </span>
                    </div>
                    
                    <div class="mb-3">
                      <strong>Description :</strong>
                      <p class="mb-0"><?= nl2br(htmlspecialchars(substr($segment['description'], 0, 150)) . (strlen($segment['description']) > 150 ? '...' : '')) ?></p>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-secondary me-2">Statut:</span>
                    <span><?= $dot ?> <?= htmlspecialchars($segment['statut']) ?></span>
                  </div>

                  <!-- Bouton pour récupérer le segment -->
                  <form method="POST" action="recuperer_segment.php" class="d-grid gap-2">
                    <input type="hidden" name="id_segment" value="<?= $segment['id'] ?>">
                    <button type="submit" class="btn btn-success">
                      <i class="fas fa-hand me-2"></i>Récupérer ce segment
                    </button>
                  </form>
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