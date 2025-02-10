<?php
session_start();
include('db.php');

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
    $query = "INSERT INTO diagnoses (patient_id, doctor_id, diagnosis) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $patient_id, $doctor_id, $diagnosis);
    $stmt->execute();
    $stmt->close();
    
    $appointment_query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'Scheduled')";
    $appointment_stmt = $conn->prepare($appointment_query);
    $appointment_stmt->bind_param("iis", $patient_id, $doctor_id, $appointment_date);
    $appointment_stmt->execute();
    $appointment_stmt->close();
    header("Location: doctor_dashboard.php");
    exit();
}

// Handle adding a medication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medication'])) {
    $patient_id = $_POST['patient_id'];
    $medication_name = $_POST['medication_name'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];
    $query = "INSERT INTO medications (patient_id, doctor_id, medication_name, dosage, instructions) VALUES (?, ?, ?, ?, ?)";
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
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
        }
        .sidebar a {
            padding: 15px;
            display: block;
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .card {
            transition: 0.3s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        
    </style>
</head>

<body>

<div class="sidebar">
    <h4 class="text-center">Doctor Dashboard</h4>
    <a href="#">Home</a>
    <a href="#">Patients</a>
    <a href="#">Appointments</a>
    <a href="#">Medications</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h2 class="text-center">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h2>

    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

    <!-- Patient Search -->
<div class="card my-4">
    <div class="card-header bg-info text-white">
        <h3>Search for a Patient</h3>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="form-group">
                <input type="text" name="search_query" class="form-control" placeholder="Enter Patient Name or ID">
            </div>
            <button type="submit" class="btn btn-info">Search</button>
        </form>
    </div>
</div>

<?php
// Check if a search query is provided
if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    $search_query = "%" . $_GET['search_query'] . "%";
    
    // Search for patients by name or ID
    $search_sql = "SELECT id, name, email FROM users WHERE (id LIKE ? OR name LIKE ?) AND role = 'patient'";
    $search_stmt = $conn->prepare($search_sql);
    $search_stmt->bind_param("ss", $search_query, $search_query);
    $search_stmt->execute();
    $search_results = $search_stmt->get_result();
?>
    <div class="card my-4">
        <div class="card-header bg-warning text-white">
            <h3>Search Results</h3>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php if ($search_results->num_rows > 0) {
                    while ($patient = $search_results->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>ID:</strong> <?php echo $patient['id']; ?> 
                            - <strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?> 
                            - <strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?>
                        </li>
                    <?php }
                } else { ?>
                    <li class="list-group-item text-danger">No results found.</li>
                <?php } ?>
            </ul>
        </div>
    </div>
<?php
    $search_stmt->close();
}
?>


    <!-- Add Medical Record -->
    <div class="card my-4">
        <div class="card-header bg-primary text-white">
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
        <div class="card-header bg-success text-white">
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
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>
                <button type="submit" name="add_diagnosis" class="btn btn-success">Add Diagnosis & Appointment</button>
            </form>
        </div>
    </div>

    <!-- Add Medication -->
<div class="card my-4">
    <div class="card-header bg-primary text-white">
        <h3>Add Medication</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <input type="number" name="patient_id" class="form-control" required placeholder="Enter Patient ID">
            </div>
            <div class="form-group">
                <input type="text" name="medication_name" class="form-control" required placeholder="Enter Medication Name">
            </div>
            <div class="form-group">
                <input type="text" name="dosage" class="form-control" required placeholder="Enter Dosage">
            </div>
            <div class="form-group">
                <textarea name="instructions" class="form-control" required placeholder="Enter Instructions"></textarea>
            </div>
            <button type="submit" name="add_medication" class="btn btn-primary">Add Medication</button>
        </form>
    </div>
</div>


    <!-- Appointments List -->
    <div class="card my-4">
        <div class="card-header bg-dark text-white">
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
</body>
</html>
