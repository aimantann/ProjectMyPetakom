<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <form id="committeeForm">
                    <div class="card border-0 shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Assign Committee Member</h4>
                        </div>
                        
                        <div class="card-body">
                            <!-- Hidden field for committee ID -->
                            <input type="hidden" id="C_committeeID" name="C_committeeID">
                            
                            <!-- Step 1: Select Event (E_eventID) -->
                            <div class="mb-4">
                                <h5>1. Select Event</h5>
                                <select class="form-select mt-2" id="E_eventID" name="E_eventID" required>
                                    <option value="" selected disabled>Choose an event...</option>
                                    <option value="1">Leadership Workshop - Nov 15</option>
                                    <option value="2">Community Service - Dec 5</option>
                                    <option value="3">Tech Challenge - Jan 20</option>
                                </select>
                            </div>
                            
                            <!-- Step 2: Select Student (U_userID) -->
                            <div class="mb-4">
                                <h5>2. Select Student</h5>
                                <div class="input-group mt-2">
                                    <input type="text" class="form-control" placeholder="Search students...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="studentList" class="mt-3">
                                    <!-- Student cards with U_userID as data attribute -->
                                    <div class="card student-card" data-userid="101">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1">Ali bin Ahmad</h6>
                                            <small class="text-muted">AB12345</small>
                                        </div>
                                    </div>
                                    <div class="card student-card" data-userid="102">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1">Siti binti Mohd</h6>
                                            <small class="text-muted">CD67890</small>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="U_userID" name="U_userID" required>
                            </div>
                            
                            <!-- Step 3: Assign Position (C_position) -->
                            <div class="mb-3">
                                <h5>3. Assign Position</h5>
                                <div class="row mt-2">
                                    <div class="col-md-8">
                                        <select class="form-select" id="C_position" name="C_position" required>
                                            <option value="" selected disabled>Select committee role...</option>
                                            <option value="Chairperson">Main Committee</option>
                                            <option value="Vice Chair">Committee</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Assign
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('committeeForm');
            const studentCards = document.querySelectorAll('.student-card');
            const userIDField = document.getElementById('U_userID');
            
            // Student selection
            studentCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selection from all cards
                    studentCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selection to clicked card
                    this.classList.add('selected');
                    
                    // Set the U_userID value
                    userIDField.value = this.getAttribute('data-userid');
                });
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!userIDField.value) {
                    alert('Please select a student');
                    return;
                }
                
                // Prepare form data according to your table structure
                const formData = {
                    C_committeeID: document.getElementById('C_committeeID').value,
                    C_position: document.getElementById('C_position').value,
                    U_userID: userIDField.value,
                    E_eventID: document.getElementById('E_eventID').value
                };
                
                console.log('Submitting:', formData);
                
                // In a real application, you would send this to your backend
                // Example using fetch:
                /*
                fetch('/api/committee', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    alert('Assignment successful!');
                    form.reset();
                    studentCards.forEach(c => c.classList.remove('selected'));
                })
                .catch(error => {
                    console.error('Error:', error);
                });
                */
                
                // For demo purposes:
                alert(`Assignment submitted:\nEvent ID: ${formData.E_eventID}\nUser ID: ${formData.U_userID}\nPosition: ${formData.C_position}`);
                form.reset();
                studentCards.forEach(c => c.classList.remove('selected'));
                userIDField.value = '';
            });
        });
    </script>
</body>
</html>