<?php
ob_start(); // Start output buffering
session_start();
include('includes/header.php');
include('includes/dbconnection.php');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mypetakom_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle merit application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_merit'])) {
    $eventId = $_POST['event_id'];
    $appliedBy = $_POST['applied_by'];

    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM meritapplication WHERE E_eventID = ?");
        $checkStmt->execute([$eventId]);

        if ($checkStmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO meritapplication (MA_meritAppStatus, MA_appliedBy, E_eventID) VALUES (?, ?, ?)");
            $stmt->execute(['Pending', $appliedBy, $eventId]);
            $_SESSION['success_message'] = "Merit Application Sent!";
        } else {
            $_SESSION['error_message'] = "Merit application already exists for this event.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header("Location: MeritEvent.php");
    exit();
}

// Fetch available events
$query = "SELECT e.* FROM event e 
          LEFT JOIN meritapplication ma ON e.E_eventID = ma.E_eventID 
          WHERE ma.E_eventID IS NULL 
          ORDER BY e.E_startDate DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>List of Events</h5>
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
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($event['E_eventStatus']); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($event['E_level']); ?></span></td>
                                    <td class="text-center">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['E_eventID']; ?>">
                                            <input type="hidden" name="applied_by" value="Event Advisor">
                                            <button type="submit" name="apply_merit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Are you sure you want to apply for merit approval for this event?')">
                                                <i class="fas fa-paper-plane me-1"></i>Apply
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                    <h5 class="text-muted">No Events Available</h5>
                    <p class="text-muted">All registered events have applied for merit or none exist.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                    <h4><?php echo count($events); ?></h4>
                    <p class="text-muted mb-0">Events Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center mb-3">
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
            <div class="card text-center mb-3">
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

<?php 
include('includes/footer.php');
ob_end_flush(); // End output buffering
?>
