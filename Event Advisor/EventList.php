<?php
session_start();
require_once 'includes/dbconnection.php';
include('includes/header.php');

// Fetch all events
$sql = "SELECT * FROM event ORDER BY E_startDate DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.2s;
            border-left: 4px solid #007bff;
        }
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.8em;
            padding: 0.25em 0.5em;
        }
        .btn-group-actions {
            gap: 0.25rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-calendar-alt me-2"></i>Event System</a>
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['username'])): ?>
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php endif; ?>
               
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-list me-2"></i>All Events</h2>
                    <a href="EventRegistrationForm.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add New Event
                    </a>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message'], $_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($event = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card event-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($event['E_name']); ?></h5>
                                            <span class="badge status-badge <?php 
                                                switch($event['E_eventStatus']) {
                                                    case 'Active': echo 'bg-success'; break;
                                                    case 'Postponed': echo 'bg-warning'; break;
                                                    case 'Cancelled': echo 'bg-danger'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars($event['E_eventStatus']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-2">
                                            <?php echo htmlspecialchars(substr($event['E_description'], 0, 100)) . '...'; ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y', strtotime($event['E_startDate'])); ?>
                                                to <?php echo date('M d, Y', strtotime($event['E_endDate'])); ?>
                                            </small><br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($event['E_geoLocation']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex btn-group-actions">
                                            <a href="check_event.php?id=<?php echo $event['E_eventID']; ?>" 
                                               class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_event.php?id=<?php echo $event['E_eventID']; ?>" 
                                               class="btn btn-outline-warning btn-sm flex-fill">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button onclick="deleteEvent(<?php echo $event['E_eventID']; ?>)" 
                                                    class="btn btn-outline-danger btn-sm flex-fill">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                            <a href="QRevent.php?id=<?php echo $event['E_eventID']; ?>" 
                                                    class="btn btn-outline-dark btn-sm flex-fill">
                                                    <i class="fas fa-qrcode"></i> QR Code
                                            </a>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Events Found</h4>
                                <p class="text-muted">There are currently no events in the system.</p>
                                <a href="EventRegistrationForm.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create New Event
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this event? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Store the event ID to delete
    let eventIdToDelete = null;
    
    // Function to set up deletion (called when delete button is clicked)
    function deleteEvent(eventId) {
        eventIdToDelete = eventId;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
    
    // Confirm deletion handler
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (eventIdToDelete) {
            // Submit the deletion request
            window.location.href = 'delete_event.php?id=' + eventIdToDelete;
        }
    });
</script>

<?php
include('includes/footer.php');
?>

</body>
</html>

<?php $conn->close(); ?>