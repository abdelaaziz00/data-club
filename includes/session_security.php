<?php
// Session Security Functions
// Include this file at the top of all pages that require authentication

// Set secure session parameters if not already set
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Session timeout settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_WARNING', 1500); // 25 minutes (warning before timeout)

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['privilege']) && $_SESSION['privilege'] === 1;
}

// Function to check if user is member
function isMember() {
    return isLoggedIn() && isset($_SESSION['privilege']) && $_SESSION['privilege'] === 2;
}

// Function to check session timeout
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $current_time = time();
    $last_activity = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 0;
    
    // Check if session has timed out
    if (($current_time - $last_activity) > SESSION_TIMEOUT) {
        // Session has expired
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = $current_time;
    return true;
}

// Function to check if session is about to expire (for warning)
function isSessionExpiringSoon() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $current_time = time();
    $last_activity = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 0;
    
    return (($current_time - $last_activity) > SESSION_WARNING);
}

// Function to regenerate session ID periodically
function regenerateSessionIfNeeded() {
    if (isLoggedIn()) {
        $current_time = time();
        $last_regeneration = isset($_SESSION['last_regeneration']) ? $_SESSION['last_regeneration'] : 0;
        
        // Regenerate session ID every 5 minutes for security
        if (($current_time - $last_regeneration) > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = $current_time;
        }
    }
}

// Function to require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
    
    if (!checkSessionTimeout()) {
        header('Location: ../auth/login.php?timeout=1');
        exit();
    }
    
    regenerateSessionIfNeeded();
}

// Function to require admin privileges
function requireAdmin() {
    requireAuth();
    
    if (!isAdmin()) {
        header('Location: ../user/home.php?error=unauthorized');
        exit();
    }
}

// Function to require member privileges
function requireMember() {
    requireAuth();
    
    if (!isMember()) {
        header('Location: ../user/home.php?error=unauthorized');
        exit();
    }
}

// Function to get current user ID
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

// Function to get current user name
function getCurrentUserName() {
    if (isLoggedIn()) {
        $first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
        $last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
        return trim($first_name . ' ' . $last_name);
    }
    return null;
}

// Function to get current user privilege level
function getCurrentUserPrivilege() {
    return isLoggedIn() ? $_SESSION['privilege'] : null;
}

// Function to logout user
function logout() {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Function to validate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to sanitize output
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Function to validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate password strength
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }
    
    return $errors;
}

// Function to log security events
function logSecurityEvent($event, $user_id = null, $details = '') {
    $log_file = sys_get_temp_dir() . '/security_events.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $user_id ?: (isLoggedIn() ? getCurrentUserId() : 'anonymous');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = sprintf(
        "[%s] Event: %s | User: %s | IP: %s | UA: %s | Details: %s\n",
        $timestamp,
        $event,
        $user_id,
        $ip,
        $user_agent,
        $details
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Auto-check session timeout on every request
if (isLoggedIn()) {
    if (!checkSessionTimeout()) {
        logSecurityEvent('session_timeout', getCurrentUserId());
        header('Location: ../auth/login.php?timeout=1');
        exit();
    }
    
    regenerateSessionIfNeeded();
}
?> 