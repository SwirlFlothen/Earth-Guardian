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

$filter_club_id = filter_input(INPUT_GET, 'club_id', FILTER_VALIDATE_INT);

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);
    $post_club_id = filter_input(INPUT_POST, 'club_id', FILTER_VALIDATE_INT);

    if (!empty($title) && !empty($content)) {
        $sql_insert = "INSERT INTO forum_posts (user_id, title, content, club_id) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issi", $user_id, $title, $content, $post_club_id);

        if ($stmt_insert->execute()) {
            $success_message = "Your post has been created.";

            // Check for Community Contributor badge (3 forum posts)
            $sql_count_posts = "SELECT COUNT(*) AS total_posts FROM forum_posts WHERE user_id = ?";
            $stmt_count_posts = $conn->prepare($sql_count_posts);
            $stmt_count_posts->bind_param("i", $user_id);
            $stmt_count_posts->execute();
            $result_count_posts = $stmt_count_posts->get_result();
            $row_count_posts = $result_count_posts->fetch_assoc();
            $stmt_count_posts->close();

            if ($row_count_posts['total_posts'] == 3) {
                $sql_badge_id = "SELECT id FROM badges WHERE name = 'Community Contributor'";
                $result_badge_id = $conn->query($sql_badge_id);
                $badge_id = $result_badge_id->fetch_assoc()['id'];

                // Check if user already has the badge
                $sql_check_badge = "SELECT * FROM user_badges WHERE user_id = ? AND badge_id = ?";
                $stmt_check_badge = $conn->prepare($sql_check_badge);
                $stmt_check_badge->bind_param("ii", $user_id, $badge_id);
                $stmt_check_badge->execute();
                $stmt_check_badge->store_result();

                if ($stmt_check_badge->num_rows == 0) {
                    $sql_award_badge = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
                    $stmt_award_badge = $conn->prepare($sql_award_badge);
                    $stmt_award_badge->bind_param("ii", $user_id, $badge_id);
                    $stmt_award_badge->execute();
                    $stmt_award_badge->close();
                }
                $stmt_check_badge->close();
            }

        } else {
            $error_message = "Failed to create post.";
        }
        $stmt_insert->close();
    } else {
        $error_message = "Please fill in both title and content.";
    }
}

// Fetch all eco clubs for filter
$sql_all_clubs = "SELECT id, name FROM eco_clubs ORDER BY name ASC";
$result_all_clubs = $conn->query($sql_all_clubs);

// Fetch forum posts with usernames and optional club filter
$sql = "SELECT fp.id, fp.title, fp.content, fp.created_at, u.username, ec.name AS club_name 
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.id 
        LEFT JOIN eco_clubs ec ON fp.club_id = ec.id ";

if ($filter_club_id) {
    $sql .= " WHERE fp.club_id = ? ";
}

$sql .= " ORDER BY fp.created_at DESC";

$stmt = $conn->prepare($sql);
if ($filter_club_id) {
    $stmt->bind_param("i", $filter_club_id);
}
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Earth Guardians</title>
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

        .forum-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .create-post-form, .post-card {
            background: rgba(46, 204, 113, 0.05);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(46, 204, 113, 0.1);
        }

        .create-post-form h2 {
            font-family: 'Poppins', sans-serif;
            color: #2ECC71;
            margin-top: 0;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input, .input-group textarea, .input-group select {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(46, 204, 113, 0.2);
            background: rgba(0,0,0, 0.2);
            color: white;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .btn {
            background: linear-gradient(45deg, #2ECC71, #27AE60);
            color: #fff;
            padding: 12px 25px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 1em;
            cursor: pointer;
            border-radius: 10px;
            border: none;
            font-weight: bold;
        }
        
        .post-card h3 {
            font-family: 'Poppins', sans-serif;
            color: #2ECC71;
            margin-top: 0;
        }

        .post-meta {
            font-size: 0.9em;
            color: #aaa;
            margin-bottom: 15px;
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
            .create-post-form, .post-card {
                padding: 20px;
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
            <li><a href="forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
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
        <h1>Community Forum</h1>
        <p>Share your ideas, discuss challenges, and connect with other Guardians.</p>
    </div>

    <div class="forum-container">
        <div class="create-post-form">
            <h2>Create a New Post</h2>
            <?php if(!empty($error_message)) echo "<p style='color:red'>$error_message</p>"; ?>
            <?php if(!empty($success_message)) echo "<p style='color:lightgreen'>$success_message</p>"; ?>
            <form action="forum.php" method="post">
                <div class="input-group">
                    <input type="text" name="title" placeholder="Post Title" required>
                </div>
                <div class="input-group">
                    <textarea name="content" rows="5" placeholder="What's on your mind?" required></textarea>
                </div>
                <button type="submit" name="create_post" class="btn">Submit Post</button>
            </form>
        </div>

        <div class="filter-section">
            <form action="forum.php" method="get">
                <label for="club_filter">Filter by Club:</label>
                <select name="club_id" id="club_filter" onchange="this.form.submit()">
                    <option value="">All Posts</option>
                    <?php while($club = $result_all_clubs->fetch_assoc()): ?>
                        <option value="<?php echo $club['id']; ?>" <?php if($filter_club_id == $club['id']) echo 'selected'; ?>><?php echo htmlspecialchars($club['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <div class="posts-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while($post = $result->fetch_assoc()): ?>
                    <div class="post-card">
                        <h3><?php echo htmlspecialchars($post['title']); ?>
                            <?php if ($post['club_name']): ?>
                                <small> (Club: <?php echo htmlspecialchars($post['club_name']); ?>)</small>
                            <?php endif; ?>
                        </h3>
                        <div class="post-meta">
                            Posted by <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts yet. Be the first to start a discussion!</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>
