<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

require_once '../../includes/db_connect.php';
require_once '../../includes/csrf.php';

$error_message = '';
$success_message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!validate_csrf()) {
        $error_message = 'Invalid CSRF token.';
    } else {
    // Sanitize and retrieve form data
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $location = htmlspecialchars($_POST['location']);
    $city = htmlspecialchars($_POST['city']);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $password = $_POST['password'];

    // Basic validation
    if (!$firstname || !$lastname || !$email || !$location || !$city || !$age || empty($password)) {
        $error_message = "Please fill all the fields correctly.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "An account with this email already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $username = strtolower($firstname . $lastname . rand(100, 999));

            // Insert user into the database
            $sql_insert = "INSERT INTO users (username, firstname, lastname, email, location, city, age, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssssis", $username, $firstname, $lastname, $email, $location, $city, $age, $hashed_password);

            if ($stmt_insert->execute()) {
                // Redirect to rules page, then to login
                $_SESSION['new_user_signup'] = true; // Flag to indicate new signup
                header("Location: ../static/rules.php");
                exit();
            } else {
                $error_message = "Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/auth.css">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="left">
            <img src="../../public/assets/Images/Signup-vector.png" alt="Signup Vector">
        </div>
        <div class="right signup-form">
            <h1>Join Earth Guardians</h1>
            <?php if(!empty($error_message)) echo "<div class='alert error'>$error_message</div>"; ?>
            <?php if(!empty($success_message)) echo "<div class='alert success'>$success_message</div>"; ?>
            <form action="signup.php" method="post">
                <?php echo csrf_input(); ?>
                <div class="name-group">
                    <div class="input-group" style="flex:1;">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="input-group" style="flex:1;">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="location">Location</label>
                    <select id="location" name="location">
                        <option value="bangladesh">Bangladesh</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="city">City</label>
                    <select id="city" name="city">
                        <option value="dhaka">Dhaka</option>
                        <option value="chittagong">Chittagong</option>
                        <option value="khulna">Khulna</option>
                        <option value="rajshahi">Rajshahi</option>
                        <option value="sylhet">Sylhet</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
            <p class="sub-text">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>