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
  <title>Profil client</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Mon profil</h2>
    <div id="profil-info">Chargement...</div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  function chargerProfil() {
    fetch('/api/client/profile/get.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        const div = document.getElementById('profil-info');
        if (!data || !data.nom) {
          div.innerHTML = 'Aucune information.';
          return;
        }
        div.innerHTML = `<b>${data.nom} ${data.prenom || ''}</b><br>Email : ${data.email}<br>Téléphone : ${data.telephone || '-'}<br>Adresse : ${data.adresse || '-'}<br>Date d'inscription : ${data.date_inscription || '-'}`;
      });
  }
  chargerProfil();
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
