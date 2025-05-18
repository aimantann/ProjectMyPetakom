<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button type="button" id="sidebarToggle" class="btn btn-light border rounded-circle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Page Title -->
        <span class="navbar-brand mb-0 h1 ms-3">Event Advisor Dashboard</span>
        
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
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <!-- <img src="images/avatar-placeholder.jpg" class="rounded-circle me-2" width="32" height="32" alt="User"> -->
                    <span class="d-none d-sm-inline">Advisor</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="admin-view-profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="admin-settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="user-logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>