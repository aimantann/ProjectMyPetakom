<?php
session_start();

// Prevent caching of the page to prevent the back button showing the dashboard after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Set a session message about needing to login again
    session_start();
    $_SESSION['login_required'] = "Your session has expired. Please login again to view the dashboard.";
    header("Location: user-login.php"); // Redirect to login if not logged in
    exit;
}

// Additional security check - validate user's session token if available
if (!isset($_SESSION['session_token']) || empty($_SESSION['session_token'])) {
    // Generate a new token if it doesn't exist
    $_SESSION['session_token'] = bin2hex(random_bytes(32));
    
    // If token is missing but user is supposedly logged in, this might be a session issue
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        session_start();
        $_SESSION['login_required'] = "For security reasons, please login again to continue.";
        header("Location: user-login.php");
        exit;
    }
}

include('includes/header.php');
include('includes/dbconnection.php');

// --- ADDED: Fetch logged-in admin's name ---
$user_name = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $query = "SELECT U_name FROM user WHERE U_email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($db_name);
    if ($stmt->fetch()) {
        $user_name = $db_name;
    }
    $stmt->close();
}

// --- DASHBOARD DATA QUERIES ---

// 1. Count of attendance records
$attendance_count_query = "SELECT COUNT(*) as total_attendance FROM attendance";
$attendance_count_result = $conn->query($attendance_count_query);
$total_attendance = $attendance_count_result->fetch_assoc()['total_attendance'];

// 2. Count of events by type/level
$events_by_type_query = "SELECT E_level, COUNT(*) as count FROM event GROUP BY E_level";
$events_by_type_result = $conn->query($events_by_type_query);
$events_by_type = [];
while ($row = $events_by_type_result->fetch_assoc()) {
    $events_by_type[] = $row;
}

// 3. Number of registered users for each role
$users_by_role_query = "SELECT U_usertype, COUNT(*) as count FROM user GROUP BY U_usertype";
$users_by_role_result = $conn->query($users_by_role_query);
$users_by_role = [];
while ($row = $users_by_role_result->fetch_assoc()) {
    $users_by_role[] = $row;
}

// 4. Number of approved memberships
$approved_memberships_query = "SELECT COUNT(*) as total FROM membership WHERE M_status = 'approved'";
$approved_memberships_result = $conn->query($approved_memberships_query);
$approved_memberships = $approved_memberships_result->fetch_assoc()['total'];

// 5. Monthly attendance trends (last 12 months)
$monthly_trends_query = "
    SELECT 
        DATE_FORMAT(a.A_checkinTime, '%Y-%m') as month,
        COUNT(*) as attendance_count
    FROM attendance a
    WHERE a.A_checkinTime >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(a.A_checkinTime, '%Y-%m')
    ORDER BY month ASC
";
$monthly_trends_result = $conn->query($monthly_trends_query);
$monthly_trends = [];
while ($row = $monthly_trends_result->fetch_assoc()) {
    $monthly_trends[] = $row;
}

// 6. Event attendance rates
$event_attendance_query = "
    SELECT 
        e.E_name,
        e.E_eventID,
        COUNT(DISTINCT ats.S_slotID) as total_slots,
        COUNT(a.A_attendanceID) as total_attendance,
        ROUND((COUNT(a.A_attendanceID) / NULLIF(COUNT(DISTINCT ats.S_slotID), 0)) * 100, 2) as attendance_rate
    FROM event e
    LEFT JOIN attendanceslot ats ON e.E_eventID = ats.E_eventID
    LEFT JOIN attendance a ON ats.S_slotID = a.S_slotID
    GROUP BY e.E_eventID, e.E_name
    HAVING total_slots > 0
    ORDER BY attendance_rate DESC
";
$event_attendance_result = $conn->query($event_attendance_query);
$event_attendance_rates = [];
while ($row = $event_attendance_result->fetch_assoc()) {
    $event_attendance_rates[] = $row;
}

// 7. Student participation by year (assuming U_usertype = 'student')
$student_participation_query = "
    SELECT 
        YEAR(a.A_checkinTime) as year,
        COUNT(DISTINCT u.U_userID) as unique_students,
        COUNT(a.A_attendanceID) as total_participations
    FROM attendance a
    JOIN user u ON a.U_userID = u.U_userID
    WHERE u.U_usertype = 'student'
    GROUP BY YEAR(a.A_checkinTime)
    ORDER BY year DESC
";
$student_participation_result = $conn->query($student_participation_query);
$student_participation = [];
while ($row = $student_participation_result->fetch_assoc()) {
    $student_participation[] = $row;
}

// Handle attendance search
$search_results = [];
if (isset($_POST['search_attendance'])) {
    $search_term = $_POST['search_term'];
    $search_date = $_POST['search_date'];
    
    $search_query = "
        SELECT 
            u.U_name,
            u.U_email,
            u.U_userID,
            a.A_checkinTime,
            a.A_location,
            e.E_name as event_name
        FROM attendance a
        JOIN user u ON a.U_userID = u.U_userID
        JOIN attendanceslot ats ON a.S_slotID = ats.S_slotID
        JOIN event e ON ats.E_eventID = e.E_eventID
        WHERE (u.U_userID = ? OR u.U_email LIKE ?)
    ";
    
    $params = [$search_term, "%$search_term%"];
    $types = "ss";
    
    if (!empty($search_date)) {
        $search_query .= " AND DATE(a.A_checkinTime) = ?";
        $params[] = $search_date;
        $types .= "s";
    }
    
    $search_query .= " ORDER BY a.A_checkinTime DESC";
    
    $search_stmt = $conn->prepare($search_query);
    $search_stmt->bind_param($types, ...$params);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
    
    while ($row = $search_result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $search_stmt->close();
}
?>

<!-- Add a meta tag to prevent back button navigation after logout -->
<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">

<!-- Add Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Add your dashboard content here -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <?php if (!empty($user_name)): ?>
            <span class="text-muted">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
        <?php endif; ?>
    </div>
    
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Attendance Records</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_attendance); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Memberships</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($approved_memberships); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo array_sum(array_column($events_by_type, 'count')); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo array_sum(array_column($users_by_role, 'count')); ?></div>
                        </div>
                        <div class="col-auto">
                            <canvas id="totalUsersChart" width="60" height="60"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- First Chart Row -->
    <div class="row">
        <!-- Monthly Attendance Trends -->
        <div class="col-xl-8 col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Attendance Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Events by Type -->
        <div class="col-xl-4 col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Events by Type</h6>
                </div>
                <div class="card-body">
                    <canvas id="eventTypesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Chart Row -->
    <div class="row">
        <!-- Event Attendance Rates -->
        <div class="col-xl-8 col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Event Attendance Rates</h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceRatesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- User Roles Distribution -->
        <div class="col-xl-4 col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Users by Role (Detailed)</h6>
                </div>
                <div class="card-body">
                    <canvas id="userRolesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row: Data Tables -->
    <div class="row">
        <!-- Student Participation by Year -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Student Participation by Year</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Unique Students</th>
                                    <th>Total Participations</th>
                                    <th>Avg per Student</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_participation as $row): ?>
                                <tr>
                                    <td><?php echo $row['year']; ?></td>
                                    <td><?php echo number_format($row['unique_students']); ?></td>
                                    <td><?php echo number_format($row['total_participations']); ?></td>
                                    <td><?php echo $row['unique_students'] > 0 ? number_format($row['total_participations'] / $row['unique_students'], 1) : '0'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Search -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Search</h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="mb-3">
                        <div class="form-group">
                            <label for="search_term">Student ID/Email:</label>
                            <input type="text" class="form-control" id="search_term" name="search_term" 
                                   placeholder="Enter Student ID or Email" value="<?php echo isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="search_date">Date (Optional):</label>
                            <input type="date" class="form-control" id="search_date" name="search_date" 
                                   value="<?php echo isset($_POST['search_date']) ? htmlspecialchars($_POST['search_date']) : ''; ?>">
                        </div>
                        <button type="submit" name="search_attendance" class="btn btn-primary">Search</button>
                    </form>

                    <?php if (!empty($search_results)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Check-in Time</th>
                                    <th>Event</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['U_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['U_email']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($result['A_checkinTime'])); ?></td>
                                    <td><?php echo htmlspecialchars($result['event_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif (isset($_POST['search_attendance'])): ?>
                    <div class="alert alert-info">No attendance records found for the specified criteria.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Width Event Analysis Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detailed Event Attendance Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Total Slots</th>
                                    <th>Total Attendance</th>
                                    <th>Attendance Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($event_attendance_rates as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['E_name']); ?></td>
                                    <td><?php echo $event['total_slots']; ?></td>
                                    <td><?php echo $event['total_attendance']; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?php echo $event['attendance_rate'] >= 75 ? 'bg-success' : ($event['attendance_rate'] >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 role="progressbar" style="width: <?php echo $event['attendance_rate']; ?>%">
                                                <?php echo $event['attendance_rate']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($event['attendance_rate'] >= 75): ?>
                                            <span class="badge badge-success">Excellent</span>
                                        <?php elseif ($event['attendance_rate'] >= 50): ?>
                                            <span class="badge badge-warning">Good</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Needs Improvement</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
// Monthly Trends Chart
const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly_trends, 'month')); ?>,
        datasets: [{
            label: 'Monthly Attendance',
            data: <?php echo json_encode(array_column($monthly_trends, 'attendance_count')); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Events by Type Chart
const eventTypesCtx = document.getElementById('eventTypesChart').getContext('2d');
const eventTypesChart = new Chart(eventTypesCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($events_by_type, 'E_level')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($events_by_type, 'count')); ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// User Roles Chart
const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
const userRolesChart = new Chart(userRolesCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($users_by_role, 'U_usertype')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($users_by_role, 'count')); ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Attendance Rates Chart
const attendanceRatesCtx = document.getElementById('attendanceRatesChart').getContext('2d');
const attendanceRatesChart = new Chart(attendanceRatesCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($event_attendance_rates, 'E_name')); ?>,
        datasets: [{
            label: 'Attendance Rate (%)',
            data: <?php echo json_encode(array_column($event_attendance_rates, 'attendance_rate')); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<!-- Add JavaScript to handle back button detection -->
<script type="text/javascript">
    // When page loads
    window.onload = function() {
        // When navigating to this page with back button
        window.addEventListener('pageshow', function(event) {
            // If navigated via browser history (back button)
            if (event.persisted || performance.navigation.type === 2) {
                // Instead of just reloading, redirect to a validation script
                window.location.href = "validate-session.php";
            }
        });
    };
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<!-- Custom CSS -->
<style>
    .card-title {
        font-size: 1.2em;
        font-weight: bold;
    }
    .card-count {
        font-size: 1.2em;
    }
    .card-footer {
        font-size: 1em;
    }
    
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .progress {
        background-color: #f8f9fc;
    }
    
    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
    }
    
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }
</style>