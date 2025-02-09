<?php
session_start();

// Dummy credentials (replace with database credentials)
$stored_users = [
    ['username' => 'patient123', 'password' => 'password', 'role' => 'patient'],
    ['username' => 'admin123', 'password' => 'adminpass', 'role' => 'admin']
];

// Dummy data for the patient's medical history and appointments
$appointments = [
    ['date' => '2025-02-07', 'time' => '10:00 AM', 'doctor' => 'Dr. Smith'],
    ['date' => '2025-02-14', 'time' => '02:00 PM', 'doctor' => 'Dr. Johnson'],
];
$medical_history = [
    'Blood Pressure: 120/80',
    'Allergy: Pollen',
    'Medications: Amlodipine'
];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Basic authentication
    foreach ($stored_users as $user) {
        if ($username === $user['username'] && $password === $user['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            // Redirect to the appropriate dashboard based on user role
            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        }
    }
    echo "<p style='color: red;'>Invalid username or password. Please try again.</p>";
}
?>
