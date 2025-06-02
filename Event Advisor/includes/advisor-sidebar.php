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
            <h6 class="mb-0">Event Advisor</h6>
            <span class="user-role">Advisor</span>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="list-unstyled sidebar-menu">
        
        <!-- Dashboard -->
        <li class="sidebar-item active">
            <a href="advisor-dashboard.php" class="sidebar-link">
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
                    <a href="advisor-viewprofile.php">
                        <i class="fas fa-id-card me-2"></i> View Profile
                    </a>
                </li>
                <li>
                    <a href="advisor-editprofile.php">
                        <i class="fas fa-user-edit me-2"></i> Edit Profile
                    </a>
                </li>
            </ul>
        </li>

         <!-- Event Management -->
        <li class="sidebar-item">
            <a href="#eventSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-calendar-alt me-2"></i>
                <span>Manage Event</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="eventSubmenu">
                <li>
                    <a href="Module2/EventList.php">
                        <i class="fas fa-list-alt me-2"></i> List Event
                    </a>
                </li>
                <li>
                    <a href="Module2/EventRegistrationForm.php">
                        <i class="fas fa-plus-circle me-2"></i> Register Event
                    </a>
                </li>
                <li>
                    <a href="Module2/CommitteeEvent.php">
                        <i class="fas fa-plus-circle me-2"></i> Committee Event
                    </a>
                </li>
                <li>
                    <a href="Module2/MeritEvent.php">
                        <i class="fas fa-plus-circle me-2"></i> Merit Event
                    </a>
                </li>
                <li>
                    <a href="Module2/QRevent.php">
                        <i class="fas fa-list-alt me-2"></i> QRCode Event
                    </a>
                </li>
            </ul>
        </li>

        <!-- Manage Attendance Slot -->
        <li class="sidebar-item">
        <a href="#attendanceSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed" aria-expanded="false" aria-controls="attendanceSubmenu">
            <i class="fas fa-calendar-check me-2"></i>
                <span>Manage Attendance Slot</span>
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <ul class="collapse list-unstyled submenu" id="attendanceSubmenu">
            <li>
                <a href="Module3/view_attendanceslot.php">
                    <i class="fas fa-eye me-2"></i> View Attendance Slot
                </a>
            </li>
            <li>
                <a href="Module3/create_slot.php">
                    <i class="fas fa-plus-circle me-2"></i> Create Attendance Slot
                </a>
            </li>
        </ul>
    </li>

        <!-- Manage Merit -->
        <li class="sidebar-item">
            <a href="#meritSubmenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                <i class="fas fa-medal me-2"></i>
                <span>Manage Merit</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled submenu" id="meritSubmenu">
                <li>
                    <a href="advisor-manage-claims.php">
                        <i class="fas fa-clipboard-check me-2"></i> Review Claims
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