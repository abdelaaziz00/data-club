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

// Handle join club request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_club'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Please login first to join a club"]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $clubId = $_POST['club_id'];
    
    // Check if user already has a request for this club
    $checkSql = "SELECT * FROM requestjoin WHERE ID_MEMBER = ? AND ID_CLUB = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $userId, $clubId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result();
    
    if ($existing->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "You have already requested to join this club"]);
        exit;
    }
    
    // Insert new join request
    $insertSql = "INSERT INTO requestjoin (ID_MEMBER, ID_CLUB, ACCEPTED) VALUES (?, ?, 0)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ii", $userId, $clubId);
    
    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Join request sent successfully! You will be notified once it's reviewed."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send join request"]);
    }
    exit;
}

// Function to get user's join status for a club
function getUserJoinStatus($conn, $userId, $clubId) {
    if (!$userId) return null; // Not logged in
    
    $sql = "SELECT ACCEPTED FROM requestjoin WHERE ID_MEMBER = ? AND ID_CLUB = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $clubId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null; // No request sent
    }
    
    $row = $result->fetch_assoc();
    return $row['ACCEPTED'] == 1 ? 'member' : 'pending';
}

// If AJAX request for clubs
if (isset($_GET['action']) && $_GET['action'] === 'get_clubs') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $sql = "SELECT ID_CLUB, NAME, LOGO, DESCRIPTION, UNIVERSITY, CITY, EMAIL, CLUB_PHONE FROM club";
    $result = $conn->query($sql);

    $clubs = [];
    $cities = [];
    $schools = [];
    
    while ($row = $result->fetch_assoc()) {
        // Track unique cities and schools for statistics
        if (!in_array($row["CITY"], $cities)) {
            $cities[] = $row["CITY"];
        }
        if (!in_array($row["UNIVERSITY"], $schools)) {
            $schools[] = $row["UNIVERSITY"];
        }
        
        // Get focus areas for this club
        $topicSql = "SELECT t.TOPIC_NAME FROM topics t 
                     INNER JOIN focuses f ON t.TOPIC_ID = f.TOPIC_ID 
                     WHERE f.ID_CLUB = ?";
        
        $topicStmt = $conn->prepare($topicSql);
        $topicStmt->bind_param("i", $row["ID_CLUB"]);
        $topicStmt->execute();
        $topicResult = $topicStmt->get_result();
        
        $topics = [];
        while ($topic = $topicResult->fetch_assoc()) {
            $topics[] = $topic["TOPIC_NAME"];
        }

        // Get member count from accepted requestjoin + club admin
        $memberCountSql = "SELECT COUNT(*) as count FROM requestjoin r JOIN member m ON r.ID_MEMBER = m.ID_MEMBER WHERE r.ID_CLUB = ? AND r.ACCEPTED = 1";
        $memberCountStmt = $conn->prepare($memberCountSql);
        $memberCountStmt->bind_param("i", $row["ID_CLUB"]);
        $memberCountStmt->execute();
        $memberCountResult = $memberCountStmt->get_result();
        $memberCount = $memberCountResult->fetch_assoc()["count"] + 1; // +1 for admin

        // Get user's join status for this club
        $userJoinStatus = getUserJoinStatus($conn, $userId, $row["ID_CLUB"]);

        $clubs[] = [
            "id" => $row["ID_CLUB"],
            "name" => $row["NAME"],
            "school" => $row["UNIVERSITY"],
            "city" => $row["CITY"],
            "description" => $row["DESCRIPTION"],
            "logo" => $row["LOGO"] ? "../static/images/" . $row["LOGO"] : substr($row["NAME"], 0, 2),
            "logoType" => $row["LOGO"] ? "image" : "text",
            "memberCount" => $memberCount,
            "established" => "2020", // Add if you have this info
            "focusAreas" => $topics ?: ["Data Science", "Machine Learning", "Analytics"],
            "userJoinStatus" => $userJoinStatus,
            "social" => [
                "instagram" => "",
                "facebook" => "",
                "linkedin" => ""
            ]
        ];
    }
    
    // Calculate statistics
    $statistics = [
        "totalClubs" => count($clubs),
        "totalCities" => count($cities),
        "totalSchools" => count($schools)
    ];
    
    $response = [
        "clubs" => $clubs,
        "statistics" => $statistics
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
    <title>Data Club - Clubs</title>
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-black-custom mb-4">
                Discover Data Science Clubs
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Connect with passionate data scientists and AI enthusiasts across Morocco's top universities. 
                Join a community, learn new skills, and build amazing projects together.
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-clubs">-</h3>
                <p class="text-gray-600">Active Clubs</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-red-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-cities">-</h3>
                <p class="text-gray-600">Cities</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-graduation-cap text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-schools">-</h3>
                <p class="text-gray-600">Universities</p>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
                <!-- Search Bar -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search clubs..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom focus:border-transparent"
                    />
                </div>
                
                <!-- Filters -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- City Filter -->
                    <div class="relative">
                        <select id="city-filter" class="w-full sm:w-40 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-slate-custom">
                            <option value="">All Cities</option>
                        </select>
                    </div>

                    <!-- School Filter -->
                    <div class="relative">
                        <select id="school-filter" class="w-full sm:w-48 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-slate-custom">
                            <option value="">All Schools</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button id="clear-filters" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 hidden">
                        Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-6">
            <p class="text-gray-600" id="results-count">
                Loading clubs...
            </p>
        </div>

        <!-- Club Cards -->
        <div id="clubs-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Clubs will be populated by JavaScript -->
        </div>

        <!-- No Results -->
        <div id="no-results" class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-black-custom mb-2">No clubs found</h3>
            <p class="text-gray-600">Try adjusting your search or filters to find more clubs.</p>
        </div>
    </main>

    <!-- Login Modal -->
    <div id="login-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black-custom">Login Required</h2>
                    <button id="close-login-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-600 mb-6">
                    You need to be logged in to join clubs. Please login to your account first.
                </p>
                <div class="flex space-x-3">
                    <a href="../auth/login.php" class="flex-1 bg-slate-custom text-white py-2 rounded-lg text-center hover:bg-slate-700 transition-colors">
                        Login
                    </a>
                    <button id="cancel-login" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let clubs = [];
        let filteredClubs = [];
        let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            fetchClubs();
            setupJoinClubHandlers();
            setupAnimations();
        });
        
        // Setup animations
        function setupAnimations() {
            // Animate stats on load
            animateStats();
            
            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        }
        
        // Animate statistics
        function animateStats() {
            const stats = [
                { element: document.getElementById('total-clubs'), value: 0 },
                { element: document.getElementById('total-cities'), value: 0 },
                { element: document.getElementById('total-schools'), value: 0 }
            ];
            
            stats.forEach((stat, index) => {
                setTimeout(() => {
                    const finalValue = parseInt(stat.element.textContent) || 0;
                    animateNumber(stat.element, 0, finalValue, 1000);
                }, index * 200);
            });
        }
        
        // Animate number counting
        function animateNumber(element, start, end, duration) {
            const startTime = performance.now();
            const difference = end - start;
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function for smooth animation
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (difference * easeOut));
                
                element.textContent = current;
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            requestAnimationFrame(updateNumber);
        }
        
        // Render join button based on user status
        function renderJoinButton(club) {
            if (!isLoggedIn) {
                return `
                    <button class="join-club-btn bg-red-custom text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-all duration-300 transform hover:scale-105 hover:shadow-lg" data-club-id="${club.id}">
                        <i class="fas fa-plus mr-1"></i>
                        Join Club
                    </button>
                `;
            } else if (!club.userJoinStatus) {
                return `
                    <button class="join-club-btn bg-red-custom text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-all duration-300 transform hover:scale-105 hover:shadow-lg" data-club-id="${club.id}">
                        <i class="fas fa-plus mr-1"></i>
                        Join Club
                    </button>
                `;
            } else if (club.userJoinStatus === 'pending') {
                return `
                    <button class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed animate-pulse" disabled>
                        <i class="fas fa-clock mr-1"></i>
                        Request Pending
                    </button>
                `;
            } else if (club.userJoinStatus === 'member') {
                return `
                    <button class="bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed" disabled>
                        <i class="fas fa-check mr-1"></i>
                        Member
                    </button>
                `;
            }
        }
        
        // Setup join club handlers
        function setupJoinClubHandlers() {
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('join-club-btn') || e.target.closest('.join-club-btn'))) {
                    e.stopPropagation(); // Prevent card click
                    const button = e.target.classList.contains('join-club-btn') ? e.target : e.target.closest('.join-club-btn');
                    const clubId = button.getAttribute('data-club-id');
                    
                    if (!isLoggedIn) {
                        showLoginModal();
                    } else {
                        joinClub(clubId, button);
                    }
                }
            });
        }
        
        // Join club function
        function joinClub(clubId, buttonElement) {
            // Disable button to prevent multiple clicks
            buttonElement.disabled = true;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';
            
            const formData = new FormData();
            formData.append('join_club', '1');
            formData.append('club_id', clubId);
            
            fetch('clubs.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button to show pending status
                    buttonElement.outerHTML = `
                        <button class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed animate-pulse" disabled>
                            <i class="fas fa-clock mr-1"></i>
                            Request Pending
                        </button>
                    `;
                    
                    // Show success message
                    showNotification(data.message, 'success');
                } else {
                    // Re-enable button on error
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = '<i class="fas fa-plus mr-1"></i> Join Club';
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-plus mr-1"></i> Join Club';
                showNotification('An error occurred while sending your request.', 'error');
            });
        }
        
        // Show notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
            notification.className += type === 'success' ? ' bg-green-100 text-green-800 border border-green-200' : ' bg-red-100 text-red-800 border border-red-200';
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                    ${message}
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
        
        // Fetch clubs function
        function fetchClubs() {
            fetch('clubs.php?action=get_clubs')
                .then(response => response.json())
                .then(data => {
                    clubs = data.clubs;
                    filteredClubs = [...clubs];
                    
                    // Update statistics
                    updateStatistics(data.statistics);
                    
                    populateFilters();
                    renderClubs();
                    setupEventListeners();
                })
                .catch(error => {
                    console.error('Error fetching clubs:', error);
                    document.getElementById('clubs-container').innerHTML = '<p class="text-red-500 col-span-full text-center">Failed to load clubs. Please try again later.</p>';
                });
        }

        // Update statistics function
        function updateStatistics(statistics) {
            document.getElementById('total-clubs').textContent = statistics.totalClubs;
            document.getElementById('total-cities').textContent = statistics.totalCities;
            document.getElementById('total-schools').textContent = statistics.totalSchools;
        }

        // Populate filter dropdowns
        function populateFilters() {
            const cities = [...new Set(clubs.map(club => club.city))].sort();
            const schools = [...new Set(clubs.map(club => club.school))].sort();
            
            const cityFilter = document.getElementById('city-filter');
            const schoolFilter = document.getElementById('school-filter');

            cityFilter.innerHTML = '<option value="">All Cities</option>';
            schoolFilter.innerHTML = '<option value="">All Schools</option>';
            
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                cityFilter.appendChild(option);
            });
            
            schools.forEach(school => {
                const option = document.createElement('option');
                option.value = school;
                option.textContent = school;
                schoolFilter.appendChild(option);
            });
        }

        // Render clubs
        function renderClubs() {
            const container = document.getElementById('clubs-container');
            const noResults = document.getElementById('no-results');
            const resultsCount = document.getElementById('results-count');
            
            if (filteredClubs.length === 0) {
                container.classList.add('hidden');
                noResults.classList.remove('hidden');
                resultsCount.textContent = 'No clubs found';
                return;
            }
            
            container.classList.remove('hidden');
            noResults.classList.add('hidden');
            resultsCount.textContent = `Showing ${filteredClubs.length} of ${clubs.length} clubs`;
            
            container.innerHTML = filteredClubs.map((club, index) => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-500 overflow-hidden group cursor-pointer animate-on-scroll opacity-0 transform translate-y-4" 
                     style="animation-delay: ${index * 100}ms; animation-fill-mode: forwards;" 
                     onclick="viewClub('${club.id}')">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-xl overflow-hidden transform transition-transform duration-300 group-hover:scale-110">
                                    ${club.logo.includes('../static/images/') ? 
                                        `<img src="${club.logo}" alt="${club.name}" class="w-full h-full object-cover">` : 
                                        club.logo
                                    }
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-black-custom group-hover:text-slate-custom transition-colors duration-300">
                                        ${club.name}
                                    </h3>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <div class="flex items-center text-gray-500 text-sm">
                                            <i class="fas fa-graduation-cap mr-1"></i>
                                            ${club.school}
                                        </div>
                                        <div class="flex items-center text-gray-500 text-sm">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            ${club.city}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="cursor-pointer">
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                ${club.description}
                            </p>

                            <div class="flex flex-wrap gap-2 mb-4">
                                ${club.focusAreas.slice(0, 3).map(area => `
                                    <span class="px-2 py-1 bg-slate-100 text-slate-custom text-xs rounded-full transition-all duration-300 hover:bg-slate-200 hover:scale-105">
                                        ${area}
                                    </span>
                                `).join('')}
                                ${club.focusAreas.length > 3 ? `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full">
                                        +${club.focusAreas.length - 3} more
                                    </span>
                                ` : ''}
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span><i class="fas fa-users mr-1"></i>${club.memberCount} members</span>
                                <span><i class="fas fa-calendar mr-1"></i>Est. ${club.established}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex space-x-2">
                                ${Object.entries(club.social).map(([platform, url]) => 
                                    url ? `
                                        <a href="${url}" target="_blank" rel="noopener noreferrer" 
                                           class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-50 hover:text-red-600 transition-all duration-300 transform hover:scale-110"
                                           onclick="event.stopPropagation()">
                                            <i class="fab fa-${platform}"></i>
                                        </a>
                                    ` : ''
                                ).join('')}
                            </div>
                            
                            ${renderJoinButton(club)}
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Add animation classes after rendering
            setTimeout(() => {
                document.querySelectorAll('.animate-on-scroll').forEach((el, index) => {
                    el.style.animationDelay = `${index * 100}ms`;
                    el.classList.add('animate-fade-in');
                });
            }, 100);
        }

        // Filter clubs
        function filterClubs() {
            const searchQuery = document.getElementById('search-input').value.toLowerCase();
            const selectedCity = document.getElementById('city-filter').value;
            const selectedSchool = document.getElementById('school-filter').value;

            filteredClubs = clubs.filter(club => {
                const matchesSearch = searchQuery === '' || 
                    club.name.toLowerCase().includes(searchQuery) ||
                    club.description.toLowerCase().includes(searchQuery) ||
                    club.school.toLowerCase().includes(searchQuery) ||
                    club.city.toLowerCase().includes(searchQuery);

                const matchesCity = selectedCity === '' || club.city === selectedCity;
                const matchesSchool = selectedSchool === '' || club.school === selectedSchool;
                
                return matchesSearch && matchesCity && matchesSchool;
            });
            
            renderClubs();
            updateClearButton();
        }

        // Update clear button visibility
        function updateClearButton() {
            const clearButton = document.getElementById('clear-filters');
            const hasFilters = document.getElementById('search-input').value ||
                              document.getElementById('city-filter').value ||
                              document.getElementById('school-filter').value;
            
            if (hasFilters) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
            }
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('search-input').value = '';
            document.getElementById('city-filter').value = '';
            document.getElementById('school-filter').value = '';
            filteredClubs = [...clubs];
            renderClubs();
            updateClearButton();
        }

        // View club details
        function viewClub(clubId) {
            window.location.href = `club.php?id=${clubId}`;
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('search-input').addEventListener('input', filterClubs);
            document.getElementById('city-filter').addEventListener('change', filterClubs);
            document.getElementById('school-filter').addEventListener('change', filterClubs);
            document.getElementById('clear-filters').addEventListener('click', clearFilters);
        }

        // Login modal functions
        function showLoginModal() {
            document.getElementById('login-modal').classList.remove('hidden');
        }

        function hideLoginModal() {
            document.getElementById('login-modal').classList.add('hidden');
        }

        // Modal event listeners
        document.getElementById('close-login-modal').addEventListener('click', hideLoginModal);
        document.getElementById('cancel-login').addEventListener('click', hideLoginModal);
        
        // Close modal when clicking outside
        document.getElementById('login-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideLoginModal();
            }
        });
    </script>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Smooth hover effects */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Pulse animation for pending requests */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</body>
</html>