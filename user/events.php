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

// If AJAX request for events
if (isset($_GET['action']) && $_GET['action'] === 'get_events') {
    $sql = "SELECT e.*, GROUP_CONCAT(t.TOPIC_NAME) as topics
            FROM evenement e
            LEFT JOIN contains c ON e.ID_EVENT = c.ID_EVENT
            LEFT JOIN topics t ON c.TOPIC_ID = t.TOPIC_ID
            GROUP BY e.ID_EVENT
            ORDER BY e.DATE ASC";
    $result = $conn->query($sql);

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            "id" => $row["ID_EVENT"],
            "title" => $row["TITLE"],
            "description" => $row["DESCRIPTION"],
            "date" => $row["DATE"],
            "time" => $row["STARTING_TIME"] . ($row["ENDING_TIME"] ? ' - ' . $row["ENDING_TIME"] : ''),
            "location" => $row["LOCATION"],
            "city" => $row["LOCATION"], // You may want to adjust this if you have a city field
            "type" => $row["EVENT_TYPE"] ?: "Event",
            "capacity" => $row["CAPACITY"] ?: 0,
            "registered" => 0, // You can fetch real count if you want
            "price" => $row["PRICE"] ?: 0,
            "organizer" => "", // You can join with club/organizer if needed
            "tags" => $row["topics"] ? explode(',', $row["topics"]) : []
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($events);
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
        let events = [];
        let filteredEvents = [];

        function fetchEvents() {
            fetch('events.php?action=get_events')
                .then(response => response.json())
                .then(data => {
                    events = data;
                    filteredEvents = [...events];
                    populateFilters();
                    renderEvents();
                    setupEventListeners();
                    updateStats();
                })
                .catch(error => {
                    document.getElementById('events-container').innerHTML = '<p class="text-red-500">Failed to load events.</p>';
                });
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

            container.innerHTML = filteredEvents.map(event => {
                const spotsLeft = event.capacity - event.registered;
                const isAlmostFull = spotsLeft <= 10;
                const isFull = spotsLeft <= 0;

                return `
                    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer" onclick="viewEvent('${event.id}')">
                        <div class="relative">
                            <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                                <div class="text-slate-400 text-6xl font-bold opacity-20">
                                    ${event.title.charAt(0)}
                                </div>
                            </div>
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${getTypeColor(event.type)}">
                                    ${event.type}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4 bg-white rounded-lg p-2 shadow-sm">
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
                                <h3 class="text-xl font-bold text-black-custom group-hover:text-slate-custom transition-colors mb-2">
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
                                    ${event.location}, ${event.city}
                                </div>
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-users mr-2"></i>
                                    ${event.registered}/${event.capacity} registered
                                    ${isAlmostFull && !isFull ? `<span class="ml-2 text-red-custom text-xs font-medium">Only ${spotsLeft} spots left!</span>` : ''}
                                    ${isFull ? '<span class="ml-2 text-red-custom text-xs font-medium">Fully booked</span>' : ''}
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                ${event.tags.slice(0, 3).map(tag => `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-md">
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
                                    <button class="${isFull ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-custom text-white hover:bg-red-600'} px-4 py-2 rounded-lg text-sm font-medium transition-colors" ${isFull ? 'disabled' : ''}>
                                        ${isFull ? 'Full' : 'Register'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
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

        // Update stats
        function updateStats() {
            document.getElementById('total-events').textContent = events.length;
            const uniqueCities = new Set(events.map(e => e.city));
            document.getElementById('total-cities').textContent = uniqueCities.size;
            const uniqueTypes = new Set(events.map(e => e.type));
            document.getElementById('total-types').textContent = uniqueTypes.size;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', fetchEvents);
    </script>
</body>
</html> 