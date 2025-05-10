<?php

// Include header file
include('includes/header.php');

// Include database connection file
include('includes/dbconnection.php');

// Query to get the count of registered users
$queryUsers = "SELECT COUNT(*) AS totalUsers FROM student";
$resultUsers = $conn->query($queryUsers);
$totalUsers = $resultUsers->fetch_assoc()['totalUsers'];

// Query to get the count of undergraduate and postgraduate students
$queryUndergraduate = "SELECT COUNT(*) AS totalUndergraduate FROM student WHERE studentType = 'Undergraduate'";
$resultUndergraduate = $conn->query($queryUndergraduate);
$totalUndergraduate = $resultUndergraduate->fetch_assoc()['totalUndergraduate'];

$queryPostgraduate = "SELECT COUNT(*) AS totalPostgraduate FROM student WHERE studentType = 'Postgraduate'";
$resultPostgraduate = $conn->query($queryPostgraduate);
$totalPostgraduate = $resultPostgraduate->fetch_assoc()['totalPostgraduate'];

// Query to get the count of registered vehicles
$queryVehicles = "SELECT COUNT(*) AS totalVehicles FROM vehicle";
$resultVehicles = $conn->query($queryVehicles);
$totalVehicles = $resultVehicles->fetch_assoc()['totalVehicles'];

// Query to get the count of cars and motorcycles
$queryCars = "SELECT COUNT(*) AS totalCars FROM vehicle WHERE vehicleType = 'Car'";
$resultCars = $conn->query($queryCars);
$totalCars = $resultCars->fetch_assoc()['totalCars'];

$queryMotorcycles = "SELECT COUNT(*) AS totalMotorcycles FROM vehicle WHERE vehicleType = 'Motorcycle'";
$resultMotorcycles = $conn->query($queryMotorcycles);
$totalMotorcycles = $resultMotorcycles->fetch_assoc()['totalMotorcycles'];

// Query to get the number of students registered per year
$queryYearCounts = "SELECT studentYear, COUNT(*) as count FROM student GROUP BY studentYear";
$resultYearCounts = $conn->query($queryYearCounts);
$yearCounts = $resultYearCounts->fetch_all(MYSQLI_ASSOC);

// Query to get the number of bookings per week for the last 10 weeks
$queryBookingsPerWeek = "SELECT YEARWEEK(bookingDate) as yearWeek, COUNT(*) as count 
                         FROM booking_history 
                         GROUP BY YEARWEEK(bookingDate) 
                         ORDER BY YEARWEEK(bookingDate) DESC 
                         LIMIT 10";
$resultBookingsPerWeek = $conn->query($queryBookingsPerWeek);
$bookingsPerWeek = array_reverse($resultBookingsPerWeek->fetch_all(MYSQLI_ASSOC)); // Reverse to show oldest first

// Fetch parking space data from the database
$queryParking = "SELECT parkingAvailabilityStatus, COUNT(*) AS count FROM parkingspace GROUP BY parkingAvailabilityStatus";
$resultParking = $conn->query($queryParking);

$totalSpaces = 0;
$availableSpaces = 0;
$unavailableSpaces = 0;

while ($row = $resultParking->fetch_assoc()) {
    if ($row['parkingAvailabilityStatus'] == 'Available') {
        $availableSpaces = $row['count'];
    } else {
        $unavailableSpaces = $row['count'];
    }
    $totalSpaces += $row['count'];
}

// Query to get the total number of bookings today, this week, and this month
$queryBookingsToday = "SELECT COUNT(*) AS totalBookingsToday FROM booking WHERE DATE(bookingDate) = CURDATE()";
$resultBookingsToday = $conn->query($queryBookingsToday);
$totalBookingsToday = $resultBookingsToday->fetch_assoc()['totalBookingsToday'];

$queryBookingsThisWeek = "SELECT COUNT(*) AS totalBookingsThisWeek FROM booking WHERE WEEK(bookingDate) = WEEK(CURDATE())";
$resultBookingsThisWeek = $conn->query($queryBookingsThisWeek);
$totalBookingsThisWeek = $resultBookingsThisWeek->fetch_assoc()['totalBookingsThisWeek'];

$queryBookingsThisMonth = "SELECT COUNT(*) AS totalBookingsThisMonth FROM booking WHERE MONTH(bookingDate) = MONTH(CURDATE())";
$resultBookingsThisMonth = $conn->query($queryBookingsThisMonth);
$totalBookingsThisMonth = $resultBookingsThisMonth->fetch_assoc()['totalBookingsThisMonth'];

// Convert PHP arrays to JavaScript
$yearLabels = json_encode(array_column($yearCounts, 'studentYear'));
$yearData = json_encode(array_column($yearCounts, 'count'));
$weekLabels = json_encode(array_column($bookingsPerWeek, 'yearWeek'));
$weekData = json_encode(array_column($bookingsPerWeek, 'count'));

// Close the database connection
$conn->close();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Admin Dashboard</h1>
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <div class="row">
        <!-- Registered Users Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <span class="card-title">Registered Students:</span>
                    <span class="card-count"><?php echo $totalUsers; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Total Undergraduate: <?php echo $totalUndergraduate; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Total Postgraduate: <?php echo $totalPostgraduate; ?></span>
                </div>
            </div>
        </div>
        <!-- Registered Vehicles Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <span class="card-title">Registered Vehicles:</span>
                    <span class="card-count"><?php echo $totalVehicles; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Cars: <?php echo $totalCars; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Motorcycles: <?php echo $totalMotorcycles; ?></span>
                </div>
            </div>
        </div>
        <!-- Parking Spaces Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <span class="card-title">Parking Spaces:</span>
                    <span class="card-count"><?php echo $totalSpaces; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Available: <?php echo $availableSpaces; ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Unavailable: <?php echo $unavailableSpaces; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bookings Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <span class="card-title">Today's Booking:</span>
                    <span class="card-count"><?php echo $totalBookingsToday; ?></span>
                </div>
                <div class="card-body">
                    <span class="card-title">Booking This Week:</span>
                    <span class="card-count"><?php echo $totalBookingsThisWeek; ?></span>
                </div>
                <div class="card-body">
                    <span class="card-title">Booking This Month:</span>
                    <span class="card-count"><?php echo $totalBookingsThisMonth; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bar Chart -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Registered Students (Year of Study)
                </div>
                <div class="card-body">
                    <canvas id="studentsYearChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <!-- Pie Chart -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Registered Vehicles Breakdown
                </div>
                <div class="card-body">
                    <canvas id="vehiclesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Parking Availability Pie Chart -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Parking Availability Status
                </div>
                <div class="card-body">
                    <canvas id="parkingPieChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Average Bookings Per Week Bar Chart -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Bookings Per Week
                </div>
                <div class="card-body">
                    <canvas id="bookingsPerWeekChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer and scripts
include('includes/footer.php');
include('includes/scripts.php');
?>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data for the bar chart
    const yearLabels = <?php echo $yearLabels; ?>;
    const yearData = <?php echo $yearData; ?>;

    // Data for the pie chart
    const vehiclesData = {
        labels: ["Cars", "Motorcycles"],
        datasets: [{
            data: [<?php echo $totalCars; ?>, <?php echo $totalMotorcycles; ?>],
            backgroundColor: ["#007bff", "#dc3545"],
        }],
    };

    // Data for the bookings per week bar chart
    const weekLabels = <?php echo $weekLabels; ?>;
    const weekData = <?php echo $weekData; ?>;

    // Bar chart configuration for students per year
    var ctxBar = document.getElementById("studentsYearChart");
    var myBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: yearLabels,
            datasets: [{
                label: "Number of Students",
                backgroundColor: "rgba(2,117,216,1)",
                borderColor: "rgba(2,117,216,1)",
                data: yearData,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    time: {
                        unit: 'year'
                    },
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 6
                    }
                }],
                yAxes: [{
                    ticks: {
                        min: 0,
                        max: Math.max(...yearData) + 10, // Adjust max value dynamically
                        maxTicksLimit: 5
                    },
                    gridLines: {
                        display: true
                    }
                }],
            },
            legend: {
                display: false
            },
        }
    });

    // Pie chart configuration for vehicles
    var ctxPie = document.getElementById("vehiclesChart");
    var myPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: vehiclesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Registered Vehicles Breakdown'
            }
        }
    });

    // Bar chart configuration for bookings per week
    var ctxBookingsBar = document.getElementById("bookingsPerWeekChart");
    var myBookingsBarChart = new Chart(ctxBookingsBar, {
        type: 'bar',
        data: {
            labels: weekLabels,
            datasets: [{
                label: "Bookings per Week",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1,
                data: weekData,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: Math.max(...weekData) + 5, // Adjust max value dynamically
                        maxTicksLimit: 5
                    },
                    gridLines: {
                        display: true
                    }
                }],
            },
            legend: {
                display: false
            },
        }
    });

    // Pie chart configuration for parking spaces
    var ctxParkingPie = document.getElementById("parkingPieChart");
    var myParkingPieChart = new Chart(ctxParkingPie, {
        type: 'pie',
        data: {
            labels: ["Available", "Unavailable"],
            datasets: [{
                data: [<?php echo $availableSpaces; ?>, <?php echo $unavailableSpaces; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
</script>

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
