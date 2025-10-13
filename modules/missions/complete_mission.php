<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mission_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$mission_id) {
    header('Location: missions.php');
    exit();
}

// Fetch mission details
$sql_mission = "SELECT title, description, points FROM missions WHERE id = ?";
$stmt_mission = $conn->prepare($sql_mission);
$stmt_mission->bind_param("i", $mission_id);
$stmt_mission->execute();
$result_mission = $stmt_mission->get_result();
$mission = $result_mission->fetch_assoc();
$stmt_mission->close();

if (!$mission) {
    header('Location: missions.php');
    exit();
}

// Check if user has already submitted this mission
$sql_check = "SELECT id FROM user_missions WHERE user_id = ? AND mission_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $mission_id);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    $error_message = "You have already submitted this mission for verification.";
}
$stmt_check->close();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['proof']) && empty($error_message)) {
    $upload_dir = '../../public/assets/uploads/';
    $file_extension = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid('proof_', true) . '.' . $file_extension;
    $upload_file = $upload_dir . $new_filename;

    // Validation
    if ($_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'An error occurred during file upload.';
    } elseif ($_FILES['proof']['size'] > 5000000) { // 5MB limit
        $error_message = 'File is too large. Maximum size is 5MB.';
    } elseif (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
        $error_message = 'Invalid file type. Only JPG, JPEG, PNG, & GIF files are allowed.';
    } else {
        if (move_uploaded_file($_FILES['proof']['tmp_name'], $upload_file)) {
            // Insert into user_missions with pending status
            $sql_insert = "INSERT INTO user_missions (user_id, mission_id, proof, status) VALUES (?, ?, ?, 'pending')";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("iis", $user_id, $mission_id, $upload_file);
            $stmt_insert->execute();
            $stmt_insert->close();

            header('Location: ../dashboard/dashboard.php?mission_submitted=true');
            exit();
        } else {
            $error_message = 'Failed to save the uploaded file.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Mission - Earth Guardians</title>
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
        .mission-details, .upload-form { background: rgba(46, 204, 113, 0.05); border-radius: 15px; padding: 30px; margin-bottom: 30px; border: 1px solid rgba(46, 204, 113, 0.1); }
        h1, h2 { font-family: 'Poppins', sans-serif; color: #2ECC71; margin-top: 0; }
        .btn { background: linear-gradient(45deg, #2ECC71, #27AE60); color: #fff; padding: 12px 25px; text-align: center; text-decoration: none; display: inline-block; font-size: 1em; cursor: pointer; border-radius: 10px; border: none; font-weight: bold; }
        .error-message { color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #ff4d4d; }
        .success-message { color: #4dff88; background: rgba(77, 255, 136, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #4dff88; }
        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .navbar .nav-links { display: none; width: 100%; flex-direction: column; background: rgba(29, 42, 33, 0.95); position: absolute; top: 75px; left: 0; padding-bottom: 20px; }
            .navbar .nav-links.active { display: flex; }
            .navbar .nav-links li { margin: 10px 0; text-align: center; }
            .navbar .nav-links a { padding: 10px; width: 90%; margin: 0 auto; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .navbar .nav-links a:hover { background: #2ECC71; border-radius: 5px; }
            .menu-toggle { display: flex; flex-direction: column; cursor: pointer; }
            .menu-toggle .bar { height: 3px; width: 25px; background-color: #fff; margin: 4px 0; transition: 0.4s; }
            .container { margin-top: 100px; }
            .mission-details, .upload-form { padding: 20px; }
            h1 { font-size: 2em; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
            <li><a href="missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../static/about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </nav>
    <div class="container">
        <div class="mission-details">
            <h1><?php echo htmlspecialchars($mission['title']); ?></h1>
            <p><?php echo htmlspecialchars($mission['description']); ?></p>
            <p><strong>Points:</strong> <?php echo $mission['points']; ?> XP</p>
        </div>
        <div class="upload-form">
            <h2>Upload Your Proof</h2>
            <?php if(!empty($error_message)): ?> <div class="error-message"><?php echo $error_message; ?></div> <?php endif; ?>
            <?php if(isset($_GET['mission_submitted'])): ?> <div class="success-message">Your mission proof has been submitted for verification.</div> <?php endif; ?>
            <?php if(empty($error_message) && !isset($_GET['mission_submitted'])): ?>
            <form action="complete_mission.php?id=<?php echo $mission_id; ?>" method="post" enctype="multipart/form-data">
                <input type="file" name="proof" required>
                <button type="submit" class="btn">Submit for Verification</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const navLinks = document.querySelector('.nav-links');

            mobileMenu.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>