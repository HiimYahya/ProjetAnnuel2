<?php
session_start();
include '../../fonctions/db.php'; 
include '../../fonctions/fonctions.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    header('Location: /site_web/features/public/login.php');
    exit;
}

$conn = getConnexion();

// Période par défaut: 30 derniers jours
$default_period = 30;

// Période sélectionnée
$period = isset($_GET['period']) ? (int)$_GET['period'] : $default_period;

// Date de début et date de fin basées sur la période
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-$period days"));

// Statistiques générales
$stats = [];

// Nouvelles inscriptions sur la période
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM utilisateurs WHERE date_inscription BETWEEN ? AND ? + INTERVAL 1 DAY");
$stmt->execute([$start_date, $end_date]);
$stats['nouvelles_inscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Répartition des utilisateurs par rôle
$stmt = $conn->query("SELECT role, COUNT(*) as total FROM utilisateurs GROUP BY role ORDER BY total DESC");
$stats['utilisateurs_par_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre de livraisons par statut
$stmt = $conn->query("SELECT statut, COUNT(*) as total FROM livraisons GROUP BY statut ORDER BY total DESC");
$stats['livraisons_par_statut'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Évolution des livraisons sur la période
$stmt = $conn->prepare("
    SELECT DATE(date_prise_en_charge) as date, COUNT(*) as total 
    FROM livraisons 
    WHERE date_prise_en_charge BETWEEN ? AND ? + INTERVAL 1 DAY
    GROUP BY DATE(date_prise_en_charge)
    ORDER BY date
");
$stmt->execute([$start_date, $end_date]);
$evolution_livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatage des données pour les graphiques
$dates = [];
$livraisons_counts = [];

foreach ($evolution_livraisons as $row) {
    $dates[] = date('d/m/Y', strtotime($row['date']));
    $livraisons_counts[] = $row['total'];
}

// Chiffre d'affaires sur la période
$stmt = $conn->prepare("SELECT SUM(montant) as total FROM paiements WHERE statut = 'effectué' AND (DATE(CURRENT_TIMESTAMP) - INTERVAL ? DAY)");
$stmt->execute([$period]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['chiffre_affaires'] = $result['total'] ?? 0;

// Moyenne des livraisons par jour
$stmt = $conn->prepare("
    SELECT AVG(daily_count) as avg_count 
    FROM (
        SELECT DATE(date_prise_en_charge) as date, COUNT(*) as daily_count 
        FROM livraisons 
        WHERE date_prise_en_charge BETWEEN ? AND ? + INTERVAL 1 DAY
        GROUP BY DATE(date_prise_en_charge)
    ) as daily_counts
");
$stmt->execute([$start_date, $end_date]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['avg_livraisons_par_jour'] = round($result['avg_count'] ?? 0, 1);

// Top 5 des livreurs les plus actifs
$stmt = $conn->query("
    SELECT u.id, u.nom, COUNT(l.id) as total_livraisons 
    FROM utilisateurs u
    JOIN livraisons l ON u.id = l.id_livreur
    WHERE u.role = 'livreur'
    GROUP BY u.id
    ORDER BY total_livraisons DESC
    LIMIT 5
");
$stats['top_livreurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Les villes les plus demandées pour les livraisons
$stmt = $conn->query("
    SELECT ville_arrivee, COUNT(*) as total 
    FROM annonces 
    GROUP BY ville_arrivee 
    ORDER BY total DESC 
    LIMIT 5
");
$stats['top_villes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Statistiques - EcoDeli Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="../../assets/js/color-modes.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<?php include '../../fonctions/header_admin.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Statistiques</h1>
            <div class="btn-group">
                <a href="?period=7" class="btn btn-outline-primary <?php echo $period == 7 ? 'active' : ''; ?>">7 jours</a>
                <a href="?period=30" class="btn btn-outline-primary <?php echo $period == 30 ? 'active' : ''; ?>">30 jours</a>
                <a href="?period=90" class="btn btn-outline-primary <?php echo $period == 90 ? 'active' : ''; ?>">90 jours</a>
                <a href="?period=365" class="btn btn-outline-primary <?php echo $period == 365 ? 'active' : ''; ?>">1 an</a>
            </div>
        </div>
        
        <!-- Cartes de statistiques générales -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card text-bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Inscriptions</h5>
                        <p class="card-text fs-1"><?php echo $stats['nouvelles_inscriptions']; ?></p>
                        <p class="card-text">nouveaux utilisateurs sur <?php echo $period; ?> jours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Chiffre d'affaires</h5>
                        <p class="card-text fs-1"><?php echo number_format($stats['chiffre_affaires'], 2, ',', ' '); ?> €</p>
                        <p class="card-text">sur les <?php echo $period; ?> derniers jours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Livraisons / Jour</h5>
                        <p class="card-text fs-1"><?php echo $stats['avg_livraisons_par_jour']; ?></p>
                        <p class="card-text">en moyenne sur <?php echo $period; ?> jours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title">Taux de complétion</h5>
                        <?php 
                        $livrees = 0;
                        $total = 0;
                        foreach ($stats['livraisons_par_statut'] as $item) {
                            if ($item['statut'] === 'livrée') {
                                $livrees = $item['total'];
                            }
                            $total += $item['total'];
                        }
                        $taux = $total > 0 ? round(($livrees / $total) * 100) : 0;
                        ?>
                        <p class="card-text fs-1"><?php echo $taux; ?>%</p>
                        <p class="card-text">des livraisons terminées avec succès</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphiques -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Évolution des livraisons</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="livraisonsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Répartition des utilisateurs</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableaux de statistiques -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Top 5 des livreurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Livreur</th>
                                        <th>Livraisons effectuées</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['top_livreurs'] as $livreur): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($livreur['nom']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2"><?php echo $livreur['total_livraisons']; ?></div>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, $livreur['total_livraisons'] * 5); ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats['top_livreurs'])): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">Aucune donnée disponible</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Villes les plus demandées</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ville</th>
                                        <th>Nombre de livraisons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['top_villes'] as $ville): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ville['ville_arrivee']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2"><?php echo $ville['total']; ?></div>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo min(100, $ville['total'] * 5); ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats['top_villes'])): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">Aucune donnée disponible</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Statuts des livraisons</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statutsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Exporter les statistiques</h5>
                    </div>
                    <div class="card-body">
                        <form action="export_stats.php" method="post" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Période</label>
                                <select name="period" class="form-select">
                                    <option value="7" <?php echo $period == 7 ? 'selected' : ''; ?>>7 derniers jours</option>
                                    <option value="30" <?php echo $period == 30 ? 'selected' : ''; ?>>30 derniers jours</option>
                                    <option value="90" <?php echo $period == 90 ? 'selected' : ''; ?>>90 derniers jours</option>
                                    <option value="365" <?php echo $period == 365 ? 'selected' : ''; ?>>Dernière année</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Format</label>
                                <select name="format" class="form-select">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <svg class="bi" width="16" height="16"><use xlink:href="#file-earmark-arrow-down"/></svg> 
                                    Télécharger le rapport
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique d'évolution des livraisons
        const livraisonsChart = new Chart(document.getElementById('livraisonsChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Nombre de livraisons',
                    data: <?php echo json_encode($livraisons_counts); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Graphique de répartition des utilisateurs
        const usersData = <?php
            $roles = [];
            $counts = [];
            foreach ($stats['utilisateurs_par_role'] as $row) {
                $roles[] = ucfirst($row['role']);
                $counts[] = $row['total'];
            }
            echo json_encode([
                'labels' => $roles,
                'data' => $counts
            ]);
        ?>;
        
        const usersChart = new Chart(document.getElementById('usersChart'), {
            type: 'doughnut',
            data: {
                labels: usersData.labels,
                datasets: [{
                    data: usersData.data,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Graphique des statuts de livraisons
        const statutsData = <?php
            $statuts = [];
            $totals = [];
            foreach ($stats['livraisons_par_statut'] as $row) {
                $statuts[] = ucfirst($row['statut']);
                $totals[] = $row['total'];
            }
            echo json_encode([
                'labels' => $statuts,
                'data' => $totals
            ]);
        ?>;
        
        const statutsChart = new Chart(document.getElementById('statutsChart'), {
            type: 'pie',
            data: {
                labels: statutsData.labels,
                datasets: [{
                    data: statutsData.data,
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.7)',  // en attente - warning
                        'rgba(13, 202, 240, 0.7)', // en cours - info
                        'rgba(25, 135, 84, 0.7)',  // livree - success
                        'rgba(220, 53, 69, 0.7)'   // annulee - danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/darkmode.js"></script>
    <?php include '../../fonctions/footer.php'; ?>
</body>
</html> 