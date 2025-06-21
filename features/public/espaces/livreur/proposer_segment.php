<?php
session_start();
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header("Location: ../../auth/login.php");
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];
$id_annonce = $_POST['id_annonce'] ?? null;
$point_relais_arrivee = $_POST['point_relais_arrivee'] ?? null;

// Récupérer les informations de l'annonce
$annonce_stmt = $conn->prepare("SELECT id, id_client, ville_depart, ville_arrivee, segmentation_possible FROM annonces WHERE id = ?");
$annonce_stmt->execute([$id_annonce]);
$annonce = $annonce_stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    header("Location: annonces_dispo.php?error=annonce_introuvable");
    exit;
}

if ($annonce['segmentation_possible'] != 1) {
    header("Location: annonces_dispo.php?error=segmentation_impossible");
    exit;
}

// Fixer l'adresse de départ à l'adresse d'origine
$segment_depart = $annonce['ville_depart'];
$point_relais_depart = null; // Pas de point relais de départ

// Déterminer l'adresse d'arrivée
if ($point_relais_arrivee === 'destination') {
    $segment_arrivee = $annonce['ville_arrivee'];
    $point_relais_arrivee = null;
} else {
    // Récupérer l'adresse du point relais d'arrivée
    $pr_stmt = $conn->prepare("SELECT id, CONCAT(adresse, ', ', code_postal, ' ', ville) as adresse FROM points_relais WHERE id = ?");
    $pr_stmt->execute([$point_relais_arrivee]);
    $pr = $pr_stmt->fetch(PDO::FETCH_ASSOC);
    $segment_arrivee = $pr ? $pr['adresse'] : $annonce['ville_arrivee'];
}

// Utiliser une transaction pour assurer l'intégrité des données
try {
    $conn->beginTransaction();
    
    // Vérifier si une livraison existe déjà pour cette annonce
    $livraison_stmt = $conn->prepare("SELECT id FROM livraisons WHERE id_annonce = ?");
    $livraison_stmt->execute([$id_annonce]);
    $livraison = $livraison_stmt->fetch(PDO::FETCH_ASSOC);
    
    $id_livraison = null;
    
    if ($livraison) {
        // Si une livraison existe déjà
        $id_livraison = $livraison['id'];
    } else {
        // Créer une nouvelle livraison
        $livraison_stmt = $conn->prepare("INSERT INTO livraisons 
            (id_client, id_livreur, id_annonce, date_prise_en_charge, statut, validation_client, segmentation_possible) 
            VALUES (?, ?, ?, NOW(), 'en attente', 1, 1)");
        $livraison_stmt->execute([$annonce['id_client'], $id_livreur, $id_annonce]);
        $id_livraison = $conn->lastInsertId();
        
        // Mettre à jour le statut de l'annonce
        $update = $conn->prepare("UPDATE annonces SET statut = 'prise en charge' WHERE id = ? AND statut = 'en attente'");
        $update->execute([$id_annonce]);
    }
    
    // Insérer le segment
    $segment_stmt = $conn->prepare("INSERT INTO segments 
        (id_livraison, id_annonce, id_livreur, adresse_depart, adresse_arrivee, point_relais_depart, point_relais_arrivee, statut, date_debut) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'en cours', NOW())");
    $segment_stmt->execute([
        $id_livraison, 
        $id_annonce, 
        $id_livreur, 
        $segment_depart, 
        $segment_arrivee, 
        $point_relais_depart, 
        $point_relais_arrivee === 'destination' ? null : $point_relais_arrivee
    ]);
    
    $conn->commit();
    header("Location: mes_livraisons.php?success=segment_cree");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    header("Location: annonces_dispo.php?error=erreur_creation&message=" . urlencode($e->getMessage()));
    exit;
}
