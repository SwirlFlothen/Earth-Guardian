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
$badge_earned = null;

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_submitted = true;
    
    if ($total_questions > 0) {
        foreach ($questions as $question_id => $question_data) {
            $field = 'question_' . $question_id;
            $selected_answer_id = isset($_POST[$field]) ? (int)$_POST[$field] : null;
            
            $correct_answer = null;
            $selected_answer_text = "Not answered";
            $is_correct = false;
            
            foreach ($question_data['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correct_answer = $answer['text'];
                }
                if ($answer['id'] === $selected_answer_id) {
                    $selected_answer_text = $answer['text'];
                    $is_correct = $answer['is_correct'];
                }
            }
            
            if ($is_correct) {
                $score++;
            }
            
            $detailed_results[] = [
                'question' => $question_data['text'],
                'selected' => $selected_answer_text,
                'correct' => $correct_answer,
                'is_correct' => $is_correct
            ];
        }
        
        $percentage = ($score / $total_questions) * 100;
        $passed = $percentage >= 70;
        
        if ($passed) {
            // Check if a badge should be awarded
            $sql_badge = "SELECT id, name, description, image_url FROM badges WHERE quiz_id = ? AND NOT EXISTS (SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = badges.id) LIMIT 1";
            $stmt_badge = $conn->prepare($sql_badge);
            $stmt_badge->bind_param("ii", $quiz_id, $user_id);
            $stmt_badge->execute();
            $result_badge = $stmt_badge->get_result();
            
            if ($result_badge->num_rows > 0) {
                $badge = $result_badge->fetch_assoc();
                
                // Award the badge
                $sql_award = "INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, NOW())";
                $stmt_award = $conn->prepare($sql_award);
                $stmt_award->bind_param("ii", $user_id, $badge['id']);
                if ($stmt_award->execute()) {
                    $badge_earned = $badge;
                }
                $stmt_award->close();
            }
            $stmt_badge->close();
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
    
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p>Test your knowledge and earn points for completing this quiz.</p>
        </div>
        
        <?php if (!$quiz_submitted): ?>
        <div class="quiz-progress">
            <div class="progress-steps">
                <?php for($i = 1; $i <= $total_questions; $i++): ?>
                <div class="progress-step <?php echo $i === $current_question ? 'active' : ($i < $current_question ? 'completed' : ''); ?>">
                    <?php echo $i; ?>
                </div>
                <?php endfor; ?>
            </div>
            <div class="progress-text">
                <span>Question <?php echo $current_question; ?></span>
                <span>of <?php echo $total_questions; ?></span>
            </div>
        </div>

        <form method="POST" id="quizForm">
            <?php
            $question_data = $questions[$current_question];
            ?>
            <div class="question-card current">
                <h2 class="question-text"><?php echo htmlspecialchars($question_data['text']); ?></h2>
                
                <div class="options-list">
                    <?php foreach($question_data['answers'] as $answer): ?>
                    <label class="option-item">
                        <input type="radio" name="question_<?php echo $question_data['id']; ?>" value="<?php echo $answer['id']; ?>" required>
                        <div class="option-text">
                            <span class="option-marker"></span>
                            <?php echo htmlspecialchars($answer['text']); ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="quiz-navigation">
                <?php if ($current_question > 1): ?>
                <button type="button" class="nav-button btn-prev">
                    <i class="fas fa-arrow-left"></i>
                    Previous
                </button>
                <?php endif; ?>
                
                <?php if ($current_question < $total_questions): ?>
                <button type="button" class="nav-button btn-next">
                    Next
                    <i class="fas fa-arrow-right"></i>
                </button>
                <?php else: ?>
                <button type="submit" name="submit_quiz" class="nav-button btn-submit">
                    Submit Quiz
                    <i class="fas fa-check"></i>
                </button>
                <?php endif; ?>
            </div>
        </form>
        <?php else: ?>
        <div class="quiz-results">
            <div class="results-header">
                <h2 class="results-score"><?php echo $score; ?>/<?php echo $total_questions; ?></h2>
                <p class="results-text">
                    <?php if ($passed): ?>
                        Congratulations! You passed the quiz with <?php echo round(($score / $total_questions) * 100); ?>%
                    <?php else: ?>
                        Keep trying! You need 70% to pass the quiz.
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($badge_earned): ?>
            <div class="badge-earned">
                <img src="<?php echo htmlspecialchars($badge_earned['image_url']); ?>" alt="<?php echo htmlspecialchars($badge_earned['name']); ?>" class="badge-image">
                <h3 class="badge-name"><?php echo htmlspecialchars($badge_earned['name']); ?></h3>
                <p class="badge-desc"><?php echo htmlspecialchars($badge_earned['description']); ?></p>
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
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quizForm');
        const questionCards = document.querySelectorAll('.question-card');
        const progressSteps = document.querySelectorAll('.progress-step');
        let currentStep = <?php echo $current_question; ?>;
        
        function showQuestion(step) {
            questionCards.forEach((card, index) => {
                if (index + 1 === step) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            progressSteps.forEach((step, index) => {
                if (index + 1 === currentStep) {
                    step.classList.add('active');
                } else if (index + 1 < currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });
            
            document.querySelector('.progress-text').innerHTML = 
                `<span>Question ${step}</span><span>of ${questionCards.length}</span>`;
        }
        
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentCard = document.querySelector(`.question-card:nth-child(${currentStep})`);
                const input = currentCard.querySelector('input[type="radio"]:checked');
                
                if (!input) {
                    alert('Please select an answer before proceeding.');
                    return;
                }
                
                if (currentStep < questionCards.length) {
                    currentStep++;
                    showQuestion(currentStep);
                }
            });
        });
        
        document.querySelectorAll('.btn-prev').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    showQuestion(currentStep);
                }
            });
        });
        
        // Show initial question
        showQuestion(currentStep);
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>