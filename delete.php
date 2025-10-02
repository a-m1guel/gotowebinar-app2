<?php

session_start();

require 'db.php';
require 'config.php';
require 'gtw_api.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

function isGtwAuthenticated() {
    return isset($_SESSION['gtw_access_token']) && time() < $_SESSION['gtw_token_expires_at'];
}

$stmt = $pdo->prepare('SELECT gotowebinar_key FROM webinars WHERE id = ?');
$stmt->execute([$id]);
$webinar = $stmt->fetch();

if ($webinar) {
    try {
        if (isGtwAuthenticated() && !empty($webinar['gotowebinar_key'])) {
            deleteGtwWebinar($webinar['gotowebinar_key']);
        }

        $stmt = $pdo->prepare('DELETE FROM webinars WHERE id = ?');
        $stmt->execute([$id]);

    } catch (Exception $e) {
    }
}
header('Location: index.php');
exit;

