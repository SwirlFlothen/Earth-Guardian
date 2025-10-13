<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $location = htmlspecialchars($_POST['location']);
    $city = htmlspecialchars($_POST['city']);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);

    if (!$firstname || !$lastname || !$email || !$location || !$city || !$age) {
        $error_message = "Please fill all the fields correctly.";
    } else {
        $sql_update = "UPDATE users SET firstname = ?, lastname = ?, email = ?, location = ?, city = ?, age = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssii", $firstname, $lastname, $email, $location, $city, $age, $user_id);

        if ($stmt_update->execute()) {
            $success_message = "Account updated successfully.";
        } else {
            $error_message = "Error updating account.";
        }
        $stmt_update->close();
    }
}

// Fetch user data
$sql_user = "SELECT firstname, lastname, email, location, city, age FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Fetch user's clubs
$sql_clubs = "SELECT ec.name, ec.description 
              FROM club_members cm
              JOIN eco_clubs ec ON cm.club_id = ec.id
              WHERE cm.user_id = ?";
$stmt_clubs = $conn->prepare($sql_clubs);
$stmt_clubs->bind_param("i", $user_id);
$stmt_clubs->execute();
$result_clubs = $stmt_clubs->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #1D2A21;
            color: #fff;
            overflow-x: hidden; /* Prevent horizontal scroll when menu is open */
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            z-index: 10;
            background: rgba(29, 42, 33, 0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .navbar .logo img {
            height: 45px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
            transition: color 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
            padding: 5px 0;
        }

        .nav-links li a:hover {
            color: #2ECC71;
            transform: translateY(-3px);
        }

        .burger {
            display: none;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 25px;
            z-index: 11;
        }

        .burger div {
            width: 25px;
            height: 3px;
            background-color: #fff;
            border-radius: 5px;
            transition: all 0.3s linear;
        }

        .nav-active {
            transform: translateX(0%) !important;
        }

        .toggle .line1 {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .toggle .line2 {
            opacity: 0;
        }

        .toggle .line3 {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .container {
            max-width: 800px;
            margin: 150px auto 40px;
            padding: 0 20px;
        }

        .account-form, .my-clubs {
            background: rgba(46, 204, 113, 0.05);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(46, 204, 113, 0.1);
            margin-bottom: 20px;
        }

        h1, .my-clubs h2 {
            font-family: 'Poppins', sans-serif;
            color: #2ECC71;
            margin-top: 0;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(46, 204, 113, 0.2);
            background: rgba(0,0,0, 0.2);
            color: white;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .btn {
            background: linear-gradient(45deg, #2ECC71, #27AE60);
            color: #fff;
            padding: 12px 25px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 1em;
            cursor: pointer;
            border-radius: 10px;
            border: none;
            font-weight: bold;
        }

        .error-message { color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #ff4d4d; }
        .success-message { color: #4dff88; background: rgba(77, 255, 136, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #4dff88; }

        .club-item {
            background: rgba(46, 204, 113, 0.03);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Adjust for fixed navbar height */
            }
            .navbar {
                padding: 15px 20px;
            }
            .nav-links {
                position: absolute;
                right: 0;
                height: 92vh;
                top: 70px;
                background-color: rgba(29, 42, 33, 0.95);
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 50%;
                transform: translateX(100%);
                transition: transform 0.5s ease-in;
                padding-top: 20px;
            }
            .nav-links li {
                opacity: 0;
                margin: 20px 0;
            }
            .nav-links li a {
                font-size: 1.2em;
            }
            .burger {
                display: flex;
            }
            .container {
                margin-top: 100px;
            }
            .account-form, .my-clubs {
                padding: 20px;
            }
        }

        @keyframes navLinkFade {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0px);
            }
        }

    </style>
</head>
<body>

    <nav class="navbar">
        <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
            <li><a href="../missions/missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="../forum/global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../static/about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>

    <div class="container">
        <div class="account-form">
            <h1>My Account</h1>
            <?php if(!empty($error_message)) echo "<p class='error-message'>$error_message</p>"; ?>
            <?php if(!empty($success_message)) echo "<p class='success-message'>$success_message</p>"; ?>
            <form action="my_account.php" method="post">
                <div class="input-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="location">Location</label>
                    <select id="location" name="location">
                        <option value="bangladesh" <?php if($user['location'] == 'bangladesh') echo 'selected'; ?>>Bangladesh</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="city">City</label>
                    <select id="city" name="city">
                        <option value="dhaka" <?php if($user['city'] == 'dhaka') echo 'selected'; ?>>Dhaka</option>
                        <option value="chittagong" <?php if($user['city'] == 'chittagong') echo 'selected'; ?>>Chittagong</option>
                        <option value="khulna" <?php if($user['city'] == 'khulna') echo 'selected'; ?>>Khulna</option>
                        <option value="rajshahi" <?php if($user['city'] == 'rajshahi') echo 'selected'; ?>>Rajshahi</option>
                        <option value="sylhet" <?php if($user['city'] == 'sylhet') echo 'selected'; ?>>Sylhet</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
                </div>
                <button type="submit" class="btn">Update Account</button>
            </form>
        </div>

        <div class="my-clubs">
            <h2>My Eco Clubs</h2>
            <?php if ($result_clubs->num_rows > 0): ?>
                <?php while($club = $result_clubs->fetch_assoc()): ?>
                    <div class="club-item">
                        <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                        <p><?php echo htmlspecialchars($club['description']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You are not a member of any eco clubs yet. <a href="../clubs/view_clubs.php">Browse clubs</a> or <a href="../clubs/create_club.php">create one</a>!</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>
