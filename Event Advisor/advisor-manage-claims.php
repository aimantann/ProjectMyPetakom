<?php
session_start();
include("includes/dbconnection.php");

// Get current time and user
$current_time = "2025-06-02 08:59:52"; // UTC time
$current_user = "roycakoi";

// Dummy events for display
$dummy_events = [
    1 => 'Programming Competition 2024',
    2 => 'Tech Talk: AI in Web Development',
    3 => 'Hackathon 2024',
    4 => 'Web Design Workshop',
    5 => 'Cybersecurity Seminar',
    6 => 'Mobile App Development Course',
    7 => 'Database Management Workshop',
    8 => 'UI/UX Design Competition',
    9 => 'Cloud Computing Seminar',
    10 => 'Data Science Workshop'
];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $claim_id = intval($_POST['claim_id']);
    $action = $_POST['action'];
    $merit_points = isset($_POST['merit_points']) ? intval($_POST['merit_points']) : 0;
    
    if ($action === 'approve') {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Get the U_userID from the meritclaim table first
            $get_user_query = "SELECT E_eventID, U_userID FROM meritclaim WHERE MC_claimID = ?";
            $stmt = mysqli_prepare($conn, $get_user_query);
            mysqli_stmt_bind_param($stmt, "i", $claim_id);
            mysqli_stmt_execute($stmt);
            $claim_result = mysqli_stmt_get_result($stmt);
            $claim_data = mysqli_fetch_assoc($claim_result);

            if (!$claim_data) {
                throw new Exception("Claim not found");
            }

            // 1. Update claim status in meritclaim table
            $update_query = "UPDATE meritclaim 
                           SET MC_claimStatus = 'Approved' 
                           WHERE MC_claimID = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $claim_id);
            mysqli_stmt_execute($stmt);

            // 2. Insert into meritawarded table with U_userID
            $award_query = "INSERT INTO meritawarded 
                          (MD_totalMerit, E_eventID, U_userID) 
                          VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $award_query);
            mysqli_stmt_bind_param($stmt, "iis", 
                $merit_points,
                $claim_data['E_eventID'],
                $claim_data['U_userID']
            );
            mysqli_stmt_execute($stmt);

            // Commit transaction
            mysqli_commit($conn);
            $_SESSION['success_message'] = "Claim has been approved and merit points awarded successfully!";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Error processing claim: " . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        // Update claim status to Rejected
        $update_query = "UPDATE meritclaim 
                        SET MC_claimStatus = 'Rejected' 
                        WHERE MC_claimID = ?";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $claim_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Claim has been rejected successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to reject claim: " . mysqli_error($conn);
        }
    }
    
    // Redirect to same page to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Update the fetch query to include U_userID
$query = "SELECT * FROM meritclaim WHERE MC_claimStatus = 'Pending' ORDER BY MC_submitDate DESC";
$result = mysqli_query($conn, $query);


// Fetch only pending claims
$query = "SELECT * FROM meritclaim WHERE MC_claimStatus = 'Pending' ORDER BY MC_submitDate DESC";
$result = mysqli_query($conn, $query);

// Merit points based on role
$merit_points_by_role = [
    'Participant' => 30,
    'Committee' => 50,
    'Main Committee' => 70
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Merit Claims - Event Advisor</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Header with User Info and Timestamp -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <a href="advisor-dashboard.php" class="btn btn-primary me-3">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <h1 class="h3 mb-0">Manage Merit Claims</h1>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($current_user); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i> <?php echo $current_time; ?> UTC
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Claims Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Pending Merit Claims</h5>
            </div>
            <div class="card-body p-0">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Claim ID</th>
                                    <th scope="col">Event Name</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Submit Date</th>
                                    <th scope="col">Document</th>
                                    <th scope="col">Merit Points</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['MC_claimID']; ?></td>
                                        <td><?php echo htmlspecialchars($dummy_events[$row['E_eventID']] ?? 'Unknown Event'); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php 
                                                echo $row['MC_role'] === 'Main Committee' ? 'bg-success' : 
                                                    ($row['MC_role'] === 'Committee' ? 'bg-primary' : 'bg-info'); 
                                            ?>">
                                                <?php echo htmlspecialchars($row['MC_role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['MC_submitDate'])); ?></td>
                                        <td>
                                            <?php if($row['MC_documentPath']): ?>
                                                <a href="<?php echo htmlspecialchars($row['MC_documentPath']); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-alt me-1"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No document</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $merit_points_by_role[$row['MC_role']] ?? '0'; ?> pts
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-success" 
                                                        onclick="approveClaim(<?php echo $row['MC_claimID']; ?>, '<?php echo $row['MC_role']; ?>', <?php echo $merit_points_by_role[$row['MC_role']] ?? '0'; ?>)">
                                                    <i class="fas fa-check me-1"></i> Approve
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        onclick="rejectClaim(<?php echo $row['MC_claimID']; ?>)">
                                                    <i class="fas fa-times me-1"></i> Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No pending merit claims found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Approve Merit Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approvalForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="claim_id" id="approve-claim-id">
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" id="approve-role" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="merit_points" class="form-label">Merit Points to Award</label>
                            <input type="number" name="merit_points" id="merit_points" class="form-control" min="1" required>
                        </div>
                        
                        <p class="mb-0"><strong>Are you sure you want to approve this claim?</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Reject Merit Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectionForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="claim_id" id="reject-claim-id">
                        
                        <p><strong>Are you sure you want to reject this claim?</strong></p>
                        <p class="text-muted mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap modals
        const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));

        function approveClaim(claimId, role, meritPoints) {
            document.getElementById('approve-claim-id').value = claimId;
            document.getElementById('approve-role').value = role;
            document.getElementById('merit_points').value = meritPoints;
            approvalModal.show();
        }

        function rejectClaim(claimId) {
            document.getElementById('reject-claim-id').value = claimId;
            rejectionModal.show();
        }
    </script>
</body>
</html>