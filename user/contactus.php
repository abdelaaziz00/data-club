<?php
session_start();
require_once 'db.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - DataClub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'red-custom': '#F05454',
                        'gray-custom': '#F5F5F5',
                        'slate-custom': '#30475E',
                        'black-custom': '#121212',
                        'brand-red': '#F05454',
                        'brand-slate': '#30475E',
                        'brand-gray': '#F5F5F5',
                        'brand-dark': '#121212'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-custom">
    <!-- Navigation -->
<?php include '../includes/header.php'; ?> 
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-black-custom mb-6">
                Get in Touch
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                We're here to help you on your data science journey. Whether you have questions about our programs, 
                want to collaborate, or need support, our team is ready to assist you. Reach out to us through any 
                of the channels below, and we'll get back to you as soon as possible.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <!-- Contact Form -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-black-custom mb-6">Send us a Message</h2>
                <form id="contact-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">
                                First Name *
                            </label>
                            <input
                                type="text"
                                id="firstName"
                                name="firstName"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors"
                                placeholder="Your first name"
                            />
                        </div>
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name *
                            </label>
                            <input
                                type="text"
                                id="lastName"
                                name="lastName"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors"
                                placeholder="Your last name"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address *
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors"
                            placeholder="your.email@example.com"
                        />
                    </div>

                    <div>
                        <label for="university" class="block text-sm font-medium text-gray-700 mb-2">
                            University/Organization
                        </label>
                        <input
                            type="text"
                            id="university"
                            name="university"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors"
                            placeholder="Your university or organization"
                        />
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Subject *
                        </label>
                        <select
                            id="subject"
                            name="subject"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors"
                        >
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="events">Events & Workshops</option>
                            <option value="clubs">Club Information</option>
                            <option value="partnership">Partnership Opportunities</option>
                            <option value="technical">Technical Support</option>
                            <option value="feedback">Feedback & Suggestions</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message *
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent transition-colors resize-vertical"
                            placeholder="Tell us how we can help you..."
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-red-custom text-white py-3 px-6 rounded-lg font-medium hover:bg-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-custom focus:ring-offset-2"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="space-y-8">
                <!-- DataClub Contact -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-black-custom mb-6">DataClub</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-slate-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <a href="mailto:contact@dataclub.ma" class="text-slate-custom hover:text-slate-700 font-medium">
                                    contact@dataclub.ma
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-slate-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <a href="tel:+212537771834" class="text-slate-custom hover:text-slate-700 font-medium">
                                    +212 537 77 18 34
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-slate-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Address</p>
                                <p class="text-gray-700">
                                    Mohammed V University<br>
                                    Faculty of Sciences<br>
                                    Rabat, Morocco
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-globe text-slate-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Social Media</p>
                                <div class="flex space-x-3 mt-2">
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MDS Talks Contact -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-black-custom mb-6">MDS Talks</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-red-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <a href="mailto:hello@mdstalks.com" class="text-red-custom hover:text-red-600 font-medium">
                                    hello@mdstalks.com
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-red-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <a href="tel:+212522230680" class="text-red-custom hover:text-red-600 font-medium">
                                    +212 522 23 06 80
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-globe text-red-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Website</p>
                                <a href="https://mdstalks.com" target="_blank" class="text-red-custom hover:text-red-600 font-medium">
                                    www.mdstalks.com
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-share-alt text-red-custom"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Social Media</p>
                                <div class="flex space-x-3 mt-2">
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-200 transition-colors">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-black-custom mb-8 text-center">Frequently Asked Questions</h2>
            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <!-- FAQ Item 1 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-1">
                            <span class="text-lg font-medium text-black-custom">What is DataClub and how does it relate to MDS Talks?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-1" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                DataClub is an educational initiative by MDS Talks that brings AI and Data Science events directly to university campuses across Morocco. While MDS Talks focuses on broader tech education and industry connections, DataClub specifically targets students interested in data science, machine learning, and AI through hands-on workshops, expert-led sessions, and community building.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-2">
                            <span class="text-lg font-medium text-black-custom">How can I join a DataClub at my university?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-2" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                You can join a DataClub by visiting our Clubs page to find the club at your university, or contact us if there isn't one yet. Most clubs welcome new members throughout the academic year. Simply click "Join Club" on the club's profile page or attend one of their events to get started. No prior experience in data science is required for most clubs.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-3">
                            <span class="text-lg font-medium text-black-custom">Are the events and workshops free to attend?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-3" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                Many of our events are free, especially introductory workshops and club meetups. Some specialized bootcamps, conferences, or certification programs may have a fee to cover materials, venue costs, or expert speaker fees. All pricing information is clearly displayed on each event's page, and we strive to keep costs minimal to ensure accessibility for all students.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-4">
                            <span class="text-lg font-medium text-black-custom">Can I start a DataClub at my university?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-4" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                Absolutely! We're always looking to expand to new universities. If there's no DataClub at your university yet, contact us through this form or email us directly. We'll provide you with resources, guidance, and support to establish a new club. We look for passionate students who can commit to organizing regular events and building a community.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-5">
                            <span class="text-lg font-medium text-black-custom">What level of experience do I need to participate?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-5" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                Our events cater to all levels, from complete beginners to advanced practitioners. Each event clearly indicates its difficulty level and prerequisites. We believe in learning by doing, so even if you're new to programming or data science, you'll find beginner-friendly workshops and supportive community members to help you get started.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 6 -->
                    <div class="border border-gray-200 rounded-lg">
                        <button class="faq-button w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors" data-target="faq-6">
                            <span class="text-lg font-medium text-black-custom">How can my company partner with DataClub?</span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                        </button>
                        <div id="faq-6" class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed">
                                We welcome partnerships with companies interested in supporting data science education. Partnership opportunities include sponsoring events, providing speakers, offering internships, or hosting workshops. Contact us with "Partnership Opportunities" as the subject, and we'll discuss how we can collaborate to benefit our student community while meeting your company's goals.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-16">
            <div class="bg-slate-custom rounded-xl p-8 text-white">
                <h2 class="text-2xl font-bold mb-4">Ready to Join the Community?</h2>
                <p class="text-lg mb-6 opacity-90">
                    Discover events, connect with peers, and advance your data science journey with DataClub.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="events.php" class="bg-red-custom text-white px-6 py-3 rounded-lg font-medium hover:bg-red-600 transition-colors">
                        Browse Events
                    </a>
                    <a href="clubs.php" class="bg-white text-slate-custom px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                        Find Your Club
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-brand-red text-white py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Logo and Links -->
            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center space-x-3 mb-6">
                    <img src="../static/images/Frame 13.png" alt="MDS Logo" class="w-40 h-30 object-contain mix-blend-multiply">
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

    <script type="text/javascript"
        src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js">
    </script>
    <script type="text/javascript">
       (function(){
          emailjs.init({
            publicKey: "BRypygMrMDJfeuTgP",
          });
       })();

       function sendMail() {
          let parms = {
             name: document.getElementById("firstName").value + " " + document.getElementById("lastName").value,
             email: document.getElementById("email").value,
             university: document.getElementById("university").value,
             subject: document.getElementById("subject").value,
             message: document.getElementById("message").value,
          };
          emailjs.send("service_0vistul","template_6vjgf8h",parms).then(alert("Email sent!"));
       }

        // Contact form submission
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            sendMail();
        });

        // FAQ functionality
        document.querySelectorAll('.faq-button').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const content = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                // Close all other FAQ items
                document.querySelectorAll('.faq-content').forEach(item => {
                    if (item.id !== targetId && !item.classList.contains('hidden')) {
                        item.classList.add('hidden');
                        const otherIcon = document.querySelector(`[data-target="${item.id}"] i`);
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                });
                
                // Toggle current FAQ item
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    content.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        // Smooth scrolling for anchor links
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

        // Add animations on page load
        document.addEventListener('DOMContentLoaded', () => {
            setupContactAnimations();
        });

        // Setup contact page animations
        function setupContactAnimations() {
            // Animate header
            const header = document.querySelector('.text-center.mb-16');
            if (header) {
                header.style.opacity = '0';
                header.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    header.style.transition = 'all 0.8s ease-out';
                    header.style.opacity = '1';
                    header.style.transform = 'translateY(0)';
                }, 100);
            }

            // Animate form and contact cards with stagger
            const cards = document.querySelectorAll('.bg-white.rounded-xl');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 200));
            });

            // Animate FAQ section
            const faqSection = document.querySelector('.bg-white.rounded-xl.shadow-lg.p-8');
            if (faqSection) {
                faqSection.style.opacity = '0';
                faqSection.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    faqSection.style.transition = 'all 0.6s ease-out';
                    faqSection.style.opacity = '1';
                    faqSection.style.transform = 'translateY(0)';
                }, 800);
            }

            // Animate CTA section
            const ctaSection = document.querySelector('.text-center.mt-16');
            if (ctaSection) {
                ctaSection.style.opacity = '0';
                ctaSection.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    ctaSection.style.transition = 'all 0.6s ease-out';
                    ctaSection.style.opacity = '1';
                    ctaSection.style.transform = 'translateY(0)';
                }, 1000);
            }
        }
    </script>

    <style>
        /* Smooth hover effects for cards */
        .bg-white.rounded-xl {
            transition: all 0.3s ease;
        }
        
        .bg-white.rounded-xl:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Form input hover and focus effects */
        input, textarea, select {
            transition: all 0.3s ease;
        }
        
        input:hover, textarea:hover, select:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(48, 71, 94, 0.1);
        }
        
        input:focus, textarea:focus, select:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(48, 71, 94, 0.15);
        }

        /* Button hover effects */
        .bg-red-custom, .bg-slate-custom {
            transition: all 0.3s ease;
        }
        
        .bg-red-custom:hover, .bg-slate-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Social media icons hover effects */
        .w-8.h-8 {
            transition: all 0.3s ease;
        }
        
        .w-8.h-8:hover {
            transform: scale(1.1);
            background-color: rgba(240, 84, 84, 0.2) !important;
        }

        /* Contact info icons hover effects */
        .w-10.h-10 {
            transition: all 0.3s ease;
        }
        
        .w-10.h-10:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* FAQ button hover effects */
        .faq-button {
            transition: all 0.3s ease;
        }
        
        .faq-button:hover {
            background-color: rgba(48, 71, 94, 0.05) !important;
            transform: translateX(4px);
        }

        /* FAQ content animation */
        .faq-content {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }
        
        .faq-content:not(.hidden) {
            max-height: 200px;
        }

        /* FAQ icon rotation animation */
        .faq-button i {
            transition: transform 0.3s ease;
        }

        /* Contact info links hover effects */
        a[href^="mailto:"], a[href^="tel:"], a[href^="http"] {
            transition: all 0.3s ease;
        }
        
        a[href^="mailto:"]:hover, a[href^="tel:"]:hover, a[href^="http"]:hover {
            transform: translateX(4px);
        }

        /* CTA section hover effects */
        .bg-slate-custom.rounded-xl {
            transition: all 0.3s ease;
        }
        
        .bg-slate-custom.rounded-xl:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Footer links hover effects */
        footer a {
            transition: all 0.3s ease;
        }
        
        footer a:hover {
            transform: translateY(-1px);
        }

        /* Form submit button loading animation */
        .loading {
            position: relative;
            overflow: hidden;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Success message animation */
        .success-message {
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Gradient background animation for CTA */
        .bg-slate-custom {
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Smooth transitions for all interactive elements */
        * {
            transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        /* Enhanced focus states */
        button:focus, a:focus, input:focus, textarea:focus, select:focus {
            outline: 2px solid #F05454;
            outline-offset: 2px;
        }

        /* Card entrance animation */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hover lift effect for cards */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Pulse animation for important elements */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Scale animation for interactive elements */
        .scale-on-hover {
            transition: transform 0.3s ease;
        }
        
        .scale-on-hover:hover {
            transform: scale(1.05);
        }
    </style>
</body>
</html>