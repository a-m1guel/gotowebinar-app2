<?php
// index.php - This is the main page. It reads and displays all webinars from our database.
require 'db.php'; // Include the database connection

// Fetch all webinars from the database
$stmt = $pdo->query('SELECT id, name, description, event_date, gotowebinar_key FROM webinarapp.webinars ORDER BY event_date DESC');
$webinars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webinar Management</title>
    <style>
        body { font-family: sans-serif; container-type: inline-size; }
        .container { max-width: 900px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007bff; }
        .actions a:hover { text-decoration: underline; }
        .actions a.delete { color: #dc3545; }
        .button { padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        h1 { border-bottom: 2px solid #f2f2f2; padding-bottom: 10px; }
        .status-synced { color: green; font-weight: bold; }
        .status-not-synced { color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Webinar Management</h1>
        <p><a href="create.php" class="button">Create New Webinar</a></p>
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Event Date</th>
                    <th>GoToWebinar Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($webinars)): ?>
                    <tr>
                        <td colspan="4">No webinars found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($webinars as $webinar): ?>
                        <tr>
                            <td><?= htmlspecialchars($webinar['name']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($webinar['event_date']))) ?></td>
                            <td>
                                <?php if (!empty($webinar['gotowebinar_key'])): ?>
                                    <span class="status-synced">Synced</span>
                                <?php else: ?>
                                    <span class="status-not-synced">Not Synced</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <!-- These links pass the specific webinar ID in the URL -->
                                <a href="edit.php?id=<?= $webinar['id'] ?>">Edit</a>
                                <a href="delete.php?id=<?= $webinar['id'] ?>" class="delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

