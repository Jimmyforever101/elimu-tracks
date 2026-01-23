<?php
$page_title = 'System Settings';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);

$success = '';
$error = '';

// Promote admin to super admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['promote_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    
    // Only super admin can promote other admins
    if (!isSuperAdmin()) {
        $error = 'Only Super Admins can promote other admins to Super Admin status';
    } else if ($admin_id == $_SESSION['user_id']) {
        $error = 'You cannot change your own admin status';
    } else {
        // Check if user is admin
        $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $check->bind_param("i", $admin_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['role'] != 'admin') {
                $error = 'User must be an admin to promote to super admin';
            } else {
                $stmt = $conn->prepare("UPDATE users SET is_super_admin = 1 WHERE id = ?");
                $stmt->bind_param("i", $admin_id);
                
                if ($stmt->execute()) {
                    $success = 'Admin promoted to Super Admin successfully';
                } else {
                    $error = 'Error promoting admin: ' . $conn->error;
                }
                $stmt->close();
            }
        } else {
            $error = 'Admin not found';
        }
        $check->close();
    }
}

// Demote super admin to regular admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['demote_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    
    // Only super admin can demote other admins
    if (!isSuperAdmin()) {
        $error = 'Only Super Admins can demote other admins';
    } else if ($admin_id == $_SESSION['user_id']) {
        $error = 'You cannot change your own admin status';
    } else {
        // Check if user is super admin
        $check = $conn->prepare("SELECT is_super_admin FROM users WHERE id = ?");
        $check->bind_param("i", $admin_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE users SET is_super_admin = 0 WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                $success = 'Admin demoted to regular admin successfully';
            } else {
                $error = 'Error demoting admin: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $error = 'Admin not found';
        }
        $check->close();
    }
}

// Get all admins
$admins = $conn->query("
    SELECT id, username, email, is_super_admin, created_at
    FROM users
    WHERE role = 'admin'
    ORDER BY is_super_admin DESC, created_at DESC
");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-cog"></i> System Settings</h1>
        <p>Configure Elimu Tracks system settings and manage admin accounts</p>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sliders-h"></i> General Settings
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>School Name</label>
                            <input type="text" class="form-control" placeholder="Elimu Yetu" value="Elimu Yetu" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>System Version</label>
                            <input type="text" class="form-control" placeholder="1.0.0" value="1.0.0" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Database</label>
                            <input type="text" class="form-control" placeholder="elimu_tracks" value="<?php echo DB_NAME; ?>" disabled>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" class="form-control" placeholder="30" value="30" min="5" max="480">
                        </div>
                        
                        <div class="form-group">
                            <label>Records Per Page</label>
                            <input type="number" class="form-control" placeholder="10" value="10" min="5" max="100">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (isSuperAdmin()): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-user-shield"></i> Manage Admins
                </div>
                <div class="card-body">
                    <?php if ($admins->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $admins->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                                <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info ms-2">You</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php if ($row['is_super_admin']): ?>
                                                    <span class="badge bg-warning"><i class="fas fa-crown"></i> Super Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Regular Admin</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="admin_id" value="<?php echo $row['id']; ?>">
                                                        <?php if ($row['is_super_admin']): ?>
                                                            <button type="submit" name="demote_admin" class="btn btn-warning btn-sm" onclick="return confirm('Demote this admin to regular admin?')">
                                                                <i class="fas fa-arrow-down"></i> Demote
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="submit" name="promote_admin" class="btn btn-success btn-sm" onclick="return confirm('Promote this admin to Super Admin?')">
                                                                <i class="fas fa-arrow-up"></i> Promote
                                                            </button>
                                                        <?php endif; ?>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">Current User</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>No admins found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> System Information
                </div>
                <div class="card-body">
                    <div class="stat-card">
                        <h5>PHP Version</h5>
                        <p><?php echo phpversion(); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Server OS</h5>
                        <p><?php echo php_uname(); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>MySQL Version</h5>
                        <p><?php echo $conn->server_info; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Current Date & Time</h5>
                        <p><?php echo date('M d, Y H:i:s'); ?></p>
                    </div>
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
