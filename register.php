<?php
require 'db.php'; // Database connection

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO patients (name, email, password, contact_number, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $contact_number, $address);

    if ($stmt->execute()) {
        $success = "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
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

        /* Register Card */
        .register-container {
            max-width: 450px;
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

        /* Custom Button with Hover */
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

        /* Alert */
        .alert {
            background: rgba(255, 0, 0, 0.3);
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Registration</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
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

        <button type="submit" class="btn btn-custom w-100">Register</button>
    </form>

    <div class="mt-3">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
