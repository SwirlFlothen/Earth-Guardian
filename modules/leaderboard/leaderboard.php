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
    <style>
        body, html {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #1D2A21;
            color: #fff;
            overflow-x: hidden; /* Prevent horizontal scroll when menu is open */
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            z-index: 10;
            background: rgba(29, 42, 33, 0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .navbar .logo img {
            height: 45px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
            transition: color 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
            padding: 5px 0;
        }

        .nav-links li a i {
            margin-right: 8px;
            font-size: 1.1em;
        }

        .nav-links li a:hover {
            color: #2ECC71;
            transform: translateY(-3px);
        }

        .burger {
            display: none;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 25px;
            z-index: 11;
        }

        .burger div {
            width: 25px;
            height: 3px;
            background-color: #fff;
            border-radius: 5px;
            transition: all 0.3s linear;
        }

        .nav-active {
            transform: translateX(0%) !important;
        }

        .toggle .line1 {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .toggle .line2 {
            opacity: 0;
        }

        .toggle .line3 {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .header {
            text-align: center;
            padding: 150px 20px 50px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('../../public/assets/Images/Dashboard-bg.jpg') no-repeat center center;
            background-size: cover;
        }

        .header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 4rem;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(46, 204, 113, 0.6);
        }

        .leaderboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(46, 204, 113, 0.05);
            border-radius: 15px;
            overflow: hidden;
        }

        .leaderboard-table th, .leaderboard-table td {
            padding: 20px;
            text-align: left;
        }

        .leaderboard-table thead {
            background: rgba(46, 204, 113, 0.2);
            font-family: 'Poppins', sans-serif;
        }

        .leaderboard-table tbody tr {
            border-bottom: 1px solid rgba(46, 204, 113, 0.1);
            transition: background-color 0.3s;
        }

        .leaderboard-table tbody tr:last-child {
            border-bottom: none;
        }

        .leaderboard-table tbody tr:hover {
            background-color: rgba(46, 204, 113, 0.1);
        }

        .leaderboard-table .rank {
            font-weight: 700;
            font-size: 1.2em;
            color: #2ECC71;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Adjust for fixed navbar height */
            }
            .navbar {
                padding: 15px 20px;
            }
            .nav-links {
                position: absolute;
                right: 0;
                height: 92vh;
                top: 70px;
                background-color: rgba(29, 42, 33, 0.95);
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 50%;
                transform: translateX(100%);
                transition: transform 0.5s ease-in;
                padding-top: 20px;
            }
            .nav-links li {
                opacity: 0;
                margin: 20px 0;
            }
            .nav-links li a {
                font-size: 1.2em;
            }
            .burger {
                display: flex;
            }
            .header {
                padding: 100px 20px 30px;
            }
            .header h1 {
                font-size: 3rem;
            }
            .leaderboard-table th, .leaderboard-table td {
                padding: 15px;
            }
        }

        @keyframes navLinkFade {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0px);
            }
        }

    </style>
</head>
<body>

    <nav class="navbar">
        <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
            <li><a href="../missions/missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
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
