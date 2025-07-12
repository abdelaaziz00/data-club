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
$username = 'root'; // Change if needed
$db_password = '';
$dbname = 'data_club';

// Initialize variables
$message = '';
$messageType = ''; // 'success' or 'error'

// Input validation and sanitization
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Password strength validation
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Too short! Need at least 8 characters.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Missing uppercase letter (A-Z).';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Missing lowercase letter (a-z).';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Missing number (0-9).';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Missing special character (!@#$%^&*).';
    }
    
    return $errors;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = isset($_POST['firstName']) ? validateInput($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? validateInput($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? validateInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password for hashing
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    // Enhanced validation
    $errors = [];
    
    // Check for empty fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = 'Please fill in all fields.';
    }
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Validate name fields (only letters, spaces, and hyphens)
    if (!empty($firstName) && !preg_match('/^[a-zA-Z\s\-]+$/', $firstName)) {
        $errors[] = 'First name can only contain letters, spaces, and hyphens.';
    }
    
    if (!empty($lastName) && !preg_match('/^[a-zA-Z\s\-]+$/', $lastName)) {
        $errors[] = 'Last name can only contain letters, spaces, and hyphens.';
    }
    
    // Validate name length
    if (strlen($firstName) < 2 || strlen($firstName) > 50) {
        $errors[] = 'First name must be between 2 and 50 characters.';
    }
    
    if (strlen($lastName) < 2 || strlen($lastName) > 50) {
        $errors[] = 'Last name must be between 2 and 50 characters.';
    }
    
    // Password validation
    if (!empty($password)) {
        $password_errors = validatePassword($password);
        $errors = array_merge($errors, $password_errors);
    }
    
    // Check password confirmation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    

    
    if (empty($errors)) {
        try {
            $conn = new mysqli($servername, $username, $db_password, $dbname);
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: ' . $conn->connect_error);
            }

            // Set charset to prevent SQL injection
            $conn->set_charset("utf8mb4");

            // Check if email already exists
            $stmt = $conn->prepare('SELECT ID_MEMBER FROM member WHERE EMAIL = ? LIMIT 1');
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = 'Email already exists. Please use a different email or try logging in.';
                $messageType = 'error';
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new member with hashed password
                $stmt2 = $conn->prepare('INSERT INTO member (FIRST_NAME, LAST_NAME, EMAIL, PASSWORD) VALUES (?, ?, ?, ?)');
                if (!$stmt2) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }

                $stmt2->bind_param('ssss', $firstName, $lastName, $email, $hashed_password);
                if ($stmt2->execute()) {
                    $inserted_id = $conn->insert_id;
                    $message = 'Account created successfully! You can now log in.';
                    $messageType = 'success';
                    
                    // Clear form data on success
                    $firstName = $lastName = $email = '';
                    
                    // Redirect to login page after a short delay
                    header('Refresh: 2; URL=login.php');
                } else {
                    throw new Exception('Registration failed: ' . $stmt2->error);
                }
                $stmt2->close();
            }
            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            $message = 'An error occurred during registration. Please try again later.';
            $messageType = 'error';
            error_log('Signup error: ' . $e->getMessage());
        }
    } else {
        // Create a more user-friendly error message
        if (count($errors) === 1) {
            $message = $errors[0];
        } elseif (count($errors) <= 3) {
            $message = 'Password needs: ' . implode(', ', $errors);
        } else {
            $message = 'Password needs: ' . implode(', ', array_slice($errors, 0, 2)) . ' and ' . (count($errors) - 2) . ' more requirements.';
        }
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataClub - Sign Up</title>
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
        <!-- Left Side - Signup Form -->
        <div class="flex-1 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md">
                <!-- Back Button -->
                <button onclick="goBack()" class="flex items-center text-brand-slate hover:text-brand-red transition-colors mb-8 group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Home
                </button>

                <!-- Signup Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-brand-dark mb-2">Join DataClub</h1>
                    <p class="text-brand-slate">Create your account and start your data science journey</p>
                </div>

                <!-- Message Display -->
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Signup Form -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <form method="POST" class="space-y-6">
                        <!-- Name Fields -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-brand-dark mb-2">
                                    First Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        id="firstName"
                                        name="firstName"
                                        type="text"
                                        required
                                        value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>"
                                        class="block w-full pl-10 pr-3 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                        placeholder="John"
                                    />
                                </div>
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-brand-dark mb-2">
                                    Last Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        id="lastName"
                                        name="lastName"
                                        type="text"
                                        required
                                        value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>"
                                        class="block w-full pl-10 pr-3 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                        placeholder="Doe"
                                    />
                                </div>
                            </div>
                        </div>

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
                                    onkeyup="checkPasswordStrength(this.value)"
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
                            <!-- Password Strength Indicator -->
                            <div id="passwordStrength" class="mt-2 text-xs">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-brand-slate">Password strength:</span>
                                    <div class="flex-1 h-2 rounded bg-gray-200 overflow-hidden relative w-40">
                                        <div id="strengthBar" class="h-2 rounded transition-all duration-300" style="width:0%; background:linear-gradient(90deg,#f87171,#fbbf24,#facc15,#4ade80,#22c55e);"></div>
                                    </div>
                                    <span id="strengthText" class="font-medium ml-2">Start typing...</span>
                                </div>
                                <ul class="space-y-1 mt-2">
                                    <li class="flex items-center"><span id="lengthIcon" class="mr-2 text-gray-400">✗</span> <span class="text-gray-700">8+ characters</span></li>
                                    <li class="flex items-center"><span id="uppercaseIcon" class="mr-2 text-gray-400">✗</span> <span class="text-gray-700">Uppercase letter (A-Z)</span></li>
                                    <li class="flex items-center"><span id="lowercaseIcon" class="mr-2 text-gray-400">✗</span> <span class="text-gray-700">Lowercase letter (a-z)</span></li>
                                    <li class="flex items-center"><span id="numberIcon" class="mr-2 text-gray-400">✗</span> <span class="text-gray-700">Number (0-9)</span></li>
                                    <li class="flex items-center"><span id="specialIcon" class="mr-2 text-gray-400">✗</span> <span class="text-gray-700">Special character (!@#$...)</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-brand-dark mb-2">
                                Confirm Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-brand-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="confirmPassword"
                                    name="confirmPassword"
                                    type="password"
                                    required
                                    class="block w-full pl-10 pr-12 py-3 border border-brand-slate/20 rounded-lg text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    onclick="togglePassword('confirmPassword')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg id="confirmPasswordEyeIcon" class="h-5 w-5 text-brand-slate hover:text-brand-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                Create Account
                            </button>
                        </div>

                        <!-- Additional Links -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600">
                                Already have an account?
                                <a href="login.php" class="text-brand-slate hover:text-brand-red transition-colors font-medium">
                                  Login
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Footer Note -->
                <div class="text-center mt-6">
                    <p class="text-xs text-gray-500">
                        By joining DataClub, you agree to our
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

        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const lengthIcon = document.getElementById('lengthIcon');
            const uppercaseIcon = document.getElementById('uppercaseIcon');
            const lowercaseIcon = document.getElementById('lowercaseIcon');
            const numberIcon = document.getElementById('numberIcon');
            const specialIcon = document.getElementById('specialIcon');

            // Check each requirement
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);

            // Update checklist icons
            lengthIcon.textContent = hasLength ? '✓' : '✗';
            lengthIcon.className = hasLength ? 'mr-2 text-green-500 font-bold' : 'mr-2 text-gray-400';
            uppercaseIcon.textContent = hasUppercase ? '✓' : '✗';
            uppercaseIcon.className = hasUppercase ? 'mr-2 text-green-500 font-bold' : 'mr-2 text-gray-400';
            lowercaseIcon.textContent = hasLowercase ? '✓' : '✗';
            lowercaseIcon.className = hasLowercase ? 'mr-2 text-green-500 font-bold' : 'mr-2 text-gray-400';
            numberIcon.textContent = hasNumber ? '✓' : '✗';
            numberIcon.className = hasNumber ? 'mr-2 text-green-500 font-bold' : 'mr-2 text-gray-400';
            specialIcon.textContent = hasSpecial ? '✓' : '✗';
            specialIcon.className = hasSpecial ? 'mr-2 text-green-500 font-bold' : 'mr-2 text-gray-400';

            // Calculate strength
            const checks = [hasLength, hasUppercase, hasLowercase, hasNumber, hasSpecial];
            const passedChecks = checks.filter(check => check).length;
            const percent = (passedChecks / 5) * 100;
            strengthBar.style.width = percent + '%';
            strengthBar.style.background =
                percent < 40 ? '#f87171' :
                percent < 60 ? '#fbbf24' :
                percent < 80 ? '#facc15' :
                percent < 100 ? '#4ade80' : '#22c55e';

            // Update strength text
            if (password.length === 0) {
                strengthText.textContent = 'Start typing...';
                strengthText.className = 'font-medium text-gray-400 ml-2';
            } else if (passedChecks === 0) {
                strengthText.textContent = 'Very Weak';
                strengthText.className = 'font-medium text-red-500 ml-2';
            } else if (passedChecks <= 2) {
                strengthText.textContent = 'Weak';
                strengthText.className = 'font-medium text-orange-500 ml-2';
            } else if (passedChecks <= 3) {
                strengthText.textContent = 'Fair';
                strengthText.className = 'font-medium text-yellow-500 ml-2';
            } else if (passedChecks <= 4) {
                strengthText.textContent = 'Good';
                strengthText.className = 'font-medium text-blue-500 ml-2';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.className = 'font-medium text-green-500 ml-2';
            }
        }
    </script>
</body>
</html> 