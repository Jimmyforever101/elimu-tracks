<?php
$page_title = 'User Profile';
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/header.php');

redirectToLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current user info
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (password_verify($current_password, $result['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $message = 'Password updated successfully!';
                $_POST = []; // Clear form
            } else {
                $error = 'Error updating password: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-user"></i> User Profile</h1>
        <p>Manage your account and password</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- User Information -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-circle"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" disabled>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Password Update -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock"></i> Change Password
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" placeholder="Enter your current password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" placeholder="Enter new password (min 6 characters)" required>
                            <small class="form-text text-muted">Must be at least 6 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" placeholder="Confirm new password" required>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn btn-primary w-100">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4 mb-4">
        <button onclick="history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
