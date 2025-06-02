<?php
session_start();
include('includes/header.php');
include("includes/dbconnection.php");

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $claim_id = intval($_POST['claim_id']);
    $action = $_POST['action'];
    $merit_points = isset($_POST['merit_points']) ? intval($_POST['merit_points']) : 0;
    
    if ($action === 'approve') {
        // Update claim status to Approved
        $update_query = "UPDATE meritclaim SET MC_claimStatus = 'Approved' WHERE MC_claimID = $claim_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Insert into meritawarded table
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
        // Update claim status to Rejected
        $update_query = "UPDATE meritclaim SET MC_claimStatus = 'Rejected' WHERE MC_claimID = $claim_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Claim rejected successfully!";
        } else {
            $error_message = "Failed to reject claim: " . mysqli_error($conn);
        }
    }
}

// Fetch all claims with event names and user names from the respective tables
$query = "SELECT mc.*, e.E_name, u.U_name 
          FROM meritclaim mc 
          LEFT JOIN event e ON mc.E_eventID = e.E_eventID 
          LEFT JOIN user u ON mc.U_userID = u.U_userID 
          ORDER BY mc.MC_submitDate DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

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
                <a href="advisor-dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
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
            <div class="table-header">
                <h2 class="table-title">Review Claims</h2>
            </div>

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
                                        <a href="<?php echo htmlspecialchars($row['MC_documentPath']); ?>" target="_blank" class="document-link">
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
        function approveClaim(claimId, role, meritPoints) {
            document.getElementById('approve-claim-id').value = claimId;
            document.getElementById('approve-role').value = role;
            document.getElementById('merit_points').value = meritPoints;
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