<?php
// Start the session
session_start();

// Destroy all session data
session_destroy();

// Redirect to home page
header("Location: ../user/home.html");
exit();
?> 