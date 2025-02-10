<?php
require 'db.php'; // Database connection

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $age = (int) $_POST['age']; // Get age input
    $role = $_POST['role']; // Role selection (patient/doctor)

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $error = "Email already exists! Please use a different email";
    } else {
        // Insert new user with age
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, contact_number, address, age, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $name, $email, $password, $contact_number, $address, $age, $role);

        if ($stmt->execute()) {
            $success = "Registration successful!";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }

    $checkStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Background Gradient */
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Registration Card */
        .register-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: #fff;
        }

        .register-container h2 {
            font-weight: bold;
        }

        /* Input Fields */
        .form-control {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: #fff;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.5);
            color: #fff;
            box-shadow: none;
        }

        /* Custom Button */
        .btn-custom {
            background: #ff4b2b;
            color: white;
            font-weight: bold;
            transition: 0.3s;
            border: none;
        }

        .btn-custom:hover {
            background: #ff416c;
            transform: scale(1.05);
        }

        /* Links */
        a {
            color: #ffde59;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Alert Styles */
        .alert-success {
            background: rgba(0, 128, 0, 0.7);
            color: white;
            border: none;
        }

        .alert-danger {
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
        }
    </style>
</head>

<body>

    <div class="register-container">
        <h2>Register</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars_decode($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars_decode($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>

            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="mb-3">
                <input type="text" name="contact_number" class="form-control" placeholder="Contact Number" required>
            </div>

            <div class="mb-3">
                <textarea name="address" class="form-control" placeholder="Address" required></textarea>
            </div>

            <div class="mb-3">
                <input type="number" name="age" class="form-control" placeholder="Age" required>
            </div>

            <div class="mb-3">
                <select name="role" class="form-control" required>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                </select>
            </div>

            <button type="submit" class="btn btn-custom w-100">Register</button>
        </form>

        <div class="mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
