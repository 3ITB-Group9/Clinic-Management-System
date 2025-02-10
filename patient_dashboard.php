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

// Fetch patient details
$query = "SELECT name, contact_number, address FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_details = $stmt->get_result()->fetch_assoc();

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
$query = "SELECT dgn.diagnosis, dgn.date_diagnosed, dgn.heart_rate, dgn.blood_pressure, dgn.height, dgn.weight, d.name AS doctor_name 
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
$query = "SELECT appointment_date, status, doctor_id
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom styles -->
</head>

<style>
    body {
        background-color: #f4f7fc;
    }

    .sidebar {
        width: 250px;
        height: 100vh;
        background: rgb(22, 98, 228);
        position: fixed;
        top: 0;
        left: 0;
        padding: 20px;
        color: white;
    }

    .sidebar a {
        display: block;
        color: white;
        padding: 10px;
        text-decoration: none;
        margin-bottom: 10px;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 5px;
    }

    .content {
        margin-left: 270px;
        padding: 20px;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Patient Panel</h3>
        <a href="#"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-user-md"></i> Doctors</a>
        <a href="#"><i class="fas fa-file-medical"></i> Medical History</a>
        <a href="#"><i class="fas fa-pills"></i> Medications</a>
        <a href="#"><i class="fas fa-calendar-check"></i> Appointments</a>
        <a href="logout.php" class="btn btn-danger w-100 mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <h2 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h2>

        <!-- Patient Details -->
        <div class="card my-4">
            <div class="card-header bg-info text-white">
                <h4><i class="fas fa-user"></i> Patient Details</h4>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo $patient_details['name']; ?></p>
                <p><strong>Contact:</strong> <?php echo $patient_details['contact_number']; ?></p>
                <p><strong>Address:</strong> <?php echo $patient_details['address']; ?></p>
            </div>
        </div>

        <!-- Medical History -->
        <div class="card my-4">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-notes-medical"></i> Medical History</h4>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $medical_history->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Doctor:</strong> <?php echo $row['doctor_name']; ?> - <?php echo $row['description']; ?>
                            <span class="badge bg-info float-end"><?php echo $row['date_added']; ?></span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Diagnoses -->
        <div class="card my-4">
            <div class="card-header bg-warning text-white">
                <h4><i class="fas fa-stethoscope"></i> Diagnoses</h4>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $diagnoses->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Diagnosis:</strong> <?php echo $row['diagnosis']; ?>
                            <span class="badge bg-warning float-end"><?php echo $row['date_diagnosed']; ?></span>
                            <br><strong>Doctor:</strong> <?php echo $row['doctor_name']; ?>
                            <br><strong>Heart Rate:</strong> <?php echo $row['heart_rate']; ?> bpm
                            <br><strong>Blood Pressure:</strong> <?php echo $row['blood_pressure']; ?>
                            <br><strong>Height:</strong> <?php echo $row['height']; ?> cm
                            <br><strong>Weight:</strong> <?php echo $row['weight']; ?> kg
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Medications -->
        <div class="card my-4">
            <div class="card-header bg-success text-white">
                <h4><i class="fas fa-pills"></i> Medications</h4>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $medications->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Medication:</strong> <?php echo $row['medication_name']; ?>
                            <br><strong>Dosage:</strong> <?php echo $row['dosage']; ?>
                            <br><strong>Instructions:</strong> <?php echo $row['instructions']; ?>
                            <span class="badge bg-success float-end"><?php echo $row['prescribed_date']; ?></span>
                            <br><strong>Doctor:</strong> <?php echo $row['doctor_name']; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Appointments -->
        <div class="card my-4">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-calendar-alt"></i> Your Appointments</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Doctor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $appointments->fetch_assoc()) { 
                            // Get doctor's name for each appointment
                            $doctor_query = "SELECT name FROM users WHERE id = ?";
                            $stmt = $conn->prepare($doctor_query);
                            $stmt->bind_param("i", $row['doctor_id']);
                            $stmt->execute();
                            $doctor_result = $stmt->get_result()->fetch_assoc();
                        ?>
                            <tr>
                                <td><?php echo $row['appointment_date']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $doctor_result['name']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
