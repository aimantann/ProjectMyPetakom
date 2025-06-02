<?php
// DB connection
include('includes/header.php');
include('includes/dbconnection.php');

// Fetch registered events
$eventQuery = "SELECT E_eventID, E_name, E_startDate FROM event";
$eventResult = $conn->query($eventQuery);

// Fetch registered students
$studentQuery = "SELECT U_userID, U_name FROM user WHERE U_usertype = 'Student'";
$studentResult = $conn->query($studentQuery);
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
    </style>
</head>
<body>
    
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form id="committeeForm" method="POST" action="process_committee.php">
                <div class="card border-0 shadow">
                    
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
             <h4 class="mb-0">Assign Committee Member</h4>
            
        </div>
                    <div class="card-body">
                        <!-- 1. Select Event -->
                        <div class="mb-4">
                            <h5>1. Select Event</h5>
                            <select class="form-select mt-2" id="E_eventID" name="E_eventID" required>
                                <option value="" selected disabled>Choose an event...</option>
                                <?php while ($row = $eventResult->fetch_assoc()): ?>
                                    <option value="<?= $row['E_eventID'] ?>">
                                        <?= htmlspecialchars($row['E_name']) ?> - <?= date('M d', strtotime($row['E_startDate'])) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- 2. Select Student -->
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


                        <!-- 3. Assign Position -->
                        <div class="mb-3">
                            <h5>3. Assign Position</h5>
                            <div class="row mt-2">
                                <div class="col-md-8">
                                    <select class="form-select" id="C_position" name="C_position" required>
                                        <option value="" selected disabled>Select committee role...</option>
                                        <option value="Main Committee">Main Committee</option>
                                        <option value="Committee">Committee</option>
                                    </select>
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
    });
</script>
<?php
include('includes/footer.php');
?>
</body>
</html>