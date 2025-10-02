<?php
// delete.php - Handles the deletion of a webinar.
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Handle the confirmation of deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We will add GoToWebinar deletion logic here later

    // Delete from our local database
    $sql = "DELETE FROM webinarapp.webinars WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}

// Fetch the webinar to confirm which one is being deleted
$stmt = $pdo->prepare('SELECT name FROM webinarapp.webinars WHERE id = ?');
$stmt->execute([$id]);
$webinar = $stmt->fetch();

if (!$webinar) {
    // No webinar found with that ID
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Webinar</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center;}
        .button { padding: 10px 15px; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; margin: 0 10px; }
        .button-danger { background-color: #dc3545; }
        .button-secondary { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirm Deletion</h1>
        <p>Are you sure you want to delete the webinar:</p>
        <p><strong><?= htmlspecialchars($webinar['name']) ?></strong></p>
        
        <form action="delete.php?id=<?= $id ?>" method="post">
            <button type="submit" class="button button-danger">Yes, Delete</button>
            <a href="index.php" class="button button-secondary">No, Cancel</a>
        </form>
    </div>
</body>
</html>
