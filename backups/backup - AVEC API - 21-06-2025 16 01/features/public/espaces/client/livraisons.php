<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
  header('Location: ../../../../public/login.php');
  exit;
}
$id_annonce = isset($_GET['id']) ? intval($_GET['id']) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suivi de la livraison</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Suivi de la livraison</h2>
    <div id="livraison-info" class="mb-4">Chargement...</div>
    <h3>Segments de livraison</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Départ</th>
          <th>Arrivée</th>
          <th>Livreur</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody id="segments-list">
        <tr><td colspan="4" class="text-center">Chargement...</td></tr>
      </tbody>
    </table>
    <button id="annuler-btn" class="btn btn-danger mt-3">Annuler la livraison</button>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  const id_annonce = <?php echo json_encode($id_annonce); ?>;
  function statutCouleur(statut) {
    statut = (statut||'').toLowerCase();
    if (statut === 'livrée') return '🟢';
    if (statut === 'en attente' || statut === 'prise en charge') return '🟠';
    if (statut === 'annulée') return '🔴';
    return '⚪';
  }
  function chargerLivraison() {
    fetch(`/api/client/livraisons/get.php?id=${id_annonce}`, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        const info = document.getElementById('livraison-info');
        if (!data || !data.annonce) {
          info.innerHTML = 'Aucune information.';
          return;
        }
        info.innerHTML = `<b>${data.annonce.titre}</b><br>Départ : ${data.annonce.ville_depart}<br>Arrivée : ${data.annonce.ville_arrivee}<br>Statut : ${statutCouleur(data.annonce.statut)} ${data.annonce.statut}`;
        const tbody = document.getElementById('segments-list');
        tbody.innerHTML = '';
        if (!data.segments || !data.segments.length) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center">Aucun segment</td></tr>';
          return;
        }
        data.segments.forEach(seg => {
          tbody.innerHTML += `<tr>
            <td>${seg.depart}</td>
            <td>${seg.arrivee}</td>
            <td>${seg.livreur || '-'}</td>
            <td>${statutCouleur(seg.statut)} ${seg.statut}</td>
          </tr>`;
        });
      });
  }
  document.getElementById('annuler-btn').onclick = function() {
    if (!confirm('Confirmer l\'annulation de la livraison ?')) return;
    fetch(`/api/client/livraisons/annuler.php?id=${id_annonce}`, { method: 'POST', credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => { if (res.success) { alert('Livraison annulée'); location.reload(); } else alert('Erreur annulation'); });
  };
  chargerLivraison();
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
