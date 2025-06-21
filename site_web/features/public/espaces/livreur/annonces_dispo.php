<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: /site_web/features/public/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
  <meta charset="UTF-8">
  <title>Annonces disponibles - EcoDeli</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include '../../../../fonctions/header_livreur.php'; ?>
  <div class="container-fluid">
    <div class="page-title">
      <h1>Annonces disponibles</h1>
    </div>
    <div class="page-content">
      <!-- Card pour filtres de recherche -->
      <div class="card mb-4">
        <div class="card-body">
          <form id="form-filtres" class="row g-3">
            <div class="col-md-3">
              <label for="ville" class="form-label">Ville</label>
              <select class="form-select" id="ville" name="ville">
                <option value="">Toutes les villes</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="prix_min" class="form-label">Prix min (€)</label>
              <input type="number" class="form-control" id="prix_min" name="prix_min" min="0" step="0.01">
            </div>
            <div class="col-md-2">
              <label for="prix_max" class="form-label">Prix max (€)</label>
              <input type="number" class="form-control" id="prix_max" name="prix_max" min="0" step="0.01">
            </div>
            <div class="col-md-2">
              <label for="tri" class="form-label">Trier par</label>
              <select class="form-select" id="tri" name="tri">
                <option value="recent">Plus récent</option>
                <option value="prix_asc">Prix croissant</option>
                <option value="prix_desc">Prix décroissant</option>
                <option value="date_livraison">Date de livraison</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <div class="d-grid gap-2 w-100">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-2"></i>Filtrer
                </button>
                <button type="button" id="btn-reset" class="btn btn-outline-secondary">
                  <i class="fas fa-undo me-2"></i>Réinitialiser
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div id="message-annonces"></div>
      <div id="annonces-list"></div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const formFiltres = document.getElementById('form-filtres');
    const villeSelect = document.getElementById('ville');
    const triSelect = document.getElementById('tri');
    const prixMinInput = document.getElementById('prix_min');
    const prixMaxInput = document.getElementById('prix_max');
    const annoncesList = document.getElementById('annonces-list');
    const messageAnnonces = document.getElementById('message-annonces');
    const btnReset = document.getElementById('btn-reset');
    let pointsRelais = [];

    function chargerVilles() {
      fetch('../../../../api/livreur/villes/get.php')
        .then(r => r.json())
        .then(data => {
          villeSelect.innerHTML = '<option value="">Toutes les villes</option>';
          if (data.villes && Array.isArray(data.villes)) {
            data.villes.forEach(ville => {
              villeSelect.innerHTML += `<option value="${ville}">${ville}</option>`;
            });
          }
        });
    }

    function chargerPointsRelais() {
      fetch('../../../../api/livreur/points_relais/get.php')
        .then(r => r.json())
        .then(data => {
          if (data.points_relais && Array.isArray(data.points_relais)) {
            pointsRelais = data.points_relais;
          }
        });
    }

    function chargerAnnonces() {
      annoncesList.innerHTML = '<div class="text-center">Chargement...</div>';
      messageAnnonces.innerHTML = '';
      const params = new URLSearchParams({
        ville: villeSelect.value,
        prix_min: prixMinInput.value,
        prix_max: prixMaxInput.value,
        tri: triSelect.value
      });
      fetch('../../../../api/livreur/annonces/get.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
          annoncesList.innerHTML = '';
          if (!data.annonces || !Array.isArray(data.annonces)) {
            messageAnnonces.innerHTML = '<div class="alert alert-danger">Erreur de format de données</div>';
            return;
          }
          if (!data.annonces.length) {
            annoncesList.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> Aucune annonce disponible correspondant à vos critères.</div>';
            return;
          }
          let html = '<div class="row row-cols-1 row-cols-md-2 g-4">';
          data.annonces.forEach(annonce => {
            const statut = (annonce.statut || '').toLowerCase();
            let dot = '<i class="fas fa-question-circle text-secondary"></i>';
            if (statut === 'livrée') dot = '<i class="fas fa-check-circle text-success"></i>';
            else if (statut === 'prise en charge' || statut === 'en cours') dot = '<i class="fas fa-truck text-warning"></i>';
            else if (statut === 'en attente') dot = '<i class="fas fa-clock text-info"></i>';
            else if (statut === 'annulée') dot = '<i class="fas fa-times-circle text-danger"></i>';
            let jours_restants = '';
            let urgence = '';
            if (annonce.date_livraison_souhaitee) {
              const dateLiv = new Date(annonce.date_livraison_souhaitee);
              const now = new Date();
              const diff = Math.ceil((dateLiv - now) / (1000*60*60*24));
              jours_restants = isNaN(diff) ? '' : `(${diff} jours)`;
              if (!isNaN(diff)) {
                urgence = diff <= 3 ? 'text-danger fw-bold' : (diff <= 7 ? 'text-warning' : 'text-success');
              }
            }
            html += `<div class="col"><div class="card h-100 shadow-sm"><div class="card-header d-flex justify-content-between align-items-center bg-white"><h5 class="card-title mb-0">${annonce.titre || ''}</h5><span class="badge bg-primary">${annonce.prix ? Number(annonce.prix).toFixed(2) + ' €' : 'Prix non défini'}</span></div><div class="card-body"><div class="mb-3"><div class="d-flex align-items-center mb-2"><span class="me-2 fw-bold">Client :</span><span>${annonce.nom_client || ''}</span></div><div class="d-flex justify-content-between mb-2"><div><i class="fas fa-map-marker-alt text-danger me-1"></i><span class="fw-bold">De :</span></div><span class="text-truncate">${annonce.ville_depart || ''}</span></div><div class="d-flex justify-content-between mb-2"><div><i class="fas fa-map-marker-alt text-success me-1"></i><span class="fw-bold">À :</span></div><span class="text-truncate">${annonce.ville_arrivee || ''}</span></div><div class="d-flex justify-content-between mb-3"><div><i class="fas fa-calendar-alt me-1"></i><span class="fw-bold">Livraison souhaitée :</span></div><span class="${urgence}">${annonce.date_livraison_souhaitee ? new Date(annonce.date_livraison_souhaitee).toLocaleDateString('fr-FR') : 'Non précisée'} ${jours_restants}</span></div><div class="mb-3"><strong>Description :</strong><p class="mb-0">${(annonce.description || '').length > 150 ? annonce.description.substring(0, 150) + '...' : annonce.description || ''}</p></div></div><div class="d-flex align-items-center mb-3"><span class="badge bg-secondary me-2">Statut:</span><span>${dot} ${annonce.statut || ''}</span></div><div class="d-flex flex-column gap-2"><form method="POST" action="accepter_annonce.php"><input type="hidden" name="id_annonce" value="${annonce.id}"><button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-1"></i> Accepter la livraison</button></form><a href="livraisons.php?id=${annonce.id}" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i> Détails</a></div>`;
            if (annonce.segmentation_possible == 1) {
              html += `<hr><h6 class="mb-3"><i class="fas fa-cut me-1"></i> Proposer un segment</h6><form method="POST" action="proposer_segment.php"><input type="hidden" name="id_annonce" value="${annonce.id}"><input type="hidden" name="point_relais_depart" value="origine"><input type="hidden" name="segment_depart" value="${annonce.ville_depart}"><input type="hidden" name="segment_arrivee" value="${annonce.ville_arrivee}"><div class="mb-3"><label class="form-label">Point de départ</label><input type="text" class="form-control form-control-sm" value="${annonce.ville_depart}" disabled></div><div class="mb-3"><label class="form-label">Point d'arrivée</label><select name="point_relais_arrivee" class="form-select form-select-sm" required><option value="destination">Adresse de destination: ${annonce.ville_arrivee}</option>`;
              pointsRelais.forEach(point => {
                html += `<option value="${point.id}">${point.nom} - ${point.ville}</option>`;
              });
              html += `</select></div><button type="submit" class="btn btn-warning w-100"><i class="fas fa-cut me-1"></i> Proposer un segment</button></form>`;
            } else {
              html += `<div class="alert alert-light mt-3 mb-0"><i class="fas fa-info-circle me-1"></i> La segmentation n'est pas possible pour cette annonce.</div>`;
            }
            html += `</div></div></div>`;
          });
          html += '</div>';
          annoncesList.innerHTML = html;
        })
        .catch(e => {
          annoncesList.innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
        });
    }

    formFiltres.addEventListener('submit', function (e) {
      e.preventDefault();
      chargerAnnonces();
    });
    btnReset.addEventListener('click', function () {
      villeSelect.value = '';
      prixMinInput.value = '';
      prixMaxInput.value = '';
      triSelect.value = 'recent';
      chargerAnnonces();
    });

    // Initialisation
    chargerVilles();
    chargerPointsRelais();
    chargerAnnonces();
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
