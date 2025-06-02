<?php
session_start();
include('includes/header.php');
include('includes/dbconnection.php');

// Fetch all pending membership applications with user info
$query = "
    SELECT m.M_membershipID, m.M_applicationDate, m.M_status, m.M_studentCard, u.U_userID, u.U_name, u.U_email
    FROM membership m
    JOIN user u ON m.U_userID = u.U_userID
    WHERE m.M_status = 'Pending'
    ORDER BY m.M_applicationDate ASC
";
$result = $conn->query($query);

?>

<div class="container mt-5">
    <h2 class="mb-4">Approve Membership Applications</h2>

    <?php if (isset($_SESSION['approve_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['approve_success']; unset($_SESSION['approve_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['approve_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['approve_error']; unset($_SESSION['approve_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Application Date</th>
                        <th>Student Card</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['U_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['U_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['M_applicationDate']); ?></td>
                            <td>
                                <?php if (!empty($row['M_studentCard'])): ?>
                                    <a href="../Student/<?php echo htmlspecialchars($row['M_studentCard']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                                <?php else: ?>
                                    <span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['M_status']); ?></span></td>
                            <td>
                                <form action="admin-membershipapprovalprocess.php" method="post" style="display:inline;">
                                    <input type="hidden" name="membership_id" value="<?php echo $row['M_membershipID']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('WARNING: Confirm to approve this application?');">Approve</button>
                                </form>
                                <form action="admin-membershipapprovalprocess.php" method="post" style="display:inline;">
                                    <input type="hidden" name="membership_id" value="<?php echo $row['M_membershipID']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('WARNING: Are you sure to reject this application?');">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No pending membership applications.</div>
    <?php endif; ?>
</div>

<?php
include('includes/footer.php');
?>