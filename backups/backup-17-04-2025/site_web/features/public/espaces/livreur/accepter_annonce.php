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
    // Insérer la livraison
    $stmt = $conn->prepare("INSERT INTO livraisons (id_client, id_livreur, id_annonce, date_prise_en_charge, statut) 
                            VALUES ((SELECT id_client FROM annonces WHERE id = ?), ?, ?, NOW(), 'en cours')");
    $stmt->execute([$id_annonce, $id_livreur, $id_annonce]);

    // Mettre à jour le statut de l'annonce
    $conn->prepare("UPDATE annonces SET statut = 'en cours' WHERE id = ?")->execute([$id_annonce]);

    // TODO : envoyer une notification push/email au client

    header("Location: mes_livraisons.php?success=1");
    exit;
} else {
    echo "ID annonce manquant.";
}