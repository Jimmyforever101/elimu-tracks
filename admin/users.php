<?php
$page_title = 'Manage Users';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);

$success = '';
$error = '';

// Add user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    
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
                $success = 'User added successfully';
            } else {
                $error = 'Error adding user: ' . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $current_user = $_SESSION['user_id'];
    $stmt->bind_param("ii", $id, $current_user);
    
    if ($stmt->execute()) {
        $success = 'User deleted successfully';
    } else {
        $error = 'Error deleting user: ' . $conn->error;
    }
    $stmt->close();
}

// Get all users
$users = $conn->query("
    SELECT u.*, d.name as department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.created_at DESC
");

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
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Are you sure?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
                            <p>No users yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
