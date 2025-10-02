<?php
// create.php - Handles the creation of a new webinar.

session_start();

require 'db.php';
require 'config.php';
require 'gtw_api.php';

$error = '';

// Check if the user is authenticated with GoToWebinar
function isGtwAuthenticated() {
    return isset($_SESSION['gtw_access_token']) && time() < $_SESSION['gtw_token_expires_at'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $event_date_string = $_POST['event_date'];
    
    $event_date_for_db = date('Y-m-d H:i:s', strtotime($event_date_string));

    if (empty($name) || empty($description) || empty($event_date_string)) {
        $error = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO webinars (name, description, event_date) VALUES (?, ?, ?)');
            $stmt->execute([$name, $description, $event_date_for_db]);
            $local_webinar_id = $pdo->lastInsertId();

            if (isGtwAuthenticated()) {
                $webinar_key_from_api = createGtwWebinar($name, $description, $event_date_for_db);

                if ($webinar_key_from_api) {
                    $update_stmt = $pdo->prepare('UPDATE webinars SET gotowebinar_key = ? WHERE id = ?');
                    $update_stmt->execute([$webinar_key_from_api, $local_webinar_id]);
                } else {
                    error_log("GoToWebinar creation failed for local ID: " . $local_webinar_id);
                }
            }

            header('Location: index.php');
            exit;

        } catch (Exception $e) {
            $error = 'Error creating webinar: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Webinar</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea, input[type="datetime-local"] { width: 100%; padding: 8px; box-sizing: border-box; border-radius: 4px; border: 1px solid #ccc; }
        .button { display: inline-block; background: #007BFF; color: #fff; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; }
        .button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
        .notice { background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Webinar</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (isGtwAuthenticated()): ?>
            <p class="notice">You are connected. This webinar will be created in your GoToWebinar account.</p>
        <?php else: ?>
            <p class="notice">You are not connected. This webinar will only be saved locally.</p>
        <?php endif; ?>

        <form action="create.php" method="post">
            <div class="form-group">
                <label for="name">Webinar Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time:</label>
                <input type="datetime-local" id="event_date" name="event_date" required>
            </div>
            <button type="submit" class="button">Create Webinar</button>
        </form>
        <p><a href="index.php">Back to Webinar List</a></p>
    </div>
</body>
</html>