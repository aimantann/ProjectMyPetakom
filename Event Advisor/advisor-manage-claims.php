<?php
session_start();
include("includes/dbconnection.php");

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $claim_id = intval($_POST['claim_id']);
    $action = $_POST['action'];
    $merit_points = isset($_POST['merit_points']) ? intval($_POST['merit_points']) : 0;
    
    if ($action === 'approve') {
        $update_query = "UPDATE meritclaim SET MC_claimStatus = 'Approved' WHERE MC_claimID = $claim_id";
        
        if (mysqli_query($conn, $update_query)) {
            $award_query = "INSERT INTO meritawarded (MC_claimID, MD_totalMerit) VALUES ($claim_id, $merit_points)";
            
            if (mysqli_query($conn, $award_query)) {
                $success_message = "Claim approved successfully and merit points awarded!";
            } else {
                $error_message = "Claim approved but failed to award merit points: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Failed to approve claim: " . mysqli_error($conn);
        }
    } elseif ($action === 'reject') {
        $update_query = "UPDATE meritclaim SET MC_claimStatus = 'Rejected' WHERE MC_claimID = $claim_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Claim rejected successfully!";
        } else {
            $error_message = "Failed to reject claim: " . mysqli_error($conn);
        }
    }
}

// Fetch all claims
$query = "SELECT * FROM meritclaim ORDER BY MC_submitDate DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex align-items-center bg-white p-3 rounded shadow-sm">
                    <a href="advisor-dashboard.php" class="btn btn-primary me-3">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <h1 class="h3 mb-0">Manage Merit Claims</h1>
                    <div class="ms-auto text-muted">
                        <small>Current User: <?php echo htmlspecialchars($_SESSION['username'] ?? 'roycakoi'); ?></small><br>
                        <small>UTC: <?php echo date('Y-m-d H:i:s'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <?php
        $stats = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
        if ($result) {
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)) {
                if (isset($stats[$row['MC_claimStatus']])) {
                    $stats[$row['MC_claimStatus']]++;
                }
            }
            mysqli_data_seek($result, 0);
        }
        ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pending Claims</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['Pending']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Approved Claims</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['Approved']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Rejected Claims</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['Rejected']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Merit Claims Management</h5>
            </div>
            <div class="card-body p-0">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Event Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Submit Date</th>
                                    <th>Document</th>
                                    <th>Merit Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $row['MC_claimID']; ?></td>
                                        <td><?php echo isset($dummy_events[$row['E_eventID']]) ? $dummy_events[$row['E_eventID']] : 'Event ' . $row['E_eventID']; ?></td>
                                        <td>
                                            <span class="role-badge <?php 
                                                echo $row['MC_role'] === 'Main Committee' ? 'bg-success' : 
                                                     ($row['MC_role'] === 'Committee' ? 'bg-primary' : 'bg-secondary'); 
                                            ?> text-white">
                                                <?php echo htmlspecialchars($row['MC_role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php 
                                                echo strtolower($row['MC_claimStatus']) === 'pending' ? 'bg-warning text-dark' : 
                                                     (strtolower($row['MC_claimStatus']) === 'approved' ? 'bg-success text-white' : 'bg-danger text-white'); 
                                            ?>">
                                                <?php echo htmlspecialchars($row['MC_claimStatus']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['MC_submitDate'])); ?></td>
                                        <td>
                                            <?php if($row['MC_documentPath']): ?>
                                                <a href="<?php echo htmlspecialchars($row['MC_documentPath']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-file-alt"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No document</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo isset($merit_points_by_role[$row['MC_role']]) ? $merit_points_by_role[$row['MC_role']] : '0'; ?> pts</span>
                                        </td>
                                        <td>
                                            <?php if (strtolower($row['MC_claimStatus']) === 'pending'): ?>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="approveClaim(<?php echo $row['MC_claimID']; ?>, '<?php echo $row['MC_role']; ?>', <?php echo $merit_points_by_role[$row['MC_role']]; ?>)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="rejectClaim(<?php echo $row['MC_claimID']; ?>)">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No merit claims found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Merit Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approvalForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="claim_id" id="approve-claim-id">
                        
                        <div class="mb-3">
                            <label class="form-label">Role:</label>
                            <input type="text" id="approve-role" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="merit_points" class="form-label">Merit Points to Award:</label>
                            <input type="number" name="merit_points" id="merit_points" class="form-control" min="1" required>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>Are you sure you want to approve this claim?</strong>
                        </div>
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
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Merit Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectionForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="claim_id" id="reject-claim-id">
                        
                        <div class="alert alert-danger">
                            <strong>Are you sure you want to reject this claim?</strong>
                            <p class="mb-0">This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
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