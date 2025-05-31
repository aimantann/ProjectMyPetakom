<?php
include('../config/dbconnection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Attendance Status</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Attendance recorded successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Invalid Student ID or Password!</div>
    <?php endif; ?>
    <a href="../advisor-dashboard.php" class="btn btn-outline-primary mb-3">← Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
