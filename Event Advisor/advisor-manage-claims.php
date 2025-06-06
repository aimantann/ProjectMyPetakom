<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include('includes/header.php');
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
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get claim details first
        $get_claim_query = "SELECT mc.*, e.E_name, e.E_level 
                           FROM meritclaim mc
                           JOIN event e ON mc.E_eventID = e.E_eventID
                           WHERE mc.MC_claimID = ?";
        $claim_stmt = $conn->prepare($get_claim_query);
        $claim_stmt->bind_param("i", $claim_id);
        $claim_stmt->execute();
        $claim_result = $claim_stmt->get_result();
        $claim_data = $claim_result->fetch_assoc();

        if ($claim_data) {
            $current_datetime = date('Y-m-d H:i:s');
            
            if ($action === 'approve') {
                
                // Update claim status
                $update_query = "UPDATE meritclaim 
                               SET MC_claimStatus = 'Approved', 
                                   MA_approvedBy = ?,
                                   MC_approvedDate = ? 
                               WHERE MC_claimID = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $_SESSION['user_id'], $current_datetime, $claim_id);
                $update_stmt->execute();

                // Insert into meritawarded table
                $award_query = "INSERT INTO meritawarded 
                              (U_userID, E_eventID, MD_totalMerit, MD_awardedDate) 
                              VALUES (?, ?, ?, ?)";
                $award_stmt = $conn->prepare($award_query);
                $award_stmt->bind_param("iiis", 
                    $claim_data['U_userID'], 
                    $claim_data['E_eventID'], 
                    $merit_points,
                    $current_datetime
                );
                $award_stmt->execute();

            } elseif ($action === 'reject') {
                // Update claim status to rejected
                $update_query = "UPDATE meritclaim 
                               SET MC_claimStatus = 'Rejected', 
                                   MA_approvedBy = ?,
                                   MC_rejectedDate = ? 
                               WHERE MC_claimID = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $_SESSION['user_id'], $current_datetime, $claim_id);
                $update_stmt->execute();
            }

            // Commit transaction
            $conn->commit();
            $success_message = "Claim successfully " . ($action === 'approve' ? 'approved' : 'rejected');

        } else {
            throw new Exception("Claim not found");
        }

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error processing claim: " . $e->getMessage();
    }
}

// Get all claims with related information
$main_query = "SELECT 
    mc.MC_claimID,
    e.E_name as event_name,
    e.E_level as event_level,
    u.U_name as student_name,
    u.U_userID as student_id,
    mc.MC_role,
    mc.MC_claimStatus,
    mc.MC_submitDate,
    mc.MC_documentPath,
    COALESCE(ma.MD_totalMerit, 0) as merit_awarded,
    ma.MD_awardID
FROM meritclaim mc
JOIN event e ON mc.E_eventID = e.E_eventID
JOIN user u ON mc.U_userID = u.U_userID
LEFT JOIN meritawarded ma ON mc.E_eventID = ma.E_eventID 
    AND mc.U_userID = ma.U_userID
WHERE mc.MC_claimStatus = 'Pending'  /* Changed from 'Submitted' to 'Pending' */
ORDER BY mc.MC_submitDate DESC";

// Get statistics for dashboard
$stats_query = "SELECT 
    MC_claimStatus,
    COUNT(*) as count
FROM meritclaim
GROUP BY MC_claimStatus";

try {
    // Execute main query
    $result = $conn->query($main_query);
    $claims = [];
    while ($row = $result->fetch_assoc()) {
        $claims[] = $row;
    }

    // Execute statistics query
    $stats_result = $conn->query($stats_query);
    $statistics = [];
    while ($row = $stats_result->fetch_assoc()) {
        $statistics[$row['MC_claimStatus']] = $row['count'];
    }

    // Get total merit points awarded
    $total_merit_query = "SELECT COALESCE(SUM(MD_totalMerit), 0) as total_merit 
                         FROM meritawarded";
    $total_merit_result = $conn->query($total_merit_query);
    $total_merit = $total_merit_result->fetch_assoc()['total_merit'];

} catch (Exception $e) {
    $error_message = "Error retrieving claims: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Claim - Event Advisor</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
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

        .claims-container {
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

        .claims-table {
            width: 100%;
            border-collapse: collapse;
        }

        .claims-table th,
        .claims-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .claims-table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .claims-table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #ffc107;
            color: #333;
        }

        .status-approved {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
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
            background-color: #6f42c1;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-approve:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-reject:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .document-link {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .document-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .no-claims {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-confirm {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-confirm:hover {
            background-color: #218838;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-left {
                width: 100%;
            }

            .back-btn {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .claims-table {
                font-size: 14px;
            }

            .claims-table th,
            .claims-table td {
                padding: 10px 8px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1 class="page-title">Review Claim</h1>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Claims Table -->
<div class="claims-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="claims-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Event Name</th>
                    <th>Role</th>
                    <th>Submit Date</th>
                    <th>Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['U_name'] ?? 'Unknown User'); ?></td>
                        <td><?php echo htmlspecialchars($row['E_name'] ?? 'Unknown Event'); ?></td>
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
                                <?php
                                $documentPath = $row['MC_documentPath'];
                                ?>
                                <a href="<?php echo htmlspecialchars($documentPath); ?>" 
                                    target="_blank" 
                                    class="document-link">
                                    <i class="fas fa-file-alt"></i> View
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No document</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if (strtolower($row['MC_claimStatus']) === 'pending'): ?>
                                    <button class="btn-approve" 
                                            onclick="approveClaim(<?php echo $row['MC_claimID']; ?>, '<?php echo $row['MC_role']; ?>', <?php echo $merit_points_by_role[$row['MC_role']]; ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn-reject" 
                                            onclick="rejectClaim(<?php echo $row['MC_claimID']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">No actions available</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-claims">
            <i class="fas fa-clipboard-list" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
            <p>No merit claims found.</p>
        </div>
    <?php endif; ?>
</div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Approve Merit Claim</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form id="approvalForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="claim_id" id="approve-claim-id">
                    
                    <div class="form-group">
                        <label class="form-label">Role:</label>
                        <input type="text" id="approve-role" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="merit_points" class="form-label">Merit Points to Award:</label>
                        <input type="number" name="merit_points" id="merit_points" class="form-control" min="1" required>
                    </div>
                    
                    <p><strong>Are you sure you want to approve this claim?</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-confirm">Approve Claim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reject Merit Claim</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form id="rejectionForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="claim_id" id="reject-claim-id">
                    
                    <p><strong>Are you sure you want to reject this claim?</strong></p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-confirm" style="background-color: #dc3545;">Reject Claim</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveClaim(claimId, role) {
            document.getElementById('approve-claim-id').value = claimId;
            document.getElementById('approve-role').value = role;
            document.getElementById('merit_points').value = merit_points_by_role[role] || 0;
            document.getElementById('approvalModal').style.display = 'block';
        }

        function rejectClaim(claimId) {
            document.getElementById('reject-claim-id').value = claimId;
            document.getElementById('rejectionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('approvalModal').style.display = 'none';
            document.getElementById('rejectionModal').style.display = 'none';
        }

        // Update window click handler to include document preview modal
        window.onclick = function(event) {
            const approvalModal = document.getElementById('approvalModal');
            const rejectionModal = document.getElementById('rejectionModal');
            const documentPreviewModal = document.getElementById('documentPreviewModal');
            
            if (event.target === approvalModal) {
                closeModal();
            }
            if (event.target === rejectionModal) {
                closeModal();
            }
            if (event.target === documentPreviewModal) {
                closeDocumentPreview();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const approvalModal = document.getElementById('approvalModal');
            const rejectionModal = document.getElementById('rejectionModal');
            if (event.target === approvalModal) {
                closeModal();
            }
            if (event.target === rejectionModal) {
                closeModal();
            }
        }
    </script>
<?php
include('includes/footer.php');
?>
</body>
</html>