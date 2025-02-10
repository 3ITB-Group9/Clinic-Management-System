<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize email input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Prepare SQL statement for users table
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Bind the correct number of variables (MUST match the SELECT statement)
    $stmt->bind_result($id, $name, $hashed_password, $role);

    // Verify login
    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'patient') {
                header("Location: patient_dashboard.php");
            } elseif ($role === 'doctor') {
                header("Location: doctor_dashboard.php");
            } else {
                $error = "Unauthorized role detected.";
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        /* Login Card */
        .login-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: #fff;
        }

        .login-container h2 {
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

    <div class="login-container">
        <h2>Patient Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>

        <div class="mt-3">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>