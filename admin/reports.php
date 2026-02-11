<?php
$page_title = 'Reports';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);

// Get attendance report
$attendance_report = $conn->query("
    SELECT d.name as department_name, COUNT(*) as total_records,
    SUM(la.boys_attendance) as total_boys, SUM(la.girls_attendance) as total_girls
    FROM lesson_attendance la
    JOIN users u ON la.user_id = u.id
    JOIN departments d ON u.department_id = d.id
    GROUP BY d.id, d.name
    ORDER BY d.name
");

$dept_names = [];
$record_counts = [];
while ($row = $attendance_report->fetch_assoc()) {
    $dept_names[] = $row['department_name'];
    $record_counts[] = $row['total_records'];
}

// Get kitchen report - daily totals
$kitchen_report = $conn->query("
    SELECT DATE(record_date) as date, SUM(plates_count) as total
    FROM kitchen_plates
    WHERE record_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(record_date)
    ORDER BY date ASC
");

$kitchen_dates = [];
$kitchen_plates = [];
while ($row = $kitchen_report->fetch_assoc()) {
    $kitchen_dates[] = $row['date'];
    $kitchen_plates[] = $row['total'];
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-file-chart-column"></i> Reports</h1>
        <p>View comprehensive school records and statistics</p>
    </div>
    
    <!-- Removed summary charts; replaced with detailed tables below -->
    
    <!-- Kitchen Detailed Report -->
    <?php
    $kitchen_detail = $conn->query("SELECT kp.*, u.username FROM kitchen_plates kp LEFT JOIN users u ON kp.user_id = u.id ORDER BY kp.record_date DESC, kp.created_at DESC LIMIT 200");
    ?>
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-utensils"></i> Kitchen Detailed Report
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="kitchenTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plates Saved</th>
                            <th>Tea Cups</th>
                            <th>Recorded By</th>
                            <th>Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($k = $kitchen_detail->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($k['record_date'])); ?></td>
                                <td><?php echo (int)$k['plates_count']; ?></td>
                                <td><?php echo (int)$k['tea_cups_count']; ?></td>
                                <td><?php echo htmlspecialchars($k['username'] ?? 'System'); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($k['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button class="btn btn-success btn-sm" onclick="exportToCSV('kitchen_report.csv', generateKitchenData())">
                    <i class="fas fa-download"></i> Download CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Detailed Attendance Report -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-table"></i> Detailed Attendance Report
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Teacher</th>
                            <th>Department</th>
                            <th>Lesson</th>
                            <th>Time</th>
                            <th>Boys</th>
                            <th>Girls</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $attendance_detail = $conn->query("
                            SELECT la.*, u.username, d.name as department_name
                            FROM lesson_attendance la
                            JOIN users u ON la.user_id = u.id
                            JOIN departments d ON u.department_id = d.id
                            ORDER BY la.created_at DESC
                            LIMIT 100
                        ");
                        
                        while ($row = $attendance_detail->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['lesson_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['lesson_time']); ?></td>
                                <td><?php echo $row['boys_attendance']; ?></td>
                                <td><?php echo $row['girls_attendance']; ?></td>
                                <td><strong><?php echo $row['boys_attendance'] + $row['girls_attendance']; ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button class="btn btn-success btn-sm" onclick="exportToCSV('attendance_report.csv', generateAttendanceData())">
                    <i class="fas fa-download"></i> Download CSV
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Generate attendance data for export
    function generateAttendanceData() {
        const table = document.getElementById('attendanceTable');
        const data = [];

        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            data.push({
                Date: row.cells[0].textContent,
                Teacher: row.cells[1].textContent,
                Department: row.cells[2].textContent,
                Lesson: row.cells[3].textContent,
                Time: row.cells[4].textContent,
                Boys: row.cells[5].textContent,
                Girls: row.cells[6].textContent,
                Total: row.cells[7].textContent
            });
        }

        return data;
    }

    // Generate kitchen data for export
    function generateKitchenData() {
        const table = document.getElementById('kitchenTable');
        const data = [];

        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            data.push({
                Date: row.cells[0].textContent,
                Plates: row.cells[1].textContent,
                TeaCups: row.cells[2].textContent,
                RecordedBy: row.cells[3].textContent,
                RecordedAt: row.cells[4].textContent
            });
        }

        return data;
    }
</script>

<!-- Back to Dashboard button removed (nav already provides access) -->

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
