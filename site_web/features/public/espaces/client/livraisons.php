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
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
<main class="flex-grow-1 container py-4">
    <h2 class="mb-4">Suivi de la livraison</h2>
    <div id="annonce-info" class="mb-4"></div>
    <div id="livraison-info">Chargement...</div>
    <hr>
    <h2>Itinéraire entre :</h2>
    <ul id="itineraire-adresses"></ul>
    <div id="map" class="mb-4" style="height: 500px;"></div>
    <div id="itineraire-info" class="alert alert-info" style="display: none;"><strong>Chargement de l'itinéraire...</strong></div>
    <hr>
    <h3>Segments de livraison</h3>
    <div id="segments-table"></div>
</main>
<?php include '../../../../fonctions/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../../assets/js/darkmode.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const id_annonce = <?php echo json_encode($id_annonce); ?>;
function statutCouleur(statut) {
  statut = (statut||'').toLowerCase();
  if (statut === 'livrée' || statut === 'livré') return '🟢';
  if (statut === 'en attente' || statut === 'prise en charge' || statut === 'en cours') return '🟠';
  if (statut === 'annulée' || statut === 'annulé') return '🔴';
  if (statut === 'en point relais') return '🟡';
  return '⚪';
}
function afficherLivraison(data) {
  // Nouvelle section : infos annonce
  const annonceInfo = document.getElementById('annonce-info');
  if (!data || !data.annonce) {
    annonceInfo.innerHTML = '<div class="alert alert-warning">Aucune information sur la livraison.</div>';
  } else {
    const a = data.annonce;
    annonceInfo.innerHTML = `
      <div class="card mb-3">
        <div class="card-header bg-primary text-white"><strong>Informations de la livraison</strong></div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li><strong>Titre :</strong> ${a.titre || ''}</li>
            <li><strong>Adresse de départ :</strong> ${a.ville_depart || ''}</li>
            <li><strong>Adresse d'arrivée :</strong> ${a.ville_arrivee || ''}</li>
            <li><strong>Dimensions (H x L x l) :</strong> ${a.hauteur || '-'} x ${a.longueur || '-'} x ${a.largeur || '-'} cm</li>
            <li><strong>Date de livraison souhaitée :</strong> ${a.date_livraison_souhaitee || ''}</li>
            <li><strong>Prix :</strong> ${a.prix ? a.prix + ' €' : ''}</li>
            <li><strong>Description :</strong><br><span>${(a.description || '').replace(/\n/g, '<br>')}</span></li>
          </ul>
        </div>
      </div>
    `;
  }

  // Ancienne logique : suivi livraison
  const info = document.getElementById('livraison-info');
  if (!data || !data.annonce) {
    info.innerHTML = 'Aucune information.';
    return;
  }
  let html = '';
  if (data.livraison) {
    const statut = statutCouleur(data.livraison.statut);
    html += `<ul>
      <li><strong>Livreur :</strong> ${data.livraison.nom_livreur ? data.livraison.nom_livreur : 'Non attribué'}</li>
      <li><strong>Statut :</strong> ${statut} ${data.livraison.statut || ''}</li>
      <li><strong>Date prise en charge :</strong> ${data.livraison.date_prise_en_charge || ''}</li>
      <li><strong>Date de livraison :</strong> ${data.livraison.date_livraison || ''}</li>
    </ul>`;
  } else {
    html += '<p>Pas encore pris en charge.</p>';
  }
  info.innerHTML = html;

  // Itinéraire adresses
  document.getElementById('itineraire-adresses').innerHTML = `
    <li><strong>Départ :</strong> ${data.annonce.ville_depart || ''}</li>
    <li><strong>Arrivée :</strong> ${data.annonce.ville_arrivee || ''}</li>
  `;

  // Segments
  const segmentsDiv = document.getElementById('segments-table');
  if (data.segments && data.segments.length) {
    let segHtml = `<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr>
      <th>Départ</th><th>Arrivée</th><th>Livreur</th><th>Statut</th><th>Point relais départ</th><th>Point relais arrivée</th>
    </tr></thead><tbody>`;
    data.segments.forEach(seg => {
      const couleur = statutCouleur(seg.statut);
      segHtml += `<tr>
        <td>${seg.adresse_depart || ''}</td>
        <td>${seg.adresse_arrivee || ''}</td>
        <td>${seg.nom_livreur || 'Non attribué'}</td>
        <td>${couleur} ${seg.statut || ''}</td>
        <td>${seg.point_relais_depart_nom ? seg.point_relais_depart_nom + ' - ' + seg.point_relais_depart_ville : '-'}</td>
        <td>${seg.point_relais_arrivee_nom ? seg.point_relais_arrivee_nom + ' - ' + seg.point_relais_arrivee_ville : '-'}</td>
      </tr>`;
    });
    segHtml += '</tbody></table></div>';
    segmentsDiv.innerHTML = segHtml;
  } else {
    segmentsDiv.innerHTML = '<div class="alert alert-info">Aucun segment n\'a encore été créé pour cette livraison.</div>';
  }

  // Carte et itinéraire
  afficherCarte(data.annonce.ville_depart, data.annonce.ville_arrivee);
}

function afficherCarte(adresseDepart, adresseArrivee) {
  if (!adresseDepart || !adresseArrivee) return;
  const map = L.map('map').setView([48.8566, 2.3522], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  async function geocode(adresse) {
    const response = await fetch(`https://api.openrouteservice.org/geocode/search?api_key=5b3ce3597851110001cf6248006b7548fe324855a5ceb0cb4f691c37&text=${encodeURIComponent(adresse)}`);
    const data = await response.json();
    return data.features[0].geometry.coordinates.reverse();
  }

  async function tracerItineraire() {
    try {
      const depart = await geocode(adresseDepart);
      const arrivee = await geocode(adresseArrivee);
      map.setView(depart, 8);
      L.marker(depart).addTo(map).bindPopup("Départ");
      L.marker(arrivee).addTo(map).bindPopup("Arrivée");
      const response = await fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
        method: "POST",
        headers: {
          "Authorization": "5b3ce3597851110001cf6248006b7548fe324855a5ceb0cb4f691c37",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ coordinates: [[depart[1], depart[0]], [arrivee[1], arrivee[0]]] }),
      });
      const data = await response.json();
      L.geoJSON(data).addTo(map);
      const distance = data.features[0].properties.summary.distance / 1000;
      const duration = data.features[0].properties.summary.duration / 60;
      const infoDiv = document.getElementById('itineraire-info');
      infoDiv.style.display = 'block';
      infoDiv.innerHTML = `
        <strong>Itinéraire :</strong><br>
        Distance : <span class="text-primary">${distance.toFixed(2)} km</span><br>
        Durée estimée : <span class="text-primary">${duration.toFixed(0)} min</span>
      `;
    } catch (err) {
      console.error(err);
      const infoDiv = document.getElementById('itineraire-info');
      infoDiv.classList.replace('alert-info', 'alert-danger');
      infoDiv.textContent = "Erreur lors du calcul de l'itinéraire.";
      infoDiv.style.display = 'block';
    }
  }
  tracerItineraire();
}

function chargerLivraison() {
  fetch('/site_web/api/client/livraisons/get.php?id=' + id_annonce, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      afficherLivraison(data);
    })
    .catch(e => {
      document.getElementById('livraison-info').innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
    });
}

chargerLivraison();
</script>
</body>
</html>
