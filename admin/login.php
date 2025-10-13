<?php
session_start();
require_once '../includes/csrf.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid form submission.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Hardcoded admin credentials
        $admin_username = 'admin';
        $admin_password = 'password123'; // In a real application, use a hashed password

        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit();
        } else {
            $error_message = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="../public/assets/js/admin.js"></script>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1><img src="../public/assets/Images/Logo.png" alt="Earth Guardians Logo" style="height: 40px; vertical-align: middle; margin-right: 10px;">Admin Login</h1>
            <?php if(!empty($error_message)) echo "<p style='color:red'>$error_message</p>"; ?>
            <form action="login.php" method="post">
                <?php echo csrf_input(); ?>
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
