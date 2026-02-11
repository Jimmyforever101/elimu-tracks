// Elimu Tracks - Custom JavaScript

// Form Validation
function validateLoginForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username) {
        showAlert('Username is required', 'danger');
        return false;
    }
    
    if (!password) {
        showAlert('Password is required', 'danger');
        return false;
    }
    
    if (password.length < 6) {
        showAlert('Password must be at least 6 characters', 'danger');
        return false;
    }
    
    return true;
}

// Attendance Form Validation
function validateAttendanceForm() {
    const lessonTitle = document.getElementById('lesson_title').value.trim();
    const lessonTime = document.getElementById('lesson_time').value.trim();
    const boysAttendance = document.getElementById('boys_attendance').value.trim();
    const girlsAttendance = document.getElementById('girls_attendance').value.trim();
    
    if (!lessonTitle) {
        showAlert('Lesson title is required', 'danger');
        return false;
    }
    
    if (!lessonTime) {
        showAlert('Lesson time is required', 'danger');
        return false;
    }
    
    if (!boysAttendance || isNaN(boysAttendance) || boysAttendance < 0) {
        showAlert('Boys attendance must be a valid number', 'danger');
        return false;
    }
    
    if (!girlsAttendance || isNaN(girlsAttendance) || girlsAttendance < 0) {
        showAlert('Girls attendance must be a valid number', 'danger');
        return false;
    }
    
    return true;
}

// Kitchen Plates Form Validation
function validateKitchenForm() {
    const platesCount = document.getElementById('plates_count').value.trim();
    const recordDate = document.getElementById('record_date').value.trim();
    
    if (!recordDate) {
        showAlert('Date is required', 'danger');
        return false;
    }
    
    if (!platesCount || isNaN(platesCount) || platesCount < 0) {
        showAlert('Plates count must be a valid number', 'danger');
        return false;
    }
    
    return true;
}

// Department Form Validation
function validateDepartmentForm() {
    const departmentName = document.getElementById('department_name').value.trim();
    
    if (!departmentName) {
        showAlert('Department name is required', 'danger');
        return false;
    }
    
    if (departmentName.length < 3) {
        showAlert('Department name must be at least 3 characters', 'danger');
        return false;
    }
    
    return true;
}

// User Form Validation
function validateUserForm() {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const role = document.getElementById('role').value.trim();
    
    if (!username || username.length < 3) {
        showAlert('Username must be at least 3 characters', 'danger');
        return false;
    }
    
    if (!email || !validateEmail(email)) {
        showAlert('Valid email is required', 'danger');
        return false;
    }
    
    if (!password || password.length < 6) {
        showAlert('Password must be at least 6 characters', 'danger');
        return false;
    }
    
    if (!role) {
        showAlert('Please select a role', 'danger');
        return false;
    }
    
    return true;
}

// Email Validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Show Alert
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Create Chart - Line Chart (Attendance Trends)
function createLineChart(canvasId, labels, data, label) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: '#1eb53a',
                backgroundColor: 'rgba(30, 181, 58, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Create Chart - Bar Chart (Attendance by Gender)
function createBarChart(canvasId, labels, boysData, girlsData) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Boys',
                    data: boysData,
                    backgroundColor: '#1eb53a'
                },
                {
                    label: 'Girls',
                    data: girlsData,
                    backgroundColor: '#f17b26'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Create Chart - Pie Chart (Department Distribution)
function createPieChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#1eb53a',
                    '#f17b26',
                    '#fcd116',
                    '#1eb53a',
                    '#dc3545',
                    '#00a3dd'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right'
                }
            }
        }
    });
}

// Export to CSV
function exportToCSV(filename, data) {
    let csv = '';
    
    // Header
    if (data.length > 0) {
        csv += Object.keys(data[0]).join(',') + '\n';
    }
    
    // Rows
    data.forEach(row => {
        csv += Object.values(row).join(',') + '\n';
    });
    
    // Create blob and download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Export to PDF (requires jsPDF library)
function exportToPDF(filename, tableId) {
    const element = document.getElementById(tableId);
    const html2canvas = window.html2canvas;
    const jsPDF = window.jsPDF;
    
    if (html2canvas && jsPDF) {
        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF();
            const imgWidth = 210;
            const pageHeight = 295;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            
            let position = 0;
            
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            pdf.save(filename);
        });
    } else {
        showAlert('PDF export library not loaded', 'warning');
    }
}

// Format Date
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

// Get Query Parameter
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check for error/success messages
    const error = getQueryParam('error');
    const success = getQueryParam('success');
    
    if (error) {
        showAlert(decodeURIComponent(error), 'danger');
    }
    
    if (success) {
        showAlert(decodeURIComponent(success), 'success');
    }
});
