<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    header('Location: ../lessons/lessons.php');
    exit();
}

// Fetch quiz details
$sql_quiz = "SELECT title FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quiz = $result_quiz->fetch_assoc();
$stmt_quiz->close();

if (!$quiz) {
    header('Location: lessons.php');
    exit();
}

// Fetch quiz questions and answers
$sql_questions = "SELECT q.id AS question_id, q.question_text, a.id AS answer_id, a.answer_text, a.is_correct 
                  FROM quiz_questions q 
                  JOIN quiz_answers a ON q.id = a.question_id 
                  WHERE q.quiz_id = ? ORDER BY q.id, a.id";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$questions = [];
while ($row = $result_questions->fetch_assoc()) {
    $questions[$row['question_id']]['question_text'] = $row['question_text'];
    $questions[$row['question_id']]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer_text' => $row['answer_text'],
        'is_correct' => $row['is_correct']
    ];
}
$stmt_questions->close();

$score = 0;
$total_questions = count($questions);
$quiz_submitted = false;

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_submitted = true;
    foreach ($questions as $question_id => $question_data) {
        if (isset($_POST['question_' . $question_id])) {
            $selected_answer_id = $_POST['question_' . $question_id];
            foreach ($question_data['answers'] as $answer) {
                if ($answer['answer_id'] == $selected_answer_id && $answer['is_correct']) {
                    $score++;
                }
            }
        }
    }

    // Award 'Knowledge Seeker' badge if passed and not already awarded
    if ($score / $total_questions >= 0.5) { // Pass if score is 50% or more
        $sql_badge_id = "SELECT id FROM badges WHERE name = 'Knowledge Seeker'";
        $result_badge_id = $conn->query($sql_badge_id);
        $badge_id = $result_badge_id->fetch_assoc()['id'];

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
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Earth Guardians Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html { margin: 0; font-family: 'Poppins', sans-serif; background: #1D2A21; color: #fff; }
        .navbar { position: fixed; top: 0; width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; z-index: 10; background: rgba(29, 42, 33, 0.8); box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .navbar .logo img { height: 45px; }
        .navbar .nav-links a { color: #fff; text-decoration: none; margin: 0 15px; font-weight: 500; transition: color 0.3s, transform 0.3s; display: inline-flex; align-items: center; }
        .navbar .nav-links a i { margin-right: 8px; font-size: 1.1em; }
        .navbar .nav-links a:hover { color: #2ECC71; transform: translateY(-3px); }
        .container { max-width: 800px; margin: 150px auto 40px; padding: 0 20px; }
        .quiz-container { background: rgba(46, 204, 113, 0.05); border-radius: 15px; padding: 40px; border: 1px solid rgba(46, 204, 113, 0.1); }
        h1 { font-family: 'Poppins', sans-serif; color: #2ECC71; margin-top: 0; font-size: 2.5em; }
        .question { margin-bottom: 20px; padding: 15px; background: rgba(46, 204, 113, 0.03); border-radius: 10px; }
        .question p { font-weight: 600; margin-bottom: 10px; }
        .answers label { display: block; margin-bottom: 8px; cursor: pointer; }
        .btn { background: linear-gradient(45deg, #2ECC71, #27AE60); color: #fff; padding: 12px 25px; text-align: center; text-decoration: none; display: inline-block; font-size: 1em; cursor: pointer; border-radius: 10px; border: none; font-weight: bold; margin-top: 20px; }
        .score-display { font-size: 1.5em; font-weight: 700; text-align: center; margin-top: 30px; color: #4dff88; }
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            .navbar .nav-links {
                display: none; width: 100%; flex-direction: column; background: rgba(29, 42, 33, 0.95); position: absolute; top: 75px; left: 0; padding-bottom: 20px;
            }
            .navbar .nav-links.active { display: flex; }
            .navbar .nav-links li { margin: 10px 0; text-align: center; }
            .navbar .nav-links a { padding: 10px; width: 90%; margin: 0 auto; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .navbar .nav-links a:hover { background: #2ECC71; border-radius: 5px; }
            .menu-toggle { display: flex; flex-direction: column; cursor: pointer; }
            .menu-toggle .bar { height: 3px; width: 25px; background-color: #fff; margin: 4px 0; transition: 0.4s; }
            .container {
                margin-top: 100px;
            }
            .quiz-container {
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
            <li><a href="missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../static/about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    <div class="container">
        <div class="quiz-container">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <?php if ($quiz_submitted): ?>
                <div class="score-display">You scored <?php echo $score; ?> out of <?php echo $total_questions; ?></div>
            <?php else: ?>
                <form action="take_quiz.php?id=<?php echo $quiz_id; ?>" method="post">
                    <?php foreach ($questions as $question_id => $question_data): ?>
                        <div class="question">
                            <p><?php echo htmlspecialchars($question_data['question_text']); ?></p>
                            <div class="answers">
                                <?php foreach ($question_data['answers'] as $answer): ?>
                                    <label>
                                        <input type="radio" name="question_<?php echo $question_id; ?>" value="<?php echo $answer['answer_id']; ?>" required>
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="submit_quiz" class="btn">Submit Quiz</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const navLinks = document.querySelector('.nav-links');

            mobileMenu.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>