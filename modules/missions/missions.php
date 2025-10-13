<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$sql = "SELECT id, title, description, points FROM missions";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
</head>
<body>

    <nav class="navbar">
        <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
            <li><a href="missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="../forum/global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../static/about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>

    <div class="header">
        <h1>Available Missions</h1>
        <p>Choose your next challenge and make a difference.</p>
    </div>

    <div class="missions-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($mission = $result->fetch_assoc()): ?>
                <div class="mission-card">
                    <h2><?php echo htmlspecialchars($mission['title']); ?></h2>
                    <p><?php echo htmlspecialchars($mission['description']); ?></p>
                    <div class="points"><?php echo $mission['points']; ?> XP</div>
                    <a href="complete_mission.php?id=<?php echo $mission['id']; ?>" class="btn">Accept Mission</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No missions available at the moment. Check back soon!</p>
        <?php endif; ?>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>