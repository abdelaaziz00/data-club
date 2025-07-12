<?php
session_start();
require_once '../user/db.php';

// Check if admin is logged in using privilege system
if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege']) || $_SESSION['privilege'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Get admin information from admin table
$stmt = $pdo->prepare("SELECT * FROM admin WHERE ID_ADMIN = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin exists, if not redirect to login
if (!$admin) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle club deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_club'])) {
    $club_id = $_POST['club_id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get all events organized by this club
        $stmt = $pdo->prepare("SELECT ID_EVENT FROM organizes WHERE ID_CLUB = ?");
        $stmt->execute([$club_id]);
        $events = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Delete event registrations
        if (!empty($events)) {
            $placeholders = str_repeat('?,', count($events) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM registre WHERE ID_EVENT IN ($placeholders)");
            $stmt->execute($events);
            
            // Delete event topics
            $stmt = $pdo->prepare("DELETE FROM contains WHERE ID_EVENT IN ($placeholders)");
            $stmt->execute($events);
            
            // Delete event speakers
            $stmt = $pdo->prepare("DELETE FROM speaks WHERE ID_EVENT IN ($placeholders)");
            $stmt->execute($events);
            
            // Delete the events themselves
            $stmt = $pdo->prepare("DELETE FROM evenement WHERE ID_EVENT IN ($placeholders)");
            $stmt->execute($events);
        }
        
        // Delete club-related records
        $pdo->prepare("DELETE FROM focuses WHERE ID_CLUB = ?")->execute([$club_id]);
        $pdo->prepare("DELETE FROM requestjoin WHERE ID_CLUB = ?")->execute([$club_id]);
        $pdo->prepare("DELETE FROM organizes WHERE ID_CLUB = ?")->execute([$club_id]);
        
        // Update member's club association to NULL
        $pdo->prepare("UPDATE member SET ID_CLUB = NULL WHERE ID_CLUB = ?")->execute([$club_id]);
        
        // Delete the club
        $pdo->prepare("DELETE FROM club WHERE ID_CLUB = ?")->execute([$club_id]);
        
        // Commit transaction
        $pdo->commit();
        
        $success_message = "Club and all its events deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $success_message = "Error deleting club: " . $e->getMessage();
    }
}

// Handle club creation request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) {
    $request_id = $_POST['request_id'];
    
    // Get request details and check if it's still pending
    $stmt = $pdo->prepare("SELECT * FROM clubcreationrequest WHERE ID_REQUEST = ? AND STATUS = 'pending'");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        // Check if member already has a club
        $stmt = $pdo->prepare("SELECT ID_CLUB FROM member WHERE ID_MEMBER = ?");
        $stmt->execute([$request['ID_MEMBER']]);
        $existing_club = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_club && $existing_club['ID_CLUB']) {
            $success_message = "Member already has a club. Cannot approve this request.";
        } else {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Create the club
                $stmt = $pdo->prepare("INSERT INTO club (ID_MEMBER, NAME, DESCRIPTION, UNIVERSITY, CITY, EMAIL, CLUB_PHONE, INSTAGRAM_LINK, LINKEDIN_LINK) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$request['ID_MEMBER'], $request['CLUB_NAME'], $request['DESCRIPTION'], $request['UNIVERSITY'], $request['CITY'], $request['EMAIL'], $request['CLUB_PHONE'], $request['INSTAGRAM_LINK'], $request['LINKEDIN_LINK']]);
                
                $club_id = $pdo->lastInsertId();
                
                // Update member's club association
                $stmt = $pdo->prepare("UPDATE member SET ID_CLUB = ? WHERE ID_MEMBER = ?");
                $stmt->execute([$club_id, $request['ID_MEMBER']]);
                
                // Copy focus areas from willfocus to focuses
                $stmt = $pdo->prepare("INSERT INTO focuses (ID_CLUB, TOPIC_ID) SELECT ?, TOPIC_ID FROM willfocus WHERE ID_REQUEST = ?");
                $stmt->execute([$club_id, $request_id]);
                
                // Update request status and store admin ID
                $stmt = $pdo->prepare("UPDATE clubcreationrequest SET STATUS = 'approved', ID_ADMIN = ? WHERE ID_REQUEST = ?");
                $stmt->execute([$admin_id, $request_id]);
                
                // Commit transaction
                $pdo->commit();
                
                $success_message = "Club creation request approved successfully!";
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $success_message = "Error approving request: " . $e->getMessage();
            }
        }
    } else {
        $success_message = "Request not found or already processed.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $request_id = $_POST['request_id'];
    
    $stmt = $pdo->prepare("UPDATE clubcreationrequest SET STATUS = 'rejected', ID_ADMIN = ? WHERE ID_REQUEST = ?");
    $stmt->execute([$admin_id, $request_id]);
    
    $success_message = "Club creation request rejected.";
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete event registrations
        $pdo->prepare("DELETE FROM registre WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Delete event topics
        $pdo->prepare("DELETE FROM contains WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Delete event speakers
        $pdo->prepare("DELETE FROM speaks WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Delete event sessions
        $pdo->prepare("DELETE FROM event_session WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Delete event from organizes
        $pdo->prepare("DELETE FROM organizes WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Delete the event itself
        $pdo->prepare("DELETE FROM evenement WHERE ID_EVENT = ?")->execute([$event_id]);
        
        // Commit transaction
        $pdo->commit();
        
        $success_message = "Event deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $success_message = "Error deleting event: " . $e->getMessage();
    }
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

// Get pending club creation requests with focus areas
$stmt = $pdo->prepare("
    SELECT ccr.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL,
           GROUP_CONCAT(t.TOPIC_NAME SEPARATOR ', ') as focus_areas,
           a.FIRST_NAME as admin_first_name, a.LAST_NAME as admin_last_name
    FROM clubcreationrequest ccr 
    JOIN member m ON ccr.ID_MEMBER = m.ID_MEMBER 
    LEFT JOIN willfocus wf ON ccr.ID_REQUEST = wf.ID_REQUEST
    LEFT JOIN topics t ON wf.TOPIC_ID = t.TOPIC_ID
    LEFT JOIN admin a ON ccr.ID_ADMIN = a.ID_ADMIN
    WHERE ccr.STATUS = 'pending' 
    GROUP BY ccr.ID_REQUEST
    ORDER BY ccr.ID_REQUEST DESC
");
$stmt->execute();
$club_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get processed club creation requests (approved/rejected) with admin info
$stmt = $pdo->prepare("
    SELECT ccr.*, m.FIRST_NAME, m.LAST_NAME, m.EMAIL,
           GROUP_CONCAT(t.TOPIC_NAME SEPARATOR ', ') as focus_areas,
           a.FIRST_NAME as admin_first_name, a.LAST_NAME as admin_last_name
    FROM clubcreationrequest ccr 
    JOIN member m ON ccr.ID_MEMBER = m.ID_MEMBER 
    LEFT JOIN willfocus wf ON ccr.ID_REQUEST = wf.ID_REQUEST
    LEFT JOIN topics t ON wf.TOPIC_ID = t.TOPIC_ID
    LEFT JOIN admin a ON ccr.ID_ADMIN = a.ID_ADMIN
    WHERE ccr.STATUS IN ('approved', 'rejected') 
    GROUP BY ccr.ID_REQUEST
    ORDER BY ccr.ID_REQUEST DESC
    LIMIT 10
");
$stmt->execute();
$processed_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        'black-custom': '#121212',
                        'gradient-primary': '#667eea',
                        'gradient-secondary': '#764ba2',
                        'gradient-success': '#11998e',
                        'gradient-warning': '#f093fb',
                        'gradient-danger': '#f093fb'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'bounce-slow': 'bounce 2s infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        .stat-card:hover::before {
            transform: translateX(100%);
        }
        .sidebar-item {
            transition: all 0.3s ease;
            position: relative;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        .sidebar-item:hover::before {
            width: 4px;
        }
        .sidebar-item.active::before {
            width: 4px;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="glass-effect border-b border-white/20 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="../static/images/mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="flex items-center space-x-3 text-gray-600 hover:text-slate-custom transition-all duration-300 group">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-custom to-red-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <?php echo strtoupper(substr($admin['FIRST_NAME'], 0, 1) . substr($admin['LAST_NAME'], 0, 1)); ?>
                            </div>
                            <div class="text-left">
                                <span class="text-sm font-medium text-black-custom"><?php echo htmlspecialchars($admin['FIRST_NAME'] . ' ' . $admin['LAST_NAME']); ?></span>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-gradient-to-r from-red-custom to-red-600 text-white text-xs rounded-full font-medium">Admin</span>
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white/80 backdrop-blur-md shadow-xl h-screen sticky top-16 border-r border-gray-200/50">
            <div class="p-6">
                <nav class="space-y-2">
                    <a href="#dashboard" onclick="showSection('dashboard')" class="nav-link active sidebar-item flex items-center px-4 py-3 text-slate-custom bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl font-medium">
                        <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                        Dashboard
                    </a>
                    <a href="#clubs" onclick="showSection('clubs')" class="nav-link sidebar-item flex items-center px-4 py-3 text-gray-600 hover:text-slate-custom hover:bg-gradient-to-r hover:from-slate-50 hover:to-slate-100 rounded-xl transition-all duration-300 font-medium">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        Clubs Management
                    </a>
                    <a href="#events" onclick="showSection('events')" class="nav-link sidebar-item flex items-center px-4 py-3 text-gray-600 hover:text-slate-custom hover:bg-gradient-to-r hover:from-slate-50 hover:to-slate-100 rounded-xl transition-all duration-300 font-medium">
                        <i class="fas fa-calendar mr-3 text-lg"></i>
                        Events Management
                    </a>
                    <a href="#requests" onclick="showSection('requests')" class="nav-link sidebar-item flex items-center px-4 py-3 text-gray-600 hover:text-slate-custom hover:bg-gradient-to-r hover:from-slate-50 hover:to-slate-100 rounded-xl transition-all duration-300 font-medium">
                        <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                        Club Requests
                        <?php if ($pending_requests > 0): ?>
                            <span class="ml-auto bg-gradient-to-r from-red-custom to-red-600 text-white text-xs px-2 py-1 rounded-full animate-bounce-slow"><?php echo $pending_requests; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Success Message -->
            <?php if (isset($success_message)): ?>
                <div id="success-message" class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6 animate-fade-in shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-600"></i>
                            <?php echo $success_message; ?>
                        </div>
                        <button onclick="dismissMessage('success-message')" class="text-green-600 hover:text-green-800 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section animate-fade-in">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-black-custom mb-2">Welcome back, <?php echo htmlspecialchars($admin['FIRST_NAME']); ?>! 👋</h1>
                    <p class="text-gray-600 text-lg">Here's what's happening with your DataClub platform today</p>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card text-white rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Total Clubs</p>
                                <p class="text-3xl font-bold"><?php echo $total_clubs; ?></p>
                                <p class="text-white/60 text-xs mt-1">Active clubs</p>
                            </div>
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-custom to-red-600 text-white rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Total Events</p>
                                <p class="text-3xl font-bold"><?php echo $total_events; ?></p>
                                <p class="text-white/60 text-xs mt-1">Organized events</p>
                            </div>
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-calendar text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Total Members</p>
                                <p class="text-3xl font-bold"><?php echo $total_members; ?></p>
                                <p class="text-white/60 text-xs mt-1">Registered users</p>
                            </div>
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-user-friends text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Pending Requests</p>
                                <p class="text-3xl font-bold"><?php echo $pending_requests; ?></p>
                                <p class="text-white/60 text-xs mt-1">Awaiting review</p>
                            </div>
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-black-custom">Recent Clubs</h3>
                            <div class="w-8 h-8 bg-gradient-to-br from-slate-custom to-slate-700 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <?php foreach (array_slice($clubs, 0, 5) as $club): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors duration-300">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg overflow-hidden">
                                            <?php if ($club['LOGO']): ?>
                                                <img src="../static/images/<?php echo htmlspecialchars($club['LOGO']); ?>" 
                                                     alt="Club Logo" 
                                                     class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-black-custom"><?php echo htmlspecialchars($club['NAME']); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo $club['member_count']; ?> members</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-black-custom">Recent Events</h3>
                            <div class="w-8 h-8 bg-gradient-to-br from-red-custom to-red-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <?php foreach (array_slice($events, 0, 5) as $event): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors duration-300">
                                    <div>
                                        <h4 class="font-semibold text-black-custom"><?php echo htmlspecialchars($event['TITLE']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($event['club_name']); ?> • <?php echo date('M d, Y', strtotime($event['DATE'])); ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-gradient-to-r from-slate-custom to-slate-700 text-white text-xs rounded-full font-medium">
                                        <?php echo htmlspecialchars($event['EVENT_TYPE']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clubs Management Section -->
            <div id="clubs-section" class="section hidden animate-fade-in">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-black-custom mb-2">Clubs Management</h1>
                    <p class="text-gray-600 text-lg">Manage all clubs on the platform</p>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-black-custom">All Clubs (<?php echo count($clubs); ?>)</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-gray-500">Live</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Owner</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">University</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Members</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Events</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200/50">
                                <?php foreach ($clubs as $club): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-sm mr-4 shadow-lg overflow-hidden">
                                                    <?php if ($club['LOGO']): ?>
                                                        <img src="../static/images/<?php echo htmlspecialchars($club['LOGO']); ?>" 
                                                             alt="Club Logo" 
                                                             class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($club['NAME']); ?></div>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">
                                                <?php echo $club['member_count']; ?> members
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                                <?php echo $club['event_count']; ?> events
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this club? This action cannot be undone.')">
                                                <input type="hidden" name="club_id" value="<?php echo $club['ID_CLUB']; ?>">
                                                <button type="submit" name="delete_club" class="text-red-600 hover:text-red-900 transition-colors duration-200">
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
            <div id="events-section" class="section hidden animate-fade-in">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-black-custom mb-2">Events Management</h1>
                    <p class="text-gray-600 text-lg">Monitor all events on the platform</p>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-black-custom">All Events (<?php echo count($events); ?>)</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-gray-500">Live</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registrations</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200/50">
                                <?php foreach ($events as $event): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($event['TITLE']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($event['LOCATION']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($event['club_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y', strtotime($event['DATE'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 bg-gradient-to-r from-slate-custom to-slate-700 text-white text-xs rounded-full font-medium">
                                                <?php echo htmlspecialchars($event['EVENT_TYPE']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                                <?php echo $event['registration_count']; ?>/<?php echo $event['CAPACITY']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $event['PRICE'] == 0 ? 'Free' : $event['PRICE'] . ' MAD'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                                                <input type="hidden" name="event_id" value="<?php echo $event['ID_EVENT']; ?>">
                                                <button type="submit" name="delete_event" class="text-red-600 hover:text-red-900 transition-colors duration-200">
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

            <!-- Club Requests Section -->
            <div id="requests-section" class="section hidden animate-fade-in">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-black-custom mb-2">Club Creation Requests</h1>
                    <p class="text-gray-600 text-lg">Review and manage club creation requests</p>
                </div>

                <?php if (empty($club_requests)): ?>
                    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-12 text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6 floating">
                            <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-black-custom mb-2">No pending requests</h3>
                        <p class="text-gray-600">All club creation requests have been processed.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($club_requests as $request): ?>
                            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 card-hover">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                            <?php echo strtoupper(substr($request['CLUB_NAME'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-black-custom"><?php echo htmlspecialchars($request['CLUB_NAME']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                Requested by <?php echo htmlspecialchars($request['FIRST_NAME'] . ' ' . $request['LAST_NAME']); ?>
                                                (<?php echo htmlspecialchars($request['EMAIL']); ?>)
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-4 py-2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white rounded-full text-sm font-medium shadow-lg">
                                        Pending Review
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">University:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['UNIVERSITY']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">City:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['CITY']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Expected Team Members:</p>
                                        <p class="text-sm text-gray-900"><?php echo $request['TEAM_MEMBERS']; ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Email:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['EMAIL']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Phone:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['CLUB_PHONE']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Instagram:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['INSTAGRAM_LINK']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-1">LinkedIn:</p>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($request['LINKEDIN_LINK']); ?></p>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Description:</p>
                                    <p class="text-sm text-gray-900 bg-gray-50 rounded-xl p-4"><?php echo htmlspecialchars($request['DESCRIPTION']); ?></p>
                                </div>

                                <div class="mb-6">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Motif:</p>
                                    <p class="text-sm text-gray-900 bg-gray-50 rounded-xl p-4"><?php echo htmlspecialchars($request['Motif']); ?></p>
                                </div>

                                <?php if (!empty($request['focus_areas'])): ?>
                                <div class="mb-6">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Focus Areas:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach (explode(', ', $request['focus_areas']) as $area): ?>
                                            <span class="px-3 py-1 bg-gradient-to-r from-slate-custom to-slate-700 text-white text-xs rounded-full font-medium">
                                                <?php echo htmlspecialchars(trim($area)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="flex space-x-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['ID_REQUEST']; ?>">
                                        <button type="submit" name="approve_request" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-300 font-medium shadow-lg">
                                            <i class="fas fa-check mr-2"></i>
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['ID_REQUEST']; ?>">
                                        <button type="submit" name="reject_request" class="bg-gradient-to-r from-red-custom to-red-600 text-white px-6 py-3 rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 font-medium shadow-lg">
                                            <i class="fas fa-times mr-2"></i>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Processed Requests Section -->
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-black-custom mb-6">Recently Processed Requests</h2>
                    <?php if (empty($processed_requests)): ?>
                        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-8 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-history text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-black-custom mb-2">No processed requests</h3>
                            <p class="text-gray-600">No requests have been approved or rejected yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($processed_requests as $request): ?>
                                <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-md p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-slate-custom to-slate-700 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                                <?php echo strtoupper(substr($request['CLUB_NAME'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-black-custom"><?php echo htmlspecialchars($request['CLUB_NAME']); ?></h4>
                                                <p class="text-sm text-gray-500">by <?php echo htmlspecialchars($request['FIRST_NAME'] . ' ' . $request['LAST_NAME']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <?php if ($request['STATUS'] === 'approved'): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Approved</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Rejected</span>
                                            <?php endif; ?>
                                            <?php if ($request['admin_first_name']): ?>
                                                <span class="text-xs text-gray-500">
                                                    by <?php echo htmlspecialchars($request['admin_first_name'] . ' ' . $request['admin_last_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
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
                link.classList.remove('active', 'text-slate-custom', 'bg-gradient-to-r', 'from-slate-50', 'to-slate-100');
                link.classList.add('text-gray-600');
            });
            
            event.target.classList.add('active', 'text-slate-custom', 'bg-gradient-to-r', 'from-slate-50', 'to-slate-100');
            event.target.classList.remove('text-gray-600');
        }

        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });

            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Auto-dismiss success messages after 5 seconds
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(() => {
                    dismissMessage('success-message');
                }, 5000);
            }
        });

        // Function to dismiss messages
        function dismissMessage(messageId) {
            const message = document.getElementById(messageId);
            if (message) {
                message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    message.remove();
                }, 500);
            }
        }
    </script>
</body>
</html>