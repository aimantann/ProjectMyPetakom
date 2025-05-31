<?php
// --- ADDED: Fetch name and role for sidebar display ---
if (!isset($conn)) {
    include('includes/dbconnection.php');
}
$user_name = '';
$user_role = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $query = "SELECT U_name, U_usertype FROM user WHERE U_email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($db_name, $db_role);
    if ($stmt->fetch()) {
        $user_name = $db_name;
        // Format role for display
        if ($db_role === 'admin') {
            $user_role = 'Administrator';
        } else if ($db_role === 'event_advisor') {
            $user_role = 'Event Advisor';
        } else if ($db_role === 'student') {
            $user_role = 'Student';
        } else {
            $user_role = ucfirst($db_role);
        }
    }
    $stmt->close();
}
?>
<nav id="sidebar" class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="logo">
            <img src="images\LOGOPETAKOM.png" alt="Logo" class="img-fluid" style="size: 1000px;">
        </div>
        <h3>MyPetakom</h3>
    </div>

    <!-- User Profile Area -->
    <div class="user-profile text-center mb-4">
        <div class="user-avatar mb-2">
            <img src="images/arep.jpg" alt="  User" class="rounded-circle">
        </div>
        <div class="user-info">
            <!-- CHANGED: Show logged-in user's name and role -->
            <h6 class="mb-0"><?php echo htmlspecialchars($user_name ?: 'Petakom Admin'); ?></h6>
            <span class="user-role"><?php echo htmlspecialchars($user_role ?: 'Administrator'); ?></span>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="list-unstyled sidebar-menu">
        <!-- Dashboard -->
        <li class="sidebar-item active">
            <a href="admin-dashboard.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Divider -->
        <li class="sidebar-divider">
            <span class="sidebar-heading">Features</span>
        </li>

        <!-- Profile Management -->
        <li class="sidebar-item">
            <a href="#profileSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-user me-2"></i>
                <span>Manage Profile</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="profileSubmenu">
                <li>
                    <a href="admin-viewuserlist.php">
                        <i class="fas fa-id-card me-2"></i> View User Profiles
                    </a>
                </li>
                <li>
                    <a href="admin-edituserlist.php">
                        <i class="fas fa-user-edit me-2"></i> Edit User Profiles
                    </a>
                </li>
            </ul>
        </li>

        <!-- Membership Management -->
        <li class="sidebar-item">
            <a href="#membershipSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-users me-2"></i>
                <span>Manage Membership</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="membershipSubmenu">
                <li>
                    <a href="admin-view-members.php">
                        <i class="fas fa-list me-2"></i> View Members
                    </a>
                </li>
                <li>
                    <a href="admin-add-member.php">
                        <i class="fas fa-user-plus me-2"></i> Add Member
                    </a>
                </li>
                <li>
                    <a href="admin-approve-membership.php">
                        <i class="fas fa-check-circle me-2"></i> Approve Requests
                    </a>
                </li>
            </ul>
        </li>

        <!-- Approve Membership Applications -->
        <li class="sidebar-item">
            <a href="#activitySubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-calendar-alt me-2"></i>
                <span>Manage Membership Applications</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="activitySubmenu">
                <li>
                    <a href="admin-approvemembership.php">
                        <i class="fas fa-list-alt me-2"></i> View Membership Applications
                    </a>
                </li>
            </ul>
        </li>

        <!-- Divider -->
        <li class="sidebar-divider">
            <span class="sidebar-heading">Settings</span>
        </li>
        
        <!-- Logout -->
        <li class="sidebar-item">
            <a href="user-logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <p>© 2025 MyPetakom</p>
    </div>
</nav>

<!-- Sidebar Toggle Button - Mobile Only -->
<button id="sidebarCollapse" class="d-lg-none btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle">
    <i class="fas fa-bars"></i>
</button>