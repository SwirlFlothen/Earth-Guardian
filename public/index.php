<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../modules/dashboard/dashboard.php");
    exit();
}

require_once '../includes/db_connect.php';

// Fetch upcoming events (missions with a future deadline)
$sql_events = "SELECT title, deadline FROM missions WHERE deadline > CURDATE() ORDER BY deadline ASC LIMIT 5";
$result_events = $conn->query($sql_events);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earth Guardians - A New Era of Environmental Action</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Main stylesheet -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- Landing-specific stylesheet -->
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <div class="bg-video"></div>

    <nav class="navbar">
        <a href="index.php" class="logo"><img src="assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../modules/auth/login.php" title="Login" aria-label="Login"><i class="fas fa-sign-in-alt"></i><span class="link-text">Login</span></a></li>
            <li><a href="../modules/auth/signup.php" title="Sign Up" aria-label="Sign Up"><i class="fas fa-user-plus"></i><span class="link-text">Sign Up</span></a></li>
            <li><a href="../modules/static/about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
        </ul>
        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>

    <div class="main-content">
        <h1 class="title">DEFEND OUR PLANET</h1>
        <p class="subtitle">Join the global movement. Complete missions. Become a legend.</p>
        <a href="../modules/auth/signup.php" class="btn">Become a Guardian</a>
    </div>

    <div class="events-section">
        <h2>Upcoming Events</h2>
        <?php if ($result_events->num_rows > 0): ?>
            <?php while($event = $result_events->fetch_assoc()): ?>
                <p class="event-item">
                    <strong><?php echo htmlspecialchars($event['title']); ?></strong> - Deadline: <?php echo date('M d, Y', strtotime($event['deadline'])); ?>
                </p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events at the moment. Check back soon!</p>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>