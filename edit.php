<?php
// edit.php - Handles editing an existing webinar.

session_start();

require 'db.php';
require 'config.php';
require 'gtw_api.php';

$error = '';
$webinar = null;
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Check if the user is authenticated with GoToWebinar
function isGtwAuthenticated() {
    return isset($_SESSION['gtw_access_token']) && time() < $_SESSION['gtw_token_expires_at'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $event_date_string = $_POST['event_date'];
    $event_date_for_db = date('Y-m-d H:i:s', strtotime($event_date_string));
    $gotowebinar_key = $_POST['gotowebinar_key'];

    if (empty($name) || empty($description) || empty($event_date_string)) {
        $error = 'All fields are required.';
    } else {
        try {
            // 1. Update the local database
            $stmt = $pdo->prepare('UPDATE webinars SET name = ?, description = ?, event_date = ? WHERE id = ?');
            $stmt->execute([$name, $description, $event_date_for_db, $id]);

            // 2. If it's a synced webinar and we are authenticated, update it on GoToWebinar
            if (isGtwAuthenticated() && !empty($gotowebinar_key)) {
                updateGtwWebinar($gotowebinar_key, $name, $description, $event_date_for_db);
            }

            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating webinar: ' . $e->getMessage();
        }
    }
} else {
    // Fetch existing webinar data for the form
    $stmt = $pdo->prepare('SELECT * FROM webinars WHERE id = ?');
    $stmt->execute([$id]);
    $webinar = $stmt->fetch();

    if (!$webinar) {
        // If no webinar is found with that ID, redirect
        header('Location: index.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Webinar</title>
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
        <h1>Edit Webinar: <?= htmlspecialchars($webinar['name'] ?? '') ?></h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <?php if (!empty($webinar['gotowebinar_key'])): ?>
            <?php if (isGtwAuthenticated()): ?>
                <p class="notice">This webinar is synced. Changes will be updated in your GoToWebinar account.</p>
            <?php else: ?>
                <p class="notice">This webinar is synced, but you are not connected. Changes will only be saved locally. <a href="<?= getGtwAuthUrl() ?>">Connect now</a> to sync changes.</p>
            <?php endif; ?>
        <?php endif; ?>

        <form action="edit.php?id=<?= $webinar['id'] ?>" method="post">
            <input type="hidden" name="gotowebinar_key" value="<?= htmlspecialchars($webinar['gotowebinar_key'] ?? '') ?>">
            <div class="form-group">
                <label for="name">Webinar Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($webinar['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($webinar['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time:</label>
                <!-- Format the date for the datetime-local input value -->
                <input type="datetime-local" id="event_date" name="event_date" value="<?= date('Y-m-d\TH:i', strtotime($webinar['event_date'])) ?>" required>
            </div>
            <button type="submit" class="button">Update Webinar</button>
        </form>
        <p><a href="index.php">Back to Webinar List</a></p>
    </div>
</body>
</html>

