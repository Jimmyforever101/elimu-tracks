<?php
$page_title = 'Log Attendance';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['teacher']);

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Add attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_title = trim($_POST['lesson_title'] ?? '');
    $lesson_time = trim($_POST['lesson_time'] ?? '');
    $lesson_end_time = trim($_POST['lesson_end_time'] ?? '');
    $boys_attendance = (int)($_POST['boys_attendance'] ?? 0);
    $girls_attendance = (int)($_POST['girls_attendance'] ?? 0);
    
    if (empty($lesson_title) || empty($lesson_time) || empty($lesson_end_time) || $boys_attendance < 0 || $girls_attendance < 0) {
        $error = 'All fields are required with valid values';
    } elseif ($lesson_end_time <= $lesson_time) {
        $error = 'Lesson end time must be after start time';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO lesson_attendance (user_id, lesson_title, lesson_time, lesson_end_time, boys_attendance, girls_attendance)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssii", $user_id, $lesson_title, $lesson_time, $lesson_end_time, $boys_attendance, $girls_attendance);
        
        if ($stmt->execute()) {
            $success = 'Attendance logged successfully!';
            // Reset form
            $lesson_title = '';
            $lesson_time = '';
            $lesson_end_time = '';
            $boys_attendance = '';
            $girls_attendance = '';
        } else {
            $error = 'Error logging attendance: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-clipboard-list"></i> Log Attendance</h1>
        <p>Record lesson attendance for your class</p>
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
                    <i class="fas fa-form"></i> Attendance Form
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return validateAttendanceForm()">
                        <div class="form-group">
                            <label for="lesson_title">Lesson Title</label>
                            <input type="text" class="form-control" id="lesson_title" name="lesson_title" 
                                   placeholder="e.g., Mathematics - Algebra" value="<?php echo htmlspecialchars($lesson_title ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="lesson_time">Start Time</label>
                                <input type="time" class="form-control" id="lesson_time" name="lesson_time" 
                                       value="<?php echo htmlspecialchars($lesson_time ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="lesson_end_time">End Time</label>
                                <input type="time" class="form-control" id="lesson_end_time" name="lesson_end_time" 
                                       value="<?php echo htmlspecialchars($lesson_end_time ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="boys_attendance">Boys Attendance</label>
                            <input type="number" class="form-control" id="boys_attendance" name="boys_attendance" 
                                   placeholder="Number of boys present" min="0" value="<?php echo htmlspecialchars($boys_attendance ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="girls_attendance">Girls Attendance</label>
                            <input type="number" class="form-control" id="girls_attendance" name="girls_attendance" 
                                   placeholder="Number of girls present" min="0" value="<?php echo htmlspecialchars($girls_attendance ?? ''); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Log Attendance
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Tips
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Lesson Title:</strong> Be descriptive (e.g., "English - Reading Comprehension")</li>
                        <li><strong>Start Time:</strong> When the lesson began (e.g., 09:30)</li>
                        <li><strong>End Time:</strong> When the lesson ended (e.g., 10:15)</li>
                        <li><strong>Boys/Girls Count:</strong> Only count present students</li>
                        <li>Records are automatically saved with the current date and your name</li>
                        <li>You can view your history anytime from the dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
