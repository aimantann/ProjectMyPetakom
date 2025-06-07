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

// Get user's merit data for current semester (Semester 3)
$query = "SELECT 
    e.E_name as event_name,
    e.E_level as event_level,
    mc.MC_role as role,
    md.MD_meritPoint as merit_awarded,
    md.MD_awardedDate as date_awarded
FROM meritawarded md
JOIN event e ON md.E_eventID = e.E_eventID
JOIN meritclaim mc ON md.E_eventID = mc.E_eventID 
    AND md.U_userID = mc.U_userID
WHERE md.U_userID = ? 
    AND mc.MC_claimStatus = 'Approved'
ORDER BY md.MD_awardedDate DESC";

// Add dummy data for previous semesters
$dummy_semester_data = [
    'Semester 1 2024' => [
        [
            'event_name' => 'Programming Competition',
            'event_level' => 'National',
            'role' => 'Main Committee',
            'merit_awarded' => 80,
            'date_awarded' => '2024-11-15'
        ],
        [
            'event_name' => 'Tech Workshop Series',
            'event_level' => 'UMPSA',
            'role' => 'Committee',
            'merit_awarded' => 20,
            'date_awarded' => '2024-10-20'
        ],
        [
            'event_name' => 'Cyber Security Seminar',
            'event_level' => 'State',
            'role' => 'Participant',
            'merit_awarded' => 30,
            'date_awarded' => '2024-09-05'
        ]
    ],
    'Semester 2 2024' => [
        [
            'event_name' => 'IT Career Fair',
            'event_level' => 'District',
            'role' => 'Main Committee',
            'merit_awarded' => 40,
            'date_awarded' => '2025-03-20'
        ],
        [
            'event_name' => 'Database Management Workshop',
            'event_level' => 'UMPSA',
            'role' => 'Committee',
            'merit_awarded' => 20,
            'date_awarded' => '2025-02-15'
        ]
    ]
];

// Calculate dummy semester totals
$dummy_semester_totals = [
    'Semester 1 2024' => 130, // 80 + 20 + 30
    'Semester 2 2024' => 60   // 40 + 20
];

try {
    // Get current semester (Semester 3) data
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize the merits array with dummy data
    $merits_by_semester = $dummy_semester_data;
    
    // Add current semester (Semester 3) data
    $current_semester = 'Semester 3 2025';
    $merits_by_semester[$current_semester] = [];
    $semester_totals = $dummy_semester_totals;
    $semester_totals[$current_semester] = 0;

    // Process current semester data from database
    while ($row = $result->fetch_assoc()) {
        $merits_by_semester[$current_semester][] = [
            'event_name' => $row['event_name'],
            'event_level' => $row['event_level'],
            'role' => $row['role'],
            'merit_awarded' => $row['merit_awarded'],
            'date_awarded' => $row['date_awarded']
        ];
        // Add to semester total
        $semester_totals[$current_semester] += $row['merit_awarded'];
    }

    // Calculate overall total including dummy data
    $total_merits = array_sum($semester_totals);

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $error_message = "Error retrieving merit data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Merit - MyPetakom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .semester-section {
            margin-bottom: 30px;
        }

        .table-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.btn-generate-qr {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.btn-generate-qr:hover {
    background-color: #0056b3;
}

.popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.popup-content {
    position: relative;
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    width: 300px;
    border-radius: 10px;
    text-align: center;
}

.close {
    position: absolute;
    right: 10px;
    top: 5px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.close:hover {
    color: #333;
}

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .merit-table th,
            .merit-table td {
                padding: 10px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Title -->
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">View Merit</h1>
                <button id="generateQRBtn" class="btn-generate-qr">
                    <i class="fas fa-qrcode"></i> Generate QR
                </button>
            </div>
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
            <div class="merit-label">Total Cumulative Merit Points</div>
        </div>

        <!-- Merit History Table -->
        <div class="merit-table-container">
            <?php if (!empty($merits_by_semester)): ?>
                <?php 
                // Display semesters in reverse chronological order
                krsort($merits_by_semester);
                foreach ($merits_by_semester as $semester => $semester_merits): 
                ?>
                    <div class="semester-section">
                        <div class="table-header">
                            <h2 class="table-title"><?php echo htmlspecialchars($semester); ?></h2>
                            <div class="semester-total">Total: <?php echo $semester_totals[$semester]; ?> points</div>
                        </div>
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
                                <?php foreach ($semester_merits as $merit): ?>
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
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-merits">
                    <i class="fas fa-trophy" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No merits earned yet. Participate in events to earn merit points!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.getElementById('generateQRBtn').addEventListener('click', function() {
        window.open('generate-qr.php', 'QR Code', 'width=400,height=400');
    });
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>