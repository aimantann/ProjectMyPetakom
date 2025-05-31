<?php
if (!isset($conn)) {
    include('includes/dbconnection.php');
}
$user_name = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $query = "SELECT U_name FROM user WHERE U_email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($db_name);
    if ($stmt->fetch()) {
        $user_name = $db_name;
    }
    $stmt->close();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button type="button" id="sidebarToggle" class="btn btn-light border rounded-circle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navbar Right Side -->
        <div class="ms-auto d-flex align-items-center">
            <!-- Search Box -->
            <!-- <form class="d-none d-md-flex me-4" role="search">
                <div class="input-group">
                    <input class="form-control border-end-0" type="search" placeholder="Search..." aria-label="Search">
                    <button class="btn btn-outline-secondary border-start-0" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form> -->
            
            <!-- Notifications
            <div class="dropdown me-3">
                <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        56
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationsDropdown">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">New membership request</a></li>
                    <li><a class="dropdown-item" href="#">Activity approval needed</a></li>
                    <li><a class="dropdown-item" href="#">System update available</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                </ul>
            </div> -->
            
            <!-- User Profile -->
            <!-- REMOVED: Dropdown menu, replaced with Hello message -->
            <span class="ms-3 fw-bold text-dark h5 mb-0">
                Welcome back, <?php echo htmlspecialchars($user_name ?: 'Advisor'); ?>
            </span>
        </div>
    </div>
</nav>