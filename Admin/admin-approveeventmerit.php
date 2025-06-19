<?php
require_once('user-validatesession.php');

include 'includes/header.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "jazz123";
$dbname = "mypetakom_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $applicationId = $_POST['application_id'];
    $action = $_POST['action'];
    $adminName = $_POST['admin_name']; // In real app, get from session
    
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE meritapplication SET MA_meritAppStatus = 'Approved', MA_approvedBy = ? WHERE MA_applicationID = ?");
            $stmt->execute([$adminName, $applicationId]);
            $success_message = "Merit application approved successfully!";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE meritapplication SET MA_meritAppStatus = 'Rejected', MA_approvedBy = ? WHERE MA_applicationID = ?");
            $stmt->execute([$adminName, $applicationId]);
            $success_message = "Merit application rejected successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch all merit applications with event details
$query = "SELECT ma.*, e.E_name, e.E_description, e.E_startDate, e.E_endDate, 
                 e.E_geoLocation, e.E_eventStatus, e.E_level
          FROM meritapplication ma 
          JOIN event e ON ma.E_eventID = e.E_eventID 
          ORDER BY 
            CASE 
              WHEN ma.MA_meritAppStatus = 'Pending' THEN 1
              WHEN ma.MA_meritAppStatus = 'Approved' THEN 2
              WHEN ma.MA_meritAppStatus = 'Rejected' THEN 3
            END,
            ma.MA_applicationID DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsQuery = "SELECT 
                  SUM(CASE WHEN MA_meritAppStatus = 'Pending' THEN 1 ELSE 0 END) as pending,
                  SUM(CASE WHEN MA_meritAppStatus = 'Approved' THEN 1 ELSE 0 END) as approved,
                  SUM(CASE WHEN MA_meritAppStatus = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                  COUNT(*) as total
               FROM meritapplication";
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Merit Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-bg {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 2rem 0;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .btn-approve {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
        }
        .btn-approve:hover {
            background: linear-gradient(45deg, #218838, #1aa179);
            color: white;
        }
        .btn-reject {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            color: white;
        }
        .btn-reject:hover {
            background: linear-gradient(45deg, #c82333, #bd2130);
            color: white;
        }
        .status-pending { background: #ffc107 !important; }
        .status-approved { background: #28a745 !important; }
        .status-rejected { background: #dc3545 !important; }
        .priority-row {
            background: rgba(255, 193, 7, 0.1);
            border-left: 4px solid #ffc107;
        }
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-light">

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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card text-center border-warning">
                    <div class="card-body">
                        <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                        <h3 class="text-warning"><?php echo $stats['pending']; ?></h3>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center border-success">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="text-success"><?php echo $stats['approved']; ?></h3>
                        <p class="text-muted mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center border-danger">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h3 class="text-danger"><?php echo $stats['rejected']; ?></h3>
                        <p class="text-muted mb-0">Rejected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center border-primary">
                    <div class="card-body">
                        <i class="fas fa-list-alt fa-2x text-primary mb-2"></i>
                        <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                        <p class="text-muted mb-0">Total</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Merit Application Requests</h4>
                <span class="badge bg-light text-primary"><?php echo count($applications); ?> Applications</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($applications) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>App ID</th>
                                    <th>Event Name</th>
                                    <th>Event Details</th>
                                    <th>Applied By</th>
                                    <th>Date Range</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr class="<?php echo $app['MA_meritAppStatus'] === 'Pending' ? 'priority-row' : ''; ?>">
                                        <td><strong>#<?php echo htmlspecialchars($app['MA_applicationID']); ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($app['E_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">Event ID: <?php echo $app['E_eventID']; ?></small>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="mb-1">
                                                    <strong>Description:</strong><br>
                                                    <?php echo htmlspecialchars(substr($app['E_description'], 0, 80)) . (strlen($app['E_description']) > 80 ? '...' : ''); ?>
                                                </div>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($app['E_level']); ?></span>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($app['E_eventStatus']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-user text-primary me-1"></i>
                                            <?php echo htmlspecialchars($app['MA_appliedBy']); ?>
                                        </td>
                                        <td class="small">
                                            <div><strong>Start:</strong> <?php echo date('M d, Y', strtotime($app['E_startDate'])); ?></div>
                                            <div><strong>End:</strong> <?php echo date('M d, Y', strtotime($app['E_endDate'])); ?></div>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                            <small><?php echo htmlspecialchars($app['E_geoLocation']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo strtolower($app['MA_meritAppStatus']); ?> px-3 py-2">
                                                <?php 
                                                $statusIcon = '';
                                                switch($app['MA_meritAppStatus']) {
                                                    case 'Pending': $statusIcon = 'fas fa-clock'; break;
                                                    case 'Approved': $statusIcon = 'fas fa-check'; break;
                                                    case 'Rejected': $statusIcon = 'fas fa-times'; break;
                                                }
                                                ?>
                                                <i class="<?php echo $statusIcon; ?> me-1"></i>
                                                <?php echo htmlspecialchars($app['MA_meritAppStatus']); ?>
                                            </span>
                                        </td>
                                        <td class="small">
                                            <?php echo $app['MA_approvedBy'] ? htmlspecialchars($app['MA_approvedBy']) : '-'; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($app['MA_meritAppStatus'] === 'Pending'): ?>
                                                <div class="btn-group" role="group">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="application_id" value="<?php echo $app['MA_applicationID']; ?>">
                                                        <input type="hidden" name="admin_name" value="Admin"> <!-- In real app, get from session -->
                                                        <button type="submit" name="action" value="approve" 
                                                                class="btn btn-approve btn-sm me-1"
                                                                onclick="return confirm('Are you sure you want to approve this merit application?')">
                                                            <i class="fas fa-check me-1"></i>Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="application_id" value="<?php echo $app['MA_applicationID']; ?>">
                                                        <input type="hidden" name="admin_name" value="Admin"> <!-- In real app, get from session -->
                                                        <button type="submit" name="action" value="reject" 
                                                                class="btn btn-reject btn-sm"
                                                                onclick="return confirm('Are you sure you want to reject this merit application?')">
                                                            <i class="fas fa-times me-1"></i>Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    <i class="fas fa-lock me-1"></i>Processed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Merit Applications</h5>
                        <p class="text-muted">No merit applications have been submitted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide success alert
        setTimeout(function() {
            var alert = document.querySelector('.alert-success');
            if (alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 4000);

        // Filter table function
        function filterTable(status) {
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const statusBadge = row.querySelector('.badge[class*="status-"]');
                    if (statusBadge && statusBadge.textContent.toLowerCase().includes(status)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }

        // Add confirmation with more details
        document.querySelectorAll('button[name="action"]').forEach(button => {
            button.addEventListener('click', function(e) {
                const action = this.value;
                const row = this.closest('tr');
                const eventName = row.querySelector('td:nth-child(2) strong').textContent;
                
                const message = action === 'approve' 
                    ? `Are you sure you want to APPROVE the merit application for "${eventName}"?`
                    : `Are you sure you want to REJECT the merit application for "${eventName}"?`;
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <?php include 'includes/footer.php' ?>

</body>
</html>