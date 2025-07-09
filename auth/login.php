<?php
session_start();
// Database config
$servername = 'localhost';
$username = 'root'; // Change if needed
$password = '';
$dbname = 'data_club';

// Initialize variables
$message = '';
$messageType = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email && $pass) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $message = 'Database connection failed: ' . $conn->connect_error;
            $messageType = 'error';
        } else {
            // Check admin table
            $stmt = $conn->prepare('SELECT ID_ADMIN, PASSWORD FROM admin WHERE EMAIL = ?');
            if (!$stmt) {
                $message = 'Prepare failed (admin): ' . $conn->error;
                $messageType = 'error';
            } else {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($row['PASSWORD'] === $pass) {
                        $_SESSION['user_id'] = $row['ID_ADMIN'];
                        $_SESSION['first_name'] = $row['FIRST_NAME'] ;
                        $_SESSION['last_name'] = $row['LAST_NAME'] ;
                        $_SESSION['privilege'] = 1;
                        $message = 'Admin login successful! Redirecting...';
                        $messageType = 'success';
                        // Redirect to admin dashboard
                        header('Location: ../admin/dashboard.php');
                        exit();
                    } else {
                        $message = 'Invalid password.';
                        $messageType = 'error';
                    }
                } else {
                    // Not found in admin, check member
                    $stmt2 = $conn->prepare('SELECT ID_MEMBER, PASSWORD FROM member WHERE EMAIL = ?');
                    if (!$stmt2) {
                        $message = 'Prepare failed (member): ' . $conn->error;
                        $messageType = 'error';
                    } else {
                        $stmt2->bind_param('s', $email);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        if ($row2 = $result2->fetch_assoc()) {
                            if ($row2['PASSWORD'] === $pass) {
                                $_SESSION['user_id'] = $row2['ID_MEMBER'];
                                $_SESSION['first_name'] = $row['FIRST_NAME'] ;
                                $_SESSION['last_name'] = $row['LAST_NAME'] ;
                                $_SESSION['privilege'] = 2;
                                $message = 'Member login successful! Redirecting...';
                                $messageType = 'success';
                                // Redirect to member dashboard
                                header('Location: ../user/home.php');
                                exit();
                            } else {
                                $message = 'Invalid password.';
                                $messageType = 'error';
                            }
                        } else {
                            $message = 'User not found.';
                            $messageType = 'error';
                        }
                        $stmt2->close();
                    }
                }
                $stmt->close();
            }
            $conn->close();
        }
    } else {
        $message = 'Please enter both email and password.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataClub - Login</title>
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
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md">
                <!-- Back Button -->
                <button onclick="goBack()" class="flex items-center text-brand-slate hover:text-brand-red transition-colors mb-8 group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Home
                </button>

                <!-- Login Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-brand-dark mb-2">Welcome Back</h1>
                    <p class="text-brand-slate">Sign in to your DataClub account</p>
                </div>

                <!-- Message Display -->
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
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

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-brand-dark mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    class="block w-full pl-10 pr-12 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg id="passwordEyeIcon" class="h-5 w-5 text-brand-slate hover:text-brand-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                Sign In
                            </button>
                        </div>

                        <!-- Additional Links -->
                        <div class="text-center">
                            <a href="#" class="text-sm text-brand-slate hover:text-brand-red transition-colors font-medium">
                                Forgot your password?
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Sign Up Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="signup.php" class="text-brand-slate hover:text-brand-red transition-colors font-medium">
                            Sign up
                        </a>
                    </p>
                </div>

                <!-- Footer Note -->
                <div class="text-center mt-6">
                    <p class="text-xs text-gray-500">
                        By signing in, you agree to our
                        <a href="#" class="text-brand-slate hover:text-brand-red transition-colors">Terms of Service</a>
                        and
                        <a href="#" class="text-brand-slate hover:text-brand-red transition-colors">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Welcome Section -->
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-brand-red to-red-600 items-center justify-center p-12 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-20 h-20 border-2 border-white rounded-full"></div>
                <div class="absolute top-32 right-20 w-16 h-16 border-2 border-white rounded-lg rotate-45"></div>
                <div class="absolute bottom-20 left-20 w-24 h-24 border-2 border-white rounded-full"></div>
                <div class="absolute bottom-32 right-10 w-12 h-12 border-2 border-white rounded-lg rotate-12"></div>
            </div>

            <div class="text-center text-white relative z-10 max-w-md">
                <!-- Logo -->
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>

                <!-- Welcome Text -->
                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                    Welcome to
                    <span class="block text-white/90">DataClub</span>
                </h1>

                <p class="text-xl text-white/90 mb-8 leading-relaxed">
                    Join Morocco's premier community of student data scientists and AI enthusiasts
                </p>

                <!-- Features List -->
                <div class="space-y-4 text-left">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                        <span class="text-white/90">Connect with 500+ data science students</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                        <span class="text-white/90">Access exclusive workshops and events</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                        <span class="text-white/90">Build your AI and ML portfolio</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                        <span class="text-white/90">Network with industry experts</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-6 mt-12 pt-8 border-t border-white/20">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">500+</div>
                        <div class="text-sm text-white/80">Active Members</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">50+</div>
                        <div class="text-sm text-white/80">Events Hosted</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'EyeIcon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                field.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        function goBack() {
            // You can customize this function to redirect to your homepage
            window.location.href = '../index.html';
        }
    </script>
</body>
</html>