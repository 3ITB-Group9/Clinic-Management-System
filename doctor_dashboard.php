<?php
// Start the session to check if the user is logged in
session_start();

// Include database connection
include('db.php');

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// Handle adding a medical record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medical_record'])) {
    $patient_id = $_POST['patient_id'];
    $description = $_POST['description'];

    $query = "INSERT INTO medical_history (patient_id, doctor_id, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $patient_id, $doctor_id, $description);
    $stmt->execute();
    $stmt->close();
}

// Handle adding a diagnosis and creating an appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_diagnosis'])) {
    $patient_id = $_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $appointment_date = $_POST['appointment_date'];

    // Insert the diagnosis into the database
    $query = "INSERT INTO diagnoses (patient_id, doctor_id, diagnosis) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $patient_id, $doctor_id, $diagnosis);
    $stmt->execute();
    $stmt->close();

    // Create an appointment for the patient
    $appointment_query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'Scheduled')";
    $appointment_stmt = $conn->prepare($appointment_query);
    $appointment_stmt->bind_param("iis", $patient_id, $doctor_id, $appointment_date);
    $appointment_stmt->execute();
    $appointment_stmt->close();

    // Redirect to the same page after adding diagnosis and appointment
    header("Location: doctor_dashboard.php");
    exit();
}

// Handle adding a medication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medication'])) {
    $patient_id = $_POST['patient_id'];
    $medication_name = $_POST['medication_name'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];

    $query = "INSERT INTO medications (patient_id, doctor_id, medication_name, dosage, instructions) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $medication_name, $dosage, $instructions);
    $stmt->execute();
    $stmt->close();
}

// Fetch appointments
$query = "SELECT appointment_date, reason, status, p.name AS patient_name 
          FROM appointments app
          JOIN users p ON app.patient_id = p.id
          WHERE app.doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom styles -->
</head>
<style>
    /* Custom styling for logout button */
    .logout-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 16px;
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }
</style>

<body>
    <div class="container mt-5">
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

        <h2 class="text-center">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h2>

        <!-- Add Medical Record -->
        <div class="card my-4">
            <div class="card-header">
                <h3>Add Medical Record</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <input type="number" name="patient_id" class="form-control" required placeholder="Enter Patient ID">
                    </div>
                    <div class="form-group">
                        <textarea name="description" class="form-control" required placeholder="Enter medical record..."></textarea>
                    </div>
                    <button type="submit" name="add_medical_record" class="btn btn-primary">Add Medical Record</button>
                </form>
            </div>
        </div>

        <!-- Add Diagnosis & Appointment -->
        <div class="card my-4">
            <div class="card-header">
                <h3>Add Diagnosis & Appointment</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <input type="number" name="patient_id" class="form-control" required placeholder="Enter Patient ID">
                    </div>
                    <div class="form-group">
                        <textarea name="diagnosis" class="form-control" required placeholder="Enter diagnosis..."></textarea>
                    </div>
                    <div class="form-group">
                        <input type="date" name="appointment_date" class="form-control" required placeholder="Select Appointment Date">
                    </div>
                    <button type="submit" name="add_diagnosis" class="btn btn-primary">Add Diagnosis & Appointment</button>
                </form>
            </div>
        </div>

        <!-- Prescribe Medication -->
        <div class="card my-4">
            <div class="card-header">
                <h3>Prescribe Medication</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <input type="number" name="patient_id" class="form-control" required placeholder="Enter Patient ID">
                    </div>
                    <div class="form-group">
                        <input type="text" name="medication_name" class="form-control" required placeholder="Medication Name">
                    </div>
                    <div class="form-group">
                        <input type="text" name="dosage" class="form-control" required placeholder="Dosage">
                    </div>
                    <div class="form-group">
                        <textarea name="instructions" class="form-control" required placeholder="Instructions"></textarea>
                    </div>
                    <button type="submit" name="add_medication" class="btn btn-primary">Prescribe Medication</button>
                </form>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="card my-4">
            <div class="card-header">
                <h3>Appointments</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $appointments->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Appointment Date:</strong> <?php echo $row['appointment_date']; ?>
                            - <strong>Status:</strong> <?php echo $row['status']; ?>
                            - <strong>Patient:</strong> <?php echo $row['patient_name']; ?>
                            - <strong>Reason:</strong> <?php echo $row['reason']; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>