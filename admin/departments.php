<?php
$page_title = 'Manage Departments';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);

$success = '';
$error = '';

// Add department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_department'])) {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $error = 'Department name is required';
    } else {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            $success = 'Department added successfully';
        } else {
            $error = 'Error adding department: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Delete department
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Department deleted successfully';
    } else {
        $error = 'Error deleting department: ' . $conn->error;
    }
    $stmt->close();
}

// Get all departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-building"></i> Manage Departments</h1>
        <p>Add, view, or delete school departments</p>
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
        <!-- Add Department Form -->
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus"></i> Add New Department
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return validateDepartmentForm()">
                        <div class="form-group">
                            <label for="name">Department Name</label>
                            <input type="text" class="form-control" id="department_name" name="name" 
                                   placeholder="e.g., English, Mathematics" required>
                        </div>
                        <button type="submit" name="add_department" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Add Department
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Departments List -->
        <div class="col-lg-8 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Departments
                </div>
                <div class="card-body">
                    <?php if ($departments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $departments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="?delete=<?php echo $row['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <p>No departments yet. Add one to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
