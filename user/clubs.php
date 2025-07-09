<?php
// Start session
session_start();

// Check if user is logged in


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

// If AJAX request for clubs
if (isset($_GET['action']) && $_GET['action'] === 'get_clubs') {
    $sql = "SELECT ID_CLUB, NAME, LOGO, DESCRIPTION, UNIVERSITY, CITY, EMAIL, CLUB_PHONE FROM club";
    $result = $conn->query($sql);

    $clubs = [];
    while ($row = $result->fetch_assoc()) {
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

        // Get member count from accepted requestjoin
        $memberCountSql = "SELECT COUNT(*) as count FROM requestjoin r JOIN member m ON r.ID_MEMBER = m.ID_MEMBER WHERE r.ID_CLUB = ? AND r.ACCEPTED = 1";
        $memberCountStmt = $conn->prepare($memberCountSql);
        $memberCountStmt->bind_param("i", $row["ID_CLUB"]);
        $memberCountStmt->execute();
        $memberCountResult = $memberCountStmt->get_result();
        $memberCount = $memberCountResult->fetch_assoc()["count"];

        $clubs[] = [
            "id" => $row["ID_CLUB"],
            "name" => $row["NAME"],
            "school" => $row["UNIVERSITY"],
            "city" => $row["CITY"],
            "description" => $row["DESCRIPTION"],
            "logo" => $row["LOGO"] ? "../static/images/" . $row["LOGO"] : substr($row["NAME"], 0, 2),
            "memberCount" => $memberCount, // Use real count
            "established" => "", // Add if you have this info
            "focusAreas" => $topics ?: ["Data Science", "Machine Learning", "Analytics"],
            "social" => [
                "instagram" => "",
                "facebook" => "",
                "linkedin" => ""
            ]
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($clubs);
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
                <h3 class="text-2xl font-bold text-black-custom" id="total-clubs">6</h3>
                <p class="text-gray-600">Active Clubs</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-red-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-cities">6</h3>
                <p class="text-gray-600">Cities</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-graduation-cap text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-schools">6</h3>
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
                Showing 6 of 6 clubs
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
        let clubs = [];
        let filteredClubs = [];

        function fetchClubs() {
            fetch('clubs.php?action=get_clubs')
                .then(response => response.json())
                .then(data => {
                    clubs = data;
                    filteredClubs = [...clubs];
                    populateFilters();
                    renderClubs();
                    setupEventListeners();
                })
                .catch(error => {
                    document.getElementById('clubs-container').innerHTML = '<p class="text-red-500">Failed to load clubs.</p>';
                });
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

            container.innerHTML = filteredClubs.map(club => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer" onclick="viewClub('${club.id}')">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-xl overflow-hidden">
                                    ${club.logo.includes('../static/images/') ? 
                                        `<img src="${club.logo}" alt="${club.name}" class="w-full h-full object-cover">` : 
                                        club.logo
                                    }
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-black-custom group-hover:text-slate-custom transition-colors">
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

                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            ${club.description}
                        </p>

                        <!-- Focus Areas -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            ${club.focusAreas.slice(0, 3).map(area => `
                                <span class="px-2 py-1 bg-slate-100 text-slate-custom text-xs rounded-full">
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
                            <span>${club.memberCount} members</span>
                            <span>Est. ${club.established}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex space-x-2">
                                ${Object.entries(club.social).map(([platform, url]) => 
                                    url ? `
                                        <a href="${url}" target="_blank" rel="noopener noreferrer" 
                                           class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-red-custom hover:bg-red-50 hover:text-red-600 transition-colors"
                                           onclick="event.stopPropagation()">
                                            <i class="fab fa-${platform}"></i>
                                        </a>
                                    ` : ''
                                ).join('')}
                            </div>
                            
                            <button class="bg-red-custom text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-colors">
                                Join Club
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
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

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', fetchClubs);
    </script>
</body>
</html>