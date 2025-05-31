<?php
session_start();
include("includes/dbconnection.php");

// Dummy database connection simulation
// In real implementation, replace with actual database connection
$dummy_merits = [
    [
        'event_name' => 'Programming Competition 2024',
        'role' => 'Main Committee',
        'merit_awarded' => 100,
        'date_awarded' => '2024-03-15'
    ],
    [
        'event_name' => 'Tech Talk: AI in Web Development',
        'role' => 'Participant',
        'merit_awarded' => 50,
        'date_awarded' => '2024-04-02'
    ],
    [
        'event_name' => 'Hackathon 2024',
        'role' => 'Committee',
        'merit_awarded' => 70,
        'date_awarded' => '2024-04-20'
    ],
    [
        'event_name' => 'Web Design Workshop',
        'role' => 'Participant',
        'merit_awarded' => 40,
        'date_awarded' => '2024-05-05'
    ],
    [
        'event_name' => 'Cybersecurity Seminar',
        'role' => 'Committee',
        'merit_awarded' => 50,
        'date_awarded' => '2024-05-12'
    ],
    [
        'event_name' => 'Mobile App Development Course',
        'role' => 'Participant',
        'merit_awarded' => 30,
        'date_awarded' => '2024-05-18'
    ]
];

// Calculate total merits
$total_merits = array_sum(array_column($dummy_merits, 'merit_awarded'));
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

        .back-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-right: 20px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #0056b3;
            text-decoration: none;
            color: white;
        }

        .back-btn i {
            margin-right: 5px;
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

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .back-btn {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .merit-table {
                font-size: 14px;
            }

            .merit-table th,
            .merit-table td {
                padding: 10px 8px;
            }

            .total-merits {
                font-size: 28px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header with Back Button and Title -->
        <div class="header">
            <a href="student-dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <h1 class="page-title">View Merit</h1>
        </div>

        <!-- Merit Summary -->
        <div class="merit-summary">
            <div class="total-merits"><?php echo $total_merits; ?></div>
            <div class="merit-label">Total Merit Points Earned</div>
        </div>

        <!-- Merit Table -->
        <div class="merit-table-container">
            <div class="table-header">
                <h2 class="table-title">Merit History</h2>
            </div>

            <?php if (!empty($dummy_merits)): ?>
                <table class="merit-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Role</th>
                            <th>Merit Awarded</th>
                            <th>Date Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dummy_merits as $merit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($merit['event_name']); ?></td>
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
    </div>
</body>
</html>