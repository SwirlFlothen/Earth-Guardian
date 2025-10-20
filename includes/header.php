<?php
// If session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <ul>
        <li><a href="../public/index.php">Home</a></li>
        <li><a href="../modules/missions/missions.php">Missions</a></li>
        <li><a href="../modules/leaderboard/leaderboard.php">Leaderboard</a></li>
        <li><a href="../modules/clubs/view_clubs.php">Clubs</a></li>
        <li><a href="../modules/forum/forum.php">Forum</a></li>
        <li><a href="../modules/lessons/lessons.php">Learning Hub</a></li>
        <li><a href="../modules/user/my_account.php">My Account</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="../modules/auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../modules/auth/login.php">Login</a></li>
            <li><a href="../modules/auth/signup.php">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>