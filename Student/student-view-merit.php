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
$available_semesters = [
    "SEMESTER II ACADEMIC SESSION 2024/2025",
    "SEMESTER I ACADEMIC SESSION 2024/2025",
    "SEMESTER II ACADEMIC SESSION 2023/2024",
    "SEMESTER I ACADEMIC SESSION 2023/2024",
    "SEMESTER II ACADEMIC SESSION 2022/2023",
    "SEMESTER I ACADEMIC SESSION 2022/2023"
];

// Get selected semester or default to current
$selected_semester = isset($_POST['semester']) ? $_POST['semester'] : "SEMESTER II ACADEMIC SESSION 2024/2025";

// Get merit data for selected semester
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
JOIN eventsemester es ON e.E_eventID = es.E_eventID
WHERE md.U_userID = ? 
    AND mc.MC_claimStatus = 'Approved'
    AND es.ES_semester = ?
ORDER BY md.MD_awardedDate DESC";

try {
    // Get current semester data
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $_SESSION['user_id'], $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize arrays for the selected semester
    $merits_by_semester = [];
    $merits_by_semester[$selected_semester] = [];
    $semester_total = 0;

    // Process semester data
    while ($row = $result->fetch_assoc()) {
        $merits_by_semester[$selected_semester][] = [
            'event_name' => $row['event_name'],
            'event_level' => $row['event_level'],
            'role' => $row['role'],
            'merit_awarded' => $row['merit_awarded'],
            'date_awarded' => $row['date_awarded']
        ];
        $semester_total += $row['merit_awarded'];
    }

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

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .page-title {
            font-size: 28px;
            color: #333;
            font-weight: 600;
        }

        .semester-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 300px;
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

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .merit-table th,
            .merit-table td {
                padding: 10px 8px;
                font-size: 14px;
            }

            .semester-select {
                min-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Title and Semester Selection -->
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">View Merit</h1>
                <form method="POST" id="semesterForm">
                    <select name="semester" class="semester-select" onchange="this.form.submit()">
                        <?php foreach ($available_semesters as $semester): ?>
                            <option value="<?php echo $semester; ?>" 
                                    <?php echo ($semester === $selected_semester) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($semester); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Merit Summary -->
        <div class="merit-summary">
            <div class="total-merits"><?php echo $semester_total; ?></div>
            <div class="merit-label">Total Merit Points for <?php echo htmlspecialchars($selected_semester); ?></div>
        </div>

        <!-- Merit History Table -->
        <div class="merit-table-container">
            <?php if (!empty($merits_by_semester[$selected_semester])): ?>
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
                        <?php foreach ($merits_by_semester[$selected_semester] as $merit): ?>
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
                    <p>No merits earned for <?php echo htmlspecialchars($selected_semester); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>