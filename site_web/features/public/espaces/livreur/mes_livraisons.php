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
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>