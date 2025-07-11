<?php
session_start();
require_once '../user/db.php';

// Check if admin is logged in using privilege system
if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege']) || $_SESSION['privilege'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Get admin information from member table
$stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle club deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_club'])) {
    $club_id = $_POST['club_id'];
    
    // Delete related records first
    $pdo->prepare("DELETE FROM focuses WHERE ID_CLUB = ?")->execute([$club_id]);
    $pdo->prepare("DELETE FROM requestjoin WHERE ID_CLUB = ?")->execute([$club_id]);
    $pdo->prepare("DELETE FROM organizes WHERE ID_CLUB = ?")->execute([$club_id]);
    
    // Delete the club
    $pdo->prepare("DELETE FROM club WHERE ID_CLUB = ?")->execute([$club_id]);
    
    $success_message = "Club deleted successfully!";
}

// Handle club creation request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) {
    $request_id = $_POST['request_id'];
    
    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM clubcreationrequest WHERE ID_REQUEST = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        // Create the club
        $stmt = $pdo->prepare("INSERT INTO club (ID_MEMBER, NAME, DESCRIPTION, UNIVERSITY, CITY) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$request['ID_MEMBER'], $request['CLUB_NAME'], $request['DESCRIPTION'], $request['UNIVERSITY'], 'City']);
        
        $club_id = $pdo->lastInsertId();
        
        // Update member's club association
        $stmt = $pdo->prepare("UPDATE member SET ID_CLUB = ? WHERE ID_MEMBER = ?");
        $stmt->execute([$club_id, $request['ID_MEMBER']]);
        
        // Update request status
        $stmt = $pdo->prepare("UPDATE clubcreationrequest SET STATUS = 'approved' WHERE ID_REQUEST = ?");
        $stmt->execute([$request_id]);
        
        $success_message = "Club creation request approved successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $request_id = $_POST['request_id'];
    
    $stmt = $pdo->prepare("UPDATE clubcreationrequest SET STATUS = 'rejected' WHERE ID_REQUEST = ?");
    $stmt->execute([$request_id]);
    
    $success_message = "Club creation request rejected.";
}

// Get dashboard statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_clubs FROM club");
$stmt->execute();
$total_clubs = $stmt->fetch(PDO::FETCH_ASSOC)['total_clubs'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_events FROM evenement");
$stmt->execute();
$total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_members FROM member");
$stmt->execute();
$total_members = $stmt->fetch(PDO::FETCH_ASSOC)['total_members'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_requests FROM clubcreationrequest WHERE STATUS = 'pending'");
$stmt->execute();
$pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['pending_requests'];

// Get all clubs
$stmt = $pdo->prepare("
    SELECT c.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL,
           (SELECT COUNT(*) FROM requestjoin rj WHERE rj.ID_CLUB = c.ID_CLUB AND rj.ACCEPTED = 1) as member_count,
           (SELECT COUNT(*) FROM organizes o WHERE o.ID_CLUB = c.ID_CLUB) as event_count
    FROM club c 
    JOIN member m ON c.ID_MEMBER = m.ID_MEMBER 
    ORDER BY c.NAME
");
$stmt->execute();
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all events
$stmt = $pdo->prepare("
    SELECT e.*, c.NAME as club_name,
           (SELECT COUNT(*) FROM registre r WHERE r.ID_EVENT = e.ID_EVENT) as registration_count
    FROM evenement e 
    JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
    JOIN club c ON o.ID_CLUB = c.ID_CLUB 
    ORDER BY e.DATE DESC
");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending club creation requests
$stmt = $pdo->prepare("
    SELECT ccr.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL 
    FROM clubcreationrequest ccr 
    JOIN member m ON ccr.ID_MEMBER = m.ID_MEMBER 
    WHERE ccr.STATUS = 'pending' 
    ORDER BY ccr.ID_REQUEST DESC
");
$stmt->execute();
$club_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DataClub</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-custom">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 bg-slate-custom rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">DC</span>
                        </div>
                        <span class="ml-2 text-xl font-bold text-black-custom">DataClub Admin</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="flex items-center space-x-2 text-gray-600 hover:text-slate-custom transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-red-custom to-red-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo strtoupper(substr($admin['FIRST_NAME'], 0, 1) . substr($admin['LAST_NAME'], 0, 1)); ?>
                            </div>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($admin['FIRST_NAME'] . ' ' . $admin['LAST_NAME']); ?></span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Admin</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg h-screen sticky top-0">
            <div class="p-6">
                <nav class="space-y-2">
                    <a href="#dashboard" onclick="showSection('dashboard')" class="nav-link active flex items-center px-4 py-2 text-slate-custom bg-slate-50 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="#clubs" onclick="showSection('clubs')" class="nav-link flex items-center px-4 py-2 text-gray-600 hover:text-slate-custom hover:bg-slate-50 rounded-lg transition-colors">
                        <i class="fas fa-users mr-3"></i>
                        Clubs Management
                    </a>
                    <a href="#events" onclick="showSection('events')" class="nav-link flex items-center px-4 py-2 text-gray-600 hover:text-slate-custom hover:bg-slate-50 rounded-lg transition-colors">
                        <i class="fas fa-calendar mr-3"></i>
                        Events Management
                    </a>
                    <a href="#requests" onclick="showSection('requests')" class="nav-link flex items-center px-4 py-2 text-gray-600 hover:text-slate-custom hover:bg-slate-50 rounded-lg transition-colors">
                        <i class="fas fa-clipboard-list mr-3"></i>
                        Club Requests
                        <?php if ($pending_requests > 0): ?>
                            <span class="ml-auto bg-red-custom text-white text-xs px-2 py-1 rounded-full"><?php echo $pending_requests; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Success Message -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-black-custom">Admin Dashboard</h1>
                    <p class="text-gray-600 mt-2">Manage clubs, events, and monitor platform activity</p>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Clubs</p>
                                <p class="text-2xl font-bold text-black-custom"><?php echo $total_clubs; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-slate-custom text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Events</p>
                                <p class="text-2xl font-bold text-black-custom"><?php echo $total_events; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar text-red-custom text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Members</p>
                                <p class="text-2xl font-bold text-black-custom"><?php echo $total_members; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-friends text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Requests</p>
                                <p class="text-2xl font-bold text-black-custom"><?php echo $pending_requests; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-black-custom mb-4">Recent Clubs</h3>
                        <div class="space-y-4">
                            <?php foreach (array_slice($clubs, 0, 5) as $club): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-slate-custom to-slate-700 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                            <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-black-custom"><?php echo htmlspecialchars($club['NAME']); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo $club['member_count']; ?> members</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-black-custom mb-4">Recent Events</h3>
                        <div class="space-y-4">
                            <?php foreach (array_slice($events, 0, 5) as $event): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-black-custom"><?php echo htmlspecialchars($event['TITLE']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($event['club_name']); ?> • <?php echo date('M d, Y', strtotime($event['DATE'])); ?></p>
                                    </div>
                                    <span class="px-2 py-1 bg-slate-100 text-slate-custom text-xs rounded-full"><?php echo htmlspecialchars($event['EVENT_TYPE']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clubs Management Section -->
            <div id="clubs-section" class="section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-black-custom">Clubs Management</h1>
                    <p class="text-gray-600 mt-2">Manage all clubs on the platform</p>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-black-custom">All Clubs (<?php echo count($clubs); ?>)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($clubs as $club): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-br from-slate-custom to-slate-700 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                                    <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($club['NAME']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($club['CITY']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($club['FIRST_NAME'] . ' ' . $club['LAST_NAME']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($club['EMAIL']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($club['UNIVERSITY']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $club['member_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $club['event_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this club? This action cannot be undone.')">
                                                <input type="hidden" name="club_id" value="<?php echo $club['ID_CLUB']; ?>">
                                                <button type="submit" name="delete_club" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Events Management Section -->
            <div id="events-section" class="section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-black-custom">Events Management</h1>
                    <p class="text-gray-600 mt-2">Monitor all events on the platform</p>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-black-custom">All Events (<?php echo count($events); ?>)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['TITLE']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($event['LOCATION']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($event['club_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y', strtotime($event['DATE'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-custom text-xs rounded-full">
                                                <?php echo htmlspecialchars($event['EVENT_TYPE']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $event['registration_count']; ?>/<?php echo $event['CAPACITY']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $event['PRICE'] == 0 ? 'Free' : $event['PRICE'] . ' MAD'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Club Requests Section -->
            <div id="requests-section" class="section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-black-custom">Club Creation Requests</h1>
                    <p class="text-gray-600 mt-2">Review and manage club creation requests</p>
                </div>

                <?php if (empty($club_requests)): ?>
                    <div class="bg-white rounded-xl shadow-md p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-black-custom mb-2">No pending requests</h3>
                        <p class="text-gray-600">All club creation requests have been processed.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($club_requests as $request): ?>
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($request['CLUB_NAME'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-black-custom"><?php echo htmlspecialchars($request['CLUB_NAME']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                Requested by <?php echo htmlspecialchars($request['FIRST_NAME'] . ' ' . $request['LAST_NAME']); ?>
                                                (<?php echo htmlspecialchars($request['EMAIL']); ?>)
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Pending</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">University:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['UNIVERSITY']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Expected Members:</p>
                                        <p class="text-sm text-gray-900"><?php echo $request['TEAM_MEMBERS']; ?></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Description:</p>
                                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['DESCRIPTION']); ?></p>
                                </div>

                                <div class="flex space-x-3">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['ID_REQUEST']; ?>">
                                        <button type="submit" name="approve_request" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                            <i class="fas fa-check mr-2"></i>
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['ID_REQUEST']; ?>">
                                        <button type="submit" name="reject_request" class="bg-red-custom text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                            <i class="fas fa-times mr-2"></i>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionName + '-section').classList.remove('hidden');
            
            // Update navigation
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active', 'text-slate-custom', 'bg-slate-50');
                link.classList.add('text-gray-600');
            });
            
            event.target.classList.add('active', 'text-slate-custom', 'bg-slate-50');
            event.target.classList.remove('text-gray-600');
        }
    </script>
</body>
</html>