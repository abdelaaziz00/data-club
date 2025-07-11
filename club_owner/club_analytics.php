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

// Get comprehensive analytics
// Member statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_members FROM requestjoin WHERE ID_CLUB = ? AND ACCEPTED = 1");
$stmt->execute([$club['ID_CLUB']]);
$total_members = $stmt->fetch(PDO::FETCH_ASSOC)['total_members'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_requests FROM requestjoin WHERE ID_CLUB = ? AND (ACCEPTED = 0 OR ACCEPTED IS NULL)");
$stmt->execute([$club['ID_CLUB']]);
$pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['pending_requests'];

// Event statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_events FROM organizes WHERE ID_CLUB = ?");
$stmt->execute([$club['ID_CLUB']]);
$total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];

$stmt = $pdo->prepare("SELECT COUNT(*) as upcoming_events FROM evenement e JOIN organizes o ON e.ID_EVENT = o.ID_EVENT WHERE o.ID_CLUB = ? AND e.DATE >= CURDATE()");
$stmt->execute([$club['ID_CLUB']]);
$upcoming_events = $stmt->fetch(PDO::FETCH_ASSOC)['upcoming_events'];

$stmt = $pdo->prepare("SELECT COUNT(*) as past_events FROM evenement e JOIN organizes o ON e.ID_EVENT = o.ID_EVENT WHERE o.ID_CLUB = ? AND e.DATE < CURDATE()");
$stmt->execute([$club['ID_CLUB']]);
$past_events = $stmt->fetch(PDO::FETCH_ASSOC)['past_events'];

// Attendance statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_registrations FROM registre r JOIN organizes o ON r.ID_EVENT = o.ID_EVENT WHERE o.ID_CLUB = ?");
$stmt->execute([$club['ID_CLUB']]);
$total_registrations = $stmt->fetch(PDO::FETCH_ASSOC)['total_registrations'];

// Average attendance per event
$avg_attendance = $total_events > 0 ? round($total_registrations / $total_events, 1) : 0;

// Event types breakdown
$stmt = $pdo->prepare("
    SELECT e.EVENT_TYPE, COUNT(*) as count 
    FROM evenement e 
    JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
    WHERE o.ID_CLUB = ? 
    GROUP BY e.EVENT_TYPE
");
$stmt->execute([$club['ID_CLUB']]);
$event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly event distribution
$stmt = $pdo->prepare("
    SELECT MONTH(e.DATE) as month, YEAR(e.DATE) as year, COUNT(*) as count 
    FROM evenement e 
    JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
    WHERE o.ID_CLUB = ? AND e.DATE >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY YEAR(e.DATE), MONTH(e.DATE)
    ORDER BY year, month
");
$stmt->execute([$club['ID_CLUB']]);
$monthly_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top events by registration
$stmt = $pdo->prepare("
    SELECT e.TITLE, e.DATE, COUNT(r.ID_MEMBER) as registrations, e.CAPACITY
    FROM evenement e 
    JOIN organizes o ON e.ID_EVENT = o.ID_EVENT 
    LEFT JOIN registre r ON e.ID_EVENT = r.ID_EVENT
    WHERE o.ID_CLUB = ? 
    GROUP BY e.ID_EVENT
    ORDER BY registrations DESC
    LIMIT 5
");
$stmt->execute([$club['ID_CLUB']]);
$top_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent member growth
$stmt = $pdo->prepare("
    SELECT DATE(rj.ACCEPTED) as join_date, COUNT(*) as new_members
    FROM requestjoin rj 
    WHERE rj.ID_CLUB = ? AND rj.ACCEPTED = 1 
    GROUP BY DATE(rj.ACCEPTED)
    ORDER BY join_date DESC
    LIMIT 30
");
$stmt->execute([$club['ID_CLUB']]);
$member_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Analytics - DataClub</title>
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
<?php include '../includes/header.php'; ?> 

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="manage_club.php" class="text-slate-custom hover:text-slate-700 transition-colors mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Manage Club
                </a>
            </div>
            <h1 class="text-3xl font-bold text-black-custom">Club Analytics</h1>
            <p class="text-gray-600 mt-2">Comprehensive analytics and insights for <?php echo htmlspecialchars($club['NAME']); ?></p>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Members</p>
                        <p class="text-2xl font-bold text-black-custom"><?php echo $total_members; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-slate-custom text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500"><?php echo $pending_requests; ?> pending requests</span>
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
                <div class="mt-4">
                    <span class="text-sm text-gray-500"><?php echo $upcoming_events; ?> upcoming</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                        <p class="text-2xl font-bold text-black-custom"><?php echo $total_registrations; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500">Avg: <?php echo $avg_attendance; ?> per event</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Success Rate</p>
                        <p class="text-2xl font-bold text-black-custom">
                            <?php echo $total_events > 0 ? round(($past_events / $total_events) * 100) : 0; ?>%
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500"><?php echo $past_events; ?> completed events</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Event Types Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-black-custom mb-4">Event Types Distribution</h3>
                <?php if (!empty($event_types)): ?>
                    <canvas id="eventTypesChart" width="400" height="200"></canvas>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-chart-pie text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No events data available</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Monthly Events Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-black-custom mb-4">Monthly Event Activity</h3>
                <?php if (!empty($monthly_events)): ?>
                    <canvas id="monthlyEventsChart" width="400" height="200"></canvas>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No monthly data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Events -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h3 class="text-lg font-bold text-black-custom mb-4">Top Events by Registration</h3>
            <?php if (!empty($top_events)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Event Title</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Registrations</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Capacity</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Fill Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_events as $event): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($event['TITLE']); ?></td>
                                    <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($event['DATE'])); ?></td>
                                    <td class="py-3 px-4"><?php echo $event['registrations']; ?></td>
                                    <td class="py-3 px-4"><?php echo $event['CAPACITY']; ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-red-custom h-2 rounded-full" style="width: <?php echo ($event['registrations'] / $event['CAPACITY']) * 100; ?>%"></div>
                                            </div>
                                            <span class="text-sm text-gray-600"><?php echo round(($event['registrations'] / $event['CAPACITY']) * 100); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-trophy text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No events data available</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Export Options -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-black-custom mb-4">Export Analytics</h3>
            <div class="flex flex-wrap gap-4">
                <button class="bg-slate-custom text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export PDF Report
                </button>
                <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </button>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-share mr-2"></i>
                    Share Analytics
                </button>
            </div>
        </div>
    </main>

    <script>
        // Event Types Chart
        <?php if (!empty($event_types)): ?>
        const eventTypesCtx = document.getElementById('eventTypesChart').getContext('2d');
        new Chart(eventTypesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($event_types, 'EVENT_TYPE')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($event_types, 'count')); ?>,
                    backgroundColor: [
                        '#F05454',
                        '#30475E',
                        '#F5F5F5',
                        '#121212',
                        '#FF6B6B',
                        '#4ECDC4'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>

        // Monthly Events Chart
        <?php if (!empty($monthly_events)): ?>
        const monthlyEventsCtx = document.getElementById('monthlyEventsChart').getContext('2d');
        new Chart(monthlyEventsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($item) {
                    return date('M Y', mktime(0, 0, 0, $item['month'], 1, $item['year']));
                }, $monthly_events)); ?>,
                datasets: [{
                    label: 'Events',
                    data: <?php echo json_encode(array_column($monthly_events, 'count')); ?>,
                    borderColor: '#F05454',
                    backgroundColor: 'rgba(240, 84, 84, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>