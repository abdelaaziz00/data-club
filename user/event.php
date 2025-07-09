<?php
// Start session
session_start();



// Database connection (adjust credentials as needed)
$mysqli = new mysqli('localhost', 'root', '', 'data_club');
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: ' . $mysqli->connect_error);
}

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$event_id) {
    echo '<div class="min-h-screen flex items-center justify-center"><div class="text-center"><h1 class="text-2xl font-bold text-black-custom mb-4">Event Not Found</h1><p class="text-gray-600 mb-6">The event you\'re looking for doesn\'t exist.</p><a href="events.html" class="bg-red-custom text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-colors">Back to Events</a></div></div>';
    exit;
}

// Fetch event details
$event_sql = "SELECT e.*, o.ID_CLUB, c.NAME as organizer_name FROM evenement e LEFT JOIN organizes o ON e.ID_EVENT = o.ID_EVENT LEFT JOIN club c ON o.ID_CLUB = c.ID_CLUB WHERE e.ID_EVENT = ?";
$event_stmt = $mysqli->prepare($event_sql);
$event_stmt->bind_param('i', $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();
if (!$event) {
    echo '<div class="min-h-screen flex items-center justify-center"><div class="text-center"><h1 class="text-2xl font-bold text-black-custom mb-4">Event Not Found</h1><p class="text-gray-600 mb-6">The event you\'re looking for doesn\'t exist.</p><a href="events.html" class="bg-red-custom text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-colors">Back to Events</a></div></div>';
    exit;
}

// Fetch speakers
$speakers = [];
$speaker_sql = "SELECT s.SPEAKER_FULLNAME, s.SPEAKER_DISCRIPTION, s.SPEAKER_COMPANY FROM speaks sp JOIN speaker s ON sp.SPEAKER_ID = s.SPEAKER_ID WHERE sp.ID_EVENT = ?";
$speaker_stmt = $mysqli->prepare($speaker_sql);
$speaker_stmt->bind_param('i', $event_id);
$speaker_stmt->execute();
$speaker_result = $speaker_stmt->get_result();
while ($row = $speaker_result->fetch_assoc()) {
    $speakers[] = $row;
}

// Fetch agenda (event sessions)
$agenda = [];
$agenda_sql = "SELECT SESSION_NAME FROM event_session WHERE ID_EVENT = ? ORDER BY SESSION_ID ASC";
$agenda_stmt = $mysqli->prepare($agenda_sql);
$agenda_stmt->bind_param('i', $event_id);
$agenda_stmt->execute();
$agenda_result = $agenda_stmt->get_result();
while ($row = $agenda_result->fetch_assoc()) {
    $agenda[] = $row['SESSION_NAME'];
}

// Fetch topics
$topics = [];
$topics_sql = "SELECT t.TOPIC_NAME FROM contains c JOIN topics t ON c.TOPIC_ID = t.TOPIC_ID WHERE c.ID_EVENT = ?";
$topics_stmt = $mysqli->prepare($topics_sql);
$topics_stmt->bind_param('i', $event_id);
$topics_stmt->execute();
$topics_result = $topics_stmt->get_result();
while ($row = $topics_result->fetch_assoc()) {
    $topics[] = $row['TOPIC_NAME'];
}

// Helper: Format date
function formatDate($date) {
    return date('l, F j, Y', strtotime($date));
}

// Helper: Get type color
function getTypeColor($type) {
    $type = strtolower($type);
    switch ($type) {
        case 'workshop': return 'bg-slate-100 text-slate-custom';
        case 'conference': return 'bg-red-100 text-red-custom';
        case 'meetup': return 'bg-gray-100 text-gray-700';
        case 'hackathon': return 'bg-red-100 text-red-custom';
        default: return 'bg-gray-100 text-gray-700';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['TITLE']) ?> - DataClub</title>
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
                Back to Events
            </button>
        </div>
        <!-- Event Profile -->
        <div id="event-profile" class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-r from-slate-custom to-slate-700 text-white p-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-6 lg:space-y-0 lg:space-x-8">
                    <div class="w-24 h-24 bg-white bg-opacity-20 rounded-xl flex items-center justify-center text-3xl font-bold">
                        <?= isset($event['organizer_name']) ? strtoupper(substr($event['organizer_name'], 0, 2)) : '?' ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-3">
                            <span class="px-3 py-1 <?= getTypeColor($event['EVENT_TYPE']) ?> rounded-full text-sm font-medium">
                                <?= htmlspecialchars($event['EVENT_TYPE']) ?>
                            </span>
                            <div class="bg-white bg-opacity-20 rounded-lg px-3 py-1">
                                <span class="text-sm font-medium"><?= formatDate($event['DATE']) ?></span>
                            </div>
                        </div>
                        <h1 class="text-3xl lg:text-4xl font-bold mb-3"><?= htmlspecialchars($event['TITLE']) ?></h1>
                        <div class="flex flex-wrap items-center gap-4 text-lg opacity-90">
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2"></i>
                                <?= htmlspecialchars($event['STARTING_TIME']) ?> - <?= htmlspecialchars($event['ENDING_TIME']) ?>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?= htmlspecialchars($event['LOCATION']) ?>, <?= htmlspecialchars($event['CITY']) ?>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users mr-2"></i>
                                <?= htmlspecialchars($event['CAPACITY']) ?> capacity
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-building mr-2"></i>
                                <?= htmlspecialchars($event['organizer_name']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-3">
                        <div class="text-right mb-2">
                            <div class="text-2xl font-bold">
                                <?= $event['PRICE'] == 0 ? 'Free' : htmlspecialchars($event['PRICE']) . ' MAD' ?>
                            </div>
                        </div>
                        <button class="bg-red-custom hover:bg-red-600 text-white px-8 py-3 rounded-lg font-medium transition-colors">
                            Register Now
                        </button>
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
                            <h2 class="text-2xl font-bold text-black-custom mb-4">About This Event</h2>
                            <div class="prose prose-gray max-w-none">
                                <p class="mb-4 text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($event['DESCRIPTION'])) ?></p>
                            </div>
                        </section>
                        <!-- Speakers -->
                        <section>
                            <h2 class="text-2xl font-bold text-black-custom mb-4">Speakers</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach ($speakers as $speaker): ?>
                                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-16 h-16 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                            <?= htmlspecialchars($speaker['SPEAKER_FULLNAME'][0]) ?>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-black-custom"><?= htmlspecialchars($speaker['SPEAKER_FULLNAME']) ?></h3>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($speaker['SPEAKER_DISCRIPTION']) ?></p>
                                            <p class="text-sm text-slate-custom"><?= htmlspecialchars($speaker['SPEAKER_COMPANY']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                        <!-- Agenda -->
                        <section>
                            <h2 class="text-2xl font-bold text-black-custom mb-4">Agenda</h2>
                            <div class="space-y-4">
                                <?php foreach ($agenda as $session): ?>
                                    <div class="flex items-start space-x-4 p-4 border border-gray-200 rounded-xl">
                                        <div class="bg-slate-custom text-white px-3 py-1 rounded-lg text-sm font-medium min-w-fit">
                                            Session
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-medium text-black-custom"><?= htmlspecialchars($session) ?></h3>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                        <!-- Tags -->
                        <section>
                            <h2 class="text-2xl font-bold text-black-custom mb-4">Topics</h2>
                            <div class="flex flex-wrap gap-3">
                                <?php foreach ($topics as $tag): ?>
                                    <span class="px-4 py-2 bg-slate-100 text-slate-custom rounded-lg font-medium">
                                        <?= htmlspecialchars($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Event Details -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="text-lg font-bold text-black-custom mb-4">Event Details</h3>
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-calendar text-slate-custom mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Date</p>
                                        <p class="font-medium"><?= formatDate($event['DATE']) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-clock text-slate-custom mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Time</p>
                                        <p class="font-medium"><?= htmlspecialchars($event['STARTING_TIME']) ?> - <?= htmlspecialchars($event['ENDING_TIME']) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-map-marker-alt text-slate-custom mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Location</p>
                                        <p class="font-medium"><?= htmlspecialchars($event['LOCATION']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($event['CITY']) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-users text-slate-custom mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Capacity</p>
                                        <p class="font-medium"><?= htmlspecialchars($event['CAPACITY']) ?> people</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-tag text-slate-custom mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Price</p>
                                        <p class="font-medium"><?= $event['PRICE'] == 0 ? 'Free' : htmlspecialchars($event['PRICE']) . ' MAD' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Related Events (Optional: implement as needed) -->
    </main>
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
<?php $mysqli->close(); ?> 