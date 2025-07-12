<?php
// Get user information if logged in
require_once '../user/db.php';
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM member WHERE ID_MEMBER = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user has a club
    $stmt = $pdo->prepare("SELECT * FROM club WHERE ID_MEMBER = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_club = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<header class="bg-white py-4 px-6 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
            <img src="../static/images/mds logo.png" alt="MDS Logo" class="w-40 h-30 object-contain">
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
            <a href="../user/home.php" class="text-slate-custom hover:text-red-custom transition-colors">Home Page</a>
            <a href="../user/events.php" class="text-slate-custom hover:text-red-custom transition-colors">Events</a>
            <a href="../user/clubs.php" class="text-slate-custom hover:text-red-custom transition-colors">Clubs</a>
            <a href="../user/contactus.php" class="text-slate-custom hover:text-red-custom transition-colors">Contact us</a>

            <?php if ($user): ?>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="profile-menu-button" class="flex items-center space-x-2 text-gray-600 hover:text-slate-custom transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-slate-custom to-slate-700 rounded-full flex items-center justify-center text-white font-bold text-sm overflow-hidden">
                                <?php if ($user['PROFILE_IMG']): ?>
                                    <img src="../static/images/<?php echo htmlspecialchars($user['PROFILE_IMG']); ?>" 
                                         alt="Profile Picture" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['FIRST_NAME'], 0, 1) . substr($user['LAST_NAME'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="dropdown-arrow"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 hidden">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['EMAIL']); ?></p>
                            </div>
                            
                            <a href="../user/profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user mr-3 text-gray-400"></i>
                                Profile
                            </a>
                            
                            <?php if ($user_club): ?>
                                <a href="../club_owner/manage_club.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-cog mr-3 text-gray-400"></i>
                                    Manage Club
                                </a>
                                <a href="../club_owner/create_event.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-calendar-plus mr-3 text-gray-400"></i>
                                    Create Event
                                </a>
                                <a href="../club_owner/manage_members.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-users mr-3 text-gray-400"></i>
                                    Manage Members
                                </a>
                                <a href="../club_owner/club_analytics.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chart-bar mr-3 text-gray-400"></i>
                                    Analytics
                                </a>
                            <?php else: ?>
                                <button onclick="openClubRequestModal()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-plus-circle mr-3 text-gray-400"></i>
                                    Request Club Creation
                                </button>
                            <?php endif; ?>
                            
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <a href="../auth/logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-4">
                    <a href="../auth/login.php" class="text-gray-600 hover:text-slate-custom px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 hover:border-slate-custom transition-colors">
                        Login
                    </a>
                    <a href="../auth/register.php" class="bg-red-custom text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-colors">
                        Signup
                    </a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileButton = document.getElementById('profile-menu-button');
    const dropdown = document.getElementById('profile-dropdown');
    const arrow = document.getElementById('dropdown-arrow');

    if (profileButton && dropdown) {
        profileButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileButton.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        });
    }
});

function openClubRequestModal() {
    // This function would open the club creation modal
    // You can implement this based on your existing modal code
    alert('Club creation request modal would open here');
}
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}
</style>