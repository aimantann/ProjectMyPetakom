<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Event Advisor Dashboard</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Font Awesome Icons -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <!-- Custom Sidebar CSS -->
    <link href="css/sidebar.css" rel="stylesheet" />
</head>
<body>
    <div class="wrapper">
        <?php
        // Include sidebar
        include('includes/advisor-sidebar.php');
        ?>
        
        <div class="content">
            <?php
            // Include top navigation bar
            include('includes/navbar-top.php');
            ?>
            
            <main class="container-fluid px-4 py-3">
                <!-- Main content goes here -->
                
                
                <!-- Add your page content here -->
                

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle function
            const toggleSidebar = document.querySelector('#sidebarToggle');
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function() {
                    document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
                });
            }
            
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>