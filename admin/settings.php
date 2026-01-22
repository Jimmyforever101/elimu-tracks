<?php
$page_title = 'System Settings';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/auth.php');

checkRole(['admin']);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-cog"></i> System Settings</h1>
        <p>Configure Elimu Tracks system settings</p>
    </div>
    
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sliders-h"></i> General Settings
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>School Name</label>
                            <input type="text" class="form-control" placeholder="Elimu Yetu" value="Elimu Yetu" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>System Version</label>
                            <input type="text" class="form-control" placeholder="1.0.0" value="1.0.0" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Database</label>
                            <input type="text" class="form-control" placeholder="elimu_tracks" value="<?php echo DB_NAME; ?>" disabled>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" class="form-control" placeholder="30" value="30" min="5" max="480">
                        </div>
                        
                        <div class="form-group">
                            <label>Records Per Page</label>
                            <input type="number" class="form-control" placeholder="10" value="10" min="5" max="100">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> System Information
                </div>
                <div class="card-body">
                    <div class="stat-card">
                        <h5>PHP Version</h5>
                        <p><?php echo phpversion(); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Server OS</h5>
                        <p><?php echo php_uname(); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>MySQL Version</h5>
                        <p><?php echo $conn->server_info; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h5>Current Date & Time</h5>
                        <p><?php echo date('M d, Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
