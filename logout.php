<?php
// Logout script
session_start();

// Destroy session
session_destroy();

// Redirect to login
header("Location: /elimu-tracks/index.php?success=Logged%20out%20successfully");
exit();
?>
