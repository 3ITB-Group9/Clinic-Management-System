<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

// Get the patient ID from the URL
$patient_id = $_GET['patient_id'] ?? null;
if (!$patient_id) {
    echo "Invalid Patient ID.";
    exit();
}

// Fetch patient details
$patient_query = "SELECT name, email FROM users WHERE id = ? AND role = 'patient'";
$patient_stmt = $conn->prepare($patient_query);
$patient_stmt->bind_param("i", $patient_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();
$patient = $patient_result->fetch_assoc();

// Fetch medical history
$history_query = "SELECT description, date_added FROM medical_history WHERE patient_id = ? ORDER BY date_added DESC";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $patient_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical History</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
        }
        .card-header {
            font-size: 18px;
            font-weight: bold;
        }
        .list-group-item {
            border-left: 3px solid #007bff;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="text-center">Medical History</h3>
    <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
    <a href="doctor_dashboard.php" class="btn btn-secondary btn-sm mb-3">Back to Dashboard</a>

    <div class="card">
        <div class="card-header bg-primary text-white">
            History Records
        </div>
        <div class="card-body">
            <?php if ($history_result->num_rows > 0) { ?>
                <ul class="list-group">
                    <?php while ($record = $history_result->fetch_assoc()) { ?>
                        <li class="list-group-item">
                            <strong>Date:</strong> <?php echo $record['date_added']; ?><br>
                            <strong>Description:</strong> <?php echo htmlspecialchars($record['description']); ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p class="text-muted text-center">No medical history found.</p>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
