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
<<<<<<< HEAD
=======
    
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
    <div class="page-content">
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
<<<<<<< HEAD
=======
      
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
<<<<<<< HEAD
      <div class="row mb-4">
        <div class="col-12 mb-4">
          <h4>Livraisons prêtes à être livrées</h4>
          <div class="mb-3">
            <input type="text" id="search-livraisons" class="form-control" placeholder="Recherche (titre, client, ville...)">
          </div>
          <div id="table-pretes"></div>
        </div>
        <div class="col-12">
          <h4>Livraisons en attente de validation client</h4>
          <div id="table-attente-validation"></div>
        </div>
      </div>
      <div id="livraisons_fusionnees"></div>
      <div class="mt-4 mb-5">
=======
      
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
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
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
<<<<<<< HEAD
    const tableAttente = document.getElementById('table-attente-validation');
    const tablePretes = document.getElementById('table-pretes');
    const searchInput = document.getElementById('search-livraisons');
    let pretes = [];
    let attente = [];
    let autres = [];
    function renderPretes() {
      if (!pretes.length) {
        tablePretes.innerHTML = '<div class="alert alert-info">Aucune livraison prête à être livrée.</div>';
      } else {
        let search = (searchInput.value || '').toLowerCase();
        let html = `<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr>
          <th>Titre</th><th>Client</th><th>De</th><th>À</th><th>Date</th><th>Prix</th><th>Statut</th><th></th>
        </tr></thead><tbody>`;
        pretes.filter(l => l.livrable === true && (
          l.titre.toLowerCase().includes(search) ||
          l.nom_client.toLowerCase().includes(search) ||
          l.ville_depart.toLowerCase().includes(search) ||
          l.ville_arrivee.toLowerCase().includes(search)
        )).forEach(l => {
          html += `<tr>
            <td>${l.titre}</td>
            <td>${l.nom_client}</td>
            <td>${l.ville_depart}</td>
            <td>${l.ville_arrivee}</td>
            <td>${l.date_prise_en_charge || '-'}</td>
            <td>${l.prix !== undefined && l.prix !== null ? l.prix + ' €' : ''}</td>
            <td><span class="badge bg-success">Prête à livrer</span></td>
            <td>
              <a href="livraisons.php?id=${l.id_annonce}" class="btn btn-sm btn-info me-2">Détails</a>
            </td>
          </tr>`;
        });
        html += '</tbody></table></div>';
        tablePretes.innerHTML = html;
      }
    }
    fetch('../../../../api/livreur/livraisons/get.php')
      .then(r => r.json())
      .then(livraisonsData => {
        pretes = [];
        attente = [];
        autres = [];
        if (livraisonsData.livraisons && Array.isArray(livraisonsData.livraisons)) {
          livraisonsData.livraisons.forEach(livraison => {
            if (livraison.validation_client == 0) {
              attente.push(livraison);
            } else {
              if (livraison.statut === 'en cours') {
                pretes.push(livraison);
              } else {
                autres.push(livraison);
              }
            }
          });
        }
        // Tableau attente validation
        if (!attente.length) {
          tableAttente.innerHTML = '<div class="alert alert-success">Aucune livraison en attente de validation client.</div>';
        } else {
          let html = `<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr>
            <th>Titre</th><th>Client</th><th>De</th><th>À</th><th>Date</th><th>Prix</th><th>Statut</th><th></th>
          </tr></thead><tbody>`;
          attente.forEach(l => {
            html += `<tr>
              <td>${l.titre}</td>
              <td>${l.nom_client}</td>
              <td>${l.ville_depart}</td>
              <td>${l.ville_arrivee}</td>
              <td>${l.date_prise_en_charge || '-'}</td>
              <td>${l.prix !== undefined && l.prix !== null ? l.prix + ' €' : ''}</td>
              <td><span class="badge bg-warning">En attente validation client</span></td>
              <td><a href="livraisons.php?id=${l.id_annonce}" class="btn btn-sm btn-info">Détails</a></td>
            </tr>`;
          });
          html += '</tbody></table></div>';
          tableAttente.innerHTML = html;
        }
        renderPretes();
      })
      .catch(e => {
        container.innerHTML = '<div class="text-danger">Erreur JS : ' + e + '</div>';
      });
    searchInput.addEventListener('input', renderPretes);

    // Ajout du JS pour le bouton marquer comme livré
    document.querySelectorAll('.btn-marquer-livre').forEach(btn => {
      btn.addEventListener('click', function() {
        const idLivraison = this.getAttribute('data-id');
        this.disabled = true;
        fetch('../../../../api/livreur/livraisons/put.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_livraison: idLivraison })
        })
        .then(r => r.json())
        .then(result => {
          if (result.success) {
            this.textContent = 'Livrée !';
            this.classList.remove('btn-success');
            this.classList.add('btn-secondary');
          } else {
            alert(result.error || 'Erreur inconnue');
            this.disabled = false;
          }
        })
        .catch(e => {
          alert('Erreur JS : ' + e);
          this.disabled = false;
        });
      });
    });
=======
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
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>