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
} else {
    echo "ID annonce manquant.";
}