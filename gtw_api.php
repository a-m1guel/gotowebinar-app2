<?php
// gtw_api.php - Helper functions for GoToWebinar API interaction.

function getGtwAccessToken($auth_code) {
    $url = 'https://api.getgo.com/oauth/v2/token';
    $auth_string = base64_encode(GOTO_CLIENT_ID . ':' . GOTO_CLIENT_SECRET);
    
    $params = [
        'grant_type' => 'authorization_code',
        'code' => $auth_code,
        'redirect_uri' => GOTO_REDIRECT_URI
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $auth_string,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        die('cURL error: ' . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($result, true);

    if (isset($data['access_token'])) {
        return $data;
    }

    return null;
}

function makeGtwApiCall($method, $endpoint, $payload = null) {
    if (!isGtwAuthenticated()) {
        return null;
    }
    
    $url = 'https://api.getgo.com/G2W/rest/v2' . $endpoint;
    $access_token = $_SESSION['gtw_access_token'];
    
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($payload) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 300) {
        error_log("GoToWebinar API Error: HTTP $http_code - $result");
        return null;
    }

    return json_decode($result, true);
}


function createGtwWebinar($name, $description, $datetime) {
    $organizerKey = $_SESSION['gtw_organizer_key'];
    $endpoint = "/organizers/{$organizerKey}/webinars";
    
    $utc = new DateTimeZone('UTC');
    $dt = new DateTime($datetime, $utc);
    $startTime = $dt->format('Y-m-d\TH:i:s\Z');
    $dt->add(new DateInterval('PT1H')); 
    $endTime = $dt->format('Y-m-d\TH:i:s\Z');

    $payload = [
        'subject' => $name,
        'description' => $description,
        'times' => [[
            'startTime' => $startTime,
            'endTime' => $endTime
        ]],
        'timeZone' => 'UTC'
    ];

    $response = makeGtwApiCall('POST', $endpoint, $payload);

    return isset($response['webinarKey']) ? $response['webinarKey'] : null;
}

function updateGtwWebinar($webinarKey, $name, $description, $datetime) {
    $organizerKey = $_SESSION['gtw_organizer_key'];
    $endpoint = "/organizers/{$organizerKey}/webinars/{$webinarKey}";

    $utc = new DateTimeZone('UTC');
    $dt = new DateTime($datetime, $utc);
    $startTime = $dt->format('Y-m-d\TH:i:s\Z');
    $dt->add(new DateInterval('PT1H'));
    $endTime = $dt->format('Y-m-d\TH:i:s\Z');
    
    $payload = [
        'subject' => $name,
        'description' => $description,
        'times' => [[
            'startTime' => $startTime,
            'endTime' => $endTime
        ]],
        'timeZone' => 'UTC'
    ];
    
    makeGtwApiCall('PUT', $endpoint, $payload);
    return true;
}

function deleteGtwWebinar($webinarKey) {
    $organizerKey = $_SESSION['gtw_organizer_key'];
    $endpoint = "/organizers/{$organizerKey}/webinars/{$webinarKey}";

    makeGtwApiCall('DELETE', $endpoint . '?sendCancellationEmails=false');
    return true;
}

function registerAttendeeForGtwWebinar($webinarKey, $firstName, $lastName, $email) {
    $organizerKey = $_SESSION['gtw_organizer_key'];
    $endpoint = "/organizers/{$organizerKey}/webinars/{$webinarKey}/registrants";

    $payload = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email
    ];
    
    $response = makeGtwApiCall('POST', $endpoint, $payload);
    
    return isset($response['registrantKey']);
}

?>

