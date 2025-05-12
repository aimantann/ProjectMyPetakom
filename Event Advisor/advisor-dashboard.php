<?php

// Include header file
include('includes/header.php');

// Include database connection file
include('includes/dbconnection.php');

// Close the database connection
$conn->close();
?>

<?php
// Include footer and scripts
include('includes/footer.php');
include('includes/scripts.php');
?>

<!-- Custom CSS -->
<style>
    .card-title {
        font-size: 1.2em;
        font-weight: bold;
    }
    .card-count {
        font-size: 1.2em;
    }
    .card-footer {
        font-size: 1em;
    }
</style>
