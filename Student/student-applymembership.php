<?php
require_once('user-validatesession.php');
include('includes/header.php');
include('includes/dbconnection.php');

// Get user's name and email for display
$user_name = '';
$user_email = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT U_name FROM user WHERE U_email = ? LIMIT 1");
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($db_name);
    if ($stmt->fetch()) {
        $user_name = $db_name;
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <!-- Display error message as alert -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Petakom Membership Application</h4>
                </div>
                <div class="card-body">
                    <form action="student-membershipprocess.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="studentCard">Upload Student Card <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="studentCard" name="studentCard" accept="image/*,application/pdf" required>
                            <div class="form-text">
                                Please upload a clear image of your student card for verification (JPEG, PNG, or PDF).
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Apply for Membership</button>
                    </form>
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <b>Note:</b> All FK students are eligible to apply. Your application will be reviewed after student card verification.
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal for Success Message -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Success</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        if (isset($_SESSION['success'])) {
            echo $_SESSION['success'];
            unset($_SESSION['success']);
        }
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<!-- JS to trigger modal if success message exists -->
<script>
<?php if (isset($_SESSION['show_success_modal'])): ?>
    var myModal = new bootstrap.Modal(document.getElementById('successModal'));
    window.onload = function() {
        myModal.show();
    }
<?php unset($_SESSION['show_success_modal']); endif; ?>
</script>