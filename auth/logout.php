<?php
// Include session security functions
require_once '../includes/session_security.php';

// Log the logout event
if (isLoggedIn()) {
    logSecurityEvent('user_logout', getCurrentUserId(), 'User logged out successfully');
}

// Perform secure logout
logout();

// Redirect to login page with success message
header('Location: login.php?logout=1');
exit();
?> 