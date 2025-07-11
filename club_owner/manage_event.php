<?php
session_start();
require_once '../user/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$event_id) {
    header('Location: profile.php');
    exit();
}

// Verify that the user owns the club that organized this event
$verify_sql = "SELECT e.*, c.NAME as club_name, c.ID_CLUB 
               FROM evenement e 
               JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
               JOIN club c ON o.ID_CLUB = c.ID_CLUB 
               WHERE e.ID_EVENT = ? AND c.ID_MEMBER = ?";
$verify_stmt = $pdo->prepare($verify_sql);
$verify_stmt->execute([$event_id, $user_id]);
$event = $verify_stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: profile.php');
    exit();
}

// Handle event update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $starting_time = $_POST['starting_time'];
    $ending_time = $_POST['ending_time'];
    $location = $_POST['location'];
    $city = $_POST['city'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];
    $event_type = $_POST['event_type'];
    
    $update_sql = "UPDATE evenement SET 
                   TITLE = ?, DESCRIPTION = ?, DATE = ?, STARTING_TIME = ?, 
                   ENDING_TIME = ?, LOCATION = ?, CITY = ?, CAPACITY = ?, 
                   PRICE = ?, EVENT_TYPE = ? 
                   WHERE ID_EVENT = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$title, $description, $date, $starting_time, $ending_time, 
                          $location, $city, $capacity, $price, $event_type, $event_id]);
    
    $success_message = "Event updated successfully!";
    
    // Refresh event data
    $verify_stmt->execute([$event_id, $user_id]);
    $event = $verify_stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch event registrations
$registrations_sql = "SELECT r.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL
                      FROM registre r 
                      JOIN member m ON r.ID_MEMBER = m.ID_MEMBER 
                      WHERE r.ID_EVENT = ? 
                      ORDER BY m.FIRST_NAME ASC";
$registrations_stmt = $pdo->prepare($registrations_sql);
$registrations_stmt->execute([$event_id]);
$registrations = $registrations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Count registrations by status
$total_registrations = count($registrations);

// Helper functions
function formatDate($date) {
    return date('l, F j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event - <?= htmlspecialchars($event['TITLE']) ?></title>
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-6">
            <a href="profile.php" class="flex items-center text-slate-custom hover:text-slate-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Profile
            </a>
        </div>

        <!-- Event Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-slate-custom to-slate-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($event['TITLE']) ?></h1>
                        <p class="opacity-90">Manage your event details and registrations</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-75">Organized by</div>
                        <div class="font-semibold"><?= htmlspecialchars($event['club_name']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Event Stats -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-slate-custom"><?= $total_registrations ?></div>
                        <div class="text-sm text-gray-600">Total Registrations</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-custom"><?= max(0, $event['CAPACITY'] - $total_registrations) ?></div>
                        <div class="text-sm text-gray-600">Available Spots</div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-slate-custom"><?= number_format(($total_registrations / $event['CAPACITY']) * 100, 1) ?>%</div>
                        <div class="text-sm text-gray-600">Capacity Filled</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Event Details -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-black-custom">Event Details</h2>
                        <button id="edit-event-btn" class="text-slate-custom hover:text-slate-700">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <p class="text-gray-900"><?= formatDate($event['DATE']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                            <p class="text-gray-900"><?= date('H:i', strtotime($event['STARTING_TIME'])) ?> - <?= date('H:i', strtotime($event['ENDING_TIME'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <p class="text-gray-900"><?= htmlspecialchars($event['LOCATION']) ?>, <?= htmlspecialchars($event['CITY']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <p class="text-gray-900"><?= htmlspecialchars($event['CAPACITY']) ?> people</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                            <p class="text-gray-900"><?= $event['PRICE'] == 0 ? 'Free' : htmlspecialchars($event['PRICE']) . ' MAD' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <p class="text-gray-900"><?= htmlspecialchars($event['EVENT_TYPE']) ?></p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-medium text-black-custom mb-2">Description</h3>
                        <p class="text-gray-600 text-sm"><?= nl2br(htmlspecialchars($event['DESCRIPTION'])) ?></p>
                    </div>

                    <div class="mt-6 flex space-x-3">
                        <a href="event.php?id=<?= $event_id ?>" class="flex-1 bg-slate-custom text-white py-2 px-4 rounded-lg text-center hover:bg-slate-700 transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            View Public Page
                        </a>
                    </div>
                </div>
            </div>

            <!-- Registrations Management -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-black-custom">Event Registrations</h2>
                        <div class="text-sm text-gray-600">
                            Total: <?= $total_registrations ?> registrations
                        </div>
                    </div>

                    <?php if (empty($registrations)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No registrations yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-y-auto" style="max-height: 420px;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <?php foreach ($registrations as $registration): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold">
                                                    <?= strtoupper(substr($registration['FIRST_NAME'], 0, 1) . substr($registration['LAST_NAME'], 0, 1)) ?>
                                                </div>
                                                <span class="font-medium text-black-custom">
                                                    <?= htmlspecialchars($registration['FIRST_NAME'] . ' ' . $registration['LAST_NAME']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                                <?= htmlspecialchars($registration['EMAIL']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Registered</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="mailto:<?= htmlspecialchars($registration['EMAIL']) ?>" class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors" title="Contact Member">
                                                    <i class="fas fa-envelope text-sm"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Event Modal -->
    <div id="edit-event-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black-custom">Edit Event</h2>
                    <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($event['TITLE']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required><?= htmlspecialchars($event['DESCRIPTION']) ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" value="<?= $event['DATE'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <input type="time" name="starting_time" value="<?= $event['STARTING_TIME'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" name="ending_time" value="<?= $event['ENDING_TIME'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($event['LOCATION']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($event['CITY']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input type="number" name="capacity" value="<?= $event['CAPACITY'] ?>" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (MAD)</label>
                            <input type="number" name="price" value="<?= $event['PRICE'] ?>" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                            <select name="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                                <option value="Workshop" <?= $event['EVENT_TYPE'] == 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                                <option value="Conference" <?= $event['EVENT_TYPE'] == 'Conference' ? 'selected' : '' ?>>Conference</option>
                                <option value="Meetup" <?= $event['EVENT_TYPE'] == 'Meetup' ? 'selected' : '' ?>>Meetup</option>
                                <option value="Hackathon" <?= $event['EVENT_TYPE'] == 'Hackathon' ? 'selected' : '' ?>>Hackathon</option>
                                <option value="Seminar" <?= $event['EVENT_TYPE'] == 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="update_event" class="flex-1 bg-slate-custom text-white py-2 rounded-lg hover:bg-slate-700 transition-colors">
                            Save Changes
                        </button>
                        <button type="button" id="cancel-edit" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const editEventBtn = document.getElementById('edit-event-btn');
        const editEventModal = document.getElementById('edit-event-modal');
        const closeEditModal = document.getElementById('close-edit-modal');
        const cancelEdit = document.getElementById('cancel-edit');

        editEventBtn.addEventListener('click', () => {
            editEventModal.classList.remove('hidden');
        });

        closeEditModal.addEventListener('click', () => {
            editEventModal.classList.add('hidden');
        });

        cancelEdit.addEventListener('click', () => {
            editEventModal.classList.add('hidden');
        });

        editEventModal.addEventListener('click', (e) => {
            if (e.target === editEventModal) {
                editEventModal.classList.add('hidden');
            }
        });

    </script>
</body>
</html>