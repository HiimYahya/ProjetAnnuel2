<?php
function getConnexion() {
    try {
        $conn = new PDO('mysql:host=localhost;dbname=PA2', 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}