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
<<<<<<< HEAD
    $url = '/api/livreur/livraisons/post.php';
    $data = json_encode(['id_annonce' => $id_annonce]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($response, true);
    if ($httpcode === 200 && !empty($result['success'])) {
        header('Location: mes_livraisons.php?success=1');
        exit;
    } else {
        $error = $result['error'] ?? 'Erreur inconnue';
        header('Location: annonces_dispo.php?error=' . urlencode($error));
        exit;
    }
=======
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
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
} else {
    echo "ID annonce manquant.";
}