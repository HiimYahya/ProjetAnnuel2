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
  <title>Mes prestations</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Mes prestations</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Description</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody id="prestations-list">
        <tr><td colspan="2" class="text-center">Chargement...</td></tr>
      </tbody>
    </table>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  function chargerPrestations() {
    fetch('/api/client/prestations/get.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        const tbody = document.getElementById('prestations-list');
        tbody.innerHTML = '';
        if (!Array.isArray(data) || !data.length) {
          tbody.innerHTML = '<tr><td colspan="2" class="text-center">Aucune prestation</td></tr>';
          return;
        }
        data.forEach(presta => {
          tbody.innerHTML += `<tr>
            <td>${presta.description || ''}</td>
            <td>${presta.statut || ''}</td>
          </tr>`;
        });
      });
  }
  chargerPrestations();
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
