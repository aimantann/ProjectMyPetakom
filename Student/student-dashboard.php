<?php
require_once('user-validatesession.php');

include('includes/header.php');
include("includes/dbconnection.php");

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Define available semesters
$semesters = [
    "SEMESTER II ACADEMIC SESSION 2024/2025",
    "SEMESTER I ACADEMIC SESSION 2024/2025",
    "SEMESTER II ACADEMIC SESSION 2023/2024",
    "SEMESTER I ACADEMIC SESSION 2023/2024",
    "SEMESTER II ACADEMIC SESSION 2022/2023",
    "SEMESTER I ACADEMIC SESSION 2022/2023"
];

// Get selected semester or default to current
$selected_semester = isset($_POST['semester']) ? $_POST['semester'] : "SEMESTER II ACADEMIC SESSION 2024/2025";

// Unified search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Event query with unified search
$event_query = "SELECT e.E_name, e.E_level, mc.MC_role, md.MD_meritPoint 
                FROM meritawarded md 
                JOIN meritclaim mc ON md.E_eventID = mc.E_eventID AND md.U_userID = mc.U_userID
                JOIN event e ON mc.E_eventID = e.E_eventID 
                JOIN eventsemester es ON e.E_eventID = es.E_eventID
                WHERE md.U_userID = ? AND es.ES_semester = ?";

if ($search) {
    $event_query .= " AND (e.E_name LIKE ? OR e.E_level LIKE ? OR mc.MC_role LIKE ?)";
    $search = "%$search%";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("issss", $_SESSION['user_id'], $selected_semester, $search, $search, $search);
} else {
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("is", $_SESSION['user_id'], $selected_semester);
}

$stmt->execute();
$events_result = $stmt->get_result();

// Role summary
$role_summary_query = "SELECT 
    mc.MC_role,
    COUNT(DISTINCT e.E_eventID) as event_count,
    SUM(md.MD_meritPoint) as total_points
FROM meritawarded md
JOIN meritclaim mc ON md.E_eventID = mc.E_eventID AND md.U_userID = mc.U_userID
JOIN event e ON mc.E_eventID = e.E_eventID
JOIN eventsemester es ON e.E_eventID = es.E_eventID
WHERE md.U_userID = ? AND es.ES_semester = ?
GROUP BY mc.MC_role";
$stmt = $conn->prepare($role_summary_query);
$stmt->bind_param("is", $_SESSION['user_id'], $selected_semester);
$stmt->execute();
$role_summary = $stmt->get_result();

// Analytics summary
$analytics_query = "SELECT 
    COUNT(DISTINCT e.E_eventID) as total_events,
    COUNT(DISTINCT mc.MC_role) as unique_roles,
    SUM(md.MD_meritPoint) as total_merit,
    AVG(md.MD_meritPoint) as avg_merit
FROM meritawarded md
JOIN meritclaim mc ON md.E_eventID = mc.E_eventID AND md.U_userID = mc.U_userID
JOIN event e ON mc.E_eventID = e.E_eventID
JOIN eventsemester es ON e.E_eventID = es.E_eventID
WHERE md.U_userID = ? AND es.ES_semester = ?";
$stmt = $conn->prepare($analytics_query);
$stmt->bind_param("is", $_SESSION['user_id'], $selected_semester);
$stmt->execute();
$analytics = $stmt->get_result()->fetch_assoc();

// Chart data
$chart_query = "SELECT 
    e.E_level,
    COUNT(*) as event_count,
    SUM(md.MD_meritPoint) as level_points
FROM meritawarded md
JOIN meritclaim mc ON md.E_eventID = mc.E_eventID AND md.U_userID = mc.U_userID
JOIN event e ON mc.E_eventID = e.E_eventID
JOIN eventsemester es ON e.E_eventID = es.E_eventID
WHERE md.U_userID = ? AND es.ES_semester = ?
GROUP BY e.E_level";
$stmt = $conn->prepare($chart_query);
$stmt->bind_param("is", $_SESSION['user_id'], $selected_semester);
$stmt->execute();
$chart_data = $stmt->get_result();

$levels = [];
$points = [];
while ($row = $chart_data->fetch_assoc()) {
    $levels[] = $row['E_level'];
    $points[] = $row['level_points'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit Dashboard - MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .semester-select {
            min-width: 300px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .analytics-card {
            text-align: center;
            padding: 20px;
        }
        .analytics-card h2 {
            color: #0d6efd;
            font-size: 2rem;
            margin: 10px 0;
        }
        .table-responsive {
            padding: 10px;
        }
        .qr-button {
            background-color: #0d6efd;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .qr-button:hover {
            background-color: #0b5ed7;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Header with Title and Semester Selection -->
        <div class="header-container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h1 class="header-title">Student Dashboard</h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <form method="POST" id="semesterForm" class="mb-0">
                    <select name="semester" class="semester-select" onchange="this.form.submit()">
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo $semester; ?>" 
                                    <?php echo ($semester === $selected_semester) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($semester); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="generate-qr.php" class="qr-button">
                    <i class="fas fa-qrcode"></i>
                    Generate QR
                </a>
            </div>
        </div>


        <!-- Analytics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card analytics-card">
                    <h5>Total Events</h5>
                    <h2><?php echo $analytics['total_events']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card analytics-card">
                    <h5>Unique Roles</h5>
                    <h2><?php echo $analytics['unique_roles']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card analytics-card">
                    <h5>Total Merit</h5>
                    <h2><?php echo $analytics['total_merit']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card analytics-card">
                    <h5>Average Merit</h5>
                    <h2><?php echo number_format($analytics['avg_merit'], 1); ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Merit Points by Event Level</h5>
                        <canvas id="levelChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Role Distribution</h5>
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Event Participation</h5>
                            <div class="col-md-4">
                                <form method="GET" class="mb-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search events..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Level</th>
                                        <th>Role</th>
                                        <th>Merit Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($event = $events_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['E_name']); ?></td>
                                        <td><?php echo htmlspecialchars($event['E_level']); ?></td>
                                        <td><?php echo htmlspecialchars($event['MC_role']); ?></td>
                                        <td><?php echo htmlspecialchars($event['MD_meritPoint']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Role Summary</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Events Participated</th>
                                        <th>Total Merit Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($role = $role_summary->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($role['MC_role']); ?></td>
                                        <td><?php echo htmlspecialchars($role['event_count']); ?></td>
                                        <td><?php echo htmlspecialchars($role['total_points']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Level Chart
    const levelChart = new Chart(document.getElementById('levelChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($levels); ?>,
            datasets: [{
                label: 'Merit Points',
                data: <?php echo json_encode($points); ?>,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Role Distribution Chart
    const roleData = {
        labels: <?php 
            $role_labels = [];
            $role_data = [];
            $role_summary->data_seek(0);
            while ($role = $role_summary->fetch_assoc()) {
                $role_labels[] = $role['MC_role'];
                $role_data[] = $role['total_points'];
            }
            echo json_encode($role_labels);
        ?>,
        datasets: [{
            data: <?php echo json_encode($role_data); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    };

    new Chart(document.getElementById('roleChart'), {
        type: 'pie',
        data: roleData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>