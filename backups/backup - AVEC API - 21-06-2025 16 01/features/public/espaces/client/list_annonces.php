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
  <title>Liste des annonces</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Liste de mes annonces</h2>
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
      <tbody id="annonces-list">
        <tr><td colspan="5" class="text-center">Chargement...</td></tr>
      </tbody>
    </table>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  function statutCouleur(statut) {
    statut = (statut||'').toLowerCase();
    if (statut === 'livrée') return '🟢';
    if (statut === 'en attente' || statut === 'prise en charge') return '🟠';
    if (statut === 'annulée') return '🔴';
    return '⚪';
  }
  function chargerAnnonces() {
    fetch('../../../../api/client/annonces/get.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        const tbody = document.getElementById('annonces-list');
        tbody.innerHTML = '';
        if (!Array.isArray(data)) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Erreur de format de données</td></tr>';
          return;
        }
        if (!data.length) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center">Aucune annonce</td></tr>';
          return;
        }
        data.forEach(annonce => {
          tbody.innerHTML += `<tr>
            <td>${annonce.titre || ''}</td>
            <td>${annonce.ville_depart || ''}</td>
            <td>${annonce.ville_arrivee || ''}</td>
            <td>${statutCouleur(annonce.statut)} ${annonce.statut || ''}</td>
            <td>
              <a href="livraisons.php?id=${annonce.id}" class="btn btn-info btn-sm">Suivi</a>
              <button class="btn btn-danger btn-sm" onclick="supprimerAnnonce(${annonce.id})">Supprimer</button>
            </td>
          </tr>`;
        });
      })
      .catch(e => {
        document.getElementById('annonces-list').innerHTML = '<tr><td colspan="5" class="text-danger">Erreur JS : ' + e + '</td></tr>';
      });
  }
  function supprimerAnnonce(id) {
    if (!confirm('Confirmer la suppression de cette annonce ?')) return;
    fetch(`../../../../api/client/annonces/delete.php?id=${id}`, { method: 'DELETE', credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => { if (res.success) chargerAnnonces(); else alert('Erreur suppression'); });
  }
  chargerAnnonces();
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
