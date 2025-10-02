<?php
session_start();
require 'config.php';

$code = $_GET['code'] ?? null;

if (!$code) {
    die('Error: No authorization code provided.');
}

$authUrl = 'https://api.getgo.com/oauth/v2/token';
$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => GOTO_REDIRECT_URI
]);

$authHeader = 'Basic ' . base64_encode(GOTO_CLIENT_ID . ':' . GOTO_CLIENT_SECRET);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                    "Authorization: " . $authHeader,
        'content' => $postData,
        'ignore_errors' => true
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($authUrl, false, $context);
$data = json_decode($response, true);

if (isset($data['access_token'])) {
    $_SESSION['access_token'] = $data['access_token'];
    $_SESSION['refresh_token'] = $data['refresh_token'];
    $_SESSION['organizer_key'] = $data['organizer_key'];
    $_SESSION['expires_in'] = time() + $data['expires_in'];

    header('Location: index.php');
    exit;
} else {
    echo "Error retrieving access token: <pre>";
    print_r($data);
    echo "</pre>";
}
?>
