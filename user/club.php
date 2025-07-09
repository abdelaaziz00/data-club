<?php
// Start session
session_start();



// Database connection
$host = "localhost";
$user = "root";
$pass = ""; // Change if you have a password
$db = "data_club";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// If AJAX request for club data
if (isset($_GET['action']) && $_GET['action'] === 'get_club') {
    $clubId = $_GET['id'] ?? null;
    
    if (!$clubId) {
        http_response_code(400);
        echo json_encode(["error" => "Club ID is required"]);
        exit;
    }

    // Get club details
    $sql = "SELECT c.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL as CREATOR_EMAIL 
            FROM club c 
            LEFT JOIN member m ON c.ID_MEMBER = m.ID_MEMBER 
            WHERE c.ID_CLUB = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clubId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Club not found"]);
        exit;
    }
    
    $club = $result->fetch_assoc();
    
    // Get club events
    $eventSql = "SELECT e.* FROM evenement e 
                 INNER JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
                 WHERE o.ID_CLUB = ? AND e.DATE >= CURDATE()
                 ORDER BY e.DATE ASC LIMIT 6";
    
    $eventStmt = $conn->prepare($eventSql);
    $eventStmt->bind_param("i", $clubId);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    
    $events = [];
    while ($event = $eventResult->fetch_assoc()) {
        $events[] = [
            "title" => $event["TITLE"],
            "date" => $event["DATE"],
            "type" => $event["EVENT_TYPE"] ?: "Event",
            "description" => $event["DESCRIPTION"],
            "location" => $event["LOCATION"],
            "time" => $event["STARTING_TIME"]
        ];
    }
    
    // Get club members (recent members)
    // First, get the club admin
    $adminSql = "SELECT m.* FROM member m INNER JOIN club c ON c.ID_MEMBER = m.ID_MEMBER WHERE c.ID_CLUB = ? LIMIT 1";
    $adminStmt = $conn->prepare($adminSql);
    $adminStmt->bind_param("i", $clubId);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminMember = $adminResult->fetch_assoc();

    $members = [];
    if ($adminMember) {
        $members[] = [
            "name" => $adminMember["FIRST_NAME"] . " " . $adminMember["LAST_NAME"],
            "role" => "Admin",
            "avatar" => substr($adminMember["FIRST_NAME"], 0, 1) . substr($adminMember["LAST_NAME"], 0, 1),
            "email" => $adminMember["EMAIL"]
        ];
    }

    // Then, get other accepted members from requestjoin
    $otherSql = "SELECT m.* FROM requestjoin r JOIN member m ON r.ID_MEMBER = m.ID_MEMBER WHERE r.ID_CLUB = ? AND r.ACCEPTED = 1 ORDER BY m.ID_MEMBER DESC LIMIT 8";
    $otherStmt = $conn->prepare($otherSql);
    $otherStmt->bind_param("i", $clubId);
    $otherStmt->execute();
    $otherResult = $otherStmt->get_result();
    while ($otherMember = $otherResult->fetch_assoc()) {
        // Avoid adding the admin again if present in requestjoin
        if (!$adminMember || $otherMember["ID_MEMBER"] != $adminMember["ID_MEMBER"]) {
            $members[] = [
                "name" => $otherMember["FIRST_NAME"] . " " . $otherMember["LAST_NAME"],
                "role" => "Member",
                "avatar" => substr($otherMember["FIRST_NAME"], 0, 1) . substr($otherMember["LAST_NAME"], 0, 1),
                "email" => $otherMember["EMAIL"]
            ];
        }
    }
    
    // Get club topics/focus areas
    $topicSql = "SELECT t.TOPIC_NAME FROM topics t 
                 INNER JOIN focuses f ON t.TOPIC_ID = f.TOPIC_ID 
                 WHERE f.ID_CLUB = ?";
    
    $topicStmt = $conn->prepare($topicSql);
    $topicStmt->bind_param("i", $clubId);
    $topicStmt->execute();
    $topicResult = $topicStmt->get_result();
    
    $topics = [];
    while ($topic = $topicResult->fetch_assoc()) {
        $topics[] = $topic["TOPIC_NAME"];
    }
    
    // Prepare response
    $response = [
        "id" => $club["ID_CLUB"],
        "name" => $club["NAME"],
        "school" => $club["UNIVERSITY"],
        "city" => $club["CITY"],
        "description" => $club["DESCRIPTION"],
        "fullDescription" => $club["DESCRIPTION"] . "\n\nThis club is dedicated to advancing knowledge and skills in data science and related fields. We welcome students from all backgrounds who are passionate about technology and innovation.",
        "logo" => $club["LOGO"] ? "../static/images/" . $club["LOGO"] : substr($club["NAME"], 0, 2),
        "memberCount" => count($members),
        "established" => "2020", // You can add this field to your database
        "website" => "https://" . strtolower(str_replace(" ", "", $club["NAME"])) . ".ma",
        "email" => $club["EMAIL"] ?: "contact@" . strtolower(str_replace(" ", "", $club["NAME"])) . ".ma",
        "phone" => $club["CLUB_PHONE"] ?: "+212 5XX XX XX XX",
        "address" => $club["UNIVERSITY"] . ", " . $club["CITY"],
        "social" => [
            "instagram" => $club["INSTAGRAM_LINK"] ?: "",
            "facebook" => "",
            "linkedin" => $club["LINKEDIN_LINK"] ?: ""
        ],
        "achievements" => [
            "Active community of " . count($members) . " members",
            "Organized multiple workshops and events",
            "Collaborated with industry partners",
            "Contributed to research and innovation"
        ],
        "focusAreas" => $topics ?: ["Data Science", "Machine Learning", "Analytics"],
        "meetingSchedule" => "Every Wednesday at 6:00 PM",
        "requirements" => "Basic programming knowledge and enthusiasm for data science",
        "events" => $events,
        "members" => $members
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Profile - DataClub</title>
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
    <header class="bg-white py-4 px-6">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <img src="../static/images/mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="home.html" class="text-brand-dark hover:text-brand-red transition-colors">Home Page</a>
                <a href="events.php" class="text-brand-dark hover:text-brand-red transition-colors">Events</a>
                <a href="clubs.php" class="text-brand-dark hover:text-brand-red transition-colors">Clubs</a>
                <a href="contactus.html" class="text-brand-dark hover:text-brand-red transition-colors">Contact us</a>
                <div class="flex space-x-2">
                    <a href="../auth/logout.php" class="bg-brand-red text-white px-4 py-2 rounded-lg font-medium">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <button onclick="history.back()" class="flex items-center text-slate-custom hover:text-slate-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Clubs
            </button>
        </div>

        <!-- Loading State -->
        <div id="loading" class="text-center py-12">
            <div class="w-16 h-16 border-4 border-red-custom border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600">Loading club information...</p>
        </div>

        <!-- Error State -->
        <div id="error" class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-custom text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-black-custom mb-4">Club Not Found</h1>
            <p class="text-gray-600 mb-6">The club you're looking for doesn't exist.</p>
            <a href="clubs.php" class="bg-red-custom text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-colors">
                Back to Clubs
            </a>
        </div>

        <!-- Club Profile -->
        <div id="club-profile" class="bg-white rounded-xl shadow-lg overflow-hidden hidden">
            <!-- Club will be populated by JavaScript -->
        </div>

        <!-- Club Events -->
        <div id="club-events-section" class="mt-12 hidden">
            <h2 class="text-2xl font-bold text-black-custom mb-6">Upcoming Events</h2>
            <div id="club-events" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Events will be populated by JavaScript -->
            </div>
        </div>

        <!-- Club Members -->
        <div id="club-members-section" class="mt-12 hidden">
            <h2 class="text-2xl font-bold text-black-custom mb-6">Recent Members</h2>
            <div id="club-members" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Members will be populated by JavaScript -->
            </div>
        </div>
    </main>

    <footer class="bg-brand-red text-white py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Logo and Links -->
            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center space-x-3 mb-6">
                    <img src="../static/images/mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
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

    <script>
        // Get club ID from URL
        function getClubId() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id');
        }

        // Fetch club data from PHP
        function fetchClubData() {
            const clubId = getClubId();
            
            if (!clubId) {
                showError();
                return;
            }

            fetch(`club.php?action=get_club&id=${clubId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Club not found');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    renderClubProfile(data);
                    renderClubEvents(data.events);
                    renderClubMembers(data.members);
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError();
                });
        }

        // Show loading state
        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('error').classList.add('hidden');
            document.getElementById('club-profile').classList.add('hidden');
            document.getElementById('club-events-section').classList.add('hidden');
            document.getElementById('club-members-section').classList.add('hidden');
        }

        // Hide loading state
        function hideLoading() {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('club-profile').classList.remove('hidden');
            document.getElementById('club-events-section').classList.remove('hidden');
            document.getElementById('club-members-section').classList.remove('hidden');
        }

        // Show error state
        function showError() {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.remove('hidden');
        }

        // Render club profile
        function renderClubProfile(club) {
            const container = document.getElementById('club-profile');
            
            container.innerHTML = `
                <!-- Hero Section -->
                <div class="relative bg-gradient-to-r from-slate-custom to-slate-700 text-white p-8">
                    <div class="flex flex-col md:flex-row items-start md:items-center space-y-6 md:space-y-0 md:space-x-8">
                        <div class="w-24 h-24 bg-white bg-opacity-20 rounded-xl flex items-center justify-center text-3xl font-bold overflow-hidden">
                            ${club.logo.includes('../static/images/') ? 
                                `<img src="${club.logo}" alt="${club.name}" class="w-full h-full object-cover">` : 
                                club.logo
                            }
                        </div>
                        <div class="flex-1">
                            <h1 class="text-3xl md:text-4xl font-bold mb-2">${club.name}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-lg opacity-90">
                                <div class="flex items-center">
                                    <i class="fas fa-graduation-cap mr-2"></i>
                                    ${club.school}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    ${club.city}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2"></i>
                                    ${club.memberCount} members
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    Est. ${club.established}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col space-y-3">
                            <button class="bg-red-custom text-white px-6 py-3 rounded-lg font-medium hover:bg-red-600 transition-colors">
                                Join Club
                            </button>
                            <div class="flex space-x-2">
                                ${Object.entries(club.social).map(([platform, url]) => 
                                    url ? `
                                        <a href="${url}" target="_blank" rel="noopener noreferrer" 
                                           class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center text-white hover:bg-opacity-30 transition-colors">
                                            <i class="fab fa-${platform}"></i>
                                        </a>
                                    ` : ''
                                ).join('')}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Sections -->
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-8">
                            <!-- About -->
                            <section>
                                <h2 class="text-2xl font-bold text-black-custom mb-4">About Us</h2>
                                <div class="prose prose-gray max-w-none">
                                    ${club.fullDescription.split('\n').map(paragraph => 
                                        paragraph.trim() ? `<p class="mb-4 text-gray-600 leading-relaxed">${paragraph}</p>` : ''
                                    ).join('')}
                                </div>
                            </section>

                            <!-- Focus Areas -->
                            <section>
                                <h2 class="text-2xl font-bold text-black-custom mb-4">Focus Areas</h2>
                                <div class="flex flex-wrap gap-3">
                                    ${club.focusAreas.map(area => `
                                        <span class="px-4 py-2 bg-slate-100 text-slate-custom rounded-lg font-medium">
                                            ${area}
                                        </span>
                                    `).join('')}
                                </div>
                            </section>

                            <!-- Achievements -->
                            <section>
                                <h2 class="text-2xl font-bold text-black-custom mb-4">Achievements</h2>
                                <div class="space-y-3">
                                    ${club.achievements.map(achievement => `
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-red-custom rounded-full mt-2 flex-shrink-0"></div>
                                            <p class="text-gray-600">${achievement}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            </section>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Contact Info -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-bold text-black-custom mb-4">Contact Information</h3>
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-envelope text-slate-custom mt-1"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Email</p>
                                            <a href="mailto:${club.email}" class="text-slate-custom hover:underline">${club.email}</a>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-phone text-slate-custom mt-1"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Phone</p>
                                            <a href="tel:${club.phone}" class="text-slate-custom hover:underline">${club.phone}</a>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-globe text-slate-custom mt-1"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Website</p>
                                            <a href="${club.website}" target="_blank" class="text-slate-custom hover:underline">${club.website}</a>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-map-marker-alt text-slate-custom mt-1"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Address</p>
                                            <p class="text-gray-700">${club.address}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Meeting Schedule -->
                            <div class="bg-red-50 rounded-xl p-6">
                                <h3 class="text-lg font-bold text-black-custom mb-4">Meeting Schedule</h3>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-clock text-red-custom"></i>
                                    <p class="text-gray-700">${club.meetingSchedule}</p>
                                </div>
                            </div>

                            <!-- Requirements -->
                            <div class="bg-slate-50 rounded-xl p-6">
                                <h3 class="text-lg font-bold text-black-custom mb-4">Requirements</h3>
                                <p class="text-gray-700">${club.requirements}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Render club events
        function renderClubEvents(events) {
            const container = document.getElementById('club-events');

            if (!events || events.length === 0) {
                container.innerHTML = '<p class="text-gray-500 col-span-full text-center">No upcoming events</p>';
                return;
            }

            container.innerHTML = events.map(event => `
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-bold text-black-custom">${event.title}</h3>
                        <span class="px-3 py-1 bg-slate-100 text-slate-custom text-xs rounded-full">${event.type}</span>
                    </div>
                    <div class="flex items-center text-gray-500 text-sm mb-4">
                        <i class="fas fa-calendar mr-2"></i>
                        ${new Date(event.date).toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}
                    </div>
                    <button class="w-full bg-red-custom text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Learn More
                    </button>
                </div>
            `).join('');
        }

        // Render club members
        function renderClubMembers(members) {
            const container = document.getElementById('club-members');

            if (!members || members.length === 0) {
                container.innerHTML = '<p class="text-gray-500 col-span-full text-center">No members to display</p>';
                return;
            }

            container.innerHTML = members.map(member => `
                <div class="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold text-lg mx-auto mb-4">
                        ${member.avatar}
                    </div>
                    <h3 class="text-lg font-bold text-black-custom mb-1">${member.name}</h3>
                    <p class="text-gray-500 text-sm">${member.role}</p>
                </div>
            `).join('');
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            showLoading();
            fetchClubData();
        });
    </script>
</body>
</html>
