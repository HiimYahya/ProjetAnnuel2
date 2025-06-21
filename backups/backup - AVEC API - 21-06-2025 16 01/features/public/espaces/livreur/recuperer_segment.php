<?php
session_start();
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];
$id_segment = $_POST['id_segment'] ?? null;

if (!$id_segment) {
    header('Location: segments_disponibles.php?error=segment_manquant');
    exit;
}

try {
    $conn->beginTransaction();
    
    // Récupérer les informations sur le segment
    $stmt = $conn->prepare("
        SELECT s.*, l.validation_client, l.id_annonce, a.titre as titre_annonce,
               pr_arrivee.nom AS point_relais_arrivee_nom, 
               pr_arrivee.adresse AS point_relais_arrivee_adresse,
               pr_arrivee.code_postal AS point_relais_arrivee_code_postal, 
               pr_arrivee.ville AS point_relais_arrivee_ville
        FROM segments s
        JOIN livraisons l ON s.id_livraison = l.id
        JOIN annonces a ON l.id_annonce = a.id
        LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id
        WHERE s.id = ?
        FOR UPDATE
    ");
    $stmt->execute([$id_segment]);
    $segment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$segment) {
        throw new Exception("Segment introuvable");
    }
    
    // Vérifier que le segment est disponible (en point relais ou en attente)
    if ($segment['statut'] !== 'en attente' && $segment['statut'] !== 'en point relais') {
        throw new Exception("Ce segment n'est pas disponible");
    }
    
    // Vérifier que la livraison a été validée par le client
    if ($segment['validation_client'] != 1) {
        throw new Exception("Cette livraison n'a pas été validée par le client");
    }
    
    // Vérifier si le livreur n'a pas déjà trop de segments en cours
    $stmt = $conn->prepare("
        SELECT COUNT(*) as nb_segments_actifs 
        FROM segments 
        WHERE id_livreur = ? AND statut = 'en cours'
    ");
    $stmt->execute([$id_livreur]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Limite à 5 segments actifs par livreur (à ajuster selon besoins)
    if ($result['nb_segments_actifs'] >= 5) {
        throw new Exception("Vous avez déjà 5 segments actifs. Finalisez-en au moins un avant d'en accepter d'autres.");
    }
    
    // Si le segment est dans un point relais, mettre à jour l'adresse de départ 
    // pour qu'elle corresponde à l'adresse du point relais
    $new_point_relais_depart = null;
    $new_adresse_depart = $segment['adresse_depart'];
    
    if ($segment['statut'] === 'en point relais') {
        // Un segment en point relais devient un nouveau segment qui part du point relais
        $new_point_relais_depart = $segment['point_relais_arrivee'];
        
        // Construire l'adresse complète du point relais pour l'adresse de départ
        if ($segment['point_relais_arrivee_nom']) {
            $new_adresse_depart = $segment['point_relais_arrivee_adresse'] . ', ' . 
                                  $segment['point_relais_arrivee_code_postal'] . ' ' . 
                                  $segment['point_relais_arrivee_ville'];
        }
    }
    
    // Mettre à jour le segment avec le nouveau livreur et la nouvelle adresse de départ si nécessaire
    $stmt = $conn->prepare("
        UPDATE segments 
        SET id_livreur = ?, 
            statut = 'en cours',
            date_debut = NOW(),
            point_relais_depart = ?,
            adresse_depart = ?
        WHERE id = ?
    ");
    $stmt->execute([$id_livreur, $new_point_relais_depart, $new_adresse_depart, $id_segment]);
    
    // Enregistrer l'action dans un log (facultatif)
    $stmt = $conn->prepare("
        INSERT INTO logs (action, id_utilisateur, details, date_action)
        VALUES (?, ?, ?, NOW())
    ");
    
    $source = $segment['statut'] === 'en point relais' ? 
             "du point relais " . $segment['point_relais_arrivee_nom'] . " (" . $segment['point_relais_arrivee_ville'] . ")" :
             "de l'adresse " . $segment['adresse_depart'];
             
    $details = "Segment #" . $id_segment . " (" . $segment['titre_annonce'] . ") récupéré " . $source;
    
    // On essaie d'insérer le log, mais si la table n'existe pas, on ne bloque pas l'opération
    try {
        $stmt->execute(['recuperation_segment', $id_livreur, $details]);
    } catch (Exception $e) {
        // Ignorer l'erreur si la table de logs n'existe pas
    }
    
    $conn->commit();
    header('Location: mes_livraisons.php?success=segment_recupere');
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    header('Location: segments_disponibles.php?error=' . urlencode($e->getMessage()));
    exit;
} 