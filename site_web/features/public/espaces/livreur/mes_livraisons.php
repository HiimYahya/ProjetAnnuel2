<?php
session_start();
require_once '../../../../fonctions/db.php';

$conn = getConnexion();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];

// Récupérer les livraisons complètes du livreur
$stmt = $conn->prepare("
    SELECT l.*, a.titre, a.description, a.ville_depart, a.ville_arrivee, u.nom AS nom_client
    FROM livraisons l
    JOIN annonces a ON l.id_annonce = a.id
    JOIN utilisateurs u ON l.id_client = u.id
    WHERE l.id_livreur = ?
    ORDER BY l.date_prise_en_charge DESC
");
$stmt->execute([$id_livreur]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les segments du livreur - uniquement ceux dont il est toujours responsable
$stmt_segments = $conn->prepare("
    SELECT s.*, a.titre, l.id_client, u.nom AS nom_client,
           pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville,
           pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville
    FROM segments s
    JOIN livraisons l ON s.id_livraison = l.id
    JOIN annonces a ON l.id_annonce = a.id
    JOIN utilisateurs u ON l.id_client = u.id
    LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id
    LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id
    WHERE s.id_livreur = ? AND l.validation_client = 1
    ORDER BY s.id DESC
");
$stmt_segments->execute([$id_livreur]);
$segments = $stmt_segments->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'historique des segments que ce livreur a livrés à un point relais
$stmt_historique = $conn->prepare("
    SELECT s.id, s.id_annonce, s.adresse_depart, s.adresse_arrivee, 
           s.date_debut, s.date_fin, a.titre,
           pr.nom AS point_relais_nom, pr.ville AS point_relais_ville
    FROM segments s
    JOIN annonces a ON s.id_annonce = a.id
    LEFT JOIN points_relais pr ON s.point_relais_arrivee = pr.id
    LEFT JOIN logs log ON log.details LIKE CONCAT('%Segment #', s.id, '%') AND log.action = 'livraison_point_relais'
    WHERE log.id_utilisateur = ? AND s.statut = 'en point relais' AND s.id_livreur IS NULL
    ORDER BY s.date_fin DESC
    LIMIT 10
");
try {
    $stmt_historique->execute([$id_livreur]);
    $historique_segments = $stmt_historique->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la table de logs n'existe pas, on ignore
    $historique_segments = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes livraisons</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<?php include '../../../../fonctions/header_livreur.php'; ?>

<body class="d-flex flex-column min-vh-100">
  <div class="container-fluid">
    <div class="page-title">
      <h1>Mes livraisons</h1>
    </div>
    
    <div class="page-content">
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
      
      <h2 class="mb-4">Mes segments de livraison en cours</h2>
      
      <?php if (empty($segments)): ?>
        <p class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          Vous n'avez aucun segment de livraison en cours.
          <a href="segments_disponibles.php" class="alert-link">Voir les segments disponibles</a>
        </p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Annonce</th>
                <th>Client</th>
                <th>Départ</th>
                <th>Arrivée</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($segments as $segment): 
                $statut = strtolower($segment['statut']);
                $couleur = match($statut) {
                  'livré' => '<i class="fas fa-check-circle text-success"></i>',
                  'en cours' => '<i class="fas fa-truck text-warning"></i>',
                  'en attente' => '<i class="fas fa-clock text-info"></i>',
                  'en point relais' => '<i class="fas fa-store text-primary"></i>',
                  'annulé' => '<i class="fas fa-times-circle text-danger"></i>',
                  default => '<i class="fas fa-question-circle text-secondary"></i>',
                };

                // Déterminer le lieu de départ et d'arrivée avec point relais si applicable
                $lieu_depart = $segment['point_relais_depart_nom'] 
                  ? "{$segment['point_relais_depart_nom']} - {$segment['point_relais_depart_ville']}" 
                  : $segment['adresse_depart'];
                
                $lieu_arrivee = $segment['point_relais_arrivee_nom'] 
                  ? "{$segment['point_relais_arrivee_nom']} - {$segment['point_relais_arrivee_ville']}" 
                  : $segment['adresse_arrivee'];
              ?>
                <tr>
                  <td><?= htmlspecialchars($segment['titre']) ?></td>
                  <td><?= htmlspecialchars($segment['nom_client']) ?></td>
                  <td><?= htmlspecialchars($lieu_depart) ?></td>
                  <td><?= htmlspecialchars($lieu_arrivee) ?></td>
                  <td><?= $couleur . ' ' . htmlspecialchars($segment['statut']) ?></td>
                  <td>
                    <?php if ($segment['statut'] === 'en cours'): ?>
                      <?php if ($segment['point_relais_arrivee']): ?>
                        <form method="POST" action="terminer_segment.php" class="d-inline">
                          <input type="hidden" name="id_segment" value="<?= $segment['id'] ?>">
                          <input type="hidden" name="type_livraison" value="point_relais">
                          <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Confirmer la livraison au point relais? Vous serez libéré de ce segment.')">
                            <i class="fas fa-store me-1"></i> Livrer au point relais
                          </button>
                        </form>
                      <?php else: ?>
                        <form method="POST" action="terminer_segment.php" class="d-inline">
                          <input type="hidden" name="id_segment" value="<?= $segment['id'] ?>">
                          <input type="hidden" name="type_livraison" value="finale">
                          <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirmer la livraison finale?')">
                            <i class="fas fa-check me-1"></i> Livrer à destination
                          </button>
                        </form>
                      <?php endif; ?>
                    <?php elseif ($segment['statut'] === 'en attente'): ?>
                      <span class="badge bg-warning">En attente de démarrage</span>
                    <?php elseif ($segment['statut'] === 'en point relais'): ?>
                      <span class="badge bg-info">Livré au point relais</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <?php if (!empty($historique_segments)): ?>
        <hr class="my-4">
        <h2 class="mb-4">Segments récemment livrés en points relais</h2>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Annonce</th>
                <th>De</th>
                <th>Livré au point relais</th>
                <th>Date de livraison</th>
                <th>État actuel</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($historique_segments as $historique): ?>
                <tr>
                  <td><?= htmlspecialchars($historique['titre']) ?></td>
                  <td><?= htmlspecialchars($historique['adresse_depart']) ?></td>
                  <td>
                    <i class="fas fa-store text-primary me-1"></i>
                    <?= htmlspecialchars($historique['point_relais_nom'] . ' - ' . $historique['point_relais_ville']) ?>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($historique['date_fin'])) ?></td>
                  <td>
                    <span class="badge bg-info">
                      <i class="fas fa-store me-1"></i> En attente de récupération
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      
      <hr class="my-4">
      
      <h2 class="mb-4">Mes livraisons complètes</h2>
      
      <?php if (empty($livraisons)): ?>
        <p class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          Vous n'avez aucune livraison complète en cours.
          <a href="annonces_dispo.php" class="alert-link">Voir les annonces disponibles</a>
        </p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Titre</th>
                <th>Client</th>
                <th>De</th>
                <th>À</th>
                <th>Date prise en charge</th>
                <th>Statut</th>
                <th>Validation client</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($livraisons as $livraison): 
                $statut = strtolower($livraison['statut']);
                $couleur = match($statut) {
                  'livrée' => '<i class="fas fa-check-circle text-success"></i>',
                  'en cours' => '<i class="fas fa-truck text-warning"></i>',
                  'en attente' => '<i class="fas fa-clock text-info"></i>',
                  'annulée' => '<i class="fas fa-times-circle text-danger"></i>',
                  default => '<i class="fas fa-question-circle text-secondary"></i>',
                };
              ?>
                <tr>
                  <td><?= htmlspecialchars($livraison['titre']) ?></td>
                  <td><?= htmlspecialchars($livraison['nom_client']) ?></td>
                  <td><?= htmlspecialchars($livraison['ville_depart']) ?></td>
                  <td><?= htmlspecialchars($livraison['ville_arrivee']) ?></td>
                  <td><?= $livraison['date_prise_en_charge'] ? date('d/m/Y', strtotime($livraison['date_prise_en_charge'])) : '-' ?></td>
                  <td><?= $couleur . ' ' . htmlspecialchars($livraison['statut']) ?></td>
                  <td>
                    <?php if ($livraison['validation_client'] == 1): ?>
                      <span class="badge bg-success">Validé</span>
                    <?php else: ?>
                      <span class="badge bg-warning">En attente</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="livraisons.php?id=<?= $livraison['id_annonce'] ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye me-1"></i> Détails
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      
      <div class="mt-4">
        <a href="annonces_dispo.php" class="btn btn-primary">
          <i class="fas fa-bullhorn me-1"></i> Voir annonces disponibles
        </a>
        <a href="segments_disponibles.php" class="btn btn-warning ms-2">
          <i class="fas fa-box me-1"></i> Voir segments disponibles
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>