<?php
$page_title = 'Manage Users';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);

$success = '';
$error = '';

$temp_password_display = '';

// Edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $department_id = (!empty($_POST['department_id']) && (int)$_POST['department_id'] > 0) ? (int)$_POST['department_id'] : null;
    
    if (empty($username) || empty($email) || empty($role)) {
        $error = 'All fields are required';
    } else {
        // Check if username/email already exists for another user
        $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $user_id);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, department_id = ? WHERE id = ?");
            $stmt->bind_param("sssii", $username, $email, $role, $department_id, $user_id);
            
            if ($stmt->execute()) {
                $success = 'User updated successfully';
            } else {
                $error = 'Error updating user: ' . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Add user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $department_id = (!empty($_POST['department_id']) && (int)$_POST['department_id'] > 0) ? (int)$_POST['department_id'] : null;
    
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $department_id);
            
            if ($stmt->execute()) {
                $success = 'User added successfully. Password: <strong>' . htmlspecialchars($password) . '</strong>';
            } else {
                $error = 'Error adding user: ' . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Reset user password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $user_id = (int)$_POST['user_id'];
    $temp_pass = bin2hex(random_bytes(6)); // Generate 12-character temporary password
    
    $hashed_temp = password_hash($temp_pass, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_temp, $user_id);
    
    if ($stmt->execute()) {
        $temp_password_display = "Temporary password for user: <strong>" . htmlspecialchars($temp_pass) . "</strong> - User should change this on next login";
        $success = 'Password reset. Temporary password generated: ' . htmlspecialchars($temp_pass);
    } else {
        $error = 'Error resetting password: ' . $conn->error;
    }
    $stmt->close();
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    if ($id == $_SESSION['user_id']) {
        $error = 'You cannot delete your own account';
    } else {
        // Check if user to be deleted is a super admin and current user is not super admin
        $check = $conn->prepare("SELECT is_super_admin FROM users WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();
        $user_to_delete = $result->fetch_assoc();
        $check->close();
        
        if ($user_to_delete && $user_to_delete['is_super_admin'] && !isSuperAdmin()) {
            $error = 'Only Super Admins can delete Super Admin accounts';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = 'User deleted successfully';
            } else {
                $error = 'Error deleting user: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}


// Get all users - hide super admins from regular admins
if (isSuperAdmin()) {
    $users = $conn->query("
        SELECT u.*, d.name as department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        ORDER BY u.created_at DESC
    ");
} else {
    $users = $conn->query("
        SELECT u.*, d.name as department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.is_super_admin = 0
        ORDER BY u.created_at DESC
    ");
}

// Get departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$dept_array = [];
while ($dept = $departments->fetch_assoc()) {
    $dept_array[] = $dept;
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <p>Add, view, or delete system users</p>
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
    
    <?php if (!empty($temp_password_display)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> <?php echo $temp_password_display; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Add User Form -->
        <div class="col-lg-5 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> Add New User
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return validateUserForm()">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password (min 6 chars)" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="kitchen">Kitchen Staff</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="department_id">Department</label>
                            <select class="form-select" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($dept_array as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="add_user" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Add User
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Users List -->
        <div class="col-lg-7 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Users
                </div>
                <div class="card-body">
                    <?php if ($users->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Reset</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['role']; ?>">
                                                    <?php echo ucfirst($row['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['department_name'] ? htmlspecialchars($row['department_name']) : '-'; ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="reset_password" class="btn btn-info btn-sm" onclick="return confirm('Generate a new temporary password for this user?')">
                                                        <i class="fas fa-key"></i> Reset
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $row['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                    <?php if ($row['is_super_admin'] && !isSuperAdmin()): ?>
                                                        <span class="text-muted small">Can't delete</span>
                                                    <?php else: ?>
                                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                                        class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
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
                            <p>No users yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modals -->
    <?php 
    // Fetch users again for modals
    if (isSuperAdmin()) {
        $users_for_modals = $conn->query("
            SELECT u.*, d.name as department_name
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            ORDER BY u.created_at DESC
        ");
    } else {
        $users_for_modals = $conn->query("
            SELECT u.*, d.name as department_name
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.is_super_admin = 0
            ORDER BY u.created_at DESC
        ");
    }
    
    while ($user = $users_for_modals->fetch_assoc()): 
    ?>
        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User: <?php echo htmlspecialchars($user['username']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            
                            <div class="form-group mb-3">
                                <label for="edit_username<?php echo $user['id']; ?>">Username</label>
                                <input type="text" class="form-control" id="edit_username<?php echo $user['id']; ?>" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_email<?php echo $user['id']; ?>">Email</label>
                                <input type="email" class="form-control" id="edit_email<?php echo $user['id']; ?>" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_role<?php echo $user['id']; ?>">Role</label>
                                <select class="form-select" id="edit_role<?php echo $user['id']; ?>" name="role" required>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="teacher" <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                    <option value="kitchen" <?php echo $user['role'] == 'kitchen' ? 'selected' : ''; ?>>Kitchen Staff</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_department<?php echo $user['id']; ?>">Department</label>
                                <select class="form-select" id="edit_department<?php echo $user['id']; ?>" name="department_id">
                                    <option value="">Select Department</option>
                                    <?php foreach ($dept_array as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo $user['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
            <!-- Back to Dashboard button removed (nav already provides access) -->
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
