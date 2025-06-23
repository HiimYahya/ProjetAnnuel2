<?php
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
  <title>Dashboard client</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Tableau de bord</h2>
    <div id="dashboard-stats">Chargement...</div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  fetch('/api/client/dashboard/get.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      const div = document.getElementById('dashboard-stats');
      if (!data) { div.innerHTML = 'Aucune donnée.'; return; }
      div.innerHTML = `
        <ul>
          <li>Annonces créées : ${data.annonces || 0}</li>
          <li>Livraisons en cours : ${data.livraisons_en_cours || 0}</li>
          <li>Livraisons livrées : ${data.livraisons_livrees || 0}</li>
        </ul>
      `;
    });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
