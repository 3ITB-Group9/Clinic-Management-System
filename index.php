<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";  // Update with your database username
$password = "";      // Update with your database password
$dbname = "clinicmanagement";  // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Dummy authentication: Assume the user is a doctor
$_SESSION['user_id'] = 1;  // Example user ID
$_SESSION['role'] = 'doctor';  // Only doctors can add symptoms & records

// Ensure only doctors can access patient records and add symptoms
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    die("Unauthorized access.");
}

// Handle symptom submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_name'], $_POST['symptoms'])) {
    $patient_name = htmlspecialchars($_POST['patient_name']);
    $symptoms = htmlspecialchars($_POST['symptoms']);

    if (!empty($patient_name) && !empty($symptoms)) {
        $stmt = $conn->prepare("INSERT INTO symptoms (patient_name, symptoms) VALUES (?, ?)");
        $stmt->bind_param("ss", $patient_name, $symptoms);
        $stmt->execute();
        $message = "Symptoms added successfully!";
        $stmt->close();
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
        $stmt = $conn->prepare("INSERT INTO users (patient_id, patient_name, diagnosis, treatment, medications) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $patient_id, $patient_name, $diagnosis, $treatment, $medications);
        $stmt->execute();
        $message = "Patient medical history added successfully!";
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// Handle search for patient history
$search_results = [];
$no_results = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_query'])) {
    $search_query = strtolower(trim($_POST['search_query']));
    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(patient_name) LIKE ? OR patient_id = ?");
    $search_query_like = "%$search_query%";
    $stmt->bind_param("ss", $search_query_like, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }

    if (empty($search_results)) {
        $no_results = true;
    }

    $stmt->close();
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
    <?php
    $result = $conn->query("SELECT * FROM symptoms ORDER BY timestamp DESC");
    while ($entry = $result->fetch_assoc()):
    ?>
        <ul class="list-group">
            <li class="list-group-item">
                <strong><?php echo $entry['patient_name']; ?></strong> - 
                <?php echo $entry['symptoms']; ?> 
                <small class="text-muted">(<?php echo $entry['timestamp']; ?>)</small>
            </li>
        </ul>
    <?php endwhile; ?>

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
                    <strong><?php echo $patient['patient_name']; ?></strong> (ID: <?php echo $patient['patient_id']; ?>)<br>
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

<?php
// Close the database connection
$conn->close();
?>
