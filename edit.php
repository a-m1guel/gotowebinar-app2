<?php

require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';

    if (!empty($name) && !empty($event_date)) {
        $sql = "UPDATE webinarapp.webinars SET name = ?, description = ?, event_date = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $event_date, $id]);

        // We will add the GoToWebinar sync logic here later
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Name and Event Date are required.";
    }
}

// Fetch the existing webinar data to populate the form
$stmt = $pdo->prepare('SELECT * FROM webinarapp.webinars WHERE id = ?');
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
    <title>Edit Webinar</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;}
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="datetime-local"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .button { padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Webinar</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="edit.php?id=<?= $webinar['id'] ?>" method="post">
            <div class="form-group">
                <label for="name">Webinar Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($webinar['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($webinar['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time</label>
                <input type="datetime-local" id="event_date" name="event_date" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($webinar['event_date']))) ?>" required>
            </div>
            <button type="submit" class="button">Update Webinar</button>
            <a href="index.php">Cancel</a>
        </form>
    </div>
</body>
</html>
