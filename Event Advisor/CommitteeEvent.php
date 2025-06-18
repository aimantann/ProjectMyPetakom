<?php
require_once('user-validatesession.php');

include('includes/dbconnection.php');

// Set timezone to match student-view-merit.php
date_default_timezone_set('Asia/Kuala_Lumpur');

// Fetch registered events with their levels
$eventQuery = "SELECT E_eventID, E_name, E_startDate, E_level FROM event";
$eventResult = $conn->query($eventQuery);

// Fetch registered students
$studentQuery = "SELECT U_userID, U_name FROM user WHERE U_usertype = 'Student'";
$studentResult = $conn->query($studentQuery);

include('includes/header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Committee | MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .student-card {
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 10px;
        }
        .student-card:hover {
            background-color: #f8f9fa;
        }
        .student-card.selected {
            background-color: #e7f1ff;
            border-left: 4px solid #0d6efd;
        }
        #studentList {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .merit-info {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 10px;
        }
        .merit-points {
            font-weight: bold;
            color: #198754;
        }
        .current-info {
            font-size: 0.8em;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    
<div class="container py-4">
    

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            if (isset($_SESSION['last_awarded_user'])) {
                $lastUserId = $_SESSION['last_awarded_user'];
                $verifyQuery = "SELECT m.MD_meritPoint, u.U_name, e.E_name, e.E_level, mc.MC_role 
                               FROM meritawarded m 
                               JOIN user u ON m.U_userID = u.U_userID 
                               JOIN event e ON m.E_eventID = e.E_eventID
                               JOIN meritclaim mc ON m.E_eventID = mc.E_eventID AND m.U_userID = mc.U_userID
                               WHERE m.U_userID = ? 
                               ORDER BY m.MD_awardedDate DESC LIMIT 1";
                $stmt = $conn->prepare($verifyQuery);
                $stmt->bind_param("i", $lastUserId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    echo "Committee member has been assigned successfully!<br>";
                    echo sprintf(
                        "Merit Points Awarded: %d points to %s for %s event %s as %s",
                        $row['MD_meritPoint'],
                        htmlspecialchars($row['U_name']),
                        htmlspecialchars($row['E_level']),
                        htmlspecialchars($row['E_name']),
                        htmlspecialchars($row['MC_role'])
                    );
                } else {
                    echo "Committee member has been assigned successfully!";
                }
            } else {
                echo "Committee member has been assigned successfully!";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            switch ($_GET['error']) {
                case 'committee':
                    echo "Error assigning committee member.";
                    break;
                case 'merit':
                    echo "Error awarding merit points.";
                    break;
                case 'duplicate':
                    echo "Student is already assigned to this event.";
                    break;
                default:
                    echo "An error occurred.";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form id="committeeForm" method="POST" action="process_committee.php">
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Assign Committee Member</h4>
                    </div>
                    <div class="card-body">
                        <!-- Semester Selection -->
                        <div class="mb-4">
                            <h5>Select Semester</h5>
                            <select class="form-select mt-2" id="ES_semester" name="ES_semester" required>
                                <option value="" selected disabled>Choose a semester...</option>
                                <option value="SEMESTER II ACADEMIC SESSION 2024/2025">SEMESTER II ACADEMIC SESSION 2024/2025</option>
                                <option value="SEMESTER I ACADEMIC SESSION 2024/2025">SEMESTER I ACADEMIC SESSION 2024/2025</option>
                                <option value="SEMESTER II ACADEMIC SESSION 2023/2024">SEMESTER II ACADEMIC SESSION 2023/2024</option>
                                <option value="SEMESTER I ACADEMIC SESSION 2023/2024">SEMESTER I ACADEMIC SESSION 2023/2024</option>
                                <option value="SEMESTER II ACADEMIC SESSION 2022/2023">SEMESTER II ACADEMIC SESSION 2022/2023</option>
                                <option value="SEMESTER I ACADEMIC SESSION 2022/2023">SEMESTER I ACADEMIC SESSION 2022/2023</option>
                            </select>
                        </div>

                        <!-- Event Selection -->
                        <div class="mb-4">
                            <h5>1. Select Event</h5>
                            <select class="form-select mt-2" id="E_eventID" name="E_eventID" required>
                                <option value="" selected disabled>Choose an event...</option>
                                <?php while ($row = $eventResult->fetch_assoc()): ?>
                                    <option value="<?= $row['E_eventID'] ?>" data-level="<?= $row['E_level'] ?>">
                                        <?= htmlspecialchars($row['E_name']) ?> - 
                                        <?= date('M d', strtotime($row['E_startDate'])) ?> 
                                        (<?= htmlspecialchars($row['E_level']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Student Selection -->
                        <div class="mb-4">
                            <h5>2. Select Student</h5>
                            <input type="text" class="form-control mb-2" id="searchInput" placeholder="Search students...">
                            <div id="studentList">
                                <?php while ($student = $studentResult->fetch_assoc()): ?>
                                    <div class="card student-card" data-userid="<?= $student['U_userID'] ?>">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1"><?= htmlspecialchars($student['U_name']) ?></h6>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <input type="hidden" id="U_userID" name="U_userID" required>
                        </div>

                        <!-- Position Assignment -->
                        <div class="mb-3">
                            <h5>3. Assign Position</h5>
                            <div class="row mt-2">
                                <div class="col-md-8">
                                    <select class="form-select" id="C_position" name="C_position" required>
                                        <option value="" selected disabled>Select committee role...</option>
                                        <option value="Main Committee">Main Committee</option>
                                        <option value="Committee">Committee</option>
                                    </select>
                                    <div class="merit-info mt-2">
                                        Merit Points to be awarded: <span id="meritPoints" class="merit-points">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Assign</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const studentCards = document.querySelectorAll('.student-card');
        const userIDField = document.getElementById('U_userID');
        const eventSelect = document.getElementById('E_eventID');
        const positionSelect = document.getElementById('C_position');
        const meritPointsDisplay = document.getElementById('meritPoints');

        // Merit points lookup table
        const meritPoints = {
            'International': {'Main Committee': 100, 'Committee': 70},
            'National': {'Main Committee': 80, 'Committee': 50},
            'State': {'Main Committee': 60, 'Committee': 40},
            'District': {'Main Committee': 40, 'Committee': 30},
            'UMPSA': {'Main Committee': 30, 'Committee': 20}
        };

        // Update merit points display
        function updateMeritPoints() {
            const selectedEvent = eventSelect.options[eventSelect.selectedIndex];
            const position = positionSelect.value;
            
            if (selectedEvent && position) {
                const level = selectedEvent.dataset.level;
                const points = meritPoints[level]?.[position] || '-';
                meritPointsDisplay.textContent = points;
            } else {
                meritPointsDisplay.textContent = '-';
            }
        }

        // Event listeners for merit points calculation
        eventSelect.addEventListener('change', updateMeritPoints);
        positionSelect.addEventListener('change', updateMeritPoints);

        // Student selection
        studentCards.forEach(card => {
            card.addEventListener('click', function () {
                studentCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                userIDField.value = this.getAttribute('data-userid');
            });
        });

        // Filter students
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', function () {
            const keyword = this.value.toLowerCase();
            studentCards.forEach(card => {
                const name = card.textContent.toLowerCase();
                card.style.display = name.includes(keyword) ? 'block' : 'none';
            });
        });

        // Form validation
        const form = document.getElementById('committeeForm');
        form.addEventListener('submit', function(e) {
            if (!userIDField.value) {
                e.preventDefault();
                alert('Please select a student');
            }
        });
    });
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>