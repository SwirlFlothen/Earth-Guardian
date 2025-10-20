<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

require_once '../../includes/db_connect.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: ../dashboard/dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "Username not found.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --primary-color: #2ECC71;
            --primary-dark: #27AE60;
            --text-color: #FFFFFF;
            --bg-dark: #12181B;
            --card-bg: rgba(18, 24, 27, 0.7);
            --border-color: rgba(255, 255, 255, 0.2);
            --input-bg: rgba(0, 0, 0, 0.2);
            --error-bg: rgba(229, 62, 62, 0.1);
            --error-border: #e53e3e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-color);
        }

        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            background: url('../../public/assets/Images/Landing-bg.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .auth-wrapper::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .form-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            width: 120px;
        }

        .form-container h1 {
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .form-container .auth-subtitle {
            text-align: center;
            color: #a0aec0;
            margin-bottom: 2.5rem;
        }

        .alert.error {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: var(--error-bg);
            border: 1px solid var(--error-border);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            color: #fed7d7;
        }

        .input-group {
            position: relative;
            margin-bottom: 2rem;
        }

        .input-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.3);
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-dark));
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.2);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: #a0aec0;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="form-container">
            <div class="logo-container">
                <a href="../../public/index.php">
                    <img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo">
                </a>
            </div>
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Log in to continue your mission.</p>
            
            <?php if ($error_message): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-user icon"></i>
                    <input type="text" id="username" name="username" placeholder="Username" required autocomplete="username">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-primary">Login</button>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>