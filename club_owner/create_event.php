<?php
session_start();
require_once '../user/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Get user's club
$stmt = $pdo->prepare("SELECT c.* FROM club c JOIN member m ON c.ID_MEMBER = m.ID_MEMBER WHERE m.ID_MEMBER = ?");
$stmt->execute([$user_id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    header('Location: ../user/profile.php');
    exit();
}

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $event_type = $_POST['event_type'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'] ?: 0;
    
    // Create event
    $stmt = $pdo->prepare("INSERT INTO evenement (TITLE, DESCRIPTION, DATE, STARTING_TIME, ENDING_TIME, LOCATION,CITY, EVENT_TYPE, PRICE, CAPACITY) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?, ?)");
    $stmt->execute([$title, $description, $date, $start_time, $end_time, $venue . ', ' .  ($address ? ', ' . $address : ''),$city , $event_type, $price, $capacity]);
    
    $event_id = $pdo->lastInsertId();
    
    // Link event to club
    $stmt = $pdo->prepare("INSERT INTO organizes (ID_CLUB, ID_EVENT) VALUES (?, ?)");
    $stmt->execute([$club['ID_CLUB'], $event_id]);
    
    // Handle speakers
    if (isset($_POST['speakers']) && is_array($_POST['speakers'])) {
        foreach ($_POST['speakers'] as $speaker) {
            if (!empty($speaker['name'])) {
                $stmt = $pdo->prepare("INSERT INTO speaker (SPEAKER_FULLNAME, SPEAKER_DISCRIPTION, SPEAKER_COMPANY) VALUES (?, ?, ?)");
                $stmt->execute([$speaker['name'], $speaker['title'], $speaker['company']]);
                
                $speaker_id = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("INSERT INTO speaks (SPEAKER_ID, ID_EVENT) VALUES (?, ?)");
                $stmt->execute([$speaker_id, $event_id]);
            }
        }
    }
    
    // Handle agenda/sessions
    if (isset($_POST['agenda']) && is_array($_POST['agenda'])) {
        foreach ($_POST['agenda'] as $session) {
            if (!empty($session['topic'])) {
                $stmt = $pdo->prepare("INSERT INTO event_session (ID_EVENT, SESSION_NAME) VALUES (?, ?)");
                $stmt->execute([$event_id, $session['time'] . ' - ' . $session['topic']]);
            }
        }
    }
    
    // Handle topics/tags
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        foreach ($_POST['tags'] as $topic_id) {
            $stmt = $pdo->prepare("INSERT INTO contains (ID_EVENT, TOPIC_ID) VALUES (?, ?)");
            $stmt->execute([$event_id, $topic_id]);
        }
    }
    
    $success_message = "Event created successfully!";
    header('Location: ../user/profile.php');
    exit();
}

// Get all topics for tags
$stmt = $pdo->prepare("SELECT * FROM topics");
$stmt->execute();
$all_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - DataClub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
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
</head>
<body class="bg-gray-custom">
    <!-- Navigation -->
<?php include '../includes/header.php'; ?> 

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="../user/profile.php" class="text-slate-custom hover:text-slate-700 transition-colors mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Profile
                </a>
            </div>
            <h1 class="text-3xl font-bold text-black-custom">Create New Event</h1>
            <p class="text-gray-600 mt-2">Organize an event for <?php echo htmlspecialchars($club['NAME']); ?></p>
        </div>

        <!-- Event Creation Form -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Title *</label>
                            <input type="text" name="title" placeholder="e.g., Machine Learning Workshop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Type *</label>
                            <select name="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                                <option value="">Select Type</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Conference">Conference</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Meetup">Meetup</option>
                                <option value="Hackathon">Hackathon</option>
                                <option value="Bootcamp">Bootcamp</option>
                                <option value="Competition">Competition</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Description *</label>
                        <textarea name="description" rows="6" placeholder="Detailed description of your event, what attendees will learn, agenda, etc." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required></textarea>
                    </div>
                </div>

                <!-- Date & Time -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Date & Time</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Date *</label>
                            <input type="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                            <input type="time" name="start_time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                            <input type="time" name="end_time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Location</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Venue Name *</label>
                            <input type="text" name="venue" placeholder="e.g., Faculty of Sciences" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                            <input type="text" name="city" placeholder="e.g., Rabat" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
                        <textarea name="address" rows="2" placeholder="Complete address with room number, building, etc." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom"></textarea>
                    </div>
                </div>

                <!-- Capacity & Pricing -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Capacity & Pricing</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Capacity *</label>
                            <input type="number" name="capacity" placeholder="e.g., 50" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registration Fee (MAD)</label>
                            <input type="number" name="price" placeholder="0 for free events" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                        </div>
                    </div>
                </div>

                <!-- Speakers -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Speakers</h2>
                    <div id="speakers-container">
                        <div class="speaker-entry border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Speaker Name</label>
                                    <input type="text" name="speakers[0][name]" placeholder="e.g., Dr. Ahmed Benali" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                                    <input type="text" name="speakers[0][title]" placeholder="e.g., ML Research Director" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company/Organization</label>
                                    <input type="text" name="speakers[0][company]" placeholder="e.g., AI Innovation Lab" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-speaker" class="text-slate-custom hover:text-slate-700 text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Add Another Speaker
                    </button>
                </div>

                <!-- Agenda -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Agenda</h2>
                    <div id="agenda-container">
                        <div class="agenda-entry border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="text" name="agenda[0][time]" placeholder="e.g., 14:00 - 14:30" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Topic/Activity</label>
                                    <input type="text" name="agenda[0][topic]" placeholder="e.g., Introduction to Machine Learning" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-agenda" class="text-slate-custom hover:text-slate-700 text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Add Agenda Item
                    </button>
                </div>

                <!-- Tags -->
                <div>
                    <h2 class="text-xl font-bold text-black-custom mb-4">Tags</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <?php foreach ($all_topics as $topic): ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="tags[]" value="<?php echo $topic['TOPIC_ID']; ?>" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm"><?php echo htmlspecialchars($topic['TOPIC_NAME']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4 pt-6">
                    <button type="submit" name="create_event" class="bg-red-custom text-white px-8 py-3 rounded-lg hover:bg-red-600 transition-colors font-medium">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Create Event
                    </button>
                    <a href="../user/profile.php" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        let speakerCount = 1;
        let agendaCount = 1;

        // Add speaker functionality
        document.getElementById('add-speaker').addEventListener('click', () => {
            const container = document.getElementById('speakers-container');
            const speakerEntry = document.createElement('div');
            speakerEntry.className = 'speaker-entry border border-gray-200 rounded-lg p-4 mb-4';
            speakerEntry.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Speaker Name</label>
                        <input type="text" name="speakers[${speakerCount}][name]" placeholder="e.g., Dr. Ahmed Benali" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                        <input type="text" name="speakers[${speakerCount}][title]" placeholder="e.g., ML Research Director" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company/Organization</label>
                        <input type="text" name="speakers[${speakerCount}][company]" placeholder="e.g., AI Innovation Lab" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                </div>
                <button type="button" class="remove-speaker text-red-custom hover:text-red-600 text-sm mt-2">
                    <i class="fas fa-trash mr-1"></i>
                    Remove Speaker
                </button>
            `;
            container.appendChild(speakerEntry);
            speakerCount++;
            
            // Add remove functionality
            speakerEntry.querySelector('.remove-speaker').addEventListener('click', () => {
                speakerEntry.remove();
            });
        });

        // Add agenda functionality
        document.getElementById('add-agenda').addEventListener('click', () => {
            const container = document.getElementById('agenda-container');
            const agendaEntry = document.createElement('div');
            agendaEntry.className = 'agenda-entry border border-gray-200 rounded-lg p-4 mb-4';
            agendaEntry.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                        <input type="text" name="agenda[${agendaCount}][time]" placeholder="e.g., 14:00 - 14:30" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Topic/Activity</label>
                        <input type="text" name="agenda[${agendaCount}][topic]" placeholder="e.g., Introduction to Machine Learning" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                </div>
                <button type="button" class="remove-agenda text-red-custom hover:text-red-600 text-sm mt-2">
                    <i class="fas fa-trash mr-1"></i>
                    Remove Item
                </button>
            `;
            container.appendChild(agendaEntry);
            agendaCount++;
            
            // Add remove functionality
            agendaEntry.querySelector('.remove-agenda').addEventListener('click', () => {
                agendaEntry.remove();
            });
        });
    </script>
</body>
</html>