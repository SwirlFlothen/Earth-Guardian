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
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="../../public/index.php">Home</a></li>
            <li><a href="../missions/missions.php">Missions</a></li>
            <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
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