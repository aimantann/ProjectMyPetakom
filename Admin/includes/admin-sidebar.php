<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <!-- Main Section -->
                <div class="sb-sidenav-menu-heading">Main</div>
                <a class="nav-link" href="admin-dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-table-columns"></i></div>
                    Dashboard
                </a>
                
                <!-- Features Section -->
                <div class="sb-sidenav-menu-heading">Features</div>

                <!-- User -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUser" aria-expanded="false" aria-controls="collapseUser">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    User
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUser" aria-labelledby="headingUser" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-add-user.php">User Registration</a>
                        <a class="nav-link" href="admin-manage-user.php">Manage Profile</a>
                    </nav>
                </div>

                <!-- Membership -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVehicle" aria-expanded="false" aria-controls="collapseVehicle">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-car"></i></i></div>
                    Membership
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseVehicle" aria-labelledby="headingVehicle" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-manage-vehicle.php">Manage Membership</a>
                    </nav>
                </div>

                <!-- Event -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseParking" aria-expanded="false" aria-controls="collapseParking">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-square-parking"></i></div>
                    Event
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseParking" aria-labelledby="headingParking" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-create-park.php">Add Event</a>
                        <a class="nav-link" href="admin-manage-area.php">Manage Event</a>
                    </nav>
                </div>

                <!-- Booking -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBooking" aria-expanded="false" aria-controls="collapseBooking">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bookmark"></i></i></div>
                    Booking
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseBooking" aria-labelledby="headingBooking" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-manage-booking.php">List of Booking</a>
                    </nav>
                </div>

                <!-- Summon -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSummon" aria-expanded="false" aria-controls="collapseSummon">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tags"></i></div>
                    Summon
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseSummon" aria-labelledby="headingSummon" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-manage-summon.php">List of Summon</a>
                    </nav>
                </div>

            </div>
        </div>
        
        <!-- Sidenav Footer -->
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <p>Admin</p>
        </div>
    </nav>
</div>

