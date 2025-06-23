<?php
session_start();
require_once '../../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: /site_web/features/auth/login.php');
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];
$conn = getConnexion();

// Récupère les livraisons du livreur avec les infos des annonces et des clients
$stmt = $conn->prepare("
    SELECT l.*, a.titre, a.ville_depart, a.ville_arrivee, a.prix, a.description, a.date_livraison_souhaitee, a.date_expiration, u.nom AS nom_client
    FROM livraisons l
    JOIN annonces a ON l.id_annonce = a.id
    JOIN utilisateurs u ON a.id_client = u.id
    WHERE l.id_livreur = ?
    ORDER BY l.date_prise_en_charge DESC
");
$stmt->execute([$id_livreur]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes livraisons</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .card:hover {
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .statut-dot {
      font-size: 1.2rem;
      vertical-align: middle;
    }
  </style>
</head>

<?php include '../../../../fonctions/header_livreur.php'; ?>

<body class="d-flex flex-column min-vh-100">
<main class="flex-grow-1 container py-4">
  <h2 class="mb-4 text-start">Mes livraisons</h2>

  <div class="row row-cols-1 row-cols-md-2 g-4">
    <?php foreach ($livraisons as $livraison):
      $statut = strtolower($livraison['statut']);
      $dot = match($statut) {
          'livrée' => '🟢',
          'en attente', 'en cours' => '🟠',
          'annulée' => '🔴',
          default => '⚪'
      };
    ?>
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <strong class="d-inline-block mb-2 text-primary"><?= htmlspecialchars($livraison['nom_client']) ?></strong>
            <h5 class="card-title"><?= htmlspecialchars($livraison['titre']) ?></h5>
            <p class="card-text">
              <strong>De :</strong> <?= htmlspecialchars($livraison['ville_depart']) ?><br>
              <strong>À :</strong> <?= htmlspecialchars($livraison['ville_arrivee']) ?><br>
              <strong>Prix :</strong> <?= $livraison['prix'] ? number_format($livraison['prix'], 2) . " €" : '-' ?><br>
              <strong>Date livraison souhaitée :</strong> <?= htmlspecialchars($livraison['date_livraison_souhaitee'] ?? '-') ?><br>
              <strong>Expiration :</strong> <?= htmlspecialchars($livraison['date_expiration'] ?? '-') ?>
            </p>
            <p><?= nl2br(htmlspecialchars($livraison['description'])) ?></p>
            <p class="card-text">
              <small class="text-muted">
                Statut : <span class="statut-dot"><?= $dot ?></span> <?= htmlspecialchars($livraison['statut']) ?>
              </small>
            </p>
            <a href="livraisons.php?id=<?= $livraison['id_annonce'] ?>" class="btn btn-sm btn-outline-primary">Suivi</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../../assets/js/darkmode.js"></script>
<?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>