<?php
session_start();
include '../../fonctions/db.php';
include '../../fonctions/fonctions.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    header('Location: /site_web/features/public/login.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['export']) || !isset($_POST['format']) || empty($_POST['export'])) {
    $_SESSION['error'] = "Erreur: Veuillez sélectionner au moins un type de données à exporter.";
    header('Location: index.php');
    exit;
}

try {
    $conn = getConnexion();
    $format = strtolower($_POST['format']);
    $tables = $_POST['export'];
    $data = [];
    $filename = 'export_ecodeli_' . date('Y-m-d_H-i-s');
    
    // Récupérer les données pour chaque table sélectionnée
    foreach ($tables as $table) {
        switch ($table) {
            case 'utilisateurs':
                $stmt = $conn->query("SELECT id, nom, email, role, date_inscription FROM utilisateurs ORDER BY id");
                $data['utilisateurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'annonces':
                $stmt = $conn->query("SELECT a.*, u.nom as nom_utilisateur 
                                    FROM annonces a 
                                    LEFT JOIN utilisateurs u ON a.id_utilisateur = u.id 
                                    ORDER BY a.id");
                $data['annonces'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'livraisons':
                $stmt = $conn->query("SELECT l.*, 
                                    c.nom as client_nom, 
                                    lv.nom as livreur_nom 
                                    FROM livraisons l 
                                    LEFT JOIN utilisateurs c ON l.id_client = c.id 
                                    LEFT JOIN utilisateurs lv ON l.id_livreur = lv.id 
                                    ORDER BY l.id");
                $data['livraisons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'paiements':
                $stmt = $conn->query("SELECT * FROM paiements ORDER BY id");
                $data['paiements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            default:
                // Ignorer les valeurs inconnues
                continue;
        }
    }
    
    // Si aucune donnée n'a été récupérée
    if (empty($data)) {
        $_SESSION['error'] = "Erreur: Aucune donnée n'a été trouvée pour l'exportation.";
        header('Location: index.php');
        exit;
    }
    
    // Générer le fichier d'export selon le format choisi
    switch ($format) {
        case 'csv':
            // Définir les en-têtes HTTP pour le téléchargement
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            
            // Ouvrir le flux de sortie
            $output = fopen('php://output', 'w');
            
            // Pour chaque table
            foreach ($data as $table => $rows) {
                if (!empty($rows)) {
                    // Écrire le nom de la table
                    fputcsv($output, [strtoupper($table)]);
                    
                    // Écrire les en-têtes de colonnes
                    fputcsv($output, array_keys($rows[0]));
                    
                    // Écrire les données
                    foreach ($rows as $row) {
                        fputcsv($output, $row);
                    }
                    
                    // Ajouter une ligne vide entre les tables
                    fputcsv($output, []);
                }
            }
            
            fclose($output);
            exit;
            
        case 'excel':
            // Pour Excel, nous utilisons CSV avec séparateur point-virgule (compatible avec Excel)
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            
            echo '<table border="1">';
            
            // Pour chaque table
            foreach ($data as $table => $rows) {
                if (!empty($rows)) {
                    echo '<tr><th colspan="' . count(array_keys($rows[0])) . '">' . strtoupper($table) . '</th></tr>';
                    
                    // En-têtes de colonnes
                    echo '<tr>';
                    foreach (array_keys($rows[0]) as $header) {
                        echo '<th>' . htmlspecialchars($header) . '</th>';
                    }
                    echo '</tr>';
                    
                    // Données
                    foreach ($rows as $row) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        echo '</tr>';
                    }
                    
                    // Ligne vide entre les tables
                    echo '<tr><td colspan="' . count(array_keys($rows[0])) . '">&nbsp;</td></tr>';
                }
            }
            
            echo '</table>';
            exit;
            
        case 'json':
            // Définir les en-têtes HTTP pour le téléchargement
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            
            // Encoder les données en JSON et les envoyer
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
            
        default:
            $_SESSION['error'] = "Format d'exportation non valide.";
            header('Location: index.php');
            exit;
    }
} catch (Exception $e) {
    // En cas d'erreur, enregistrer le message et rediriger
    $_SESSION['error'] = "Une erreur est survenue lors de l'exportation: " . $e->getMessage();
    header('Location: index.php');
    exit;
}