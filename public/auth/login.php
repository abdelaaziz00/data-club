<?php
session_start();
// Database config
$servername = 'localhost';
$username = 'root'; // Change if needed
$password = '';
$dbname = 'data_club';

// Initialize variables
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email && $pass) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $message = 'Database connection failed: ' . $conn->connect_error;
        } else {
            // Check admin table
            $stmt = $conn->prepare('SELECT ID_ADMIN, PASSWORD FROM admin WHERE EMAIL = ?');
            if (!$stmt) {
                $message = 'Prepare failed (admin): ' . $conn->error;
            } else {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($row['PASSWORD'] === $pass) {
                        $_SESSION['user_id'] = $row['ID_ADMIN'];
                        $_SESSION['privilege'] = 1;
                        $message = 'Admin login successful!';
                        // Optionally redirect here
                    } else {
                        $message = 'Invalid password.';
                    }
                } else {
                    // Not found in admin, check member
                    $stmt2 = $conn->prepare('SELECT ID_MEMBER, PASSWORD FROM member WHERE EMAIL = ?');
                    if (!$stmt2) {
                        $message = 'Prepare failed (member): ' . $conn->error;
                    } else {
                        $stmt2->bind_param('s', $email);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        if ($row2 = $result2->fetch_assoc()) {
                            if ($row2['PASSWORD'] === $pass) {
                                $_SESSION['user_id'] = $row2['ID_MEMBER'];
                                $_SESSION['privilege'] = 2;
                                $message = 'Member login successful!';
                                // Optionally redirect here
                            } else {
                                $message = 'Invalid password.';
                            }
                        } else {
                            $message = 'User not found.';
                        }
                        $stmt2->close();
                    }
                }
                $stmt->close();
            }
            $conn->close();
        }
    } else {
        $message = 'Please enter both email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
