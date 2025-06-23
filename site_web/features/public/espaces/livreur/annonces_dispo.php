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
            <div class="col-md-4">
              <label for="search" class="form-label">Recherche</label>
              <input type="text" class="form-control" id="search" name="search" placeholder="Titre, ville, client...">
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
    const searchInput = document.getElementById('search');
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
        search: searchInput.value,
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
            html += `<div class="col"><div class="card h-100 shadow-sm"><div class="card-header d-flex justify-content-between align-items-center bg-white"><h5 class="card-title mb-0">${annonce.titre || ''}</h5><span class="badge bg-primary">${annonce.prix ? Number(annonce.prix).toFixed(2) + ' €' : 'Prix non défini'}</span></div><div class="card-body"><div class="mb-3"><div class="d-flex align-items-center mb-2"><span class="me-2 fw-bold">Client :</span><span>${annonce.nom_client || ''}</span></div><div class="d-flex justify-content-between mb-2"><div><i class="fas fa-map-marker-alt text-danger me-1"></i><span class="fw-bold">De :</span></div><span class="text-truncate">${annonce.ville_depart || ''}</span></div><div class="d-flex justify-content-between mb-2"><div><i class="fas fa-map-marker-alt text-success me-1"></i><span class="fw-bold">À :</span></div><span class="text-truncate">${annonce.ville_arrivee || ''}</span></div><div class="d-flex justify-content-between mb-3"><div><i class="fas fa-calendar-alt me-1"></i><span class="fw-bold">Livraison souhaitée :</span></div><span class="${urgence}">${annonce.date_livraison_souhaitee ? new Date(annonce.date_livraison_souhaitee).toLocaleDateString('fr-FR') : 'Non précisée'} ${jours_restants}</span></div><div class="mb-3"><strong>Description :</strong><p class="mb-0">${(annonce.description || '').length > 150 ? annonce.description.substring(0, 150) + '...' : annonce.description || ''}</p></div></div><div class="d-flex align-items-center mb-3"><span class="badge bg-secondary me-2">Statut:</span><span>${dot} ${annonce.statut || ''}</span></div><div class="d-flex flex-column gap-2"><button type="button" class="btn btn-success w-100 btn-accepter-livraison" data-id="${annonce.id}"><i class="fas fa-check me-1"></i> Accepter la livraison</button><a href="livraisons.php?id=${annonce.id}" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i> Détails</a></div>`;
            if (annonce.segmentation_possible == 1) {
              html += `<hr><h6 class="mb-3"><i class="fas fa-cut me-1"></i> Proposer un segment</h6>
              <div class="input-group mb-2">
                <select class="form-select form-select-sm select-point-relais" data-id-annonce="${annonce.id}" required>
                  <option value="">Choisir un point relais...</option>`;
              pointsRelais.forEach(point => {
                html += `<option value="${point.id}">${point.nom} - ${point.ville}</option>`;
              });
              html += `</select>
                <button type="button" class="btn btn-warning btn-proposer-segment" data-id-annonce="${annonce.id}"><i class="fas fa-cut me-1"></i> Proposer un segment</button>
              </div>`;
            } else {
              html += `<div class="alert alert-light mt-3 mb-0"><i class="fas fa-info-circle me-1"></i> La segmentation n'est pas possible pour cette annonce.</div>`;
            }
            html += `</div></div></div>`;
          });
          html += '</div>';
          annoncesList.innerHTML = html;
          setTimeout(() => {
            document.querySelectorAll('.btn-accepter-livraison').forEach(btn => {
              btn.addEventListener('click', function () {
                const id_annonce = this.getAttribute('data-id');
                this.disabled = true;
                fetch('http://localhost/site_web/api/livreur/livraisons/post.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'same-origin',
                  body: JSON.stringify({ id_annonce })
                })
                .then(r => r.json())
                .then(result => {
                  if (result.success) {
                    messageAnnonces.innerHTML = '<div class="alert alert-success">Livraison acceptée !</div>';
                    chargerAnnonces();
                  } else {
                    messageAnnonces.innerHTML = '<div class="alert alert-danger">' + (result.error || 'Erreur inconnue') + '</div>';
                    this.disabled = false;
                  }
                })
                .catch(e => {
                  messageAnnonces.innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
                  this.disabled = false;
                });
              });
            });
            // JS pour proposer un segment
            document.querySelectorAll('.btn-proposer-segment').forEach(btn => {
              btn.addEventListener('click', function () {
                const id_annonce = this.getAttribute('data-id-annonce');
                const select = this.parentElement.querySelector('.select-point-relais');
                const id_point_relais = select.value;
                if (!id_point_relais) {
                  messageAnnonces.innerHTML = '<div class="alert alert-warning">Veuillez choisir un point relais.</div>';
                  return;
                }
                this.disabled = true;
                fetch('http://localhost/site_web/api/livreur/livraisons/segment.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'same-origin',
                  body: JSON.stringify({ id_annonce, point_relais_arrivee: id_point_relais })
                })
                .then(r => r.json())
                .then(result => {
                  if (result.success) {
                    messageAnnonces.innerHTML = '<div class="alert alert-success">Segment proposé avec succès !</div>';
                    chargerAnnonces();
                  } else {
                    messageAnnonces.innerHTML = '<div class="alert alert-danger">' + (result.error || 'Erreur inconnue') + '</div>';
                    this.disabled = false;
                  }
                })
                .catch(e => {
                  messageAnnonces.innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
                  this.disabled = false;
                });
              });
            });
          }, 100);
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
      document.getElementById('search').value = '';
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
