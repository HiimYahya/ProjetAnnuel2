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
$type_livraison = $_POST['type_livraison'] ?? 'point_relais'; // 'point_relais' ou 'finale'

if (!$id_segment) {
    header('Location: mes_livraisons.php?error=segment_manquant');
    exit;
}

try {
    $conn->beginTransaction();
    
    // Récupérer les informations sur le segment avec verrouillage FOR UPDATE
    $stmt = $conn->prepare("
        SELECT s.*, l.id AS id_livraison, l.id_annonce, l.id_client, 
               a.statut AS statut_annonce, a.titre,
               pr.nom AS point_relais_nom, pr.ville AS point_relais_ville
        FROM segments s
        JOIN livraisons l ON s.id_livraison = l.id
        JOIN annonces a ON l.id_annonce = a.id
        LEFT JOIN points_relais pr ON s.point_relais_arrivee = pr.id
        WHERE s.id = ? AND s.id_livreur = ?
        FOR UPDATE
    ");
    $stmt->execute([$id_segment, $id_livreur]);
    $segment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$segment) {
        throw new Exception("Segment non trouvé ou vous n'êtes pas autorisé à le modifier");
    }
    
    // Vérifier que le segment est bien en cours
    if ($segment['statut'] !== 'en cours') {
        throw new Exception("Le segment n'est pas dans un état permettant cette action");
    }
    
    // Traitement en fonction du type de livraison
    if ($type_livraison === 'point_relais') {
        // Vérifier que le segment a bien un point relais d'arrivée défini
        if (!$segment['point_relais_arrivee']) {
            throw new Exception("Ce segment ne possède pas de point relais d'arrivée");
        }
        
        // Mettre à jour le segment - libérer le livreur actuel pour qu'un autre puisse reprendre
        $stmt = $conn->prepare("
            UPDATE segments 
            SET statut = 'en point relais', 
                date_fin = NOW(),
                id_livreur = NULL
            WHERE id = ?
        ");
        $stmt->execute([$id_segment]);
        
        // Enregistrer dans l'historique que ce livreur a livré au point relais
        try {
            $stmt = $conn->prepare("
                INSERT INTO logs (action, id_utilisateur, details, date_action)
                VALUES (?, ?, ?, NOW())
            ");
            $details = "Segment #" . $id_segment . " (" . $segment['titre'] . ") livré au point relais " . 
                      $segment['point_relais_nom'] . " à " . $segment['point_relais_ville'];
            $stmt->execute(['livraison_point_relais', $id_livreur, $details]);
        } catch (Exception $e) {
            // Ignorer si la table n'existe pas
        }
        
        // Récupérer les informations du point relais pour la notification
        $stmt = $conn->prepare("
            SELECT nom, ville
            FROM points_relais
            WHERE id = ?
        ");
        $stmt->execute([$segment['point_relais_arrivee']]);
        $point_relais = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Enregistrer une notification pour le client (si la table existe)
        try {
            $stmt = $conn->prepare("
                INSERT INTO notifications (id_utilisateur, type, message, date_creation, lu)
                VALUES (?, 'segment_point_relais', ?, NOW(), 0)
            ");
            $message = "Votre colis \"{$segment['titre']}\" a été déposé au point relais {$point_relais['nom']} à {$point_relais['ville']}. " .
                       "Il est maintenant disponible pour être récupéré par un autre livreur qui se chargera de la suite de la livraison.";
            $stmt->execute([$segment['id_client'], $message]);
        } catch (Exception $e) {
            // Si la table n'existe pas, on ignore cette étape
        }
        
        $success_message = "Segment livré avec succès au point relais. Vous êtes libéré de ce segment.";
    } else {
        // Livraison finale
        
        // Mettre à jour le segment comme livré
        $stmt = $conn->prepare("
            UPDATE segments 
            SET statut = 'livré', 
                date_fin = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$id_segment]);
        
        // Vérifier si c'était le dernier segment de la livraison
        $stmt = $conn->prepare("
            SELECT COUNT(*) as non_livres 
            FROM segments 
            WHERE id_livraison = ? 
            AND statut NOT IN ('livré', 'annulé')
        ");
        $stmt->execute([$segment['id_livraison']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si c'est le dernier segment, mettre à jour la livraison et l'annonce
        if ($result['non_livres'] == 0) {
            // Mettre à jour la livraison
            $stmt = $conn->prepare("
                UPDATE livraisons 
                SET statut = 'livrée', 
                    date_livraison = NOW(),
                    reception_confirmee = 1
                WHERE id = ?
            ");
            $stmt->execute([$segment['id_livraison']]);
            
            // Mettre à jour l'annonce
            $stmt = $conn->prepare("
                UPDATE annonces 
                SET statut = 'livrée' 
                WHERE id = ?
            ");
            $stmt->execute([$segment['id_annonce']]);
            
            // Créer une notification pour le client (si la table existe)
            try {
                $stmt = $conn->prepare("
                    INSERT INTO notifications (id_utilisateur, type, message, date_creation, lu)
                    VALUES (?, 'livraison_complete', ?, NOW(), 0)
                ");
                $message = "Votre livraison \"{$segment['titre']}\" est maintenant terminée. Merci d'avoir utilisé nos services !";
                $stmt->execute([$segment['id_client'], $message]);
            } catch (Exception $e) {
                // Si la table n'existe pas, on ignore cette étape
            }
            
            $success_message = "Livraison complète terminée avec succès !";
        } else {
            $success_message = "Segment livré avec succès à destination";
        }
    }
    
    $conn->commit();
    header('Location: mes_livraisons.php?success=' . urlencode($success_message));
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    header('Location: mes_livraisons.php?error=' . urlencode($e->getMessage()));
    exit;
} 