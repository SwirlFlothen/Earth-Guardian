<nav class="navbar">
    <ul>
        <li><a href="/EarthGuardian/public/index.php">Home</a></li>
        <li><a href="/EarthGuardian/modules/missions/missions.php">Missions</a></li>
        <li><a href="/EarthGuardian/modules/leaderboard/leaderboard.php">Leaderboard</a></li>
        <li><a href="/EarthGuardian/modules/clubs/view_clubs.php">Clubs</a></li>
        <li><a href="/EarthGuardian/modules/forum/forum.php">Forum</a></li>
        <li><a href="/EarthGuardian/modules/user/my_account.php">My Account</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/EarthGuardian/modules/auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/EarthGuardian/modules/auth/login.php">Login</a></li>
            <li><a href="/EarthGuardian/modules/auth/signup.php">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>