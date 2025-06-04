<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include('includes/header.php');
include("includes/dbconnection.php");

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Get the current user's ID from session
$user_id = $_SESSION['user_id'];

// Merit points calculation based on event level and role
function calculateMeritPoints($eventLevel, $role) {
    $meritTable = [
        'International' => [
            'Main Committee' => 100,
            'Committee' => 70,
            'Participant' => 50
        ],
        'National' => [
            'Main Committee' => 80,
            'Committee' => 50,
            'Participant' => 40
        ],
        'State' => [
            'Main Committee' => 60,
            'Committee' => 40,
            'Participant' => 30
        ],
        'District' => [
            'Main Committee' => 40,
            'Committee' => 30,
            'Participant' => 15
        ],
        'UMPSA' => [
            'Main Committee' => 30,
            'Committee' => 20,
            'Participant' => 5
        ]
    ];

    return isset($meritTable[$eventLevel][$role]) ? $meritTable[$eventLevel][$role] : 0;
}

// Get user's merit data with event details
$query = "SELECT 
    e.E_name as event_name,
    e.E_level as event_level,
    mc.MC_role as role,
    ma.MD_totalMerit as merit_awarded,
    mc.MC_submitDate as date_awarded
FROM meritawarded ma
JOIN event e ON ma.E_eventID = e.E_eventID
JOIN meritclaim mc ON ma.E_eventID = mc.E_eventID AND ma.U_userID = mc.U_userID
WHERE ma.U_userID = ?
ORDER BY mc.MC_submitDate DESC";

try {
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['U_userID']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all merit records
    $merits = [];
    while ($row = $result->fetch_assoc()) {
        $merits[] = [
            'event_name' => $row['event_name'],
            'event_level' => $row['event_level'],
            'role' => $row['role'],
            'merit_awarded' => $row['merit_awarded'],
            'date_awarded' => $row['date_awarded'],
            'calculated_merit' => calculateMeritPoints($row['event_level'], $row['role'])
        ];
    }

    // Calculate total merits
    $total_query = "SELECT COALESCE(SUM(MD_totalMerit), 0) as total 
                    FROM meritawarded 
                    WHERE U_userID = ?";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->bind_param("i", $_SESSION['U_userID']);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_merits = $total_row['total'];

    // Close statements
    $stmt->close();
    $total_stmt->close();

} catch (Exception $e) {
    $error_message = "Error retrieving merit data: " . $e->getMessage();
}

// Merit points reference table data
$meritTableData = [
    'headers' => ['Event Level', 'Main Committee', 'Committee', 'Participant'],
    'rows' => [
        ['International', 100, 70, 50],
        ['National', 80, 50, 40],
        ['State', 60, 40, 30],
        ['District', 40, 30, 15],
        ['UMPSA', 30, 20, 5]
    ]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Merit - MyPetakom</title>
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
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 28px;
            color: #333;
            font-weight: 600;
        }

        .merit-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .total-merits {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .merit-label {
            font-size: 16px;
            opacity: 0.9;
        }

        .merit-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .merit-table {
            width: 100%;
            border-collapse: collapse;
        }

        .merit-table th,
        .merit-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .merit-table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .merit-table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-main {
            background-color: #28a745;
            color: white;
        }

        .role-committee {
            background-color: #007bff;
            color: white;
        }

        .role-participant {
            background-color: #ffc107;
            color: #333;
        }

        .merit-points {
            font-weight: bold;
            color: #28a745;
            font-size: 18px;
        }

        .no-merits {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
        }

        /* Merit Points Reference Table styles */
        .merit-points-table {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 30px;
        }

        .merit-points-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .merit-points-table th,
        .merit-points-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .merit-points-table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .merit-points-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .merit-points-table td:first-child {
            font-weight: 600;
            text-align: left;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .merit-table,
            .merit-points-table {
                font-size: 14px;
            }

            .merit-table th,
            .merit-table td,
            .merit-points-table th,
            .merit-points-table td {
                padding: 10px 8px;
            }

            .total-merits {
                font-size: 28px;
            }

            .role-badge {
                padding: 3px 8px;
                font-size: 11px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header with Title -->
        <div class="header">
            <h1 class="page-title">View Merit</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Merit Summary -->
        <div class="merit-summary">
            <div class="total-merits"><?php echo $total_merits; ?></div>
            <div class="merit-label">Total Merit Points Earned</div>
        </div>

        <!-- Merit History Table -->
        <div class="merit-table-container">
            <div class="table-header">
                <h2 class="table-title">Merit History</h2>
            </div>

            <?php if (!empty($merits)): ?>
                <table class="merit-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Level</th>
                            <th>Role</th>
                            <th>Merit Points</th>
                            <th>Date Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($merits as $merit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($merit['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($merit['event_level']); ?></td>
                                <td>
                                    <span class="role-badge <?php 
                                        echo $merit['role'] === 'Main Committee' ? 'role-main' : 
                                             ($merit['role'] === 'Committee' ? 'role-committee' : 'role-participant'); 
                                    ?>">
                                        <?php echo htmlspecialchars($merit['role']); ?>
                                    </span>
                                </td>
                                <td class="merit-points"><?php echo $merit['merit_awarded']; ?></td>
                                <td><?php echo date('d M Y', strtotime($merit['date_awarded'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-merits">
                    <i class="fas fa-trophy" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No merits earned yet. Participate in events to earn merit points!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Merit Points Reference Table -->
        <div class="merit-points-table">
            <div class="table-header">
                <h2 class="table-title">Merit Points Reference Table</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Event Level</th>
                        <th>Main Committee</th>
                        <th>Committee</th>
                        <th>Participant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>International</td>
                        <td>100</td>
                        <td>70</td>
                        <td>50</td>
                    </tr>
                    <tr>
                        <td>National</td>
                        <td>80</td>
                        <td>50</td>
                        <td>40</td>
                    </tr>
                    <tr>
                        <td>State</td>
                        <td>60</td>
                        <td>40</td>
                        <td>30</td>
                    </tr>
                    <tr>
                        <td>District</td>
                        <td>40</td>
                        <td>30</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td>UMPSA</td>
                        <td>30</td>
                        <td>20</td>
                        <td>5</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php include('includes/footer.php'); ?>
</body>
</html>