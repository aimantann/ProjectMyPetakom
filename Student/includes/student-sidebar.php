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
        if ($db_role === 'student') {
            $user_role = 'Student';
        } else if ($db_role === 'admin') {
            $user_role = 'Administrator';
        } else if ($db_role === 'event_advisor') {
            $user_role = 'Event Advisor';
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
            <h6 class="mb-0"><?php echo htmlspecialchars($user_name ?: 'Student'); ?></h6>
            <span class="user-role"><?php echo htmlspecialchars($user_role ?: 'Student'); ?></span>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="list-unstyled sidebar-menu">
        <!-- Dashboard -->
        <li class="sidebar-item active">
            <a href="student-dashboard.php" class="sidebar-link">
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
                    <a href="student-viewprofile.php">
                        <i class="fas fa-id-card me-2"></i> View Profile
                    </a>
                </li>
                <li>
                    <a href="student-editprofile.php">
                        <i class="fas fa-user-edit me-2"></i> Edit Profile
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

        <!-- Activity Management -->
        <li class="sidebar-item">
            <a href="#activitySubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-calendar-alt me-2"></i>
                <span>Manage Activities</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="activitySubmenu">
                <li>
                    <a href="admin-view-activities.php">
                        <i class="fas fa-list-alt me-2"></i> View Activities
                    </a>
                </li>
                <li>
                    <a href="admin-add-activity.php">
                        <i class="fas fa-plus-circle me-2"></i> Add Activity
                    </a>
                </li>
            </ul>
        </li>

        <!-- Merit Management -->
        <li class="sidebar-item">
            <a href="#meritSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-medal me-2"></i>
                <span>Manage Merit</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="meritSubmenu">
                <li>
                    <a href="student-view-merit.php">
                        <i class="fas fa-eye me-2"></i> View Merit
                    </a>
                </li>
                <li>
                    <a href="student-claim-merit.php">
                        <i class="fas fa-plus-circle me-2"></i> Claim Merit
                    </a>
                </li>
                <li>
                    <a href="student-my-merit-claims.php">
                        <i class="fas fa-list-alt me-2"></i> My Merit Claims
                    </a>
                </li>
            </ul>
        </li>


         <!-- Manage Event Attendance -->
         <li class="sidebar-item">
                <a href="#attendanceSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span>Manage Event Attendance</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled submenu" id="attendanceSubmenu">
                    <li>
                        <a href="../Student/scan_qr.php">
                            <i class="fas fa-qrcode me-2"></i> Scan QR Code
                        </a>
                    </li>
                 </ul>
             </li>

        <!-- Divider -->
        <li class="sidebar-divider">
            <span class="sidebar-heading">Settings</span>
        </li>

        <!-- Settings -->
        <li class="sidebar-item">
            <a href="admin-settings.php" class="sidebar-link">
                <i class="fas fa-cog me-2"></i>
                <span>Settings</span>
            </a>
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