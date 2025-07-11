<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    
    if ($password) {
        $stmt = $pdo->prepare("UPDATE member SET FIRST_NAME = ?, LAST_NAME = ?, EMAIL = ?, PASSWORD = ? WHERE ID_MEMBER = ?");
        $stmt->execute([$first_name, $last_name, $email, $password, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE member SET FIRST_NAME = ?, LAST_NAME = ?, EMAIL = ? WHERE ID_MEMBER = ?");
        $stmt->execute([$first_name, $last_name, $email, $user_id]);
    }
    
    $success_message = "Profile updated successfully!";
}

// Handle club creation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_club_request'])) {
    $club_name = $_POST['club_name'];
    $school = $_POST['school'];
    $city = $_POST['city'];
    $expected_members = $_POST['expected_members'];
    $description = $_POST['description'];
    $motivation = $_POST['motivation'];
    $phone = $_POST['phone'];
    $instagram = $_POST['instagram'];
    
    // Get selected focus areas
    $focus_areas = isset($_POST['focus_areas']) ? implode(',', $_POST['focus_areas']) : '';
    
    $stmt = $pdo->prepare("INSERT INTO clubcreationrequest (ID_MEMBER, CLUB_NAME, DESCRIPTION, TEAM_MEMBERS, SOCIAL_LINKS, STATUS, UNIVERSITY) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$user_id, $club_name, $description . ' | Motivation: ' . $motivation . ' | Focus: ' . $focus_areas . ' | Phone: ' . $phone . ' | Instagram: ' . $instagram, $expected_members, $instagram, $school]);
    
    $club_request_message = "Club creation request submitted successfully!";
}

// Handle withdraw request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_request'])) {
    $club_id = $_POST['club_id'];
    $stmt = $pdo->prepare("DELETE FROM requestjoin WHERE ID_MEMBER = ? AND ID_CLUB = ?");
    $stmt->execute([$user_id, $club_id]);
    $withdraw_message = "Request withdrawn successfully!";
}

// Fetch user information
$stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has a club (is a club owner)
$my_club = null;
$my_club_events = [];
if ($user['ID_CLUB']) {
    // Fetch club information
    $stmt = $pdo->prepare("SELECT * FROM club WHERE ID_MEMBER = ?");
    $stmt->execute([$user_id]);
    $my_club = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($my_club) {
        // Fetch club events
        $stmt = $pdo->prepare("
            SELECT e.*, COUNT(r.ID_MEMBER) as registered_count 
            FROM evenement e 
            JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
            LEFT JOIN registre r ON e.ID_EVENT = r.ID_EVENT 
            WHERE o.ID_CLUB = ? 
            GROUP BY e.ID_EVENT 
            ORDER BY e.DATE ASC
        ");
        $stmt->execute([$my_club['ID_CLUB']]);
        $my_club_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch clubs user is part of (join requests)
$stmt = $pdo->prepare("
    SELECT c.*, rj.ACCEPTED, 
           CASE 
               WHEN rj.ACCEPTED = 1 THEN 'Member'
               WHEN rj.ACCEPTED = 0 THEN 'Request Sent'
               ELSE 'Pending'
           END as status
    FROM requestjoin rj 
    JOIN club c ON rj.ID_CLUB = c.ID_CLUB 
    WHERE rj.ID_MEMBER = ?
");
$stmt->execute([$user_id]);
$clubs_part_of = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate member since date (assuming registration date is when the member was created)
$member_since = "January 2023"; // You might want to add a registration_date column to track this properly
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - DataClub</title>
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

        <?php if (isset($club_request_message)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                <?php echo $club_request_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($withdraw_message)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                <?php echo $withdraw_message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="relative bg-gradient-to-r from-slate-custom to-slate-700 h-32">
                <div class="absolute -bottom-16 left-8">
                    <div class="relative">
                        <div class="w-32 h-32 bg-white rounded-full p-2 shadow-lg">
                            <div class="w-full h-full bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold text-4xl">
                                <?php echo strtoupper(substr($user['FIRST_NAME'], 0, 1) . substr($user['LAST_NAME'], 0, 1)); ?>
                            </div>
                        </div>
                        <button class="absolute bottom-2 right-2 w-8 h-8 bg-red-custom text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="pt-20 pb-6 px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-3xl font-bold text-black-custom mb-2"><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></h1>
                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($user['EMAIL']); ?></p>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-calendar mr-2"></i>
                            Member since <?php echo $member_since; ?>
                        </div>
                    </div>
                    <button id="edit-profile-btn" class="bg-slate-custom text-white px-6 py-3 rounded-lg font-medium hover:bg-slate-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Details -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-black-custom mb-4">Personal Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['FIRST_NAME']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['LAST_NAME']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" value="••••••••" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" readonly>
                        </div>
                    </div>
                </div>

                <!-- Clubs I'm Part Of -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-black-custom mb-4">Clubs I'm Part Of</h2>
                    <?php if (empty($clubs_part_of)): ?>
                        <p class="text-gray-500 text-center py-4">You haven't joined any clubs yet.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($clubs_part_of as $club): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-slate-custom to-slate-700 rounded-lg flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-medium text-black-custom"><?php echo htmlspecialchars($club['NAME']); ?></h3>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($club['UNIVERSITY']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <?php if ($club['ACCEPTED'] == 1): ?>
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Member</span>
                                        <?php elseif ($club['ACCEPTED'] == 0): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Request Sent</span>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="club_id" value="<?php echo $club['ID_CLUB']; ?>">
                                                <button type="submit" name="withdraw_request" class="text-red-custom hover:text-red-600 text-xs">
                                                    [Withdraw]
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Club Information & Events -->
            <div class="lg:col-span-2 space-y-8">
                <?php if ($my_club): ?>
                    <!-- My Club Section -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-custom to-slate-700 text-white p-6">
                            <h2 class="text-2xl font-bold mb-2">My Club</h2>
                            <p class="opacity-90">Your current club leadership and activities</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-start space-x-6 mb-6">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-2xl">
                                    <?php echo strtoupper(substr($my_club['NAME'], 0, 2)); ?>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-black-custom mb-2"><?php echo htmlspecialchars($my_club['NAME']); ?></h3>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            <?php echo htmlspecialchars($my_club['UNIVERSITY']); ?>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            <?php echo htmlspecialchars($my_club['CITY']); ?>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-envelope mr-2"></i>
                                            <?php echo htmlspecialchars($my_club['EMAIL']); ?>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($my_club['DESCRIPTION']); ?></p>
                                    <div class="flex items-center space-x-3">
                                        <span class="px-3 py-1 bg-red-100 text-red-custom rounded-full text-sm font-medium">President</span>
                                        <span class="px-3 py-1 bg-slate-100 text-slate-custom rounded-full text-sm font-medium">Club Owner</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-slate-50 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-slate-custom"><?php echo count($my_club_events); ?></div>
                                    <div class="text-sm text-gray-600">Events Organized</div>
                                </div>
                                <div class="bg-red-50 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-red-custom">
                                        <?php 
                                        $total_attendees = 0;
                                        foreach ($my_club_events as $event) {
                                            $total_attendees += $event['registered_count'];
                                        }
                                        echo $total_attendees;
                                        ?>
                                    </div>
                                    <div class="text-sm text-gray-600">Total Attendees</div>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-slate-custom">
                                        <?php echo count(array_filter($my_club_events, function($event) { return strtotime($event['DATE']) >= time(); })); ?>
                                    </div>
                                    <div class="text-sm text-gray-600">Upcoming Events</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <a href="../club_owner/manage_club.php" class="bg-slate-custom text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                                    <i class="fas fa-cog mr-2"></i>
                                    Manage Club
                                </a>
                                <a href="../club_owner/create_event.php" class="bg-red-custom text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create Event
                                </a>
                                <a href="club.php?id=<?php echo $my_club['ID_CLUB']; ?>" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    View Club Page
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Club Events -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-black-custom">My Club Events</h2>
                            <a href="events.php" class="text-slate-custom hover:text-slate-700 text-sm font-medium">
                                View All Events
                                <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        <?php if (empty($my_club_events)): ?>
                            <p class="text-gray-500 text-center py-8">No events organized yet. <a href="create_event.php" class="text-red-custom hover:underline">Create your first event!</a></p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($my_club_events, 0, 3) as $event): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h3 class="font-bold text-black-custom mb-1"><?php echo htmlspecialchars($event['TITLE']); ?></h3>
                                                <div class="flex items-center text-sm text-gray-600 space-x-4">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        <?php echo date('M d, Y', strtotime($event['DATE'])); ?>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?php echo date('H:i', strtotime($event['STARTING_TIME'])) . ' - ' . date('H:i', strtotime($event['ENDING_TIME'])); ?>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-users mr-1"></i>
                                                        <?php echo $event['registered_count']; ?>/<?php echo $event['CAPACITY']; ?> registered
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 bg-slate-100 text-slate-custom text-xs rounded-full"><?php echo htmlspecialchars($event['EVENT_TYPE']); ?></span>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars(substr($event['DESCRIPTION'], 0, 150)) . '...'; ?></p>
                                        <div class="flex items-center justify-between">
                                            <div class="text-lg font-bold text-black-custom">
                                                <?php echo $event['PRICE'] == 0 ? 'Free' : $event['PRICE'] . ' MAD'; ?>
                                            </div>
                                            <a href="../club_owner/manage_event.php?id=<?php echo $event['ID_EVENT']; ?>" class="text-slate-custom hover:text-slate-700 text-sm font-medium">
                                                Manage Event
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Create Club Invitation Section -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-md overflow-hidden border border-red-200">
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-red-custom rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-plus text-white text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-black-custom mb-3">Start Your Own Club</h2>
                            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                Have a passion for data science or AI? Create your own club and bring together like-minded students at your university.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-white rounded-lg p-4">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-lightbulb text-red-custom"></i>
                                    </div>
                                    <h3 class="font-medium text-black-custom mb-1">Share Ideas</h3>
                                    <p class="text-sm text-gray-600">Connect with students who share your interests</p>
                                </div>
                                <div class="bg-white rounded-lg p-4">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-calendar-alt text-red-custom"></i>
                                    </div>
                                    <h3 class="font-medium text-black-custom mb-1">Organize Events</h3>
                                    <p class="text-sm text-gray-600">Host workshops, seminars, and competitions</p>
                                </div>
                                <div class="bg-white rounded-lg p-4">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-trophy text-red-custom"></i>
                                    </div>
                                    <h3 class="font-medium text-black-custom mb-1">Build Impact</h3>
                                    <p class="text-sm text-gray-600">Make a difference in your university community</p>
                                </div>
                            </div>

                            <button id="create-club-btn" class="bg-red-custom text-white px-8 py-3 rounded-lg font-medium hover:bg-red-600 transition-colors">
                                <i class="fas fa-rocket mr-2"></i>
                                Request Club Creation
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black-custom">Edit Profile</h2>
                    <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['FIRST_NAME']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['LAST_NAME']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="update_profile" class="flex-1 bg-slate-custom text-white py-2 rounded-lg hover:bg-slate-700 transition-colors">
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

    <!-- Club Creation Form Modal -->
    <div id="club-creation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-black-custom">Request Club Creation</h2>
                    <button id="close-club-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Club Name *</label>
                            <input type="text" name="club_name" placeholder="e.g., Data Science Club" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School *</label>
                            <input type="text" name="school" placeholder="e.g., Mohammed V University" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                            <input type="text" name="city" placeholder="e.g., Rabat" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Members</label>
                            <input type="number" name="expected_members" placeholder="e.g., 50" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Club Description *</label>
                        <textarea name="description" rows="4" placeholder="Describe your club's mission, goals, and activities..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Focus Areas *</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="Machine Learning" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">Machine Learning</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="Data Science" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">Data Science</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="AI Research" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">AI Research</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="Web Development" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">Web Development</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="Cybersecurity" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">Cybersecurity</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="focus_areas[]" value="Other" class="mr-2 text-slate-custom focus:ring-slate-custom">
                                <span class="text-sm">Other</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Why do you want to start this club? *</label>
                        <textarea name="motivation" rows="3" placeholder="Tell us about your motivation and vision..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required></textarea>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-black-custom mb-2">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone" placeholder="+212 6XX XXX XXX" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instagram Profile</label>
                                <input type="text" name="instagram" placeholder="@username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="create_club_request" class="flex-1 bg-red-custom text-white py-3 rounded-lg hover:bg-red-600 transition-colors font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Request
                        </button>
                        <button type="button" id="cancel-club" class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const editProfileModal = document.getElementById('edit-profile-modal');
        const closeEditModal = document.getElementById('close-edit-modal');
        const cancelEdit = document.getElementById('cancel-edit');

        const createClubBtn = document.getElementById('create-club-btn');
        const clubCreationModal = document.getElementById('club-creation-modal');
        const closeClubModal = document.getElementById('close-club-modal');
        const cancelClub = document.getElementById('cancel-club');

        // Edit Profile Modal
        editProfileBtn.addEventListener('click', () => {
            editProfileModal.classList.remove('hidden');
        });

        closeEditModal.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });

        cancelEdit.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });

        // Club Creation Modal
        if (createClubBtn) {
            createClubBtn.addEventListener('click', () => {
                clubCreationModal.classList.remove('hidden');
            });
        }

        closeClubModal.addEventListener('click', () => {
            clubCreationModal.classList.add('hidden');
        });

        cancelClub.addEventListener('click', () => {
            clubCreationModal.classList.add('hidden');
        });

        // Close modals when clicking outside
        editProfileModal.addEventListener('click', (e) => {
            if (e.target === editProfileModal) {
                editProfileModal.classList.add('hidden');
            }
        });

        clubCreationModal.addEventListener('click', (e) => {
            if (e.target === clubCreationModal) {
                clubCreationModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>