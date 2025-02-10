<?php
// Start the session to check if the user is logged in
session_start();

// Include database connection
include('db.php');

// Check if the user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Fetch medical history
$query = "SELECT mh.description, mh.date_added, d.name AS doctor_name 
          FROM medical_history mh
          JOIN users d ON mh.doctor_id = d.id
          WHERE mh.patient_id = ? ORDER BY mh.date_added DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$medical_history = $stmt->get_result();

// Fetch diagnoses
$query = "SELECT dgn.diagnosis, dgn.date_diagnosed, d.name AS doctor_name 
          FROM diagnoses dgn
          JOIN users d ON dgn.doctor_id = d.id
          WHERE dgn.patient_id = ? ORDER BY dgn.date_diagnosed DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$diagnoses = $stmt->get_result();

// Fetch medications
$query = "SELECT med.medication_name, med.dosage, med.instructions, med.prescribed_date, d.name AS doctor_name 
          FROM medications med
          JOIN users d ON med.doctor_id = d.id
          WHERE med.patient_id = ? ORDER BY med.prescribed_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$medications = $stmt->get_result();

// Fetch appointments
$query = "SELECT appointment_date, reason, status 
          FROM appointments
          WHERE patient_id = ? ORDER BY appointment_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
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

        <h2 class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h2>

        <div class="card my-4">
            <div class="card-header">
                <h3>Medical History</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $medical_history->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Doctor:</strong> <?php echo $row['doctor_name']; ?> - <?php echo $row['description']; ?>
                            <span class="badge badge-info float-right"><?php echo $row['date_added']; ?></span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="card my-4">
            <div class="card-header">
                <h3>Diagnoses</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $diagnoses->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Diagnosis:</strong> <?php echo $row['diagnosis']; ?>
                            <span class="badge badge-warning float-right"><?php echo $row['date_diagnosed']; ?></span>
                            <br><strong>Doctor:</strong> <?php echo $row['doctor_name']; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="card my-4">
            <div class="card-header">
                <h3>Medications</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $medications->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Medication:</strong> <?php echo $row['medication_name']; ?>, <?php echo $row['dosage']; ?>
                            <br><strong>Instructions:</strong> <?php echo $row['instructions']; ?>
                            <span class="badge badge-success float-right"><?php echo $row['prescribed_date']; ?></span>
                            <br><strong>Doctor:</strong> <?php echo $row['doctor_name']; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="card my-4">
            <div class="card-header">
                <h3>Your Appointments</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $appointments->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Appointment Date:</strong> <?php echo $row['appointment_date']; ?>
                            - <strong>Status:</strong> <?php echo $row['status']; ?>
                            - <strong>Doctor:</strong> <?php echo $row['doctor_id']; ?> <!-- You can fetch doctor details if needed -->
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
$stmt->close();
$conn->close();
?>