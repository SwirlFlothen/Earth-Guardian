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
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>

    <nav class="navbar">
        <ul>
            <li><a href="../../public/index.php">Home</a></li>
            <li><a href="../missions/missions.php">Missions</a></li>
            <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
            <li><a href="../clubs/view_clubs.php">Clubs</a></li>
            <li><a href="forum.php">Forum</a></li>
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
