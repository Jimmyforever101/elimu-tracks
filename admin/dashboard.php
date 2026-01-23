<?php
$page_title = 'Admin Dashboard';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

// Check if user is admin
checkRole(['admin']);

// Handle delete attendance record
$delete_message = '';
$delete_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_attendance'])) {
    $attendance_id = (int)($_POST['attendance_id'] ?? 0);
    
    if ($attendance_id > 0) {
        $stmt = $conn->prepare("DELETE FROM lesson_attendance WHERE id = ?");
        $stmt->bind_param("i", $attendance_id);
        
        if ($stmt->execute()) {
            $delete_message = 'Attendance record deleted successfully!';
        } else {
            $delete_error = 'Error deleting record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Handle delete kitchen record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_kitchen'])) {
    $kitchen_id = (int)($_POST['kitchen_id'] ?? 0);
    
    if ($kitchen_id > 0) {
        $stmt = $conn->prepare("DELETE FROM kitchen_plates WHERE id = ?");
        $stmt->bind_param("i", $kitchen_id);
        
        if ($stmt->execute()) {
            $delete_message = 'Kitchen record deleted successfully!';
        } else {
            $delete_error = 'Error deleting kitchen record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get statistics
$total_departments = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'")->fetch_assoc()['count'];
$total_attendance = $conn->query("SELECT COUNT(*) as count FROM lesson_attendance WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$total_kitchen = $conn->query("SELECT COUNT(*) as count FROM kitchen_plates")->fetch_assoc()['count'];

// Get recent attendance
$recent_attendance = $conn->query("
    SELECT la.*, u.username, d.name as department_name
    FROM lesson_attendance la
    JOIN users u ON la.user_id = u.id
    JOIN departments d ON u.department_id = d.id
    ORDER BY la.created_at DESC
    LIMIT 5
");

// Get recent kitchen records with kitchen user info
$recent_kitchen = $conn->query("
    SELECT kp.*, u.username
    FROM kitchen_plates kp
    LEFT JOIN users u ON kp.user_id = u.id
    ORDER BY kp.record_date DESC, kp.created_at DESC
    LIMIT 5
");

// Get kitchen data for this week
$kitchen_week = $conn->query("
    SELECT DATE(record_date) as date, SUM(plates_count) as total
    FROM kitchen_plates
    WHERE record_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(record_date)
    ORDER BY date ASC
");

$dates = [];
$plates_data = [];
while ($row = $kitchen_week->fetch_assoc()) {
    $dates[] = $row['date'];
    $plates_data[] = $row['total'];
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
        <p>Welcome back, <?php echo getCurrentUser(); ?>! Here's an overview of your school records.</p>
    </div>
    
    <?php if ($delete_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($delete_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($delete_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($delete_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-building"></i> Total Departments</h5>
                <div class="stat-value"><?php echo $total_departments; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-users"></i> Total Users</h5>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-clipboard"></i> Today's Attendance Records</h5>
                <div class="stat-value"><?php echo $total_attendance; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <a href="/elimu-tracks/kitchen/dashboard.php" style="text-decoration: none; color: inherit;">
                <div class="stat-card" style="cursor: pointer; transition: transform 0.2s;">
                    <h5><i class="fas fa-utensils"></i> Kitchen records</h5>
                    <div class="stat-value"><?php echo $total_kitchen; ?></div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs"></i> Quick Actions
                </div>
                <div class="card-body">
                    <a href="/elimu-tracks/admin/departments.php" class="btn btn-primary btn-sm mb-2">
                        <i class="fas fa-plus"></i> Add Department
                    </a>
                    <a href="/elimu-tracks/admin/users.php" class="btn btn-success btn-sm mb-2">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                    <a href="/elimu-tracks/admin/reports.php" class="btn btn-info btn-sm mb-2">
                        <i class="fas fa-file-csv"></i> View Reports
                    </a>
                    <a href="/elimu-tracks/admin/settings.php" class="btn btn-warning btn-sm mb-2">
                        <i class="fas fa-sliders-h"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Kitchen Week Chart
        <div class="col-lg-6 col-md-12">
            <div class="chart-wrapper">
                <h5 class="card-title"><i class="fas fa-chart-bar"></i> Kitchen Plates - This Week</h5>
                <div class="chart-container">
                    <canvas id="kitchenChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    -->
    <!-- Recent Attendance -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Attendance Records
        </div>
        <div class="card-body">
            <?php if ($recent_attendance->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Department</th>
                                <th>Lesson Title</th>
                                <th>Time Range</th>
                                <th>Boys</th>
                                <th>Girls</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_attendance->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lesson_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lesson_time']) . ' - ' . htmlspecialchars($row['lesson_end_time']); ?></td>
                                    <td><?php echo $row['boys_attendance']; ?></td>
                                    <td><?php echo $row['girls_attendance']; ?></td>
                                    <td><strong><?php echo $row['boys_attendance'] + $row['girls_attendance']; ?></strong></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                            <input type="hidden" name="attendance_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_attendance" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No attendance records yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Kitchen Records -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-utensils"></i> Recent Kitchen Records
        </div>
        <div class="card-body">
            <?php if ($recent_kitchen->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Username</th>
                                <th>Plates Saved</th>
                                <th>Tea Cups Served</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_kitchen->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['record_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></td>
                                    <td><?php echo $row['plates_count']; ?></td>
                                    <td><?php echo $row['tea_cups_count']; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this kitchen record?');">
                                            <input type="hidden" name="kitchen_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_kitchen" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No kitchen records yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Kitchen chart
    <?php if (!empty($dates)): ?>
    createLineChart('kitchenChart', <?php echo json_encode($dates); ?>, <?php echo json_encode($plates_data); ?>, 'Plates Saved');
    <?php endif; ?>
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
