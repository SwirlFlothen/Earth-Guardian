<?php
session_start();
require_once '../../includes/db_connect.php';

// Fetch top 10 users
$sql = "SELECT username, points FROM users ORDER BY points DESC LIMIT 10";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Earth Guardians</title>
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
            <li><a href="leaderboard.php">Leaderboard</a></li>
            <li><a href="../clubs/view_clubs.php">Clubs</a></li>
            <li><a href="../forum/forum.php">Forum</a></li>
            <li><a href="../lessons/lessons.php">Learning Hub</a></li>
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
        <h1>Top Guardians</h1>
        <p>See who is leading the charge in protecting our planet.</p>
    </div>

    <div class="leaderboard-container">
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Guardian</th>
                    <th>XP</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="rank"><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo $user['points']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>
