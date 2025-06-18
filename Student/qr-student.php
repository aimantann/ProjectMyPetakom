<?php
include("includes/dbconnection.php");

// Get student ID from URL and decode it
$student_id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;

if (!$student_id) {
    die("Invalid QR code");
}

// Get student information and merit summary
$query = "SELECT 
    u.U_name,
    u.U_email,
    es.ES_semester,
    SUM(md.MD_meritPoint) as semester_points
FROM user u
LEFT JOIN meritclaim mc ON u.U_userID = mc.U_userID
LEFT JOIN meritawarded md ON mc.E_eventID = md.E_eventID AND mc.U_userID = md.U_userID
LEFT JOIN event e ON mc.E_eventID = e.E_eventID
LEFT JOIN eventsemester es ON e.E_eventID = es.E_eventID
WHERE u.U_userID = ? AND mc.MC_claimStatus = 'Approved'
GROUP BY es.ES_semester
ORDER BY es.ES_semester DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$student_info = null;
$merit_summary = [];
$total_merits = 0;

while ($row = $result->fetch_assoc()) {
    if (!$student_info) {
        $student_info = [
            'name' => $row['U_name'],
            'email' => $row['U_email']
        ];
    }
    if ($row['semester_points']) {
        $merit_summary[$row['ES_semester']] = $row['semester_points'];
        $total_merits += $row['semester_points'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Merit Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .merit-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 600px;
        }
        .total-merits {
            font-size: 48px;
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
            margin: 20px 0;
        }
        .semester-breakdown {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .current-datetime {
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="merit-card">
            <h2 class="text-center mb-4">Student Merit Summary</h2>
            
            <?php if ($student_info): ?>
                <div class="text-center mb-4">
                    <h4><?php echo htmlspecialchars($student_info['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($student_info['email']); ?></p>
                </div>

                <div class="total-merits">
                    <?php echo $total_merits; ?>
                    <div class="fs-6 text-muted">Total Merit Points</div>
                </div>

                <div class="semester-breakdown">
                    <h5 class="mb-3">Semester Breakdown</h5>
                    <?php foreach ($merit_summary as $semester => $points): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($semester); ?></span>
                            <span class="fw-bold"><?php echo $points; ?> points</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="current-datetime">
                    Last Updated: <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    Student information not found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>