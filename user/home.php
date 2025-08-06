<?php
session_start();
require_once 'db.php';

// $user_id = $_SESSION['user_id'];

// $stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
// $stmt->execute([$user_id]);
// $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* Glassmorphic effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Subtle gradient background */
        body {
            background: linear-gradient(135deg, #F5F5F5 0%, #ffffff 50%, #F5F5F5 100%);
        }
        
        /* Hero text reveal animation */
        .hero-text {
            background: linear-gradient(135deg, #30475E 0%, #F05454 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0;
            transform: translateY(30px);
            animation: heroReveal 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes heroReveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Elegant button hover */
        .btn-primary {
            background: linear-gradient(135deg, #F05454 0%, #e74c3c 100%);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(240, 84, 84, 0.3);
        }
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(48, 71, 94, 0.15);
        }
        
        /* Animated underline */
        .underline-animate {
            position: relative;
        }
        
        .underline-animate::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: linear-gradient(90deg, #F05454, #30475E);
            transition: width 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .underline-animate:hover::after {
            width: 100%;
        }
        
        /* Parallax effect */
        .parallax-slow {
            transform: translateZ(0);
            will-change: transform;
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #30475E 0%, #F05454 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Animated border */
        .animated-border {
            position: relative;
            background: linear-gradient(90deg, #F05454, #30475E, #F05454);
            background-size: 200% 100%;
            animation: borderFlow 3s ease-in-out infinite;
        }
        
        @keyframes borderFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        /* Stats counter animation */
        .stat-number {
            font-weight: 700;
            color: #30475E;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="relative min-h-screen">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="relative py-32 px-6 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-slate/5 to-brand-red/5"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="hero-text text-5xl lg:text-6xl font-bold mb-8 leading-tight">
                        Empowering the Next Generation of 
                        <span class="gradient-text">Data Scientists</span>
                    </h1>
                    <p class="text-xl text-gray-700 mb-10 leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                        Join DataClub and unlock your potential in AI and Data Science. Connect with like-minded peers and industry experts to enhance your skills and knowledge.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-6" data-aos="fade-up" data-aos-delay="400">
                        <button class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg">
                            <i class="fas fa-rocket mr-2"></i>Join Us
                        </button>
                        <button class="border-2 border-brand-slate text-brand-slate px-8 py-4 rounded-lg font-semibold text-lg hover:bg-brand-slate hover:text-white transition-all duration-300 underline-animate">
                            <i class="fas fa-info-circle mr-2"></i>Learn More
                        </button>
                    </div>
                </div>
                <div class="relative" data-aos="fade-left" data-aos-duration="1000">
                    <div class="glass rounded-3xl p-8 backdrop-blur-lg">
                        <img src="https://images.pexels.com/photos/8386440/pexels-photo-8386440.jpeg" 
                             alt="Data Analytics Dashboard" 
                             class="w-full h-80 object-cover rounded-2xl shadow-2xl parallax-slow">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 px-6 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center card-hover glass rounded-2xl p-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl font-bold gradient-text mb-2">
                        <span class="stat-number" data-target="500">0</span>+
                    </div>
                    <p class="text-gray-600 font-medium">Active Members</p>
                </div>
                <div class="text-center card-hover glass rounded-2xl p-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl font-bold gradient-text mb-2">
                        <span class="stat-number" data-target="50">0</span>+
                    </div>
                    <p class="text-gray-600 font-medium">Events Organized</p>
                </div>
                <div class="text-center card-hover glass rounded-2xl p-8" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl font-bold gradient-text mb-2">
                        <span class="stat-number" data-target="25">0</span>+
                    </div>
                    <p class="text-gray-600 font-medium">Expert Speakers</p>
                </div>
                <div class="text-center card-hover glass rounded-2xl p-8" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-4xl font-bold gradient-text mb-2">
                        <span class="stat-number" data-target="15">0</span>+
                    </div>
                    <p class="text-gray-600 font-medium">University Clubs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold gradient-text mb-6">Why Choose DataClub?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Unlock your potential in data science and AI through our comprehensive programs
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover glass rounded-2xl p-8 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-brand-red to-red-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-microchip text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-brand-slate mb-4">AI Workshops</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Hands-on sessions with real-world datasets and cutting-edge AI tools. Learn from industry experts and build practical skills.
                    </p>
                </div>
                
                <div class="card-hover glass rounded-2xl p-8 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-brand-red to-red-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-brain text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-brand-slate mb-4">Expert Talks</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Connect with top data scientists and AI professionals. Gain insights from industry leaders and expand your network.
                    </p>
                </div>
                
                <div class="card-hover glass rounded-2xl p-8 text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-brand-red to-red-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-brand-slate mb-4">Networking</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Build meaningful connections with peers and professionals. Join a community of passionate data scientists and AI enthusiasts.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-24 px-6 bg-gradient-to-br from-brand-slate/5 to-brand-red/5">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div data-aos="fade-right" data-aos-duration="1000" class="pt-8">
                    <h2 class="text-4xl font-bold gradient-text mb-8">What is DataClub?</h2>
                    <p class="text-xl text-gray-700 mb-8 leading-relaxed">
                        DataClub is a vibrant community dedicated to empowering students and young professionals in the fields of AI and Data Science. Our goal is to foster collaboration, innovation, and skill development through engaging events and resources.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                            Join Community
                        </button>
                        <button class="border-2 border-brand-slate text-brand-slate px-6 py-3 rounded-lg font-semibold hover:bg-brand-slate hover:text-white transition-all duration-300 underline-animate">
                            Learn More
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
                <div class="relative" data-aos="fade-left" data-aos-duration="1000">
                    <div class="glass rounded-3xl p-8 backdrop-blur-lg">
                        <img src="https://images.pexels.com/photos/7688336/pexels-photo-7688336.jpeg" 
                             alt="AI and Machine Learning" 
                             class="w-full h-96 object-cover rounded-2xl shadow-2xl ">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-brand-red text-white py-16 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="animated-border h-1 mb-12 rounded-full"></div>
            
            <div class="flex flex-col items-center mb-12">
                <div class="flex items-center space-x-3 mb-8">
                    <img src="../static/images/Frame 13.png" alt="MDS Logo" class="w-40 h-30 object-contain mix-blend-multiply">
                </div>
                
                <div class="flex flex-wrap justify-center gap-8 mb-8">
                    <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Home Page</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Events List</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Clubs List</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Contact Us</a>
                </div>
                
                <div class="flex space-x-8">
                    <a href="#" class="text-white/80 hover:text-white transition-all duration-300 hover:scale-110">
                        <i class="fab fa-instagram text-2xl"></i>
                    </a>
                    <a href="#" class="text-white/80 hover:text-white transition-all duration-300 hover:scale-110">
                        <i class="fab fa-linkedin text-2xl"></i>
                    </a>
                </div>
            </div>
            
            <div class="border-t border-white/20 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                    <p class="text-white/80 mb-4 md:mb-0">© 2025 DataClub. All rights reserved.</p>
                    <div class="flex flex-wrap gap-6">
                        <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Privacy Policy</a>
                        <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Terms of Service</a>
                        <a href="#" class="text-white/80 hover:text-white transition-colors underline-animate">Cookie Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Simple counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    counter.textContent = Math.floor(current);
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    }
                }, 20);
            });
        }

        // Trigger counters when they come into view
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe stats section
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        } else {
            // If stats section doesn't exist, trigger on page load
            setTimeout(animateCounters, 1000);
        }

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax-slow');
            
            parallaxElements.forEach(element => {
                const speed = 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>