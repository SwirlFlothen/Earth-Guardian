<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$lesson_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$lesson_id) {
    header('Location: lessons.php');
    exit();
}

// Fetch lesson details
$sql_lesson = "SELECT title, content FROM lessons WHERE id = ?";
$stmt_lesson = $conn->prepare($sql_lesson);
$stmt_lesson->bind_param("i", $lesson_id);
$stmt_lesson->execute();
$result_lesson = $stmt_lesson->get_result();
$lesson = $result_lesson->fetch_assoc();
$stmt_lesson->close();

if (!$lesson) {
    header('Location: lessons.php');
    exit();
}

// Fetch quiz details for this lesson
$sql_quiz = "SELECT id, title FROM quizzes WHERE lesson_id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $lesson_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quiz = $result_quiz->fetch_assoc();
$stmt_quiz->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #1D2A21;
            color: #fff;
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

        .navbar .nav-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
        }

        .navbar .nav-links a i {
            margin-right: 8px;
            font-size: 1.1em;
        }

        .navbar .nav-links a:hover {
            color: #2ECC71;
            transform: translateY(-3px);
        }

        .container {
            max-width: 800px;
            margin: 150px auto 40px;
            padding: 0 20px;
        }

        .lesson-content {
            background: rgba(46, 204, 113, 0.05);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(46, 204, 113, 0.1);
            line-height: 1.8;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            color: #2ECC71;
            margin-top: 0;
            font-size: 2.5em;
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
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            .navbar .nav-links {
                display: none;
            }
            .container {
                margin-top: 100px;
            }
            .lesson-content {
                padding: 20px;
            }
            h1 {
                font-size: 2em;
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
            <li><a href="lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="lesson-content">
            <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
            <div><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></div>
            <?php if ($quiz): ?>
                <a href="../missions/take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn">Take Quiz: <?php echo htmlspecialchars($quiz['title']); ?></a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
