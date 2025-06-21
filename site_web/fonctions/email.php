<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function envoyerEmailVerification($email, $nom, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'votre_email@gmail.com'; // À remplacer par votre email
        $mail->Password = 'votre_mot_de_passe_app'; // À remplacer par votre mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Destinataires
        $mail->setFrom('votre_email@gmail.com', 'Votre Site');
        $mail->addAddress($email, $nom);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Vérification de votre email';
        
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/site_web/features/public/verifier_email.php?token=" . $token;
        
        $mail->Body = "
            <h1>Bonjour $nom,</h1>
            <p>Merci de vous être inscrit sur notre site. Pour finaliser votre inscription, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href='$verificationLink'>Vérifier mon email</a></p>
            <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : " . $mail->ErrorInfo);
        return false;
    }
}
?> 