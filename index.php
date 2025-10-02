<?php
// index.php - Main page to list webinars and manage GoToWebinar connection.

// session_start() must be the very first thing on the page to work correctly.
session_start();

require 'config.php';
require 'db.php';
require 'gtw_api.php';

// Fetch all webinars from the local database
$stmt = $pdo->query("SELECT id, name, description, event_date, gotowebinar_key FROM webinars ORDER BY event_date DESC");
$webinars = $stmt->fetchAll();

// Helper function to check if the user is authenticated with GoToWebinar
function isGtwAuthenticated() {
    return isset($_SESSION['gtw_access_token']) && time() < $_SESSION['gtw_token_expires_at'];
}

// Helper function to generate the authentication URL
function getGtwAuthUrl() {
    $params = [
        'client_id' => GOTO_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => GOTO_REDIRECT_URI
    ];
    return 'https://api.getgo.com/oauth/v2/authorize?' . http_build_query($params);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Webinar Management</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #555; }
        .button { display: inline-block; background: #007BFF; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-bottom: 20px; }
        .button-logout { background-color: #dc3545; }
        .auth-status { padding: 15px; background: #e9ecef; border: 1px solid #ced4da; border-radius: 5px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        td a { margin-right: 10px; }
        .status-synced { color: green; font-weight: bold; }
        .status-not-synced { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h1>Webinar Management System</h1>

    <!-- GoToWebinar Authentication Status Bar -->
    <div class="auth-status">
        <?php if (isGtwAuthenticated()): ?>
            <span><strong>GoToWebinar Status:</strong> <span class="status-synced">Connected</span></span>
            <a href="logout.php" class="button button-logout">Disconnect</a>
        <?php else: ?>
            <span><strong>GoToWebinar Status:</strong> Not Connected</span>
            <a href="<?= getGtwAuthUrl() ?>" class="button">Connect to GoToWebinar</a>
        <?php endif; ?>
    </div>

    <a href="create.php" class="button">Create New Webinar</a>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Event Date & Time</th>
                <th>Sync Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($webinars)): ?>
                <tr>
                    <td colspan="4">No webinars found. Create one to get started.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($webinars as $webinar): ?>
                    <tr>
                        <td><?= htmlspecialchars($webinar['name']) ?></td>
                        <td><?= htmlspecialchars($webinar['event_date']) ?></td>
                        <td>
                            <?php if (!empty($webinar['gotowebinar_key'])): ?>
                                <span class="status-synced">Synced</span>
                            <?php else: ?>
                                <span class="status-not-synced">Not Synced</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $webinar['id'] ?>">Edit</a>
                            <a href="delete.php?id=<?= $webinar['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php if (!empty($webinar['gotowebinar_key']) && isGtwAuthenticated()): ?>
                                <a href="register.php?id=<?= $webinar['id'] ?>">Register</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

