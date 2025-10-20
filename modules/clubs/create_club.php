<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_club'])) {
    $club_name = htmlspecialchars($_POST['club_name']);
    $club_description = htmlspecialchars($_POST['club_description']);
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

    if (empty($club_name) || empty($club_description) || $latitude === false || $longitude === false) {
        $error_message = "Please fill in all fields correctly, including valid latitude and longitude.";
    } else {
        // Insert into eco_clubs
        $sql_insert_club = "INSERT INTO eco_clubs (name, description, creator_id, latitude, longitude) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert_club = $conn->prepare($sql_insert_club);
        $stmt_insert_club->bind_param("ssidd", $club_name, $club_description, $user_id, $latitude, $longitude);

        if ($stmt_insert_club->execute()) {
            $new_club_id = $stmt_insert_club->insert_id;
            $stmt_insert_club->close();

            // Add creator as a member
            $sql_add_member = "INSERT INTO club_members (club_id, user_id) VALUES (?, ?)";
            $stmt_add_member = $conn->prepare($sql_add_member);
            $stmt_add_member->bind_param("ii", $new_club_id, $user_id);
            $stmt_add_member->execute();
            $stmt_add_member->close();

            header('Location: view_clubs.php?club_created=true');
            exit();
        } else {
            $error_message = "Error creating club: " . $stmt_insert_club->error;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Eco Club - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html { margin: 0; font-family: 'Poppins', sans-serif; background: #1D2A21; color: #fff; }
        .navbar { position: fixed; top: 0; width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; z-index: 10; background: rgba(29, 42, 33, 0.8); box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .navbar .logo img { height: 45px; }
        .navbar .nav-links a { color: #fff; text-decoration: none; margin: 0 15px; font-weight: 500; transition: color 0.3s, transform 0.3s; display: inline-flex; align-items: center; }
        .navbar .nav-links a i { margin-right: 8px; font-size: 1.1em; }
        .navbar .nav-links a:hover { color: #2ECC71; transform: translateY(-3px); }
        .container { max-width: 800px; margin: 150px auto 40px; padding: 0 20px; }
        .form-container { background: rgba(46, 204, 113, 0.05); border-radius: 15px; padding: 30px; border: 1px solid rgba(46, 204, 113, 0.1); }
        h1 { font-family: 'Poppins', sans-serif; color: #2ECC71; margin-top: 0; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input, .input-group textarea { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid rgba(46, 204, 113, 0.2); background: rgba(0,0,0, 0.2); color: white; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .btn { background: linear-gradient(45deg, #2ECC71, #27AE60); color: #fff; padding: 12px 25px; text-align: center; text-decoration: none; display: inline-block; font-size: 1em; cursor: pointer; border-radius: 10px; border: none; font-weight: bold; }
        .error-message { color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #ff4d4d; }
        .success-message { color: #4dff88; background: rgba(77, 255, 136, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #4dff88; }
        @media (max-width: 768px) { .navbar { padding: 15px 20px; } .navbar .nav-links { display: none; } .container { margin-top: 100px; } .form-container { padding: 20px; } h1 { font-size: 2em; } }
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
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="form-container">
            <h1>Create New Eco Club</h1>
            <?php if(!empty($error_message)): ?> <div class="error-message"><?php echo $error_message; ?></div> <?php endif; ?>
            <?php if(!empty($success_message)): ?> <div class="success-message"><?php echo $success_message; ?></div> <?php endif; ?>
            <form action="create_club.php" method="post">
                <div class="input-group">
                    <label for="club_name">Club Name</label>
                    <input type="text" name="club_name" id="club_name" placeholder="Club Name" required>
                </div>
                <div class="input-group">
                    <label for="club_description">Description</label>
                    <textarea name="club_description" id="club_description" placeholder="Club Description" rows="5" required></textarea>
                </div>
                <div class="input-group">
                    <label for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" placeholder="e.g., 23.8103" required>
                </div>
                <div class="input-group">
                    <label for="longitude">Longitude</label>
                    <input type="text" name="longitude" id="longitude" placeholder="e.g., 90.4125" required>
                </div>
                <button type="submit" name="create_club" class="btn">Create Club</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
