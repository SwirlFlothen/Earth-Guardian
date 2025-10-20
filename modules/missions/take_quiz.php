<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    header('Location: ../lessons/lessons.php');
    exit();
}

// Fetch quiz details (prepared statement)
$sql_quiz = "SELECT title FROM quizzes WHERE id = ? LIMIT 1";
if (!($stmt_quiz = $conn->prepare($sql_quiz))) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quiz = $result_quiz ? $result_quiz->fetch_assoc() : null;
$stmt_quiz->close();

if (!$quiz) {
    header('Location: ../lessons/lessons.php');
    exit();
}

// Fetch quiz questions and answers
$sql_questions = "SELECT q.id AS question_id, q.question_text, a.id AS answer_id, a.answer_text, a.is_correct
                  FROM quiz_questions q 
                  JOIN quiz_answers a ON q.id = a.question_id 
                  WHERE q.quiz_id = ? 
                  ORDER BY q.id, a.id";
if (!($stmt_questions = $conn->prepare($sql_questions))) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$questions = [];
$total_questions = 0;
$current_question = 1;

while ($row = $result_questions->fetch_assoc()) {
    $qid = (int)$row['question_id'];
    if (!isset($questions[$qid])) {
        $total_questions++;
        $questions[$qid] = [
            'id' => $qid,
            'text' => $row['question_text'],
            'answers' => []
        ];
    }
    $questions[$qid]['answers'][] = [
        'id' => (int)$row['answer_id'],
        'text' => $row['answer_text'],
        'is_correct' => (int)$row['is_correct']
    ];
}
$stmt_questions->close();

$score = 0;
$total_questions = count($questions);
$quiz_submitted = false;
$detailed_results = [];
$passed = false;

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_submitted = true;

    // Prevent division by zero later
    if ($total_questions === 0) {
        $quiz_submitted = false; // nothing to submit
    } else {
        foreach ($questions as $question_id => $question_data) {
            $field = 'question_' . $question_id;
            $selected_answer_id = isset($_POST[$field]) ? (int)$_POST[$field] : null;

            $correct_answer_id = null;
            foreach ($question_data['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correct_answer_id = $answer['answer_id'];
                    break;
                }
            }

            $is_correct = ($selected_answer_id !== null && $selected_answer_id === $correct_answer_id);
            if ($is_correct) {
                $score++;
            }

            $detailed_results[$question_id] = [
                'selected' => $selected_answer_id,
                'correct' => $correct_answer_id,
                'question_text' => $question_data['question_text'],
                'answers' => $question_data['answers'],
                'is_correct' => $is_correct
            ];
        }

        // Award 'Knowledge Seeker' badge if passed and not already awarded
        $passed = ($score / $total_questions) >= 0.5; // Pass if score is 50% or more
        if ($passed) {
            // Use prepared statements to get badge id
            $badge_name = 'Knowledge Seeker';
            $sql_badge = "SELECT id FROM badges WHERE name = ? LIMIT 1";
            if ($stmt_badge = $conn->prepare($sql_badge)) {
                $stmt_badge->bind_param('s', $badge_name);
                $stmt_badge->execute();
                $res = $stmt_badge->get_result();
                $badge_row = $res ? $res->fetch_assoc() : null;
                $stmt_badge->close();

                if ($badge_row && isset($badge_row['id'])) {
                    $badge_id = (int)$badge_row['id'];

                    $sql_check_badge = "SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = ? LIMIT 1";
                    if ($stmt_check_badge = $conn->prepare($sql_check_badge)) {
                        $stmt_check_badge->bind_param('ii', $user_id, $badge_id);
                        $stmt_check_badge->execute();
                        $stmt_check_badge->store_result();

                        if ($stmt_check_badge->num_rows === 0) {
                            $sql_award_badge = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
                            if ($stmt_award_badge = $conn->prepare($sql_award_badge)) {
                                $stmt_award_badge->bind_param('ii', $user_id, $badge_id);
                                $stmt_award_badge->execute();
                                $stmt_award_badge->close();
                            }
                        }
                        $stmt_check_badge->close();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Earth Guardian Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/quiz.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div class="quiz-container">
            <div class="quiz-header">
                <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <p>Test your knowledge and earn points for completing this quiz</p>
            </div>

            <?php if ($quiz_submitted): ?>
                <div class="quiz-results">
                    <div class="results-header">
                        <h2 class="results-score"><?php echo $score; ?>/<?php echo $total_questions; ?></h2>
                        <p class="results-text">
                            <?php
                            $percentage = ($total_questions > 0) ? ($score / $total_questions) * 100 : 0;
                            if ($passed):
                            ?>
                                Congratulations! You passed the quiz with <?php echo round($percentage); ?>%
                            <?php else: ?>
                                Keep trying! You need 70% to pass the quiz.
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($passed): ?>
                    <div class="badge-earned">
                        <img src="../../public/assets/Images/badges/knowledge_seeker.png" alt="Knowledge Seeker Badge" class="badge-image">
                        <h3 class="badge-name">Knowledge Seeker</h3>
                        <p class="badge-desc">Awarded for successfully completing a quiz and demonstrating your environmental knowledge!</p>
                    </div>
                    <?php endif; ?>

                    <div class="results-actions">
                        <a href="missions.php" class="nav-button btn-prev">
                            <i class="fas fa-arrow-left"></i>
                            Back to Missions
                        </a>
                        <?php if (!$passed): ?>
                        <a href="?id=<?php echo $quiz_id; ?>" class="nav-button btn-next">
                            Try Again
                            <i class="fas fa-redo"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if (empty($questions)): ?>
                    <div class="quiz-card">
                        <p>This quiz has no questions yet. Please check back later.</p>
                        <div class="quiz-navigation">
                            <a href="missions.php" class="nav-button btn-prev">
                                <i class="fas fa-arrow-left"></i>
                                Back to Missions
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="quiz-progress">
                        <div class="progress-steps">
                            <?php for($i = 1; $i <= $total_questions; $i++): ?>
                            <div class="progress-step <?php echo $i === 1 ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <div class="progress-text">
                            <span>Question 1</span>
                            <span>of <?php echo $total_questions; ?></span>
                        </div>
                    </div>

                    <form id="quizForm" action="?id=<?php echo $quiz_id; ?>" method="post">
                        <?php $qIndex = 1; foreach ($questions as $question_id => $question_data): ?>
                            <div class="step" data-index="<?php echo $qIndex - 1; ?>" <?php echo ($qIndex === 1) ? 'style="display:block"' : 'style="display:none"'; ?>>
                                <div class="question-card">
                                    <h2 class="question-text"><?php echo htmlspecialchars($question_data['text']); ?></h2>
                                    <div class="options-list">
                                        <?php foreach ($question_data['answers'] as $answer): ?>
                                            <label class="option-item">
                                                <input type="radio" name="question_<?php echo $question_id; ?>" value="<?php echo $answer['id']; ?>" required>
                                                <div class="option-text">
                                                    <span class="option-marker"></span>
                                                    <?php echo htmlspecialchars($answer['text']); ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php $qIndex++; endforeach; ?>

                        <div class="quiz-navigation">
                            <button type="button" id="prevBtn" class="nav-button btn-prev" disabled>
                                <i class="fas fa-arrow-left"></i>
                                Previous
                            </button>
                            <button type="button" id="nextBtn" class="nav-button btn-next">
                                Next
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" id="submitBtn" name="submit_quiz" class="nav-button btn-submit" style="display:none">
                                Submit Quiz
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../public/assets/js/main.js"></script>
    <script src="../../public/assets/js/quiz_interaction.js"></script>
</body>
</html>
<?php $conn->close(); ?>