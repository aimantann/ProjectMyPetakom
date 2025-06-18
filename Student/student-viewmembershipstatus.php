<?php
require_once('user-validatesession.php');
include('includes/header.php');
include('includes/dbconnection.php');

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: user-login.php");
    exit;
}

// Fetch student user ID
$user_email = $_SESSION['email'];
$user_id = null;
$stmt = $conn->prepare("SELECT U_userID FROM user WHERE U_email = ? LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>User not found.</div></div>";
    include('includes/footer.php');
    exit;
}

// Fetch membership applications for this student
$query = "SELECT M_membershipID, M_applicationDate, M_status, M_studentCard FROM membership WHERE U_userID = ? ORDER BY M_applicationDate DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2 class="mb-4">My Membership Application History</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Application Date</th>
                        <th>Student Card</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['M_applicationDate']); ?></td>
                            <td>
                                <?php if (!empty($row['M_studentCard'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['M_studentCard']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                                <?php else: ?>
                                    <span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = $row['M_status'];
                                $badge = "secondary";
                                if ($status === "Pending") $badge = "warning text-dark";
                                else if ($status === "Approved") $badge = "success";
                                else if ($status === "Rejected") $badge = "danger";
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You have not submitted any membership applications yet.</div>
    <?php endif; ?>

    <a href="student-applymembership.php" class="btn btn-primary mt-3">Apply for Membership</a>
</div>

<?php
include('includes/footer.php');
?>