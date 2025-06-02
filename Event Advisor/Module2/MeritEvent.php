<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypetakom_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle merit application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_merit'])) {
    $eventId = $_POST['event_id'];
    $appliedBy = $_POST['applied_by']; // This should come from session in real application
    
    try {
        // Check if application already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM meritapplication WHERE E_eventID = ?");
        $checkStmt->execute([$eventId]);
        
        if ($checkStmt->fetchColumn() == 0) {
            // Insert new merit application
            $stmt = $pdo->prepare("INSERT INTO meritapplication (MA_meritAppStatus, MA_appliedBy, E_eventID) VALUES (?, ?, ?)");
            $stmt->execute(['Pending', $appliedBy, $eventId]);
            $success_message = "Merit Application Sent!";
        } else {
            $error_message = "Merit application already exists for this event.";
        }
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch events that haven't applied for merit yet
$query = "SELECT e.* FROM event e 
          LEFT JOIN meritapplication ma ON e.E_eventID = ma.E_eventID 
          WHERE ma.E_eventID IS NULL 
          ORDER BY e.E_startDate DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit Event Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .btn-apply {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            transition: all 0.3s;
        }
        .btn-apply:hover {
            background: linear-gradient(45deg, #218838, #1aa179);
            transform: scale(1.05);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="header-bg">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-trophy me-3"></i>Merit Event Application</h1>
                    <p class="mb-0 mt-2">Apply for merit approval for your registered events</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Events List -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i>Events Available for Merit Application</h4>
            </div>
            <div class="card-body p-0">
                <?php if (count($events) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event ID</th>
                                    <th>Event Name</th>
                                    <th>Description</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Level</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($event['E_eventID']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($event['E_name']); ?></td>
                                        <td>
                                            <span class="text-muted">
                                                <?php echo htmlspecialchars(substr($event['E_description'], 0, 50)) . (strlen($event['E_description']) > 50 ? '...' : ''); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($event['E_startDate'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($event['E_endDate'])); ?></td>
                                        <td><i class="fas fa-map-marker-alt text-primary me-1"></i><?php echo htmlspecialchars($event['E_geoLocation']); ?></td>
                                        <td>
                                            <span class="badge bg-info status-badge">
                                                <?php echo htmlspecialchars($event['E_eventStatus']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary status-badge">
                                                <?php echo htmlspecialchars($event['E_level']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="event_id" value="<?php echo $event['E_eventID']; ?>">
                                                <input type="hidden" name="applied_by" value="Event Advisor"> <!-- In real app, get from session -->
                                                <button type="submit" name="apply_merit" class="btn btn-apply btn-sm" 
                                                        onclick="return confirm('Are you sure you want to apply for merit approval for this event?')">
                                                    <i class="fas fa-paper-plane me-1"></i>Apply for Merit
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Events Available</h5>
                        <p class="text-muted">All registered events have already applied for merit approval or no events are registered yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                        <h4><?php echo count($events); ?></h4>
                        <p class="text-muted mb-0">Events Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                        <h4>
                            <?php 
                            $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM meritapplication WHERE MA_meritAppStatus = 'Pending'");
                            $pendingStmt->execute();
                            echo $pendingStmt->fetchColumn();
                            ?>
                        </h4>
                        <p class="text-muted mb-0">Pending Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4>
                            <?php 
                            $approvedStmt = $pdo->prepare("SELECT COUNT(*) FROM meritapplication WHERE MA_meritAppStatus = 'Approved'");
                            $approvedStmt->execute();
                            echo $approvedStmt->fetchColumn();
                            ?>
                        </h4>
                        <p class="text-muted mb-0">Approved Applications</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-hide success alert -->
    <script>
        setTimeout(function() {
            var alert = document.querySelector('.alert-success');
            if (alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000);
    </script>
</body>
</html>