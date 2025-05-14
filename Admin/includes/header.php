<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin Dashboard</title>

    <!-- External Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Font Awesome Library -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php
    // Include top navigation bar
    include('includes/navbar-top.php');
    ?>
    <div id="layoutSidenav">
        <?php
        // Include sidebar
        include('includes/admin-sidebar.php');
        ?>
        <div id="layoutSidenav_content">
            <main>
                <!--  main content goes here -->
            </main>
        </div>
    </div>
</body>
</html>
