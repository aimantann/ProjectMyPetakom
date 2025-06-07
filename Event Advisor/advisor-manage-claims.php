<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include("includes/dbconnection.php");

// Check if user is logged in and is an advisor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'event_advisor') {
    $_SESSION['login_required'] = "Please login as an advisor to access this page.";
    header('Location: user-login.php');
    exit();
}

// Handle claim actions (approve/reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['claim_id'])) {
    $action = $_POST['action'];
    $claim_id = $_POST['claim_id'];
    $current_date = date('Y-m-d H:i:s');
    
    try {
        // Start transaction
        $conn->begin_transaction();

        if ($action === 'approve') {
            // First get the claim details to calculate merit points
            $get_claim_query = "SELECT mc.*, e.E_level, e.E_name 
                                FROM meritclaim mc 
                                JOIN event e ON mc.E_eventID = e.E_eventID 
                                WHERE mc.MC_claimID = ?";
            $stmt = $conn->prepare($get_claim_query);
            $stmt->bind_param("i", $claim_id);
            $stmt->execute();
            $claim_result = $stmt->get_result();
            $claim_data = $claim_result->fetch_assoc();

            if ($claim_data) {
                // Update claim status to Approved
                $update_query = "UPDATE meritclaim 
                                SET MC_claimStatus = 'Approved'
                                WHERE MC_claimID = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("i", $claim_id);
                $update_stmt->execute();

                // Insert into meritawarded table
                $insert_query = "INSERT INTO meritawarded 
                            (U_userID, E_eventID, MD_awardedDate, MD_meritPoint) 
                            VALUES (?, ?, ?, ?)";
                $merit_points = calculateMeritPoints($claim_data['E_level'], $claim_data['MC_role']);
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("iisi", 
                    $claim_data['U_userID'], 
                    $claim_data['E_eventID'], 
                    $current_date,
                    $merit_points
                );
                $insert_stmt->execute();

                $success_message = "Claim has been approved successfully!";
            }
        } elseif ($action === 'reject') {
            // Update claim status to Rejected
            $update_query = "UPDATE meritclaim 
                        SET MC_claimStatus = 'Rejected'
                        WHERE MC_claimID = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $claim_id);
    
            if ($update_stmt->execute()) {
                $success_message = "Claim has been rejected successfully!";
            }
        }

        // Commit transaction
        $conn->commit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error processing claim: " . $e->getMessage();
    }
}

// Function to calculate merit points based on event level and role
function calculateMeritPoints($eventLevel, $role) {
    $points = [
        'International' => [
            'Main Committee' => 100,
            'Committee' => 70,
            'Participant' => 50
        ],
        'National' => [
            'Main Committee' => 80,
            'Committee' => 50,
            'Participant' => 40
        ],
        'State' => [
            'Main Committee' => 60,
            'Committee' => 40,
            'Participant' => 30
        ],
        'District' => [
            'Main Committee' => 40,
            'Committee' => 30,
            'Participant' => 15
        ],
        'UMPSA' => [
            'Main Committee' => 30,
            'Committee' => 20,
            'Participant' => 5
        ]
    ];

    return isset($points[$eventLevel][$role]) ? $points[$eventLevel][$role] : 0;
}

// Get all submitted claims (only show claims with 'Submitted' status)
$main_query = "SELECT 
    mc.MC_claimID,
    e.E_name as event_name,
    e.E_level as event_level,
    u.U_name as student_name,
    u.U_userID as student_id,
    mc.MC_role,
    mc.MC_claimStatus,
    mc.MC_submitDate,
    mc.MC_documentPath
FROM meritclaim mc
JOIN event e ON mc.E_eventID = e.E_eventID
JOIN user u ON mc.U_userID = u.U_userID
WHERE mc.MC_claimStatus = 'Submitted'
ORDER BY mc.MC_submitDate DESC";

try {
    $result = $conn->query($main_query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    $error_message = "Error retrieving claims: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Claims - Event Advisor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .role-main {
            background-color: #ffc107;
            color: #000;
        }
        .role-committee {
            background-color: #6c757d;
            color: #fff;
        }
        .role-participant {
            background-color: #28a745;
            color: #fff;
        }
        .document-link {
            color: #0d6efd;
            text-decoration: none;
        }
        .document-link:hover {
            text-decoration: underline;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Review Claims</h2>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Claims Table -->
        <div class="card">
            <div class="card-body">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Event Name</th>
                                    <th>Role</th>
                                    <th>Submit Date</th>
                                    <th>Official Letter</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                        <td>
                                            <span class="role-badge <?php 
                                                echo $row['MC_role'] === 'Main Committee' ? 'role-main' : 
                                                     ($row['MC_role'] === 'Committee' ? 'role-committee' : 'role-participant'); 
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
                                                <span class="text-muted">No document</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-approve me-1" 
                                                    onclick="approveClaim(<?php echo $row['MC_claimID']; ?>)">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-reject" 
                                                    onclick="rejectClaim(<?php echo $row['MC_claimID']; ?>)">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="h5 text-muted">No submitted claims found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript for handling approve/reject actions -->
    <script>
    function approveClaim(claimId) {
        if (confirm('Are you sure you want to approve this claim?')) {
            submitClaimAction('approve', claimId);
        }
    }

    function rejectClaim(claimId) {
        if (confirm('Are you sure you want to reject this claim?')) {
            submitClaimAction('reject', claimId);
        }
    }

    function submitClaimAction(action, claimId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;

        const claimIdInput = document.createElement('input');
        claimIdInput.type = 'hidden';
        claimIdInput.name = 'claim_id';
        claimIdInput.value = claimId;

        form.appendChild(actionInput);
        form.appendChild(claimIdInput);
        document.body.appendChild(form);
        form.submit();
    }
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>