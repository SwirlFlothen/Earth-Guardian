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
            max-width: 500px; /* Slightly wider for more fields */
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
            margin-bottom: 1.5rem; /* Slightly less margin for more fields */
        }

        .input-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            -webkit-appearance: none; /* Remove default select styling */
            -moz-appearance: none;
            appearance: none;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.3);
        }

        /* Custom arrow for select */
        .input-group select {
            padding-right: 3rem; /* Make space for custom arrow */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23a0aec0%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095.1c3.6-3.6%205.4-7.8%205.4-12.8%200-5-1.8-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.8em;
        }

        .name-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .name-group .input-group {
            flex: 1;
            margin-bottom: 0;
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

        @media (max-width: 600px) {
            .name-group {
                flex-direction: column;
            }
            .form-container {
                padding: 2rem;
            }
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
            <h1>Join Earth Guardians</h1>
            <p class="auth-subtitle">Create your account and start making a difference.</p>
            
            <?php if ($error_message): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrf_input(); ?>
                <div class="name-group">
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="firstname" name="firstname" placeholder="First Name" required value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="lastname" name="lastname" placeholder="Last Name" required value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                    </div>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <select id="location" name="location">
                        <option value="bangladesh">Bangladesh</option>
                    </select>
                </div>

                <div class="input-group">
                    <i class="fas fa-city icon"></i>
                    <select id="city" name="city">
                        <option value="dhaka">Dhaka</option>
                        <option value="chittagong">Chittagong</option>
                        <option value="khulna">Khulna</option>
                        <option value="rajshahi">Rajshahi</option>
                        <option value="sylhet">Sylhet</option>
                    </select>
                </div>

                <div class="input-group">
                    <i class="fas fa-calendar-alt icon"></i>
                    <input type="number" id="age" name="age" placeholder="Age" required value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-primary">Sign Up</button>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
