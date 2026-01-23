<?php
// Authentication and Authorization
//session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function redirectToLogin() {
    if (!isLoggedIn()) {
        header("Location: /elimu-tracks/index.php");
        exit();
    }
}

function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: /elimu-tracks/index.php");
        exit();
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /elimu-tracks/index.php?error=Unauthorized Access");
        exit();
    }
}

function getCurrentUser() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
}

function getCurrentRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getCurrentDepartment() {
    return isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;
}

function isSuperAdmin() {
    return isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1;
}

function checkSuperAdmin() {
    if (!isLoggedIn()) {
        header("Location: /elimu-tracks/index.php");
        exit();
    }
    
    if (!isSuperAdmin()) {
        header("Location: /elimu-tracks/admin/dashboard.php?error=Super Admin access required");
        exit();
    }
}
?>
