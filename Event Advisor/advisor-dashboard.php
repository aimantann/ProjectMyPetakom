<?php
require_once('user-validatesession.php');

// Prevent caching of the page to prevent the back button showing the dashboard after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Set a session message about needing to login again
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

// --- ADDED: Fetch logged-in advisor's name ---
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

// Database connection
$servername = "localhost";
$username = "root"; // database username
$password = ""; // database password
$dbname = "mypetakom_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables with default values
$totalEvents = 0;
$totalCommittees = 0;
$pendingApplications = 0;
$approvedApplications = 0;
$eventStatusData = [];
$committeePositionsData = [];
$recentEvents = [];
$recentApplications = [];
$committees = [];

// Fetch data from database
try {
    // Get total events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM event");
    $totalEvents = $stmt->fetch()['total'] ?? 0;

    // Get total committees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM eventcommittee");
    $totalCommittees = $stmt->fetch()['total'] ?? 0;

    // Get pending applications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM meritapplication WHERE MA_meritAppStatus = 'Pending'");
    $pendingApplications = $stmt->fetch()['total'] ?? 0;

    // Get approved applications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM meritapplication WHERE MA_meritAppStatus = 'Approved'");
    $approvedApplications = $stmt->fetch()['total'] ?? 0;

    // Get event status distribution with fallback data
    $stmt = $pdo->query("SELECT E_eventStatus, COUNT(*) as count FROM event GROUP BY E_eventStatus");
    $eventStatusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no data, provide sample data
    if (empty($eventStatusData)) {
        $eventStatusData = [
            ['E_eventStatus' => 'Pending', 'count' => 0],
            ['E_eventStatus' => 'Approved', 'count' => 0],
            ['E_eventStatus' => 'Completed', 'count' => 0],
            ['E_eventStatus' => 'Cancelled', 'count' => 0]
        ];
    }

    // Get committee positions distribution with fallback data
    $stmt = $pdo->query("SELECT C_position, COUNT(*) as count FROM eventcommittee GROUP BY C_position");
    $committeePositionsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no data, provide sample data
    if (empty($committeePositionsData)) {
        $committeePositionsData = [
            ['C_position' => 'Chairman', 'count' => 0],
            ['C_position' => 'Secretary', 'count' => 0],
            ['C_position' => 'Treasurer', 'count' => 0],
            ['C_position' => 'Member', 'count' => 0]
        ];
    }

    // Get recent events
    $stmt = $pdo->query("SELECT E_eventID, E_name, E_startDate, E_eventStatus FROM event ORDER BY E_startDate DESC LIMIT 10");
    $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent applications
    $stmt = $pdo->query("SELECT MA_applicationID, MA_meritAppStatus, E_eventID, MA_appliedBy FROM meritapplication ORDER BY MA_applicationID DESC LIMIT 10");
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get committees
    $stmt = $pdo->query("SELECT C_committeeID, C_position, U_userID, E_eventID FROM eventcommittee ORDER BY C_committeeID DESC LIMIT 10");
    $committees = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Function to safely output JSON data for JavaScript
function safeJsonEncode($data) {
    return json_encode($data, JSON_HEX_APOS | JSON_HEX_QUOT);
}

?>

<!-- Add a meta tag to prevent back button navigation after logout -->
<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPetakom Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .border-left-primary { border-left: 4px solid #007bff !important; }
        .border-left-success { border-left: 4px solid #28a745 !important; }
        .border-left-warning { border-left: 4px solid #ffc107 !important; }
        .border-left-info { border-left: 4px solid #17a2b8 !important; }
        .text-gray-800 { color: #5a5c69 !important; }
        .text-gray-300 { color: #dddfeb !important; }
        .shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
    </style>
</head>
<body>

<!-- Add your dashboard content here -->
<div class="container-fluid p-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">MyPetakom Event Advisor Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>
    
    <!-- Content Row - Key Metrics -->
    <div class="row">
        <!-- Total Events Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Events</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $totalEvents; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Committees Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Total Committees</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $totalCommittees; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Applications Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pending Applications</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $pendingApplications; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Applications Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Approved Applications</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $approvedApplications; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row - Charts -->
    <div class="row">
        <!-- Event Status Distribution -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Event Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="eventStatusChart"></canvas>
                    </div>
                    <!-- Debug info -->
                    <div class="mt-2 text-muted small">
                        
                        <?php if (!empty($eventStatusData)): ?>
                            (<?php echo implode(', ', array_map(function($item) { return $item['E_eventStatus'] . ':' . $item['count']; }, $eventStatusData)); ?>)
                        <?php endif; ?>
                    </div>
                    <?php if (array_sum(array_column($eventStatusData, 'count')) == 0): ?>
                    <div class="text-center text-muted mt-3">
                        <i class="fas fa-info-circle"></i> No event data available yet
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Committee Positions Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Committee Positions</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="height: 250px;">
                        <canvas id="committeePositionsChart"></canvas>
                    </div>
                    <!-- Debug info -->
                    <div class="mt-2 text-muted small">
                        
                        <?php if (!empty($committeePositionsData)): ?>
                            (<?php echo implode(', ', array_map(function($item) { return $item['C_position'] . ':' . $item['count']; }, $committeePositionsData)); ?>)
                        <?php endif; ?>
                    </div>
                    <?php if (array_sum(array_column($committeePositionsData, 'count')) == 0): ?>
                    <div class="text-center text-muted mt-3">
                        <i class="fas fa-info-circle"></i> No committee data available yet
                    </div>
                    <?php else: ?>
                    <div class="mt-4 text-center small">
                        <?php 
                        $colors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger'];
                        foreach($committeePositionsData as $index => $position): 
                            if($position['count'] > 0): ?>
                                <span class="mr-2">
                                    <i class="fas fa-circle <?php echo $colors[$index % count($colors)]; ?>"></i> 
                                    <?php echo htmlspecialchars($position['C_position']) . ' (' . $position['count'] . ')'; ?>
                                </span>
                            <?php endif;
                        endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row - Tables -->
    <div class="row">
        <!-- Events Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Recent Events</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Event ID</th>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentEvents)): ?>
                                    <?php foreach($recentEvents as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['E_eventID']); ?></td>
                                        <td><?php echo htmlspecialchars($event['E_name']); ?></td>
                                        <td><?php echo htmlspecialchars($event['E_startDate']); ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = '';
                                            switch(strtolower($event['E_eventStatus'])) {
                                                case 'approved': $badgeClass = 'bg-success'; break;
                                                case 'pending': $badgeClass = 'bg-warning'; break;
                                                case 'rejected': $badgeClass = 'bg-danger'; break;
                                                case 'completed': $badgeClass = 'bg-info'; break;
                                                default: $badgeClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($event['E_eventStatus']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No events found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Merit Applications Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Recent Merit Applications</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Status</th>
                                    <th>Event ID</th>
                                    <th>Applied By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentApplications)): ?>
                                    <?php foreach($recentApplications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['MA_applicationID']); ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = '';
                                            switch(strtolower($app['MA_meritAppStatus'])) {
                                                case 'approved': $badgeClass = 'bg-success'; break;
                                                case 'pending': $badgeClass = 'bg-warning'; break;
                                                case 'rejected': $badgeClass = 'bg-danger'; break;
                                                default: $badgeClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($app['MA_meritAppStatus']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($app['E_eventID']); ?></td>
                                        <td><?php echo htmlspecialchars($app['MA_appliedBy']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No applications found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Committee Management Section -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Event Committees</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Committee ID</th>
                                    <th>Position</th>
                                    <th>User ID</th>
                                    <th>Event ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($committees)): ?>
                                    <?php foreach($committees as $committee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($committee['C_committeeID']); ?></td>
                                        <td><?php echo htmlspecialchars($committee['C_position']); ?></td>
                                        <td><?php echo htmlspecialchars($committee['U_userID']); ?></td>
                                        <td><?php echo htmlspecialchars($committee['E_eventID']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No committees found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart scripts with improved error handling -->
<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    
    // Get chart elements
    const eventStatusCanvas = document.getElementById('eventStatusChart');
    const committeePositionsCanvas = document.getElementById('committeePositionsChart');
    
    if (!eventStatusCanvas || !committeePositionsCanvas) {
        console.error('Chart canvas elements not found');
        return;
    }

    // Prepare data for charts
    const eventStatusData = <?php echo safeJsonEncode($eventStatusData); ?>;
    const committeePositionsData = <?php echo safeJsonEncode($committeePositionsData); ?>;
    
    console.log('Event Status Data:', eventStatusData);
    console.log('Committee Positions Data:', committeePositionsData);

    // Event Status Chart - Always show with sample data if empty
    const ctx1 = eventStatusCanvas.getContext('2d');
    
    // Ensure we have data to display
    let eventLabels = [];
    let eventCounts = [];
    
    if (eventStatusData && eventStatusData.length > 0) {
        eventLabels = eventStatusData.map(item => item.E_eventStatus || 'Unknown');
        eventCounts = eventStatusData.map(item => parseInt(item.count) || 0);
    } else {
        // Show sample data structure
        eventLabels = ['Pending', 'Approved', 'Completed', 'Cancelled'];
        eventCounts = [0, 0, 0, 0];
    }
    
    const eventStatusChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: eventLabels,
            datasets: [{
                label: 'Number of Events',
                data: eventCounts,
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',   // Warning/Pending
                    'rgba(40, 167, 69, 0.8)',   // Success/Approved
                    'rgba(23, 162, 184, 0.8)',  // Info/Completed
                    'rgba(220, 53, 69, 0.8)',   // Danger/Cancelled
                    'rgba(108, 117, 125, 0.8)'  // Secondary/Other
                ],
                borderColor: [
                    'rgba(255, 193, 7, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: eventCounts.every(count => count === 0) ? 'No event data available yet' : 'Event Status Distribution'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });

    // Committee Positions Chart - Always show with sample data if empty
    const ctx2 = committeePositionsCanvas.getContext('2d');
    
    let committeeLabels = [];
    let committeeCounts = [];
    
    if (committeePositionsData && committeePositionsData.length > 0) {
        // Filter out zero counts for pie chart
        const filteredData = committeePositionsData.filter(item => parseInt(item.count) > 0);
        
        if (filteredData.length > 0) {
            committeeLabels = filteredData.map(item => item.C_position || 'Unknown');
            committeeCounts = filteredData.map(item => parseInt(item.count) || 0);
        } else {
            // Show all positions with zero counts
            committeeLabels = committeePositionsData.map(item => item.C_position || 'Unknown');
            committeeCounts = committeePositionsData.map(item => 0);
        }
    } else {
        // Show sample data structure
        committeeLabels = ['Chairman', 'Secretary', 'Treasurer', 'Member'];
        committeeCounts = [0, 0, 0, 0];
    }
    
    // For pie chart, if all counts are zero, show a placeholder
    const hasRealData = committeeCounts.some(count => count > 0);
    
    if (hasRealData) {
        const committeePositionsChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: committeeLabels,
                datasets: [{
                    data: committeeCounts,
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.8)',   // Primary
                        'rgba(40, 167, 69, 0.8)',   // Success
                        'rgba(23, 162, 184, 0.8)',  // Info
                        'rgba(255, 193, 7, 0.8)',   // Warning
                        'rgba(220, 53, 69, 0.8)'    // Danger
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Committee Positions Distribution'
                    }
                }
            }
        });
    } else {
        // Create a simple placeholder chart for zero data
        const placeholderChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['rgba(108, 117, 125, 0.3)'],
                    borderColor: ['rgba(108, 117, 125, 0.5)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'No Committee Data Available'
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    }
    
    console.log('Charts initialized successfully');
});
</script>

</body>
</html>

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
</style>