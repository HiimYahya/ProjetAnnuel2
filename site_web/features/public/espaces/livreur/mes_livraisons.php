<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}
$id_livreur = $_SESSION['utilisateur']['id'];
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
      <div id="segments_en_cours"></div>
      
      <?php if (!empty($historique_segments)): ?>
        <hr class="my-4">
        <h2 class="mb-4">Segments récemment livrés en points relais</h2>
        <div id="historique_segments"></div>
      <?php endif; ?>
      
      <hr class="my-4">
      
      <h2 class="mb-4">Mes livraisons complètes</h2>
      <div id="livraisons_completes"></div>
      
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
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // Segments en cours
    const segmentsContainer = document.getElementById('segments_en_cours');
    segmentsContainer.innerHTML = '<tr><td colspan="5" class="text-center">Chargement...</td></tr>';
    fetch('../../../../api/livreur/segments/get.php')
      .then(r => r.json())
      .then(data => {
        segmentsContainer.innerHTML = '';
        if (!data.segments || !Array.isArray(data.segments)) {
          segmentsContainer.innerHTML = '<div class="text-danger">Erreur de format de données</div>';
          return;
        }
        if (!data.segments.length) {
          segmentsContainer.innerHTML = `<p class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Vous n'avez aucun segment de livraison en cours.<a href="segments_disponibles.php" class="alert-link">Voir les segments disponibles</a></p>`;
          return;
        }
        let html = `<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr><th>Annonce</th><th>Client</th><th>Départ</th><th>Arrivée</th><th>Statut</th></tr></thead><tbody>`;
        data.segments.forEach(segment => {
          html += `<tr><td>${segment.titre}</td><td>${segment.nom_client}</td><td>${segment.lieu_depart}</td><td>${segment.lieu_arrivee}</td><td>${segment.statut}</td></tr>`;
        });
        html += `</tbody></table></div>`;
        segmentsContainer.innerHTML = html;
      })
      .catch(e => {
        segmentsContainer.innerHTML = '<div class="text-danger">Erreur JS : ' + e + '</div>';
      });

    // Historique segments
    const histoContainer = document.getElementById('historique_segments');
    if (histoContainer) {
      histoContainer.innerHTML = '<tr><td colspan="5" class="text-center">Chargement...</td></tr>';
      fetch('../../../../api/livreur/segments/historique.php')
        .then(r => r.json())
        .then(data => {
          histoContainer.innerHTML = '';
          if (!data.historique || !Array.isArray(data.historique) || !data.historique.length) return;
          let html = `<hr class="my-4"><h2 class="mb-4">Segments récemment livrés en points relais</h2><div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr><th>Annonce</th><th>De</th><th>Livré au point relais</th><th>Date de livraison</th><th>État actuel</th></tr></thead><tbody>`;
          data.historique.forEach(h => {
            html += `<tr><td>${h.titre}</td><td>${h.adresse_depart}</td><td><i class='fas fa-store text-primary me-1'></i>${h.point_relais_nom} - ${h.point_relais_ville}</td><td>${h.date_fin}</td><td><span class='badge bg-info'><i class='fas fa-store me-1'></i>En attente de récupération</span></td></tr>`;
          });
          html += `</tbody></table></div>`;
          histoContainer.innerHTML = html;
        })
        .catch(e => {
          histoContainer.innerHTML = '<div class="text-danger">Erreur JS : ' + e + '</div>';
        });
    }

    // Livraisons complètes
    const livraisonsContainer = document.getElementById('livraisons_completes');
    livraisonsContainer.innerHTML = '<tr><td colspan="7" class="text-center">Chargement...</td></tr>';
    fetch('../../../../api/livreur/livraisons/get.php')
      .then(r => r.json())
      .then(data => {
        livraisonsContainer.innerHTML = '';
        if (!data.livraisons || !Array.isArray(data.livraisons)) {
          livraisonsContainer.innerHTML = '<div class="text-danger">Erreur de format de données</div>';
          return;
        }
        if (!data.livraisons.length) {
          livraisonsContainer.innerHTML = `<p class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Vous n'avez aucune livraison complète en cours.<a href="annonces_dispo.php" class="alert-link">Voir les annonces disponibles</a></p>`;
          return;
        }
        let html = `<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr><th>Titre</th><th>Client</th><th>De</th><th>À</th><th>Date prise en charge</th><th>Statut</th><th>Validation client</th></tr></thead><tbody>`;
        data.livraisons.forEach(livraison => {
          html += `<tr><td>${livraison.titre}</td><td>${livraison.nom_client}</td><td>${livraison.ville_depart}</td><td>${livraison.ville_arrivee}</td><td>${livraison.date_prise_en_charge}</td><td>${livraison.statut}</td><td>${livraison.validation_client ? '<span class=\'badge bg-success\'>Validé</span>' : '<span class=\'badge bg-warning\'>En attente</span>'}</td></tr>`;
        });
        html += `</tbody></table></div>`;
        livraisonsContainer.innerHTML = html;
      })
      .catch(e => {
        livraisonsContainer.innerHTML = '<div class="text-danger">Erreur JS : ' + e + '</div>';
      });
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>