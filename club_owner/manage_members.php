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

// Handle accept request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_request'])) {
    $member_id = $_POST['member_id'];
    $stmt = $pdo->prepare("UPDATE requestjoin SET ACCEPTED = 1 WHERE ID_MEMBER = ? AND ID_CLUB = ?");
    $stmt->execute([$member_id, $club['ID_CLUB']]);
    $success_message = "Member request accepted successfully!";
}

// Handle reject request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $member_id = $_POST['member_id'];
    $stmt = $pdo->prepare("DELETE FROM requestjoin WHERE ID_MEMBER = ? AND ID_CLUB = ?");
    $stmt->execute([$member_id, $club['ID_CLUB']]);
    $success_message = "Member request rejected successfully!";
}

// Handle remove member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    $member_id = $_POST['member_id'];
    $stmt = $pdo->prepare("DELETE FROM requestjoin WHERE ID_MEMBER = ? AND ID_CLUB = ?");
    $stmt->execute([$member_id, $club['ID_CLUB']]);
    $success_message = "Member removed successfully!";
}

// Get current members
$stmt = $pdo->prepare("
    SELECT m.*, rj.ACCEPTED 
    FROM member m 
    JOIN requestjoin rj ON m.ID_MEMBER = rj.ID_MEMBER 
    WHERE rj.ID_CLUB = ? AND rj.ACCEPTED = 1
    ORDER BY m.FIRST_NAME, m.LAST_NAME
");
$stmt->execute([$club['ID_CLUB']]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending requests
$stmt = $pdo->prepare("
    SELECT m.*, rj.ACCEPTED 
    FROM member m 
    JOIN requestjoin rj ON m.ID_MEMBER = rj.ID_MEMBER 
    WHERE rj.ID_CLUB = ? AND (rj.ACCEPTED = 0 OR rj.ACCEPTED IS NULL)
    ORDER BY m.FIRST_NAME, m.LAST_NAME
");
$stmt->execute([$club['ID_CLUB']]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - DataClub</title>
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
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="manage_club.php" class="text-slate-custom hover:text-slate-700 transition-colors mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Manage Club
                </a>
            </div>
            <h1 class="text-3xl font-bold text-black-custom">Manage Members</h1>
            <p class="text-gray-600 mt-2">Manage your club members and join requests for <?php echo htmlspecialchars($club['NAME']); ?></p>
        </div>

        <!-- Success Message -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Current Members -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black-custom">Current Members</h2>
                    <span class="px-3 py-1 bg-slate-100 text-slate-custom rounded-full text-sm font-medium">
                        <?php echo count($members); ?> members
                    </span>
                </div>

                <?php if (empty($members)): ?>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-black-custom mb-2">No members yet</h3>
                        <p class="text-gray-600">Your club doesn't have any members yet. Accept join requests to grow your community.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($members as $member): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($member['FIRST_NAME'], 0, 1) . substr($member['LAST_NAME'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-black-custom">
                                            <?php echo htmlspecialchars($member['FIRST_NAME'] . ' ' . $member['LAST_NAME']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($member['EMAIL']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Active Member</span>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this member?')">
                                        <input type="hidden" name="member_id" value="<?php echo $member['ID_MEMBER']; ?>">
                                        <button type="submit" name="remove_member" class="text-red-custom hover:text-red-600 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pending Requests -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black-custom">Join Requests</h2>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        <?php echo count($pending_requests); ?> pending
                    </span>
                </div>

                <?php if (empty($pending_requests)): ?>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-plus text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-black-custom mb-2">No pending requests</h3>
                        <p class="text-gray-600">All join requests have been processed. New requests will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($pending_requests as $request): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($request['FIRST_NAME'], 0, 1) . substr($request['LAST_NAME'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-black-custom">
                                            <?php echo htmlspecialchars($request['FIRST_NAME'] . ' ' . $request['LAST_NAME']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($request['EMAIL']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="member_id" value="<?php echo $request['ID_MEMBER']; ?>">
                                        <button type="submit" name="accept_request" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition-colors">
                                            <i class="fas fa-check mr-1"></i>
                                            Accept
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="member_id" value="<?php echo $request['ID_MEMBER']; ?>">
                                        <button type="submit" name="reject_request" class="bg-red-custom text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                            <i class="fas fa-times mr-1"></i>
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

        <!-- Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-slate-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom"><?php echo count($members); ?></h3>
                <p class="text-gray-600">Active Members</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom"><?php echo count($pending_requests); ?></h3>
                <p class="text-gray-600">Pending Requests</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom"><?php echo count($members) + count($pending_requests); ?></h3>
                <p class="text-gray-600">Total Requests</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-red-custom text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-black-custom">
                    <?php echo count($members) > 0 ? round((count($members) / (count($members) + count($pending_requests))) * 100) : 0; ?>%
                </h3>
                <p class="text-gray-600">Acceptance Rate</p>
            </div>
        </div>
    </main>
</body>
</html>