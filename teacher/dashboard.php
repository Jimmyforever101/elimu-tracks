<?php
$page_title = 'Teacher Dashboard';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['teacher']);

$user_id = $_SESSION['user_id'];
$dept_id = getCurrentDepartment();

// Get teacher statistics
$total_lessons = $conn->query("SELECT COUNT(*) as count FROM lesson_attendance WHERE user_id = $user_id")->fetch_assoc()['count'];
$total_boys = $conn->query("SELECT SUM(boys_attendance) as total FROM lesson_attendance WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;
$total_girls = $conn->query("SELECT SUM(girls_attendance) as total FROM lesson_attendance WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;

// Get recent attendance
$recent = $conn->query("
    SELECT * FROM lesson_attendance 
    WHERE user_id = $user_id
    ORDER BY created_at DESC
    LIMIT 5
");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-chalkboard-user"></i> Teacher Dashboard</h1>
        <p>Welcome, <?php echo getCurrentUser(); ?>! Track your lesson attendance here.</p>
    </div>
    
    <!-- Statistics 
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-book"></i> Total Lessons</h5>
                <div class="stat-value"><?php echo $total_lessons; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-boy"></i> Total Boys</h5>
                <div class="stat-value"><?php echo $total_boys; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-girl"></i> Total Girls</h5>
                <div class="stat-value"><?php echo $total_girls; ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-users"></i> Total Attendance</h5>
                <div class="stat-value"><?php echo $total_boys + $total_girls; ?></div>
            </div>
        </div>
    </div>
    -->
    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-cogs"></i> Quick Actions
        </div>
        <div class="card-body">
            <a href="/elimu-tracks/teacher/attendance.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-plus"></i> Log Attendance
            </a>
            <a href="/elimu-tracks/teacher/history.php" class="btn btn-info btn-lg">
                <i class="fas fa-history"></i> View History
            </a>
        </div>
    </div>
    
    <!-- Recent Records -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-clock"></i> Recent Attendance Records
        </div>
        <div class="card-body">
            <?php if ($recent->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lesson</th>
                                <th>Time Range</th>
                                <th>Boys</th>
                                <th>Girls</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['lesson_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lesson_time']) . ' - ' . htmlspecialchars($row['lesson_end_time']); ?></td>
                                    <td><?php echo $row['boys_attendance']; ?></td>
                                    <td><?php echo $row['girls_attendance']; ?></td>
                                    <td><strong><?php echo $row['boys_attendance'] + $row['girls_attendance']; ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No attendance records yet. Start logging attendance!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
