<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$sql = "SELECT id, title, SUBSTRING(content, 1, 100) AS excerpt FROM lessons";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Hub - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>

    <nav class="navbar">
        <ul>
            <li><a href="../../public/index.php">Home</a></li>
            <li><a href="../missions/missions.php">Missions</a></li>
            <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
            <li><a href="../clubs/view_clubs.php">Clubs</a></li>
            <li><a href="../forum/forum.php">Forum</a></li>
            <li><a href="lessons.php">Learning Hub</a></li>
            <li><a href="../user/my_account.php">My Account</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="header">
        <h1>Learning Hub</h1>
        <p>Knowledge is power. Empower yourself to protect the planet.</p>
    </div>

    <div class="lessons-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($lesson = $result->fetch_assoc()): ?>
                <div class="lesson-card">
                    <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
                    <p><?php echo htmlspecialchars($lesson['excerpt']); ?>...</p>
                    <a href="view_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn">Read More</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No lessons available at the moment. Check back soon!</p>
        <?php endif; ?>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>