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

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Please login first to register for events"]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $eventId = $_POST['event_id'];
    
    // Check if user is already registered
    $checkSql = "SELECT COUNT(*) as count FROM registre WHERE ID_EVENT = ? AND ID_MEMBER = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $eventId, $userId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result();
    
    if ($existing->fetch_assoc()["count"] > 0) {
        echo json_encode(["success" => false, "message" => "You are already registered for this event"]);
        exit;
    }
    
    // Check if event is full
    $capacitySql = "SELECT CAPACITY FROM evenement WHERE ID_EVENT = ?";
    $capacityStmt = $conn->prepare($capacitySql);
    $capacityStmt->bind_param("i", $eventId);
    $capacityStmt->execute();
    $capacityResult = $capacityStmt->get_result();
    $capacity = $capacityResult->fetch_assoc()["CAPACITY"];
    
    $registeredSql = "SELECT COUNT(*) as count FROM registre WHERE ID_EVENT = ?";
    $registeredStmt = $conn->prepare($registeredSql);
    $registeredStmt->bind_param("i", $eventId);
    $registeredStmt->execute();
    $registeredResult = $registeredStmt->get_result();
    $registered = $registeredResult->fetch_assoc()["count"];
    
    if ($registered >= $capacity) {
        echo json_encode(["success" => false, "message" => "This event is already full"]);
        exit;
    }
    
    // Register user for event
    $insertSql = "INSERT INTO registre (ID_EVENT, ID_MEMBER) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ii", $eventId, $userId);
    
    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Successfully registered for the event!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register for the event"]);
    }
    exit;
}

// If AJAX request for events
if (isset($_GET['action']) && $_GET['action'] === 'get_events') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $sql = "SELECT e.*, GROUP_CONCAT(t.TOPIC_NAME) as topics, c.NAME as club_name
            FROM evenement e
            LEFT JOIN contains ct ON e.ID_EVENT = ct.ID_EVENT
            LEFT JOIN topics t ON ct.TOPIC_ID = t.TOPIC_ID
            LEFT JOIN organizes o ON e.ID_EVENT = o.ID_EVENT
            LEFT JOIN club c ON o.ID_CLUB = c.ID_CLUB
            GROUP BY e.ID_EVENT
            ORDER BY e.DATE ASC";
    $result = $conn->query($sql);

    $events = [];
    $cities = [];
    $types = [];
    
    while ($row = $result->fetch_assoc()) {
        // Track unique cities and types for statistics
        if (!in_array($row["CITY"], $cities)) {
            $cities[] = $row["CITY"];
        }
        if (!in_array($row["EVENT_TYPE"], $types)) {
            $types[] = $row["EVENT_TYPE"];
        }
        
        // Check if user is registered for this event
        $isRegistered = false;
        if ($userId) {
            $registrationSql = "SELECT COUNT(*) as isRegistered FROM registre WHERE ID_EVENT = ? AND ID_MEMBER = ?";
            $registrationStmt = $conn->prepare($registrationSql);
            $registrationStmt->bind_param("ii", $row["ID_EVENT"], $userId);
            $registrationStmt->execute();
            $registrationResult = $registrationStmt->get_result();
            $isRegistered = $registrationResult->fetch_assoc()["isRegistered"] > 0;
        }
        
        // Get actual registration count
        $registeredCountSql = "SELECT COUNT(*) as count FROM registre WHERE ID_EVENT = ?";
        $registeredCountStmt = $conn->prepare($registeredCountSql);
        $registeredCountStmt->bind_param("i", $row["ID_EVENT"]);
        $registeredCountStmt->execute();
        $registeredCountResult = $registeredCountStmt->get_result();
        $registeredCount = $registeredCountResult->fetch_assoc()["count"];
        
        $events[] = [
            "id" => $row["ID_EVENT"],
            "title" => $row["TITLE"],
            "description" => $row["DESCRIPTION"],
            "date" => $row["DATE"],
            "time" => $row["STARTING_TIME"] . ($row["ENDING_TIME"] ? ' - ' . $row["ENDING_TIME"] : ''),
            "location" => $row["LOCATION"],
            "city" => $row["CITY"] ?: "Unknown City",
            "type" => $row["EVENT_TYPE"] ?: "Event",
            "capacity" => $row["CAPACITY"] ?: 0,
            "registered" => $registeredCount,
            "price" => $row["PRICE"] ?: 0,
            "organizer" => $row["club_name"] ?: "Unknown Club",
            "tags" => $row["topics"] ? explode(',', $row["topics"]) : [],
            "isRegistered" => $isRegistered
        ];
    }
    
    // Calculate statistics
    $statistics = [
        "totalEvents" => count($events),
        "totalCities" => count($cities),
        "totalTypes" => count($types)
    ];
    
    $response = [
        "events" => $events,
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
    <title>Data Club - Events</title>
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
                Upcoming Data Science Events
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Join workshops, conferences, and meetups to expand your knowledge, network with peers, 
                and stay updated with the latest trends in data science and AI.
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-events">-</h3>
                <p class="text-gray-600">Upcoming Events</p>
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
                    <i class="fas fa-filter text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom" id="total-types">-</h3>
                <p class="text-gray-600">Event Types</p>
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
                        placeholder="Search events..."
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

                    <!-- Type Filter -->
                    <div class="relative">
                        <select id="type-filter" class="w-full sm:w-40 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-slate-custom">
                            <option value="">All Types</option>
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
                Loading events...
            </p>
        </div>

        <!-- Event Cards -->
        <div id="events-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Events will be populated by JavaScript -->
        </div>

        <!-- No Results -->
        <div id="no-results" class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-black-custom mb-2">No events found</h3>
            <p class="text-gray-600">Try adjusting your search or filters to find more events.</p>
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
                    You need to be logged in to register for events. Please login to your account first.
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
        let events = [];
        let filteredEvents = [];
        let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            fetchEvents();
            setupEventHandlers();
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
                { element: document.getElementById('total-events'), value: 0 },
                { element: document.getElementById('total-cities'), value: 0 },
                { element: document.getElementById('total-types'), value: 0 }
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

        // Fetch events function
        function fetchEvents() {
            fetch('events.php?action=get_events')
                .then(response => response.json())
                .then(data => {
                    events = data.events;
                    filteredEvents = [...events];
                    
                    // Update statistics
                    updateStatistics(data.statistics);
                    
                    populateFilters();
                    renderEvents();
                    setupEventListeners();
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    document.getElementById('events-container').innerHTML = '<p class="text-red-500 col-span-full text-center">Failed to load events. Please try again later.</p>';
                });
        }

        // Update statistics function
        function updateStatistics(statistics) {
            document.getElementById('total-events').textContent = statistics.totalEvents;
            document.getElementById('total-cities').textContent = statistics.totalCities;
            document.getElementById('total-types').textContent = statistics.totalTypes;
        }

        // Populate filter dropdowns
        function populateFilters() {
            const cities = [...new Set(events.map(event => event.city))].sort();
            const types = [...new Set(events.map(event => event.type))].sort();
            
            const cityFilter = document.getElementById('city-filter');
            const typeFilter = document.getElementById('type-filter');

            cityFilter.innerHTML = '<option value="">All Cities</option>';
            typeFilter.innerHTML = '<option value="">All Types</option>';
            
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                cityFilter.appendChild(option);
            });
            
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                typeFilter.appendChild(option);
            });
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                weekday: 'short', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        // Get type color
        function getTypeColor(type) {
            switch (type.toLowerCase()) {
                case 'workshop':
                    return 'bg-slate-100 text-slate-custom';
                case 'conference':
                    return 'bg-red-100 text-red-custom';
                case 'meetup':
                    return 'bg-gray-100 text-gray-700';
                case 'hackathon':
                    return 'bg-red-100 text-red-custom';
                default:
                    return 'bg-gray-100 text-gray-700';
            }
        }

        // Render events
        function renderEvents() {
            const container = document.getElementById('events-container');
            const noResults = document.getElementById('no-results');
            const resultsCount = document.getElementById('results-count');
            
            if (filteredEvents.length === 0) {
                container.classList.add('hidden');
                noResults.classList.remove('hidden');
                resultsCount.textContent = 'No events found';
                return;
            }
            
            container.classList.remove('hidden');
            noResults.classList.add('hidden');
            resultsCount.textContent = `Showing ${filteredEvents.length} of ${events.length} events`;
            
            container.innerHTML = filteredEvents.map((event, index) => {
                const spotsLeft = event.capacity - event.registered;
                const isAlmostFull = spotsLeft <= 10;
                const isFull = spotsLeft <= 0;

                return `
                    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-500 overflow-hidden group cursor-pointer animate-on-scroll opacity-0 transform translate-y-4" 
                         style="animation-delay: ${index * 100}ms; animation-fill-mode: forwards;" 
                         onclick="viewEvent('${event.id}')">
                        <div class="relative">
                            <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center transform transition-transform duration-300 group-hover:scale-105">
                                <div class="text-slate-400 text-6xl font-bold opacity-20">
                                    ${event.title.charAt(0)}
                                </div>
                            </div>
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${getTypeColor(event.type)} transition-all duration-300 hover:scale-105">
                                    ${event.type}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4 bg-white rounded-lg p-2 shadow-sm transform transition-transform duration-300 group-hover:scale-110">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                                        ${formatDate(event.date).split(' ')[0]}
                                    </div>
                                    <div class="text-lg font-bold text-black-custom">
                                        ${formatDate(event.date).split(' ')[2]}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ${formatDate(event.date).split(' ')[1]}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="mb-4">
                                <h3 class="text-xl font-bold text-black-custom group-hover:text-slate-custom transition-colors duration-300 mb-2">
                                    ${event.title}
                                </h3>
                                <p class="text-gray-600 text-sm line-clamp-2">
                                    ${event.description}
                                </p>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-clock mr-2"></i>
                                    ${event.time}
                                </div>
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    ${event.location}${event.city && event.city !== 'Unknown City' ? ', ' + event.city : ''}
                                </div>
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-users mr-2"></i>
                                    ${event.registered}/${event.capacity} registered
                                    ${isAlmostFull && !isFull ? `<span class="ml-2 text-red-custom text-xs font-medium animate-pulse">Only ${spotsLeft} spots left!</span>` : ''}
                                    ${isFull ? '<span class="ml-2 text-red-custom text-xs font-medium">Fully booked</span>' : ''}
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                ${event.tags.slice(0, 3).map(tag => `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-md transition-all duration-300 hover:bg-gray-200 hover:scale-105">
                                        ${tag}
                                    </span>
                                `).join('')}
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    by ${event.organizer}
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-black-custom">
                                            ${event.price === 0 ? 'Free' : `${event.price} MAD`}
                                        </div>
                                    </div>
                                    ${event.isRegistered ? 
                                        '<button class="bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed animate-pulse" disabled><i class="fas fa-check mr-1"></i>Registered</button>' :
                                        `<button class="${isFull ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-custom text-white hover:bg-red-600'} px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 transform hover:scale-105 hover:shadow-lg" ${isFull ? 'disabled' : ''} onclick="event.stopPropagation(); registerForEvent('${event.id}')">
                                            ${isFull ? 'Full' : 'Register'}
                                        </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Add animation classes after rendering
            setTimeout(() => {
                document.querySelectorAll('.animate-on-scroll').forEach((el, index) => {
                    el.style.animationDelay = `${index * 100}ms`;
                    el.classList.add('animate-fade-in');
                });
            }, 100);
        }

        // Filter events
        function filterEvents() {
            const searchQuery = document.getElementById('search-input').value.toLowerCase();
            const selectedCity = document.getElementById('city-filter').value;
            const selectedType = document.getElementById('type-filter').value;

            filteredEvents = events.filter(event => {
                const matchesSearch = searchQuery === '' || 
                    event.title.toLowerCase().includes(searchQuery) ||
                    event.description.toLowerCase().includes(searchQuery) ||
                    event.organizer.toLowerCase().includes(searchQuery) ||
                    event.tags.some(tag => tag.toLowerCase().includes(searchQuery));

                const matchesCity = selectedCity === '' || event.city === selectedCity;
                const matchesType = selectedType === '' || event.type === selectedType;
                
                return matchesSearch && matchesCity && matchesType;
            });
            
            renderEvents();
            updateClearButton();
        }

        // Update clear button visibility
        function updateClearButton() {
            const clearButton = document.getElementById('clear-filters');
            const hasFilters = document.getElementById('search-input').value ||
                              document.getElementById('city-filter').value ||
                              document.getElementById('type-filter').value;
            
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
            document.getElementById('type-filter').value = '';
            filteredEvents = [...events];
            renderEvents();
            updateClearButton();
        }

        // View event details
        function viewEvent(eventId) {
            window.location.href = `event.php?id=${eventId}`;
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('search-input').addEventListener('input', filterEvents);
            document.getElementById('city-filter').addEventListener('change', filterEvents);
            document.getElementById('type-filter').addEventListener('change', filterEvents);
            document.getElementById('clear-filters').addEventListener('click', clearFilters);
        }

        // Setup event handlers
        function setupEventHandlers() {
            document.addEventListener('click', function(e) {
                if (e.target && e.target.textContent === 'Register') {
                    e.stopPropagation();
                    const eventId = e.target.getAttribute('onclick').match(/'([^']+)'/)[1];
                    
                    if (!isLoggedIn) {
                        showLoginModal();
                    } else {
                        registerForEvent(eventId);
                    }
                }
            });
        }

        // Register for event function
        function registerForEvent(eventId) {
            const formData = new FormData();
            formData.append('register_event', '1');
            formData.append('event_id', eventId);
            
            fetch('events.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh events to update registration status
                    fetchEvents();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while registering for the event.', 'error');
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
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
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
        
        /* Pulse animation for pending requests and urgent messages */
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