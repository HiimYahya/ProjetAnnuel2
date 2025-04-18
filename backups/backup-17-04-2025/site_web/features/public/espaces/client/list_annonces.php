<?php
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

// Récupérer toutes les annonces avec les infos client
$stmt = $conn->prepare("
    SELECT a.*, u.nom AS nom_client
    FROM annonces a
    JOIN utilisateurs u ON a.id_client = u.id
    WHERE a.statut = 'en attente'
    ORDER BY a.date_annonce DESC
");
$stmt->execute();
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    header('Location: ../../../../public/login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes annonces</title>
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

<?php include '../../../../fonctions/header_client.php'; ?>

<body class="d-flex flex-column min-vh-100">

  <main class="flex-grow-1">
    <div class="container py-4">
      <h2 class="mb-4 text-start">Toutes les annonces</h2>

      <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($annonces as $annonce): 
          $statut = strtolower($annonce['statut']);
          $dot = match($statut) {
              'livrée' ,'en attente' => '🟢',
              'prise en charge', 'en cours' => '🟠',
              'annulée' => '🔴',
              default => '⚪'
          };
        ?>
          <div class="col">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <strong class="d-inline-block mb-2 text-primary"><?= htmlspecialchars($annonce['nom_client']) ?></strong>
                <h5 class="card-title"><?= htmlspecialchars($annonce['titre']) ?></h5>
                <p class="card-text">
                  <strong>De :</strong> <?= htmlspecialchars($annonce['ville_depart']) ?><br>
                  <strong>À :</strong> <?= htmlspecialchars($annonce['ville_arrivee']) ?><br>
                  <strong>Prix :</strong> <?= $annonce['prix'] ? number_format($annonce['prix'], 2) . " €" : '-' ?><br>
                  <strong>Date livraison :</strong> <?= htmlspecialchars($annonce['date_livraison_souhaitee'] ?? '-') ?><br>
                  <strong>Expiration :</strong> <?= htmlspecialchars($annonce['date_expiration'] ?? '-') ?><br>
                  <strong>Description :</strong> <?= nl2br(htmlspecialchars($annonce['description'])) ?>
                </p>
                <p class="card-text">
                  <small class="text-muted">
                    Statut : <span class="statut-dot"><?= $dot ?></span> <?= htmlspecialchars($annonce['statut']) ?>
                  </small>
                </p>
                <a href="livraisons.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-outline-primary">Suivi</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <?php include '../../../../fonctions/footer.php'; ?>
  
</body>
</html>
