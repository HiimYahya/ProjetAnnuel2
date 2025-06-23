<?php
session_start();
include '../../../../fonctions/db.php';

$conn = getConnexion();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}

$id_annonce = $_GET['id'] ?? null;
if (!$id_annonce) {
    echo "ID annonce manquant.";
    exit;
}

// Récupérer la livraison avec nom du livreur
$stmt = $conn->prepare("
    SELECT l.*, u.nom AS nom_livreur
    FROM livraisons l
    LEFT JOIN utilisateurs u ON l.id_livreur = u.id
    WHERE l.id_annonce = ?
");
$stmt->execute([$id_annonce]);
$livraison = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer l'annonce
$stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$stmtAnnonce->execute([$id_annonce]);
$annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    echo "Annonce introuvable.";
    exit;
}

$adresse_depart = json_encode($annonce['ville_depart']);
$adresse_arrivee = json_encode($annonce['ville_arrivee']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi de la livraison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 500px; }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../../../../fonctions/header_client.php'; ?>

    <main class="flex-grow-1 container py-4">
        <h2 class="mb-4">Suivi de la livraison</h2>

        <?php if ($livraison): ?>
            <?php
                $statut = strtolower($livraison['statut']);
                $couleur = match($statut) {
                    'livrée' => '🟢',
                    'en attente', 'en cours' => '🟠',
                    'annulée' => '🔴',
                    default => '⚪',
                };
            ?>
            <ul>
                <li><strong>Livreur :</strong> <?= $livraison['nom_livreur'] ? htmlspecialchars($livraison['nom_livreur']) : 'Non attribué' ?></li>
                <li><strong>Statut :</strong> <?= $couleur . ' ' . htmlspecialchars($livraison['statut']) ?></li>
                <li><strong>Date prise en charge :</strong> <?= htmlspecialchars($livraison['date_prise_en_charge']) ?: '' ?></li>
                <li><strong>Date de livraison :</strong> <?= htmlspecialchars($livraison['date_livraison']) ?: '' ?></li>
                <br>
                <h3>Description de l'annonce :</h3>
                    <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
            </ul>
        <?php else: ?>
            <p>Pas encore pris en charge.</p>
        <?php endif; ?>

        <hr>
        <h2>Itinéraire entre :</h2>
            
            <ul>
                <li><strong>Départ :</strong> <?= htmlspecialchars($annonce['ville_depart']) ?></li>
                <li><strong>Arrivée :</strong> <?= htmlspecialchars($annonce['ville_arrivee']) ?></li>
            </ul>

        <div id="map" class="mb-4"></div>

        <div id="itineraire-info" class="alert alert-info" style="display: none;">
            <strong>Chargement de l'itinéraire...</strong>
        </div>

        <hr>
    </main>

    <?php include '../../../../fonctions/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../assets/js/darkmode.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const adresseDepart = <?= $adresse_depart ?>;
        const adresseArrivee = <?= $adresse_arrivee ?>;

        const map = L.map('map').setView([480.8566, 2.3522], 60);
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
                infoDiv.textContent = "Erreur lors du calcul de l’itinéraire.";
                infoDiv.style.display = 'block';
            }
        }

        tracerItineraire();
    </script>
</body>
</html>
