<?php
session_start();
require_once 'db.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moroccan Data Scientists</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#F05454',
                        'brand-slate': '#30475E',
                        'brand-gray': '#F5F5F5',
                        'brand-dark': '#121212',
                                                'red-custom': '#F05454',
                        'gray-custom': '#F5F5F5',
                        'slate-custom': '#30475E',
                        'black-custom': '#121212'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-gray">
    <!-- Header -->
    <header class="bg-white py-4 px-6">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <img src="../static/images/mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="home.html" class="text-brand-dark hover:text-brand-red transition-colors">Home Page</a>
                <a href="events.html" class="text-brand-dark hover:text-brand-red transition-colors">Events</a>
                <a href="clubs.html" class="text-brand-dark hover:text-brand-red transition-colors">Clubs</a>
                <a href="contactus.html" class="text-brand-dark hover:text-brand-red transition-colors">Contact us</a>

                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="profile-menu-button" class="flex items-center space-x-2 text-gray-600 hover:text-slate-custom transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo strtoupper(substr($user['FIRST_NAME'], 0, 1) . substr($user['LAST_NAME'], 0, 1)); ?>
                            </div>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-20 px-6">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div>
                <h1 class="text-5xl font-bold text-brand-slate mb-6 leading-tight">
                    Empowering the Next Generation of Data Scientists
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Join DataClub and unlock your potential in AI and Data Science. Connect with like-minded peers and industry experts to enhance your skills and knowledge.
                </p>
                <div class="flex space-x-4">
                    <button class="bg-brand-red text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                        Join Us
                    </button>
                    <button class="border border-brand-red text-brand-red px-6 py-3 rounded-lg font-semibold hover:bg-brand-red hover:text-white transition-colors">
                        Learn More
                    </button>
                </div>
            </div>

            <!-- Right Image -->
            <div class="flex justify-center">
                <img src="https://images.pexels.com/photos/8386440/pexels-photo-8386440.jpeg" alt="Data Analytics Dashboard" class="w-full h-80 object-cover rounded-lg shadow-lg">
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-20 px-6 bg-white">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div>
                <p class="text-sm text-brand-slate mb-2">DataClub</p>
                <h2 class="text-4xl font-bold text-brand-red mb-6">What is DataClub?</h2>
                <p class="text-gray-600 mb-8 leading-relaxed">
                    DataClub is a vibrant community dedicated to empowering students and young professionals in the fields of AI and Data Science. Our goal is to foster collaboration, innovation, and skill development through engaging events and resources.
                </p>
                <div class="flex space-x-4">
                    <button class="border border-brand-slate text-brand-slate px-6 py-2 rounded-lg font-medium hover:bg-brand-slate hover:text-white transition-colors">
                        Join
                    </button>
                    <button class="text-brand-slate font-medium flex items-center hover:text-brand-red transition-colors">
                        Learn
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right Image -->
            <div class="flex justify-center">
                <img src="https://images.pexels.com/photos/7688336/pexels-photo-7688336.jpeg" alt="AI and Machine Learning" class="w-full h-96 object-cover rounded-lg shadow-lg">

            </div>
        </div>
    </section>

    <!-- Why Join Section -->
    <section class="py-20 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-brand-dark mb-4">Why Join DataClub?</h2>
            <p class="text-gray-600 mb-16">Unlock your potential in data science and AI</p>

            <div class="grid md:grid-cols-4 gap-8">
                <!-- Expert Talks -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-red rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-brand-dark mb-2">Expert Talks</h3>
                    <p class="text-gray-600 text-sm">Learn from industry leaders and data science experts</p>
                </div>

                <!-- Hands-on Workshops -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-red rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-brand-dark mb-2">Hands-on Workshops</h3>
                    <p class="text-gray-600 text-sm">Practice with real datasets and cutting-edge tools</p>
                </div>

                <!-- AI Projects -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-red rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-brand-dark mb-2">AI Projects</h3>
                    <p class="text-gray-600 text-sm">Build portfolio-worthy projects in AI and machine learning</p>
                </div>

                <!-- Networking -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-red rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-brand-dark mb-2">Networking</h3>
                    <p class="text-gray-600 text-sm">Connect with peers and professionals in the field</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-brand-red text-white py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Logo and Links -->
            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center space-x-3 mb-6">
                    <img src="mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
                </div>

                <!-- Navigation Links -->
                <div class="flex space-x-8 mb-6">
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Home Page</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Events List</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">clubs list</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Contact Us</a>
                </div>

                <!-- Social Links -->
                <div class="flex space-x-8">
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Instagram Page</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">LinkedIn Page</a>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="border-t border-white/20 pt-6">
                <div class="flex justify-between items-center text-sm">
                    <p class="text-white/80">© 2025 DataClub. All rights reserved.</p>
                    <div class="flex space-x-6">
                        <a href="#" class="text-white/80 hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="text-white/80 hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="text-white/80 hover:text-white transition-colors">Cookie Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>