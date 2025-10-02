<?php
// logout.php - Clears the session to disconnect from GoToWebinar.
session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;
?>