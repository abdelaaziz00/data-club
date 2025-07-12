<?php
// Set session security parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_path', '/');
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Database config
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'data_club';

// Initialize variables
$message = '';
$messageType = '';
$token_valid = false;
$user_type = '';
$user_id = '';

// Password strength validation
function validatePassword($password) {
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

// Validate reset token
function validateResetToken($token) {
    global $servername, $username, $password, $dbname;
    
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");

        // Check admin table first
        $stmt = $conn->prepare('SELECT ID_ADMIN, RESET_TOKEN_EXPIRY FROM admin WHERE RESET_TOKEN = ? AND RESET_TOKEN_EXPIRY > NOW() LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            return ['valid' => true, 'user_type' => 'admin', 'user_id' => $user['ID_ADMIN']];
        }
        
        $stmt->close();

        // Check member table
        $stmt2 = $conn->prepare('SELECT ID_MEMBER, RESET_TOKEN_EXPIRY FROM member WHERE RESET_TOKEN = ? AND RESET_TOKEN_EXPIRY > NOW() LIMIT 1');
        $stmt2->bind_param('s', $token);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            $user = $result2->fetch_assoc();
            $stmt2->close();
            $conn->close();
            return ['valid' => true, 'user_type' => 'member', 'user_id' => $user['ID_MEMBER']];
        }
        
        $stmt2->close();
        $conn->close();
        
        return ['valid' => false];
        
    } catch (Exception $e) {
        error_log('Token validation error: ' . $e->getMessage());
        return ['valid' => false];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        $message = 'Invalid reset link.';
        $messageType = 'error';
    } elseif (empty($new_password) || empty($confirm_password)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        // Validate password strength
        $password_errors = validatePassword($new_password);
        if (!empty($password_errors)) {
            $message = implode(' ', $password_errors);
            $messageType = 'error';
        } else {
            // Validate token
            $token_validation = validateResetToken($token);
            
            if (!$token_validation['valid']) {
                $message = 'Invalid or expired reset link. Please request a new one.';
                $messageType = 'error';
            } else {
                try {
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    if ($conn->connect_error) {
                        throw new Exception('Database connection failed: ' . $conn->connect_error);
                    }

                    $conn->set_charset("utf8mb4");

                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password and clear reset token
                    if ($token_validation['user_type'] === 'admin') {
                        $stmt = $conn->prepare('UPDATE admin SET PASSWORD = ?, RESET_TOKEN = NULL, RESET_TOKEN_EXPIRY = NULL WHERE ID_ADMIN = ?');
                    } else {
                        $stmt = $conn->prepare('UPDATE member SET PASSWORD = ?, RESET_TOKEN = NULL, RESET_TOKEN_EXPIRY = NULL WHERE ID_MEMBER = ?');
                    }
                    
                    $stmt->bind_param('si', $hashed_password, $token_validation['user_id']);
                    
                    if ($stmt->execute()) {
                        $message = 'Password updated successfully! You can now log in with your new password.';
                        $messageType = 'success';
                        
                        // Redirect to login page after 3 seconds
                        header('Refresh: 3; URL=login.php');
                    } else {
                        throw new Exception('Failed to update password: ' . $stmt->error);
                    }
                    
                    $stmt->close();
                    $conn->close();
                    
                } catch (Exception $e) {
                    $message = 'An error occurred. Please try again later.';
                    $messageType = 'error';
                    error_log('Password reset error: ' . $e->getMessage());
                }
            }
        }
    }
} else {
    // Validate token on page load
    $token = $_GET['token'] ?? '';
    if (!empty($token)) {
        $token_validation = validateResetToken($token);
        if ($token_validation['valid']) {
            $token_valid = true;
            $user_type = $token_validation['user_type'];
            $user_id = $token_validation['user_id'];
        } else {
            $message = 'Invalid or expired reset link. Please request a new one.';
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid reset link.';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataClub - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#F05454',
                        'brand-slate': '#30475E',
                        'brand-gray': '#F5F5F5',
                        'brand-dark': '#121212'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-brand-gray">
    <div class="min-h-screen flex">
        <!-- Left Side - Reset Password Form -->
        <div class="flex-1 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md">
                <!-- Back Button -->
                <button onclick="goBack()" class="flex items-center text-brand-slate hover:text-brand-red transition-colors mb-8 group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Login
                </button>

                <!-- Reset Password Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-brand-dark mb-2">Reset Password</h1>
                    <p class="text-brand-slate">Enter your new password below</p>
                </div>

                <!-- Message Display -->
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Reset Password Form -->
                <?php if ($token_valid): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <form method="POST" class="space-y-6">
                            <!-- New Password Field -->
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-brand-dark mb-2">
                                    New Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        id="new_password"
                                        name="new_password"
                                        type="password"
                                        required
                                        class="block w-full pl-10 pr-12 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        onclick="togglePassword('new_password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    >
                                        <svg id="new_passwordEyeIcon" class="h-5 w-5 text-brand-slate hover:text-brand-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                                </p>
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-brand-dark mb-2">
                                    Confirm New Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        id="confirm_password"
                                        name="confirm_password"
                                        type="password"
                                        required
                                        class="block w-full pl-10 pr-12 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        onclick="togglePassword('confirm_password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    >
                                        <svg id="confirm_passwordEyeIcon" class="h-5 w-5 text-brand-slate hover:text-brand-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button
                                    type="submit"
                                    class="w-full bg-brand-red text-white py-3 px-4 rounded-lg font-semibold hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 transition-all duration-300 transform hover:scale-[1.02] shadow-lg"
                                >
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Back to Login Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Remember your password?
                        <a href="login.php" class="text-brand-slate hover:text-brand-red transition-colors font-medium">
                            Back to Login
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Image/Illustration -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand-red to-red-600 items-center justify-center">
            <div class="text-center text-white">
                <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-4">Secure Password Reset</h2>
                <p class="text-lg opacity-90">Set a new secure password for your account.</p>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'EyeIcon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
            } else {
                field.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html> 