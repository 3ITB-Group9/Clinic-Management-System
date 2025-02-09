<?php
session_start();

// Dummy authentication: Assume the user is a doctor
$_SESSION['user_id'] = 1;  // Example user ID
$_SESSION['role'] = 'doctor';  // Only doctors can add symptoms & records

// Ensure only doctors can access patient records and add symptoms
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    die("Unauthorized access.");
}

// Initialize session arrays
if (!isset($_SESSION['symptoms'])) {
    $_SESSION['symptoms'] = [];
}

if (!isset($_SESSION['patients'])) {
    $_SESSION['patients'] = [];
}

// Handle symptom submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_name'], $_POST['symptoms'])) {
    $patient_name = htmlspecialchars($_POST['patient_name']);
    $symptoms = htmlspecialchars($_POST['symptoms']);

    if (!empty($patient_name) && !empty($symptoms)) {
        $_SESSION['symptoms'][] = [
            'patient_name' => $patient_name,
            'symptoms' => $symptoms,
            'timestamp' => date("Y-m-d H:i:s")
        ];
        $message = "Symptoms added successfully!";
    } else {
        $message = "Please fill in all fields.";
    }
}

// Handle new patient record submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_id'], $_POST['patient_name_record'], $_POST['diagnosis'], $_POST['treatment'], $_POST['medications'])) {
    $patient_id = htmlspecialchars($_POST['patient_id']);
    $patient_name = htmlspecialchars($_POST['patient_name_record']);
    $diagnosis = htmlspecialchars($_POST['diagnosis']);
    $treatment = htmlspecialchars($_POST['treatment']);
    $medications = htmlspecialchars($_POST['medications']);

    if (!empty($patient_id) && !empty($patient_name) && !empty($diagnosis) && !empty($treatment) && !empty($medications)) {
        $_SESSION['patients'][] = [
            'id' => $patient_id,
            'name' => $patient_name,
            'diagnosis' => $diagnosis,
            'treatment' => $treatment,
            'medications' => $medications
        ];
        $message = "Patient medical history added successfully!";
    } else {
        $message = "Please fill in all fields.";
    }
}

// Handle search for patient history
$search_results = [];
$no_results = false; // Flag to track if no results are found
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_query'])) {
    $search_query = strtolower(trim($_POST['search_query']));

    foreach ($_SESSION['patients'] as $patient) {
        if (strpos(strtolower($patient['name']), $search_query) !== false || (string)$patient['id'] === $search_query) {
            $search_results[] = $patient;
        }
    }
    
    if (empty($search_results)) {
        $no_results = true; // Set the flag to true if no results are found
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Documentation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Medical Documentation System</h2>

    <!-- Display Confirmation Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Form for Adding Symptoms -->
    <h3>Add Patient Symptoms</h3>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="patient_name" class="form-label">Patient Name:</label>
            <input type="text" class="form-control" id="patient_name" name="patient_name" required>
        </div>

        <div class="mb-3">
            <label for="symptoms" class="form-label">Symptoms:</label>
            <textarea class="form-control" id="symptoms" name="symptoms" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Symptoms</button>
    </form>

    <hr>

    <!-- Display Recorded Symptoms -->
    <h3>Recorded Symptoms</h3>
    <ul class="list-group">
        <?php foreach ($_SESSION['symptoms'] as $entry): ?>
            <li class="list-group-item">
                <strong><?php echo $entry['patient_name']; ?></strong> - 
                <?php echo $entry['symptoms']; ?> 
                <small class="text-muted">(<?php echo $entry['timestamp']; ?>)</small>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>

    <!-- Form for Adding New Patient Record -->
    <h3>Add Patient Medical History</h3>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="patient_id" class="form-label">Patient ID:</label>
            <input type="text" class="form-control" id="patient_id" name="patient_id" required>
        </div>
        <div class="mb-3">
            <label for="patient_name_record" class="form-label">Patient Name:</label>
            <input type="text" class="form-control" id="patient_name_record" name="patient_name_record" required>
        </div>
        <div class="mb-3">
            <label for="diagnosis" class="form-label">Diagnosis:</label>
            <input type="text" class="form-control" id="diagnosis" name="diagnosis" required>
        </div>
        <div class="mb-3">
            <label for="treatment" class="form-label">Treatment:</label>
            <input type="text" class="form-control" id="treatment" name="treatment" required>
        </div>
        <div class="mb-3">
            <label for="medications" class="form-label">Medications:</label>
            <input type="text" class="form-control" id="medications" name="medications" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Add Patient Record</button>
    </form>

    <hr>

    <!-- Search for Patient History -->
    <h3>Search Medical History</h3>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="search_query" class="form-label">Search by Name or Patient ID:</label>
            <input type="text" class="form-control" id="search_query" name="search_query" required>
        </div>
        <button type="submit" class="btn btn-info w-100">Search</button>
    </form>

    <!-- Display Search Results -->
    <?php if ($no_results): ?>
        <div class="alert alert-warning mt-4">
            No medical history found.
        </div>
    <?php elseif (!empty($search_results)): ?>
        <h3 class="mt-4">Medical History Results</h3>
        <ul class="list-group">
            <?php foreach ($search_results as $patient): ?>
                <li class="list-group-item">
                    <strong><?php echo $patient['name']; ?></strong> (ID: <?php echo $patient['id']; ?>)<br>
                    <b>Diagnosis:</b> <?php echo $patient['diagnosis']; ?><br>
                    <b>Treatment:</b> <?php echo $patient['treatment']; ?><br>
                    <b>Medications:</b> <?php echo $patient['medications']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div>
</body>
</html>