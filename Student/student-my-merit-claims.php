<?php
require_once('user-validatesession.php');
include('includes/header.php');
include("includes/dbconnection.php");

if (isset($_POST['action']) && isset($_POST['claim_id'])) {
    $action = $_POST['action'];
    $claim_id = $_POST['claim_id'];

    if ($action === 'submit_claim') {
        // Update claim status to Submitted
        $update_query = "UPDATE meritclaim SET MC_claimStatus = 'Submitted' WHERE MC_claimID = ? AND U_userID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $claim_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Claim has been submitted successfully for review!";
            echo "<script>
                window.location.href = 'student-my-merit-claims.php';
            </script>";
            exit();
        } else {
            $error_message = "Error submitting claim. Please try again.";
        }
        $stmt->close();
    }
}

// Display success message if claim was saved
$success_message = '';
if (isset($_SESSION['claim_saved'])) {
    $success_message = $_SESSION['claim_saved'];
    unset($_SESSION['claim_saved']); // Clear the message after displaying
}

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Fetch claims from database with event names using JOIN
$query = "SELECT 
    mc.MC_claimID,
    e.E_name,
    mc.MC_role,
    mc.MC_claimStatus,
    mc.MC_submitDate,
    mc.MC_documentPath,
    COALESCE(ma.MD_meritPoint, 0) as merit_awarded
FROM meritclaim mc
LEFT JOIN event e ON mc.E_eventID = e.E_eventID
LEFT JOIN meritawarded ma ON mc.E_eventID = ma.E_eventID AND mc.U_userID = ma.U_userID
WHERE mc.U_userID = ?
ORDER BY mc.MC_submitDate DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Handle success message
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Claim submitted successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Merit Claims - MyPetakom</title>
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
            justify-content: flex-start; /* Changed from space-between */
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

        .new-claim-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.3s;
        }

        .new-claim-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            text-decoration: none;
            color: white;
        }

        .new-claim-btn i {
            margin-right: 8px;
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

        .status-submitted {
            background-color: #17a2b8;
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
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .btn-edit:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #218838;
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
            opacity: 1;
            transition: opacity 5s ease-out;
        }

        .alert.fade-out {
            opacity: 0;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        /* Modal Styles */
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
            max-width: 600px;
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

        .required {
            color: #dc3545;
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

        .role-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .role-option {
            display: none;
        }

        .role-option + label {
            display: block;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .role-option:checked + label {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .role-option + label:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .role-option:checked + label:hover {
            background-color: #0056b3;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-save {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-save:hover {
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

            .role-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header with Title only -->
        <div class="header">
            <h1 class="page-title">My Merit Claims</h1>
        </div>

        <!-- Success Message -->
        <?php if ($success_message): ?>
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Claims Table -->
        <div class="claims-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="claims-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Submit Date</th>
                            <th>Official Letter</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr id="row-<?php echo $row['MC_claimID']; ?>">
                                <td><?php echo htmlspecialchars($row['E_name'] ?? 'Unknown Event'); ?></td>
                                <td>
                                    <span class="role-badge <?php 
                                        echo $row['MC_role'] === 'Main Committee' ? 'role-main' : 
                                             ($row['MC_role'] === 'Committee' ? 'role-committee' : 'role-participant'); 
                                    ?>">
                                        <?php echo htmlspecialchars($row['MC_role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php 
                                        echo strtolower($row['MC_claimStatus']) === 'pending' ? 'status-pending' : 
                                            (strtolower($row['MC_claimStatus']) === 'submitted' ? 'status-submitted' : 
                                            (strtolower($row['MC_claimStatus']) === 'approved' ? 'status-approved' : 'status-rejected')); 
                                    ?>">
                                        <?php echo htmlspecialchars($row['MC_claimStatus']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $submitDate = new DateTime($row['MC_submitDate']);
                                        echo $submitDate->format('d M Y'); 
                                    ?>
                                </td>
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
                                            <button class="btn-edit" 
                                                    onclick="editClaim(<?php echo $row['MC_claimID']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-delete" 
                                                    onclick="deleteClaim(<?php echo $row['MC_claimID']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                            <button class="btn-submit" 
                                                    onclick="submitClaim(<?php echo $row['MC_claimID']; ?>)">
                                                <i class="fas fa-paper-plane"></i> Submit
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
                    <p>No merit claims submitted yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Merit Claim</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    
                    <!-- Event Selection -->
                    <div class="form-group">
                        <label for="edit-event" class="form-label">
                            Select Event <span class="required">*</span>
                        </label>
                        <select name="event_id" id="edit-event" class="form-control" required>
                            <option value="">-- Choose an Event --</option>
                            <?php
                            // Fetch events from the event table
                            $events_query = "SELECT E_eventID, E_name FROM event ORDER BY E_name";
                            $events_result = mysqli_query($conn, $events_query);
                            while($event = mysqli_fetch_assoc($events_result)): 
                            ?>
                                <option value="<?php echo $event['E_eventID']; ?>">
                                    <?php echo htmlspecialchars($event['E_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Role Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            Your Role in the Event <span class="required">*</span>
                        </label>
                        <div class="role-options">
                            <input type="radio" name="role" id="edit-participant" value="Participant" class="role-option" required>
                            <label for="edit-participant">
                                <i class="fas fa-user"></i>
                                Participant
                            </label>

                            <input type="radio" name="role" id="edit-committee" value="Committee" class="role-option" required>
                            <label for="edit-committee">
                                <i class="fas fa-users"></i>
                                Committee
                            </label>

                            <input type="radio" name="role" id="edit-main-committee" value="Main Committee" class="role-option" required>
                            <label for="edit-main-committee">
                                <i class="fas fa-crown"></i>
                                Main Committee
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add fade out effect for success message
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.add('fade-out');
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 5000); // Wait for fade out animation to complete
            }, 100);
        }
        
        function editClaim(id) {
            // Fetch claim data
            fetch(`get-claim.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                // Populate modal with data
                document.getElementById('edit-id').value = data.MC_claimID;
                document.getElementById('edit-event').value = data.E_eventID;
                
                // Set role radio button
                const roleRadios = document.querySelectorAll('input[name="role"]');
                roleRadios.forEach(radio => {
                    if (radio.value === data.MC_role) {
                        radio.checked = true;
                    }
                });
                
                // Show modal
                document.getElementById('editModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching claim data');
            });
        }

        function deleteClaim(id) {
            if (confirm('Are you sure you want to delete this claim? This action cannot be undone.')) {
                fetch(`delete-claim.php?id=${id}`, { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.text())
                .then(result => {
                    if (result.trim() === 'success') {
                        // Remove row from table
                        document.getElementById(`row-${id}`).remove();
                        
                        // Check if table is now empty
                        const tbody = document.querySelector('.claims-table tbody');
                        if (tbody && tbody.children.length === 0) {
                            location.reload(); // Reload to show "no claims" message
                        }
                    } else {
                        alert('Failed to delete claim. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting claim');
                });
            }
        }

        function submitClaim(id) {
            if (confirm('Are you sure you want to submit this claim for review? You won\'t be able to edit or delete it after submission.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'submit_claim';

                const claimIdInput = document.createElement('input');
                claimIdInput.type = 'hidden';
                claimIdInput.name = 'claim_id';
                claimIdInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(claimIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Handle edit form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update-claim.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'success') {
                    alert('Claim updated successfully!');
                    closeModal();
                    location.reload(); // Reload to show updated data
                } else {
                    alert('Failed to update claim: ' + result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating claim: ' + error.message);
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
<?php
include('includes/footer.php');
?>
</body>
</html>