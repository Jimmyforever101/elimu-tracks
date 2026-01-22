<?php
$page_title = 'Kitchen Dashboard';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['kitchen', 'admin']);

// Handle delete record (admin only)
$delete_message = '';
$delete_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
    if (getCurrentRole() === 'admin') {
        $record_id = (int)($_POST['record_id'] ?? 0);
        
        if ($record_id > 0) {
            $stmt = $conn->prepare("DELETE FROM kitchen_plates WHERE id = ?");
            $stmt->bind_param("i", $record_id);
            
            if ($stmt->execute()) {
                $delete_message = 'Record deleted successfully!';
            } else {
                $delete_error = 'Error deleting record: ' . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $delete_error = 'Only admins can delete records';
    }
}

// Get statistics
$total_records = $conn->query("SELECT COUNT(*) as count FROM kitchen_plates")->fetch_assoc()['count'];
$total_plates = $conn->query("SELECT SUM(plates_count) as total FROM kitchen_plates")->fetch_assoc()['total'] ?? 0;
$total_cups = $conn->query("SELECT SUM(tea_cups_count) as total FROM kitchen_plates")->fetch_assoc()['total'] ?? 0;
$today_plates = $conn->query("SELECT SUM(plates_count) as total FROM kitchen_plates WHERE DATE(record_date) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$today_cups = $conn->query("SELECT SUM(tea_cups_count) as total FROM kitchen_plates WHERE DATE(record_date) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$week_plates = $conn->query("SELECT SUM(plates_count) as total FROM kitchen_plates WHERE record_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['total'] ?? 0;
$week_cups = $conn->query("SELECT SUM(tea_cups_count) as total FROM kitchen_plates WHERE record_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['total'] ?? 0;
$month_plates = $conn->query("SELECT SUM(plates_count) as total FROM kitchen_plates WHERE YEAR(record_date) = YEAR(NOW()) AND MONTH(record_date) = MONTH(NOW())")->fetch_assoc()['total'] ?? 0;
$month_cups = $conn->query("SELECT SUM(tea_cups_count) as total FROM kitchen_plates WHERE YEAR(record_date) = YEAR(NOW()) AND MONTH(record_date) = MONTH(NOW())")->fetch_assoc()['total'] ?? 0;
$year_plates = $conn->query("SELECT SUM(plates_count) as total FROM kitchen_plates WHERE YEAR(record_date) = YEAR(NOW())")->fetch_assoc()['total'] ?? 0;
$year_cups = $conn->query("SELECT SUM(tea_cups_count) as total FROM kitchen_plates WHERE YEAR(record_date) = YEAR(NOW())")->fetch_assoc()['total'] ?? 0;

// Get weekly data for chart
$weekly_data = $conn->query("
    SELECT DATE(record_date) as date, SUM(plates_count) as plates, SUM(tea_cups_count) as cups
    FROM kitchen_plates
    WHERE record_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(record_date)
    ORDER BY date ASC
");

$dates = [];
$plates_counts = [];
$cups_counts = [];
while ($row = $weekly_data->fetch_assoc()) {
    $dates[] = $row['date'];
    $plates_counts[] = $row['plates'];
    $cups_counts[] = $row['cups'];
}

// Get recent records
$recent = $conn->query("
    SELECT * FROM kitchen_plates
    ORDER BY record_date DESC
    LIMIT 5
");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-utensils"></i> Kitchen Dashboard</h1>
        <p>Welcome to the Kitchen Department. Track daily plate & cups records here.</p>
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
    
    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-calendar-day"></i> Today's Plates</h5>
                <div class="stat-value"><?php echo $today_plates; ?></div>
                <small>Tea Cups: <?php echo $today_cups; ?></small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-calendar-week"></i> This Week</h5>
                <div class="stat-value"><?php echo $week_plates; ?></div>
                <small>Tea Cups: <?php echo $week_cups; ?></small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-calendar-alt"></i> This Month</h5>
                <div class="stat-value"><?php echo $month_plates; ?></div>
                <small>Tea Cups: <?php echo $month_cups; ?></small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="stat-card">
                <h5><i class="fas fa-calendar"></i> This Year</h5>
                <div class="stat-value"><?php echo $year_plates; ?></div>
                <small>Tea Cups: <?php echo $year_cups; ?></small>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-cogs"></i> Quick Actions
        </div>
        <div class="card-body">
            <a href="/elimu-tracks/kitchen/plates.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-plus"></i> Record Plates & Tea Cups
            </a>
        </div>
    </div>
    
    <!-- Weekly Chart -->
    <div class="row mt-4">
        <div class="col-lg-8 col-md-12">
            <div class="chart-wrapper">
                <h5 class="card-title"><i class="fas fa-chart-line"></i> Plates & Tea Cups - This Week</h5>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Information
                </div>
                <div class="card-body">
                    <div class="stat-card">
                        <h5>Plates Average/Day</h5>
                        <p class="stat-value"><?php echo $total_records > 0 ? round($total_plates / $total_records, 0) : 0; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Tea Cups Average/Day</h5>
                        <p class="stat-value"><?php echo $total_records > 0 ? round($total_cups / $total_records, 0) : 0; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Last Updated</h5>
                        <p><?php echo $recent->num_rows > 0 ? ($recent_record = $recent->fetch_assoc()) ? date('M d, Y H:i', strtotime($recent_record['created_at'])) : 'N/A' : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Records -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-clock"></i> Recent Records
        </div>
        <div class="card-body">
            <?php 
            // Reset for second fetch
            $recent = $conn->query("
                SELECT * FROM kitchen_plates
                ORDER BY record_date DESC
                LIMIT 10
            ");
            if ($recent->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Plates Count</th>
                                <th>Tea Cups Served</th>
                                <?php if (getCurrentRole() === 'admin'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['record_date'])); ?></td>
                                    <td><?php echo $row['plates_count']; ?></td>
                                    <td><?php echo $row['tea_cups_count']; ?></td>
                                    <?php if (getCurrentRole() === 'admin'): ?>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                            <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_record" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No records yet. <a href="/elimu-tracks/kitchen/plates.php">Record your first entry</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    <?php if (!empty($dates)): ?>
    // Create chart with both plates and tea cups data
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Plates Saved',
                    data: <?php echo json_encode($plates_counts); ?>,
                    backgroundColor: '#0d6efd',
                    borderColor: '#0a58ca',
                    borderWidth: 1
                },
                {
                    label: 'Tea Cups Served',
                    data: <?php echo json_encode($cups_counts); ?>,
                    backgroundColor: '#fd7e14',
                    borderColor: '#d36f0f',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
    <?php endif; ?>
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
