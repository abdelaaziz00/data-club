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

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo'])) {
    $upload_dir = '../static/images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $error_message = "Failed to create upload directory.";
            return;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        $error_message = "Upload directory is not writable.";
        return;
    }
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        
        // Validation
        $errors = array();
        
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        if ($file_size > 2097152) { // 2MB limit
            $errors[] = "File size must be less than 2MB.";
        }
        
        // Additional security checks
        if ($file_size === 0) {
            $errors[] = "The uploaded file is empty.";
        }
        
        // Check if file is actually an image
        $image_info = getimagesize($file_tmp);
        if ($image_info === false) {
            $errors[] = "The uploaded file is not a valid image.";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $new_file_name = 'club_logo_' . $club['ID_CLUB'] . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old logo if exists
                if ($club['LOGO'] && file_exists($upload_dir . $club['LOGO'])) {
                    unlink($upload_dir . $club['LOGO']);
                }
                
                // Update database
                $stmt = $pdo->prepare("UPDATE club SET LOGO = ? WHERE ID_CLUB = ?");
                $stmt->execute([$new_file_name, $club['ID_CLUB']]);
                
                $success_message = "Club logo uploaded successfully!";
                
                // Refresh club data
                $stmt = $pdo->prepare("SELECT * FROM club WHERE ID_CLUB = ?");
                $stmt->execute([$club['ID_CLUB']]);
                $club = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = "Failed to upload file.";
            }
        } else {
            $error_message = implode(" ", $errors);
        }
    } else {
        $error_message = "Please select a file to upload.";
    }
}

// Handle logo removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_logo'])) {
    $upload_dir = '../static/images/';
    
    if ($club['LOGO'] && file_exists($upload_dir . $club['LOGO'])) {
        unlink($upload_dir . $club['LOGO']);
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE club SET LOGO = NULL WHERE ID_CLUB = ?");
    $stmt->execute([$club['ID_CLUB']]);
    
    $success_message = "Club logo removed successfully!";
    
    // Refresh club data
    $stmt = $pdo->prepare("SELECT * FROM club WHERE ID_CLUB = ?");
    $stmt->execute([$club['ID_CLUB']]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle club update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_club'])) {
    $name = $_POST['name'];
    $university = $_POST['university'];
    $city = $_POST['city'];
    $description = $_POST['description'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $instagram = $_POST['instagram'];
    $linkedin = $_POST['linkedin'];
    
    $stmt = $pdo->prepare("UPDATE club SET NAME = ?, UNIVERSITY = ?, CITY = ?, DESCRIPTION = ?, EMAIL = ?, CLUB_PHONE = ?, INSTAGRAM_LINK = ?, LINKEDIN_LINK = ? WHERE ID_CLUB = ?");
    $stmt->execute([$name, $university, $city, $description, $email, $phone, $instagram, $linkedin, $club['ID_CLUB']]);
    
    $success_message = "Club information updated successfully!";
    
    // Refresh club data
    $stmt = $pdo->prepare("SELECT * FROM club WHERE ID_CLUB = ?");
    $stmt->execute([$club['ID_CLUB']]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get club statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as member_count FROM requestjoin WHERE ID_CLUB = ? AND ACCEPTED = 1");
$stmt->execute([$club['ID_CLUB']]);
$member_count = $stmt->fetch(PDO::FETCH_ASSOC)['member_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as event_count FROM organizes WHERE ID_CLUB = ?");
$stmt->execute([$club['ID_CLUB']]);
$event_count = $stmt->fetch(PDO::FETCH_ASSOC)['event_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_attendees FROM registre r JOIN organizes o ON r.ID_EVENT = o.ID_EVENT WHERE o.ID_CLUB = ?");
$stmt->execute([$club['ID_CLUB']]);
$total_attendees = $stmt->fetch(PDO::FETCH_ASSOC)['total_attendees'];

$stmt = $pdo->prepare("SELECT COUNT(*) as active_projects FROM evenement e JOIN organizes o ON e.ID_EVENT = o.ID_EVENT WHERE o.ID_CLUB = ? AND e.DATE >= CURDATE()");
$stmt->execute([$club['ID_CLUB']]);
$active_projects = $stmt->fetch(PDO::FETCH_ASSOC)['active_projects'];

// Get focus areas
$stmt = $pdo->prepare("SELECT t.TOPIC_NAME FROM focuses f JOIN topics t ON f.TOPIC_ID = t.TOPIC_ID WHERE f.ID_CLUB = ?");
$stmt->execute([$club['ID_CLUB']]);
$focus_areas = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all available topics
$stmt = $pdo->prepare("SELECT * FROM topics");
$stmt->execute();
$all_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Club - DataClub</title>
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
                <a href="../user/profile.php" class="text-slate-custom hover:text-slate-700 transition-colors mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Profile
                </a>
            </div>
            <h1 class="text-3xl font-bold text-black-custom">Manage Club</h1>
            <p class="text-gray-600 mt-2">Update your club information and settings</p>
        </div>

        <!-- Success Message -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <form method="POST" class="space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h2 class="text-xl font-bold text-black-custom mb-4">Basic Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Club Name *</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($club['NAME']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">School *</label>
                                    <input type="text" name="university" value="<?php echo htmlspecialchars($club['UNIVERSITY']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($club['CITY']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h2 class="text-xl font-bold text-black-custom mb-4">Description</h2>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Club Description *</label>
                                <textarea name="description" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required><?php echo htmlspecialchars($club['DESCRIPTION']); ?></textarea>
                            </div>
                        </div>

                        <!-- Focus Areas -->
                        <div>
                            <h2 class="text-xl font-bold text-black-custom mb-4">Focus Areas</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <?php foreach ($all_topics as $topic): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="focus_areas[]" value="<?php echo $topic['TOPIC_ID']; ?>" 
                                               <?php echo in_array($topic['TOPIC_NAME'], $focus_areas) ? 'checked' : ''; ?>
                                               class="mr-2 text-slate-custom focus:ring-slate-custom">
                                        <span class="text-sm"><?php echo htmlspecialchars($topic['TOPIC_NAME']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <h2 class="text-xl font-bold text-black-custom mb-4">Contact Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($club['EMAIL']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($club['CLUB_PHONE']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div>
                            <h2 class="text-xl font-bold text-black-custom mb-4">Social Media</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                    <input type="url" name="instagram" value="<?php echo htmlspecialchars($club['INSTAGRAM_LINK']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                                    <input type="url" name="linkedin" value="<?php echo htmlspecialchars($club['LINKEDIN_LINK']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-custom">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-4 pt-6">
                            <button type="submit" name="update_club" class="bg-slate-custom text-white px-6 py-3 rounded-lg hover:bg-slate-700 transition-colors font-medium">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                            <a href="../user/profile.php" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Club Logo -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-black-custom mb-4">Club Logo</h3>
                    <div class="text-center">
                        <?php if ($club['LOGO']): ?>
                            <div class="w-24 h-24 mx-auto mb-4">
                                <img src="../static/images/<?php echo htmlspecialchars($club['LOGO']); ?>" 
                                     alt="Club Logo" 
                                     class="w-full h-full object-cover rounded-xl">
                            </div>
                        <?php else: ?>
                            <div class="w-24 h-24 bg-gradient-to-br from-slate-custom to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4">
                                <?php echo strtoupper(substr($club['NAME'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Logo Preview Container -->
                        <div id="logo-preview"></div>
                        
                        <form method="POST" enctype="multipart/form-data" class="space-y-3">
                            <div class="relative">
                                <input type="file" name="logo" id="logo" accept="image/*" class="hidden" onchange="updateFileName(this)">
                                <label for="logo" class="bg-red-custom text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm cursor-pointer inline-block">
                                    <i class="fas fa-upload mr-2"></i>
                                    Choose File
                                </label>
                                <span id="file-name" class="text-xs text-gray-500 ml-2"></span>
                            </div>
                            <button type="submit" name="upload_logo" class="bg-slate-custom text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors text-sm w-full">
                                <i class="fas fa-save mr-2"></i>
                                Upload Logo
                            </button>
                        </form>
                        
                        <?php if ($club['LOGO']): ?>
                            <form method="POST" class="mt-3">
                                <button type="submit" name="remove_logo" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm w-full">
                                    <i class="fas fa-trash mr-2"></i>
                                    Remove Logo
                                </button>
                            </form>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 mt-2">Recommended: 200x200px, PNG or JPG (max 2MB)</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-black-custom mb-4">Club Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Members</span>
                            <span class="font-bold text-black-custom"><?php echo $member_count; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Events Organized</span>
                            <span class="font-bold text-black-custom"><?php echo $event_count; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Attendees</span>
                            <span class="font-bold text-black-custom"><?php echo $total_attendees; ?>+</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Upcoming Events</span>
                            <span class="font-bold text-black-custom"><?php echo $active_projects; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-black-custom mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="create_event.php" class="w-full bg-red-custom text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-center block">
                            <i class="fas fa-plus mr-2"></i>
                            Create Event
                        </a>
                        <a href="manage_members.php" class="w-full border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors text-center block">
                            <i class="fas fa-users mr-2"></i>
                            Manage Members
                        </a>
                        <a href="club_analytics.php" class="w-full border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors text-center block">
                            <i class="fas fa-chart-bar mr-2"></i>
                            View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Show selected filename and preview
        function updateFileName(input) {
            const fileName = input.files[0]?.name;
            const fileNameSpan = document.getElementById('file-name');
            const previewContainer = document.getElementById('logo-preview');
            
            if (fileName) {
                fileNameSpan.textContent = fileName;
                
                // Create preview
                const file = input.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                            <div class="w-24 h-24 mx-auto mb-4">
                                <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover rounded-xl">
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                fileNameSpan.textContent = '';
                previewContainer.innerHTML = '';
            }
        }
    </script>
</body>
</html>