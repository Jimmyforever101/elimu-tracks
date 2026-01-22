<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: /elimu-tracks/admin/dashboard.php");
    } elseif ($role == 'teacher') {
        header("Location: /elimu-tracks/teacher/dashboard.php");
    } elseif ($role == 'kitchen') {
        header("Location: /elimu-tracks/kitchen/dashboard.php");
    }
    exit();
}

require_once(__DIR__ . '/config/db.php');

$error = '';
$username = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        // Query user
        $stmt = $conn->prepare("SELECT id, username, password, role, department_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department_id'] = $user['department_id'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: /elimu-tracks/admin/dashboard.php");
                } elseif ($user['role'] == 'teacher') {
                    header("Location: /elimu-tracks/teacher/dashboard.php");
                } elseif ($user['role'] == 'kitchen') {
                    header("Location: /elimu-tracks/kitchen/dashboard.php");
                }
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Username not found';
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Elimu Tracks</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/elimu-tracks/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="text-center mb-4">
                <i class="fas fa-graduation-cap" style="font-size: 48px; color: #667eea;"></i>
                <h2 class="mt-3">Elimu Tracks</h2>
                <p class="text-muted">School Records Management System</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validateLoginForm()">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <hr class="my-3">
            
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/elimu-tracks/assets/js/scripts.js"></script>
</body>
</html>
