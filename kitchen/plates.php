<?php
$page_title = 'Record Plates';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['kitchen', 'admin']);

$success = '';
$error = '';

// Add kitchen record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plates_count = (int)($_POST['plates_count'] ?? 0);
    $tea_cups_count = (int)($_POST['tea_cups_count'] ?? 0);
    $record_date = trim($_POST['record_date'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($record_date) || ($plates_count < 0 && $tea_cups_count < 0)) {
        $error = 'Date is required and at least one count (plates or tea cups) must be greater than 0';
    } else {
        $stmt = $conn->prepare("INSERT INTO kitchen_plates (user_id, record_date, plates_count, tea_cups_count) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $user_id, $record_date, $plates_count, $tea_cups_count);
        
        if ($stmt->execute()) {
            $success = 'Record saved successfully!';
            // Reset form
            $plates_count = '';
            $tea_cups_count = '';
            $record_date = date('Y-m-d');
        } else {
            $error = 'Error saving record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Set default date to today
$default_date = isset($record_date) ? $record_date : date('Y-m-d');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-utensils"></i> Record Daily Items</h1>
        <p>Log the number of plates saved and tea cups served daily</p>
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
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-form"></i> Daily Record Form
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return validateKitchenForm()">
                        <div class="form-group">
                            <label for="record_date">Date</label>
                            <input type="date" class="form-control" id="record_date" name="record_date" 
                                   value="<?php echo htmlspecialchars($default_date); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="plates_count">Plates Saved</label>
                            <input type="number" class="form-control" id="plates_count" name="plates_count" 
                                   placeholder="Number of plates saved" min="0" value="<?php echo htmlspecialchars($plates_count ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tea_cups_count">Tea Cups Served</label>
                            <input type="number" class="form-control" id="tea_cups_count" name="tea_cups_count" 
                                   placeholder="Number of tea cups served" min="0" value="<?php echo htmlspecialchars($tea_cups_count ?? ''); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Save Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Instructions
                </div>
                <div class="card-body">
                    <h6>Plates Saved</h6>
                    <ul>
                        <li>Record the total number of plates that were saved/reused for that day</li>
                        <li>This includes all plates from breakfast, lunch, and other meals</li>
                    </ul>
                    
                    <h6 class="mt-3">Tea Cups Served</h6>
                    <ul>
                        <li>Record the total number of tea cups served during the day</li>
                        <li>This includes all tea/beverages served to students and staff</li>
                    </ul>
                    
                    <hr>
                    
                    <ul>
                        <li>Records can be edited by the admin if needed</li>
                        <li>All historical data is available in the dashboard</li>
                        <li>Admins can view trends and generate reports from this data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Today's and Recent Records 
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Records
        </div>
        <div class="card-body">
            <?php
            $recent_records = $conn->query("
                SELECT * FROM kitchen_plates
                ORDER BY record_date DESC
                LIMIT 10
            ");
            
            if ($recent_records->num_rows > 0):
            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Plates Count</th>
                                <th>Recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_records->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['record_date'])); ?></td>
                                    <td><?php echo $row['plates_count']; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No records yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>-->
    
    <div class="mt-4 mb-4">
        <button onclick="history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
