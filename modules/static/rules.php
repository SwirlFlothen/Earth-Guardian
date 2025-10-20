<?php
session_start();
$is_new_signup = false;
if (isset($_SESSION['new_user_signup']) && $_SESSION['new_user_signup'] === true) {
    $is_new_signup = true;
    unset($_SESSION['new_user_signup']); // Unset the flag after checking
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Rules - Earth Guardians</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar">
            <ul>
                <li><a href="../../public/index.php">Home</a></li>
                <li><a href="../missions/missions.php">Missions</a></li>
                <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
                <li><a href="../clubs/view_clubs.php">Clubs</a></li>
                <li><a href="../forum/forum.php">Forum</a></li>
                <li><a href="../lessons/lessons.php">Learning Hub</a></li>
                <li><a href="../user/my_account.php">My Account</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    <?php else: ?>
        <nav class="navbar">
            <ul>
                <li><a href="../../public/index.php">Home</a></li>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign Up</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="howtoplay.php">How to Play</a></li>
                <li><a href="rules.php">Rules</a></li>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="container">
        <div class="content-box">
            <h2>Game Rules</h2>
            <ul class="rules-list">
                <li>- Cheating or using unauthorized tools will result in immediate suspension or ban.</li>
                <li>- All players must show respect and avoid offensive or harassing behavior.</li>
                <li>- Creating multiple accounts for any advantage is strictly prohibited.</li>
                <li>- Players must truthfully report all game-related activities and completions.</li>
                <li>- Manipulating or bypassing game mechanics for unfair advantage is forbidden.</li>
                <li>- Sharing account details or accessing others' accounts is not allowed.</li>
                <li>- Team members must contribute fairly, or the entire team may face penalties.</li>
            </ul>
            <?php if ($is_new_signup): ?>
                <p>Thank you for signing up! Please review the rules before proceeding.</p>
                <a href="../auth/login.php" class="btn">Proceed to Login</a>
            <?php else: ?>
                <a href="../dashboard/dashboard.php" class="btn">Agree</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>