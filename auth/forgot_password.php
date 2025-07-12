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

// Rate limiting for password reset requests
function checkResetRateLimit($email) {
    $attempts_file = sys_get_temp_dir() . '/reset_attempts.json';
    $max_attempts = 3;
    $lockout_time = 3600; // 1 hour
    
    $attempts = [];
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    $current_time = time();
    $user_attempts = isset($attempts[$email]) ? $attempts[$email] : [];
    
    // Remove old attempts
    $user_attempts = array_filter($user_attempts, function($time) use ($current_time, $lockout_time) {
        return ($current_time - $time) < $lockout_time;
    });
    
    if (count($user_attempts) >= $max_attempts) {
        return false; // Account is locked
    }
    
    // Add current attempt
    $user_attempts[] = $current_time;
    $attempts[$email] = $user_attempts;
    
    file_put_contents($attempts_file, json_encode($attempts));
    return true;
}

// Generate secure reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Send reset email (placeholder - implement actual email sending)
function sendResetEmail($email, $token) {
    // In a real implementation, you would use PHPMailer or similar
    // For now, we'll just log the token
    $reset_link = "http://localhost/data-club/auth/reset_password.php?token=" . $token;
    
    // Log the reset request for development
    $log_file = sys_get_temp_dir() . '/password_resets.log';
    $log_entry = date('Y-m-d H:i:s') . " - Reset requested for: $email\n";
    $log_entry .= "Reset link: $reset_link\n\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    return true; // Assume email was sent successfully
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif (!checkResetRateLimit($email)) {
        $message = 'Too many reset attempts. Please try again in 1 hour.';
        $messageType = 'error';
    } else {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: ' . $conn->connect_error);
            }

            $conn->set_charset("utf8mb4");

            // Check if email exists in either admin or member table
            $stmt = $conn->prepare('SELECT ID_ADMIN, FIRST_NAME, LAST_NAME FROM admin WHERE EMAIL = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $admin_result = $stmt->get_result();
            
            if ($admin_result->num_rows > 0) {
                $user = $admin_result->fetch_assoc();
                $user_type = 'admin';
                $user_id = $user['ID_ADMIN'];
            } else {
                $stmt2 = $conn->prepare('SELECT ID_MEMBER, FIRST_NAME, LAST_NAME FROM member WHERE EMAIL = ? LIMIT 1');
                $stmt2->bind_param('s', $email);
                $stmt2->execute();
                $member_result = $stmt2->get_result();
                
                if ($member_result->num_rows > 0) {
                    $user = $member_result->fetch_assoc();
                    $user_type = 'member';
                    $user_id = $user['ID_MEMBER'];
                } else {
                    $message = 'If an account with this email exists, you will receive a password reset link.';
                    $messageType = 'success'; // Don't reveal if email exists or not
                    $conn->close();
                    exit();
                }
                $stmt2->close();
            }
            $stmt->close();

            // Generate reset token
            $reset_token = generateResetToken();
            $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token in database
            if ($user_type === 'admin') {
                $update_stmt = $conn->prepare('UPDATE admin SET RESET_TOKEN = ?, RESET_TOKEN_EXPIRY = ? WHERE ID_ADMIN = ?');
            } else {
                $update_stmt = $conn->prepare('UPDATE member SET RESET_TOKEN = ?, RESET_TOKEN_EXPIRY = ? WHERE ID_MEMBER = ?');
            }
            
            $update_stmt->bind_param('ssi', $reset_token, $token_expiry, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Send reset email
            if (sendResetEmail($email, $reset_token)) {
                $message = 'If an account with this email exists, you will receive a password reset link.';
                $messageType = 'success';
            } else {
                $message = 'Failed to send reset email. Please try again later.';
                $messageType = 'error';
            }

            $conn->close();
            
        } catch (Exception $e) {
            $message = 'An error occurred. Please try again later.';
            $messageType = 'error';
            error_log('Password reset error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataClub - Forgot Password</title>
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
        <!-- Left Side - Forgot Password Form -->
        <div class="flex-1 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md">
                <!-- Back Button -->
                <button onclick="goBack()" class="flex items-center text-brand-slate hover:text-brand-red transition-colors mb-8 group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Login
                </button>

                <!-- Forgot Password Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-brand-dark mb-2">Forgot Password?</h1>
                    <p class="text-brand-slate">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <!-- Message Display -->
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Forgot Password Form -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <form method="POST" class="space-y-6">
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-brand-dark mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                    placeholder="john@example.com"
                                />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                                type="submit"
                                class="w-full bg-brand-red text-white py-3 px-4 rounded-lg font-semibold hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 transition-all duration-300 transform hover:scale-[1.02] shadow-lg"
                            >
                                Send Reset Link
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Back to Login Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Remember your password?
                        <a href="login.php" class="text-brand-slate hover:text-brand-red transition-colors font-medium">
                            Back to Login
                        </a>
                    </p>
                </div>

                <!-- Footer Note -->
                <div class="text-center mt-6">
                    <p class="text-xs text-gray-500">
                        The reset link will expire in 1 hour for security reasons.
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
                <p class="text-lg opacity-90">We'll help you get back to your account safely and securely.</p>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html> 