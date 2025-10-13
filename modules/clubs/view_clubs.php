<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Handle Join Club
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_club'])) {
    $club_id = filter_input(INPUT_POST, 'club_id', FILTER_VALIDATE_INT);

    // Check if user is already a member
    $sql_check_member = "SELECT * FROM club_members WHERE club_id = ? AND user_id = ?";
    $stmt_check_member = $conn->prepare($sql_check_member);
    $stmt_check_member->bind_param("ii", $club_id, $user_id);
    $stmt_check_member->execute();
    $stmt_check_member->store_result();

    if ($stmt_check_member->num_rows > 0) {
        $error_message = "You are already a member of this club.";
    } else {
        $sql_join_club = "INSERT INTO club_members (club_id, user_id) VALUES (?, ?)";
        $stmt_join_club = $conn->prepare($sql_join_club);
        $stmt_join_club->bind_param("ii", $club_id, $user_id);
        if ($stmt_join_club->execute()) {
            $success_message = "Successfully joined the club!";
        } else {
            $error_message = "Error joining club.";
        }
        $stmt_join_club->close();
    }
    $stmt_check_member->close();
}

// Fetch clubs user is a member of
$sql_user_clubs = "SELECT club_id FROM club_members WHERE user_id = ?";
$stmt_user_clubs = $conn->prepare($sql_user_clubs);
$stmt_user_clubs->bind_param("i", $user_id);
$stmt_user_clubs->execute();
$result_user_clubs = $stmt_user_clubs->get_result();
$user_clubs = [];
while ($row = $result_user_clubs->fetch_assoc()) {
    $user_clubs[] = $row['club_id'];
}
$stmt_user_clubs->close();

// Fetch all eco clubs
$sql_clubs = "SELECT ec.id, ec.name, ec.description, ec.latitude, ec.longitude, u.username AS creator_username 
              FROM eco_clubs ec 
              JOIN users u ON ec.creator_id = u.id 
              ORDER BY ec.created_at DESC";
$result_clubs = $conn->query($sql_clubs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Clubs - Earth Guardians</title>
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

        .container { max-width: 1200px; margin: 150px auto 40px; padding: 0 20px; }
        .clubs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .club-card { background: rgba(46, 204, 113, 0.05); border-radius: 15px; padding: 30px; border: 1px solid rgba(46, 204, 113, 0.1); transition: transform 0.3s, box-shadow 0.3s; }
        .club-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .club-card h2 { font-family: 'Poppins', sans-serif; color: #2ECC71; margin-top: 0; }
        .club-card p { font-size: 0.9em; }
        .club-card .creator { font-size: 0.8em; color: #aaa; margin-top: 10px; }
        .btn { background: linear-gradient(45deg, #2ECC71, #27AE60); color: #fff; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 0.9em; cursor: pointer; border-radius: 10px; border: none; font-weight: bold; margin-top: 20px; transition: background 0.3s, transform 0.3s; }
        .btn:hover { background: linear-gradient(45deg, #27AE60, #2ECC71); transform: translateY(-2px); }
        .error-message { color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #ff4d4d; }
        .success-message { color: #4dff88; background: rgba(77, 255, 136, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #4dff88; }
        .club-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .club-actions .btn { margin: 0 5px; }
        .club-actions form { display: inline-block; }
        .member-status { background: #34495E; color: #fff; cursor: default; }
        .member-status:hover { background: #34495E; transform: none; }
        .join-btn { background: linear-gradient(45deg, #3498DB, #2980B9); }
        .forum-btn { background: linear-gradient(45deg, #9B59B6, #8E44AD); }
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
            .container {
                margin-top: 100px;
            }
            .clubs-grid {
                grid-template-columns: 1fr;
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
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="../forum/global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
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
    <div class="container">
        <h1>Eco Clubs</h1>
        <?php if(!empty($error_message)): ?> <div class="error-message"><?php echo $error_message; ?></div> <?php endif; ?>
        <?php if(!empty($success_message)): ?> <div class="success-message"><?php echo $success_message; ?></div> <?php endif; ?>
        <p><a href="create_club.php" class="btn">Create New Club</a></p>
        <div class="clubs-grid">
            <?php if ($result_clubs->num_rows > 0): ?>
                <?php while($club = $result_clubs->fetch_assoc()): ?>
                    <div class="club-card">
                        <h2><?php echo htmlspecialchars($club['name']); ?></h2>
                        <p><?php echo htmlspecialchars($club['description']); ?></p>
                        <p class="creator">Created by: <?php echo htmlspecialchars($club['creator_username']); ?></p>
                                                <div class="club-actions">
                                                    <?php if (in_array($club['id'], $user_clubs)): ?>
                                                        <span class="btn member-status">Member</span>
                                                    <?php else: ?>
                                                        <form action="" method="post" style="display:inline;">
                                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                                            <button type="submit" name="join_club" class="btn join-btn">Join Club</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="../forum/forum.php?club_id=<?php echo $club['id']; ?>" class="btn forum-btn">View Forum</a>
                                                </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No eco clubs available yet. Be the first to create one!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const navSlide = () => {
            const burger = document.querySelector('.burger');
            const nav = document.querySelector('.nav-links');
            const navLinks = document.querySelectorAll('.nav-links li');
        }

    </script>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>