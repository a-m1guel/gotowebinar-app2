<?php
// callback.php - Handles the redirect back from GoToWebinar after authorization.

session_start();
require 'config.php';
require 'gtw_api.php';

// Check if GoToWebinar provided an authorization code in the URL
if (isset($_GET['code'])) {
    $authCode = $_GET['code'];
    
    // Exchange the authorization code for an access token and store the response
    $tokenData = getGtwAccessToken($authCode);
    
    if ($tokenData) {
        // SUCCESS: The token data was retrieved. Now, save it to the session.
        
        // Store the actual token
        $_SESSION['gtw_access_token'] = $tokenData['access_token'];
        
        // Store the organizer key, which is needed for all other API calls
        $_SESSION['gtw_organizer_key'] = $tokenData['organizer_key'];
        
        // Calculate and store the token's expiration time
        $_SESSION['gtw_token_expires_at'] = time() + $tokenData['expires_in'];
        
        // Redirect back to the main page
        header('Location: index.php');
        exit;
    } else {
        // Handle failure
        echo "Error: Could not obtain access token. Check your logs and credentials.";
        exit;
    }
} else {
    // Handle cases where the 'code' parameter is missing
    echo "Error: Authorization code not found.";
    exit;
}
?>