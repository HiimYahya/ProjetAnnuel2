<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}
$id_annonce = $_GET['id'] ?? null;
if (!$id_annonce) {
    echo "ID annonce manquant.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi de la livraison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>#map { height: 500px; }</style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include '../../../../fonctions/header_livreur.php'; ?>
<main class="flex-grow-1 container py-4">
    <h2 class="mb-4">Suivi de la livraison</h2>
    <div id="livraison-info"></div>
    <hr>
    <h2>Itinéraire entre :</h2>
    <ul>
        <li><strong>Départ :</strong> <span id="adresse_depart"></span></li>
        <li><strong>Arrivée :</strong> <span id="adresse_arrivee"></span></li>
    </ul>
    <div id="map" class="mb-4"></div>
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
document.addEventListener('DOMContentLoaded', function () {
  const id_annonce = <?php echo json_encode($id_annonce); ?>;
  const livraisonInfo = document.getElementById('livraison-info');
  const adresseDepartSpan = document.getElementById('adresse_depart');
  const adresseArriveeSpan = document.getElementById('adresse_arrivee');
  const segmentsTable = document.getElementById('segments-table');
  const itineraireInfo = document.getElementById('itineraire-info');
  let map;

  function statutCouleur(statut) {
    statut = (statut || '').toLowerCase();
    if (statut === 'livrée') return '🟢';
    if (statut === 'en attente' || statut === 'en cours') return '🟠';
    if (statut === 'annulée') return '🔴';
    return '⚪';
  }
  function statutCouleurSegment(statut) {
    statut = (statut || '').toLowerCase();
    if (statut === 'livré') return '🟢';
    if (statut === 'en cours') return '🟠';
    if (statut === 'en attente' || statut === 'en point relais') return '🟡';
    if (statut === 'annulé') return '🔴';
    return '⚪';
  }

  function afficherLivraison(data) {
    if (!data.livraison || !data.annonce) {
      livraisonInfo.innerHTML = '<div class="alert alert-danger">Livraison ou annonce introuvable.</div>';
      return;
    }
    const l = data.livraison;
    const a = data.annonce;
    const couleur = statutCouleur(l.statut);
    livraisonInfo.innerHTML = `<ul>
      <li><strong>Livreur :</strong> ${l.nom_livreur ? l.nom_livreur : 'Non attribué'}</li>
      <li><strong>Statut :</strong> ${couleur} ${l.statut}</li>
      <li><strong>Date prise en charge :</strong> ${l.date_prise_en_charge || ''}</li>
      <li><strong>Date de livraison :</strong> ${l.date_livraison || ''}</li>
      <br>
      <h3>Description de l'annonce :</h3>
      <p>${(a.description || '').replace(/\n/g, '<br>')}</p>
    </ul>`;
    adresseDepartSpan.textContent = a.ville_depart || '';
    adresseArriveeSpan.textContent = a.ville_arrivee || '';
    afficherSegments(data.segments);
    tracerItineraire(a.ville_depart, a.ville_arrivee);
  }

  function afficherSegments(segments) {
    if (!segments || !segments.length) {
      segmentsTable.innerHTML = '<div class="alert alert-info">Aucun segment n\'a encore été créé pour cette livraison.</div>';
      return;
    }
    let html = `<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Départ</th><th>Arrivée</th><th>Livreur</th><th>Statut</th><th>Point relais départ</th><th>Point relais arrivée</th></tr></thead><tbody>`;
    segments.forEach(segment => {
      html += `<tr><td>${segment.adresse_depart || ''}</td><td>${segment.adresse_arrivee || ''}</td><td>${segment.nom_livreur || 'Non attribué'}</td><td>${statutCouleurSegment(segment.statut)} ${segment.statut}</td><td>${segment.point_relais_depart_nom ? segment.point_relais_depart_nom + ' - ' + segment.point_relais_depart_ville : '-'}</td><td>${segment.point_relais_arrivee_nom ? segment.point_relais_arrivee_nom + ' - ' + segment.point_relais_arrivee_ville : '-'}</td></tr>`;
    });
    html += '</tbody></table></div>';
    segmentsTable.innerHTML = html;
  }

  async function geocode(adresse) {
    const response = await fetch(`https://api.openrouteservice.org/geocode/search?api_key=5b3ce3597851110001cf6248006b7548fe324855a5ceb0cb4f691c37&text=${encodeURIComponent(adresse)}`);
    const data = await response.json();
    return data.features[0].geometry.coordinates.reverse();
  }

  async function tracerItineraire(adresseDepart, adresseArrivee) {
    itineraireInfo.style.display = 'block';
    itineraireInfo.className = 'alert alert-info';
    itineraireInfo.innerHTML = "<strong>Chargement de l'itinéraire...</strong>";
    try {
      const depart = await geocode(adresseDepart);
      const arrivee = await geocode(adresseArrivee);
      if (!map) {
        map = L.map('map').setView(depart, 8);
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap'
        }).addTo(map);
      } else {
        map.setView(depart, 8);
      }
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
      itineraireInfo.className = 'alert alert-info';
      itineraireInfo.innerHTML = `<strong>Itinéraire :</strong><br>Distance : <span class="text-primary">${distance.toFixed(2)} km</span><br>Durée estimée : <span class="text-primary">${duration.toFixed(0)} min</span>`;
    } catch (err) {
      console.error(err);
      itineraireInfo.className = 'alert alert-danger';
      itineraireInfo.textContent = "Erreur lors du calcul de l'itinéraire.";
      itineraireInfo.style.display = 'block';
    }
  }

  // Chargement initial
  fetch('../../../../api/livreur/livraisons/detail.php?id=' + encodeURIComponent(id_annonce))
    .then(r => r.json())
    .then(data => {
      afficherLivraison(data);
    })
    .catch(e => {
      livraisonInfo.innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
    });
});
</script>
</body>
</html>
