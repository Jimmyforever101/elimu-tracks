<?php
$page_title = 'Attendance History';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['teacher']);

$user_id = $_SESSION['user_id'];

// Get all attendance records for this teacher
$records = $conn->query("
    SELECT * FROM lesson_attendance 
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");

// Get statistics
$total_lessons = $records->num_rows;
$result = $conn->query("
    SELECT SUM(boys_attendance) as boys, SUM(girls_attendance) as girls
    FROM lesson_attendance
    WHERE user_id = $user_id
");
$stats = $result->fetch_assoc();
$total_boys = $stats['boys'] ?? 0;
$total_girls = $stats['girls'] ?? 0;
$total_attendance = $total_boys + $total_girls;

// Reset result pointer
$records = $conn->query("
    SELECT * FROM lesson_attendance 
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-history"></i> Attendance History</h1>
        <p>View all your attendance records</p>
    </div>
    
    <!-- Statistics 
    <div class="row mb-4">
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
                <div class="stat-value"><?php echo $total_attendance; ?></div>
            </div>
        </div>
    </div>
    -->
    <!-- Records Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table"></i> All Records
            </div>
            <button class="btn btn-success btn-sm" onclick="exportToCSV('attendance_history.csv', generateTableData())">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
        <div class="card-body">
            <?php if ($records->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="recordsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lesson Title</th>
                                <th>Time Range</th>
                                <th>Boys</th>
                                <th>Girls</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $records->fetch_assoc()): ?>
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
                    <p>No attendance records yet. <a href="/elimu-tracks/teacher/attendance.php">Log your first attendance</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function generateTableData() {
        const table = document.getElementById('recordsTable');
        const data = [];
        
        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            data.push({
                Date: row.cells[0].textContent,
                'Lesson Title': row.cells[1].textContent,
                Time: row.cells[2].textContent,
                Boys: row.cells[3].textContent,
                Girls: row.cells[4].textContent,
                Total: row.cells[5].textContent
            });
        }
        
        return data;
    }
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
