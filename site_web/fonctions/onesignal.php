<?php
function envoyerNotification($titre, $message, $data = []) {
    $appId = "VOTRE_APP_ID"; // À remplacer par votre App ID OneSignal
    $restApiKey = "VOTRE_REST_API_KEY"; // À remplacer par votre Rest API Key

    $content = [
        "en" => $message
    ];

    $heading = [
        "en" => $titre
    ];

    $fields = [
        'app_id' => $appId,
        'included_segments' => ['All'],
        'data' => $data,
        'contents' => $content,
        'headings' => $heading
    ];

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $restApiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Fonction pour envoyer une notification à un utilisateur spécifique
function envoyerNotificationUtilisateur($titre, $message, $userId, $data = []) {
    $appId = "VOTRE_APP_ID"; // À remplacer par votre App ID OneSignal
    $restApiKey = "VOTRE_REST_API_KEY"; // À remplacer par votre Rest API Key

    $content = [
        "en" => $message
    ];

    $heading = [
        "en" => $titre
    ];

    $fields = [
        'app_id' => $appId,
        'include_player_ids' => [$userId],
        'data' => $data,
        'contents' => $content,
        'headings' => $heading
    ];

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $restApiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?> 