<?php
session_start();
include('includes/header.php');
include('includes/dbconnection.php');

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid event ID";
    $_SESSION['message_type'] = "danger";
    header("Location: EventList.php");
    exit();
}

$event_id = (int)$_GET['id'];

// Fetch event details
$sql = "SELECT * FROM event WHERE E_eventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Event not found";
    $_SESSION['message_type'] = "danger";
    header("Location: EventList.php");
    exit();
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event - <?php echo htmlspecialchars($event['E_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .event-detail {
            margin-bottom: 1.5rem;
        }
        .event-detail-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-calendar-alt me-2"></i>Event System</a>
            <div class="navbar-nav ms-auto">
                
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="event-header text-center">
                    <h2><?php echo htmlspecialchars($event['E_name']); ?></h2>
                    <span class="badge bg-<?php 
                        switch($event['E_eventStatus']) {
                            case 'Active': echo 'success'; break;
                            case 'Postponed': echo 'warning'; break;
                            case 'Cancelled': echo 'danger'; break;
                            default: echo 'secondary';
                        }
                    ?>">
                        <?php echo htmlspecialchars($event['E_eventStatus']); ?>
                    </span>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="event-detail">
                            <h5 class="event-detail-label">Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($event['E_description'])); ?></p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 event-detail">
                                <h5 class="event-detail-label">Start Date</h5>
                                <p><?php echo date('F j, Y', strtotime($event['E_startDate'])); ?></p>
                            </div>
                            <div class="col-md-6 event-detail">
                                <h5 class="event-detail-label">End Date</h5>
                                <p><?php echo date('F j, Y', strtotime($event['E_endDate'])); ?></p>
                            </div>
                        </div>

                        <div class="event-detail">
                            <h5 class="event-detail-label">Location</h5>
                            <p><?php echo htmlspecialchars($event['E_geoLocation']); ?></p>
                        </div>

                        <!-- Added Event Level Display -->
                        <div class="event-detail">
                            <h5 class="event-detail-label">Event Level</h5>
                            <p>
                                <?php 
                                $level_badge_class = '';
                                switch($event['E_level']) {
                                    case 'International': $level_badge_class = 'bg-danger'; break;
                                    case 'National': $level_badge_class = 'bg-warning text-dark'; break;
                                    case 'State': $level_badge_class = 'bg-info'; break;
                                    case 'District': $level_badge_class = 'bg-primary'; break;
                                    case 'UMPSA': $level_badge_class = 'bg-secondary'; break;
                                    default: $level_badge_class = 'bg-light text-dark';
                                }
                                ?>
                                <span class="badge <?php echo $level_badge_class; ?>">
                                    <i class="fas fa-layer-group me-1"></i>
                                    <?php echo htmlspecialchars($event['E_level']); ?>
                                </span>
                            </p>
                        </div>

                        <?php if (!empty($event['E_approvalLetter'])): ?>
                        <div class="event-detail">
                            <h5 class="event-detail-label">Approval Letter</h5>
                            <a href="<?php echo htmlspecialchars($event['E_approvalLetter']); ?>" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-file-download me-2"></i>Download
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <a href="EventList.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                    <a href="edit_event.php?id=<?php echo $event['E_eventID']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
include('includes/footer.php');
?>
</body>
</html>

<?php $conn->close(); ?>