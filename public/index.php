<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../modules/dashboard/dashboard.php");
    exit();
}

require_once '../includes/db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earth Guardians - Defend Our Planet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing_v2.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
</head>
<body>

    <div class="background-container"></div>
    <div class="background-overlay"></div>

    <nav class="navbar">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="../modules/missions/missions.php">Missions</a></li>
            <li><a href="../modules/leaderboard/leaderboard.php">Leaderboard</a></li>
            <li><a href="../modules/clubs/view_clubs.php">Clubs</a></li>
            <li><a href="../modules/forum/forum.php">Forum</a></li>
            <li><a href="../modules/user/my_account.php">My Account</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../modules/auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../modules/auth/login.php">Login</a></li>
                <li><a href="../modules/auth/signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Defend Our Planet</h1>
            <p>Join a global movement of guardians dedicated to protecting the Earth. Complete missions, earn rewards, and make a real-world impact.</p>
            <div class="cta-group">
                <a href="../modules/auth/signup.php" class="btn btn-primary">Become a Guardian <i class="fas fa-arrow-right"></i></a>
                <a href="../modules/static/howtoplay.php" class="btn btn-secondary">How It Works <i class="fas fa-play-circle"></i></a>
            </div>
        </div>
    </header>

    <main>
        <section class="content-section">
            <div class="section-header">
                <h2>Take Action, See Results</h2>
                <p>Our platform empowers you to make a tangible difference.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Engaging Missions</h3>
                    <p>Participate in real-world challenges, from local cleanups to global conservation efforts.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Community Collaboration</h3>
                    <p>Connect with fellow guardians, join Eco Clubs, and work together on impactful projects.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Track Your Impact</h3>
                    <p>Monitor your progress, earn badges, and see the collective impact of the community.</p>
                </div>
            </div>
        </section>

        <section class="content-section final-cta">
            <h2>Ready to Join the Fight?</h2>
            <p>Your journey as an Earth Guardian starts now. Sign up and take your first step towards a healthier planet.</p>
            <a href="../modules/auth/signup.php" class="btn btn-primary">Start Your Journey Today</a>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>