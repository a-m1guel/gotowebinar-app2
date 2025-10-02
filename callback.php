<?php
// callback.php - Handles the redirect back from GoToWebinar after authorization.

session_start();
require 'config.php';
require 'gtw_api.php';

if (isset($_GET['code'])) {
    $authCode = $_GET['code'];
    
    $tokenData = getGtwAccessToken($authCode);
    
    if ($tokenData) {  
        $_SESSION['gtw_access_token'] = $tokenData['access_token'];
        $_SESSION['gtw_organizer_key'] = $tokenData['organizer_key'];
        $_SESSION['gtw_token_expires_at'] = time() + $tokenData['expires_in'];
        
        header('Location: index.php');
        exit;
    } else {
        echo "Error: Could not obtain access token. Check your logs and credentials.";
        exit;
    }
} else {
    echo "Error: Authorization code not found.";
    exit;
}
?>