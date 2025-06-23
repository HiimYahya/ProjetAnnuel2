<?php

session_start();
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}

$id_annonce = $_POST['id_annonce'] ?? null;
$id_livreur = $_SESSION['utilisateur']['id'];

if ($id_annonce) {
    // Vérifier si l'annonce existe
    $annonce_stmt = $conn->prepare("SELECT id_client, segmentation_possible FROM annonces WHERE id = ?");
    $annonce_stmt->execute([$id_annonce]);
    $annonce = $annonce_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        header("Location: annonces_dispo.php?error=annonce_introuvable");
        exit;
    }

    // Insérer la livraison avec validation_client = 0 (en attente de validation)
    $stmt = $conn->prepare("INSERT INTO livraisons 
                            (id_client, id_livreur, id_annonce, date_prise_en_charge, statut, validation_client, segmentation_possible) 
                            VALUES (?, ?, ?, NOW(), 'en attente', 0, ?)");
    $stmt->execute([$annonce['id_client'], $id_livreur, $id_annonce, $annonce['segmentation_possible']]);

    $id_livraison = $conn->lastInsertId();
    
    // Créer un segment pour la livraison complète (non segmentée)
    if ($annonce['segmentation_possible'] == 0) {
        $stmt = $conn->prepare("INSERT INTO segments 
                                (id_livraison, id_annonce, id_livreur, adresse_depart, adresse_arrivee, statut)
                                SELECT ?, id, ?, ville_depart, ville_arrivee, 'en attente'
                                FROM annonces WHERE id = ?");
        $stmt->execute([$id_livraison, $id_livreur, $id_annonce]);
    }

    // Mettre à jour le statut de l'annonce à "prise en charge"
    $conn->prepare("UPDATE annonces SET statut = 'prise en charge' WHERE id = ?")->execute([$id_annonce]);

    // TODO : envoyer une notification push/email au client pour validation

    header("Location: mes_livraisons.php?success=1");
    exit;
} else {
    echo "ID annonce manquant.";
}