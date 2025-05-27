<?php
include("includes/dbconnection.php"); 
$query = "SELECT * FROM meritclaim"; 
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Merit Claims</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">My Merit Claims</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Claim ID</th>
                    <th>Merit Title</th>
                    <th>Claim Status</th>
                    <th>Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr id="row-<?php echo $row['MC_claimID']; ?>">
                        <td><?php echo $row['MC_claimID']; ?></td>
                        <td><?php echo $row['MC_role']; ?></td>
                        <td><?php echo $row['MC_claimStatus']; ?></td>
                        <td><?php echo $row['MC_documentPath']; ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editClaim(<?php echo $row['MC_claimID']; ?>)">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteClaim(<?php echo $row['MC_claimID']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form id="editForm" method="post" action="update-claim.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                  <input type="hidden" name="id" id="edit-id">
                  <div class="mb-3">
                      <label for="edit-title" class="form-label">Title</label>
                      <input type="text" class="form-control" name="title" id="edit-title" required>
                  </div>
                  <div class="mb-3">
                      <label for="edit-description" class="form-label">Description</label>
                      <textarea class="form-control" name="description" id="edit-description" required></textarea>
                  </div>
                  <div class="mb-3">
                      <label for="edit-points" class="form-label">Points</label>
                      <input type="number" class="form-control" name="points" id="edit-points" required>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save changes</button>
              </div>
            </div>
        </form>
      </div>
    </div>

    <script>
        function editClaim(id) {
            fetch(`get-claim.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit-id').value = data.id;
                document.getElementById('edit-title').value = data.title;
                document.getElementById('edit-description').value = data.description;
                document.getElementById('edit-points').value = data.points;
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        }

        function deleteClaim(id) {
            if (confirm('Are you sure you want to delete this claim?')) {
                fetch(`delete-claim.php?id=${id}`, { method: 'POST' })
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        document.getElementById(`row-${id}`).remove();
                    } else {
                        alert('Failed to delete claim.');
                    }
                });
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
