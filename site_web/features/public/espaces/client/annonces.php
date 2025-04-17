<?php

require_once '../../../../fonctions/db.php';

$conn = getConnexion();

session_start();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
  header('Location: ../../../../public/login.php');
  exit;
}

$id_client = $_SESSION['utilisateur']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $adresse_depart = $_POST['adresse_depart'] ?? '';
    $adresse_arrivee = $_POST['adresse_arrivee'] ?? '';
    $taille = $_POST['taille'] ?? 0;
    $prix = $_POST['prix'] ?? 0;
    $date_livraison = $_POST['date_livraison'] ?? null;
    $date_expiration = $_POST['date_expiration'] ?? null;

    $stmt = $conn->prepare("INSERT INTO annonces 
        (id_client, titre, description, ville_depart, ville_arrivee, taille, prix, date_livraison_souhaitee, date_expiration, date_annonce, statut) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'en attente')");
    $stmt->execute([$id_client, $titre, $description, $adresse_depart, $adresse_arrivee, $taille, $prix, $date_livraison, $date_expiration]);

    header("Location: annonces.php?success=1");
    exit;
    
}

$stmt = $conn->prepare("SELECT * FROM annonces WHERE id_client = ? ORDER BY date_annonce DESC");
$stmt->execute([$id_client]);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes annonces</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include '../../../../fonctions/header_client.php'; ?>

<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Créer une annonce</h2>

    <form method="POST" class="mb-5">
      <div class="mb-3">
        <label class="form-label">Titre</label>
        <input type="text" name="titre" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Adresse de départ</label>
        <input type="text" name="adresse_depart" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Adresse d'arrivée</label>
        <input type="text" name="adresse_arrivee" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Taille (en cm, kg...)</label>
        <input type="number" name="taille" class="form-control" min="0" step="any" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Date de livraison souhaitée</label>
        <input type="date" name="date_livraison" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Prix du transport (€)</label>
        <input type="number" name="prix" class="form-control" min="0" step="0.01" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Date d'expiration de l'annonce</label>
        <input type="date" name="date_expiration" class="form-control" required>
      </div>

      <button class="btn btn-primary">Publier l'annonce</button>
    </form>

    <h2 class="mb-4">Mes annonces</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Titre</th>
          <th>Départ</th>
          <th>Arrivée</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($annonces as $annonce): ?>
        <?php
          $statut = strtolower($annonce['statut']);
          $couleur = match($statut) {
              'livrée' => '🟢',
              'en attente', 'prise en charge' => '🟠',
              'annulée' => '🔴',
              default => '⚪',
          };
        ?>
        <tr>
            <td><?= htmlspecialchars($annonce['titre']) ?></td>
            <td><?= htmlspecialchars($annonce['ville_depart']) ?></td>
            <td><?= htmlspecialchars($annonce['ville_arrivee']) ?></td>
            <td><?= $couleur . ' ' . htmlspecialchars($annonce['statut']) ?></td>
            <td>
                <a href="livraisons.php?id=<?= $annonce['id'] ?>" class="btn btn-info btn-sm">Suivi</a>
                <a href="../../../modify/delete.php?table=annonces&id=<?= $annonce['id'] ?>" 
                class="btn btn-danger btn-sm"
                onclick="return confirm('Confirmer la suppression de cette annonce ?')">
                Supprimer
                </a>
            </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <?php include '../../../../fonctions/footer.php'; ?>

</body>
</html>
