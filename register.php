<?php
// register.php - Handles the registration of a new attendee for a synced webinar.

session_start();
require 'config.php';
require 'db.php';
require 'gtw_api.php';

// If user isn't connected they can't register
if (!isGtwAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Get the local webinar ID from the URL.
$webinar_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$webinar_id) {
    header('Location: index.php');
    exit;
}

// Fetch the webinar details from db
$stmt = $pdo->prepare("SELECT name, gotowebinar_key FROM webinarapps.webinars WHERE id = ?");
$stmt->execute([$webinar_id]);
$webinar = $stmt->fetch();

if (!$webinar || empty($webinar['gotowebinar_key'])) {
    echo "Error: This webinar is not synced with GoToWebinar or does not exist.";
    echo '<br><a href="index.php">Go Back</a>';
    exit;
}

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);


    if (!empty($firstName) && !empty($lastName) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $success = registerAttendeeForGtwWebinar($webinar['gotowebinar_key'], $firstName, $lastName, $email);
        
        if ($success) {
            $message = "<p style='color: green;'>Successfully registered $firstName $lastName for the webinar!</p>";
        } else {
            $message = "<p style='color: red;'>Registration failed. The user may already be registered or there was an API error.</p>";
        }
    } else {
        $message = "<p style='color: red;'>Please fill out all fields with valid information.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register for Webinar</title>
        <style>
            body { font-family: sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1, h2, h3 { color: #555; }
            form { margin-top: 20px; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; }
            .form-group input[type="text"], .form-group input[type="email"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .button { display: inline-block; background: #007BFF; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-top: 15px; border: none; cursor: pointer; }
            a.button { margin-top: 20px; background-color: #6c757d; }
        </style>
    </head>
    <body>

    <div class="container">
        <h2>Register Attendee</h2>
        <h3>For Webinar: <?= htmlspecialchars($webinar['name']) ?></h3>

        <?= $message ?>

        <form action="register.php?id=<?= $webinar_id ?>" method="post">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="button">Register</button>
        </form>

        <a href="index.php" class="button">Back to List</a>
    </div>

    </body>
</html>
