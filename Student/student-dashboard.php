<?php
session_start();
include('includes/header.php');
include('includes/dbconnection.php');

// Security checks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Dummy data for previous semesters
$dummy_data = [
    'Semester 1 2024' => [
        'total_merit' => 130,
        'event_count' => 3,
        'roles' => [
            'Main Committee' => 1,
            'Committee' => 1,
            'Participant' => 1
        ],
        'levels' => [
            'National' => 1,
            'UMPSA' => 1,
            'State' => 1
        ]
    ],
    'Semester 2 2024' => [
        'total_merit' => 60,
        'event_count' => 2,
        'roles' => [
            'Main Committee' => 1,
            'Committee' => 1
        ],
        'levels' => [
            'District' => 1,
            'UMPSA' => 1
        ]
    ]
];

// Available semesters (including dummy and current)
$available_semesters = ['Semester 1 2024', 'Semester 2 2024', 'Semester 3 2025'];

// Get selected semester or default to current
$selected_semester = isset($_POST['semester']) ? $_POST['semester'] : 'Semester 3 2025';

// Get data for selected semester
if ($selected_semester === 'Semester 3 2025') {
    // Use real data for current semester
    $merit_query = "SELECT 
        SUM(md.MD_meritPoint) as total_merit,
        COUNT(*) as event_count
    FROM meritawarded md
    WHERE md.U_userID = ? 
    AND MONTH(md.MD_awardedDate) BETWEEN 5 AND 8
    AND YEAR(md.MD_awardedDate) = 2025";

    $stmt = $conn->prepare($merit_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $merit_result = $stmt->get_result();
    $merit_data = $merit_result->fetch_assoc();

    // Get real role distribution
    $role_query = "SELECT 
        mc.MC_role as role,
        COUNT(*) as count
    FROM meritclaim mc
    JOIN meritawarded md ON mc.E_eventID = md.E_eventID AND mc.U_userID = md.U_userID
    WHERE mc.U_userID = ? 
    AND mc.MC_claimStatus = 'Approved'
    AND MONTH(md.MD_awardedDate) BETWEEN 5 AND 8
    AND YEAR(md.MD_awardedDate) = 2025
    GROUP BY mc.MC_role";

    $stmt = $conn->prepare($role_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $role_data = [];
    $role_labels = [];
    while ($row = $role_result->fetch_assoc()) {
        $role_labels[] = $row['role'];
        $role_data[] = $row['count'];
    }

    // Get real event level distribution
    $level_query = "SELECT 
        e.E_level as level,
        COUNT(*) as count
    FROM meritclaim mc
    JOIN event e ON mc.E_eventID = e.E_eventID
    JOIN meritawarded md ON mc.E_eventID = md.E_eventID AND mc.U_userID = md.U_userID
    WHERE mc.U_userID = ? 
    AND mc.MC_claimStatus = 'Approved'
    AND MONTH(md.MD_awardedDate) BETWEEN 5 AND 8
    AND YEAR(md.MD_awardedDate) = 2025
    GROUP BY e.E_level";

    $stmt = $conn->prepare($level_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $level_result = $stmt->get_result();
    $level_labels = [];
    $level_data = [];
    while ($row = $level_result->fetch_assoc()) {
        $level_labels[] = $row['level'];
        $level_data[] = $row['count'];
    }
} else {
    // Use dummy data for previous semesters
    $semester_data = $dummy_data[$selected_semester];
    $merit_data = [
        'total_merit' => $semester_data['total_merit'],
        'event_count' => $semester_data['event_count']
    ];
    
    $role_labels = array_keys($semester_data['roles']);
    $role_data = array_values($semester_data['roles']);
    
    $level_labels = array_keys($semester_data['levels']);
    $level_data = array_values($semester_data['levels']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - MyPetakom</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            min-height: 100vh;
        }

        .dashboard-container {
            padding: 10px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-size: 20px;
            color: #333;
            font-weight: bold;
        }

        .semester-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .stat-card {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }

        .charts-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .chart-box {
            flex: 1;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            text-align: center;
        }

        canvas {
            width: 100% !important;
            height: 200px !important;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Student Dashboard</h1>
            <form method="post" id="semesterForm">
                <select name="semester" class="semester-select" onchange="this.form.submit()">
                    <?php foreach ($available_semesters as $semester): ?>
                        <option value="<?php echo $semester; ?>" <?php echo ($semester === $selected_semester) ? 'selected' : ''; ?>>
                            <?php echo $semester; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo isset($merit_data['total_merit']) ? $merit_data['total_merit'] : 0; ?></div>
                <div class="stat-label">Total Merit Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo isset($merit_data['event_count']) ? $merit_data['event_count'] : 0; ?></div>
                <div class="stat-label">Events Participated</div>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-box">
                <div class="chart-title">Merit Points Distribution</div>
                <canvas id="meritChart"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">Participation by Role</div>
                <canvas id="roleChart"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">Events by Level</div>
                <canvas id="levelChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('meritChart'), {
            type: 'bar',
            data: {
                labels: ['Merit Points'],
                datasets: [{
                    data: [<?php echo isset($merit_data['total_merit']) ? $merit_data['total_merit'] : 0; ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('roleChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($role_labels ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($role_data ?? []); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('levelChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($level_labels ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($level_data ?? []); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>