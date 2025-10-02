<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';

    if (!empty($name) && !empty($event_date)) {
        $sql = "INSERT INTO webinarapp.webinars (name, description, event_date) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$name, $description, $event_date]);

        header("Location: index.php");
        exit;
    } else {
        $error = "Name and Event Date are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Webinar</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;}
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="datetime-local"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .button { padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Webinar</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="create.php" method="post">
            <div class="form-group">
                <label for="name">Webinar Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time</label>
                <input type="datetime-local" id="event_date" name="event_date" required>
            </div>
            <button type="submit" class="button">Save Webinar</button>
            <a href="index.php">Cancel</a>
        </form>
    </div>
</body>
</html>
