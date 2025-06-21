<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$conn = getConnexion();
$id = $_SESSION['utilisateur']['id'];

$data = $_POST;
$nom = $data['nom'] ?? '';
$email = $data['email'] ?? '';
$mot_de_passe = $data['mot_de_passe'] ?? '';
$photo_profil = null;

$sql = "UPDATE utilisateurs SET nom = ?, email = ?";
$params = [$nom, $email];

if (!empty($mot_de_passe)) {
    $sql .= ", mot_de_passe = ?";
    $params[] = password_hash($mot_de_passe, PASSWORD_BCRYPT);
}

if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
    $upload_dir = __DIR__ . '/../../../uploads/';
    $ext = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('photo_') . '.' . $ext;
    $target_path = $upload_dir . $filename;
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($ext), $allowed)) {
        move_uploaded_file($_FILES['photo_profil']['tmp_name'], $target_path);
        $sql .= ", photo_profil = ?";
        $params[] = $filename;
    }
}

$sql .= " WHERE id = ?";
$params[] = $id;

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$_SESSION['utilisateur']['nom'] = $nom;
$_SESSION['utilisateur']['email'] = $email;

echo json_encode(['success' => true]); 