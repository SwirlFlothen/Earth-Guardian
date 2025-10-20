<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$selected_quiz_id = null;
$selected_quiz_title = '';

// Handle Add/Edit/Delete Quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf()) {
        die('Invalid CSRF token');
    }
    if (isset($_POST['add_quiz'])) {
        $lesson_id = $_POST['lesson_id'];
        $title = $_POST['title'];
        $sql = "INSERT INTO quizzes (lesson_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $lesson_id, $title);
        $stmt->execute();
    } elseif (isset($_POST['edit_quiz'])) {
        $id = $_POST['id'];
        $lesson_id = $_POST['lesson_id'];
        $title = $_POST['title'];
        $sql = "UPDATE quizzes SET lesson_id = ?, title = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $lesson_id, $title, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_quiz'])) {
        $id = $_POST['id'];
        // Delete associated questions and answers first
        $conn->query("DELETE FROM quiz_answers WHERE question_id IN (SELECT id FROM quiz_questions WHERE quiz_id = $id)");
        $conn->query("DELETE FROM quiz_questions WHERE quiz_id = $id");
        $sql = "DELETE FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['add_question'])) {
        $quiz_id = $_POST['quiz_id'];
        $question_text = $_POST['question_text'];
        $sql = "INSERT INTO quiz_questions (quiz_id, question_text) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $quiz_id, $question_text);
        $stmt->execute();
        $selected_quiz_id = $quiz_id; // Stay on the same quiz
    } elseif (isset($_POST['add_answer'])) {
        $question_id = $_POST['question_id'];
        $answer_text = $_POST['answer_text'];
        $is_correct = isset($_POST['is_correct']) ? 1 : 0;
        $sql = "INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
        $stmt->execute();
        // Need to re-select quiz to display questions/answers
        $quiz_info = $conn->query("SELECT quiz_id FROM quiz_questions WHERE id = $question_id")->fetch_assoc();
        $selected_quiz_id = $quiz_info['quiz_id'];
    } elseif (isset($_POST['delete_question'])) {
        $question_id = $_POST['question_id'];
        $quiz_info = $conn->query("SELECT quiz_id FROM quiz_questions WHERE id = $question_id")->fetch_assoc();
        $conn->query("DELETE FROM quiz_answers WHERE question_id = $question_id");
        $conn->query("DELETE FROM quiz_questions WHERE id = $question_id");
        $selected_quiz_id = $quiz_info['quiz_id'];
    } elseif (isset($_POST['delete_answer'])) {
        $answer_id = $_POST['answer_id'];
        $question_info = $conn->query("SELECT question_id FROM quiz_answers WHERE id = $answer_id")->fetch_assoc();
        $quiz_info = $conn->query("SELECT quiz_id FROM quiz_questions WHERE id = {$question_info['question_id']}")->fetch_assoc();
        $conn->query("DELETE FROM quiz_answers WHERE id = $answer_id");
        $selected_quiz_id = $quiz_info['quiz_id'];
    }
    // Redirect to prevent form resubmission
    header('Location: manage_quizzes.php' . ($selected_quiz_id ? '?quiz_id=' . $selected_quiz_id : ''));
    exit();
}

// Fetch all quizzes
$quizzes = $conn->query("SELECT q.id, q.title, l.title AS lesson_title FROM quizzes q JOIN lessons l ON q.lesson_id = l.id ORDER BY q.id DESC");

// Fetch lessons for quiz creation/editing
$lessons = $conn->query("SELECT id, title FROM lessons ORDER BY title ASC");

// If a quiz is selected, fetch its questions and answers
if (isset($_GET['quiz_id'])) {
    $selected_quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
    $selected_quiz_info = $conn->query("SELECT title FROM quizzes WHERE id = $selected_quiz_id")->fetch_assoc();
    $selected_quiz_title = $selected_quiz_info['title'];

    $questions_data = $conn->query("SELECT q.id AS question_id, q.question_text, a.id AS answer_id, a.answer_text, a.is_correct 
                                    FROM quiz_questions q 
                                    LEFT JOIN quiz_answers a ON q.id = a.question_id 
                                    WHERE q.quiz_id = $selected_quiz_id ORDER BY q.id, a.id");
    $questions = [];
    while ($row = $questions_data->fetch_assoc()) {
        $questions[$row['question_id']]['question_text'] = $row['question_text'];
        if ($row['answer_id']) {
            $questions[$row['question_id']]['answers'][] = [
                'answer_id' => $row['answer_id'],
                'answer_text' => $row['answer_text'],
                'is_correct' => $row['is_correct']
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="../public/assets/js/admin.js"></script>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container">
        <div class="form-container">
            <h2>Add New Quiz</h2>
            <form action="" method="post">
                <?php echo csrf_input(); ?>
                <select name="lesson_id" required>
                    <option value="">Select Lesson</option>
                    <?php while($lesson = $lessons->fetch_assoc()): ?>
                        <option value="<?php echo $lesson['id']; ?>"><?php echo htmlspecialchars($lesson['title']); ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="title" placeholder="Quiz Title" required>
                <button type="submit" name="add_quiz" class="btn btn-primary">Add Quiz</button>
            </form>
        </div>

        <h2>Existing Quizzes</h2>
        <div class="table-card">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Lesson</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $quizzes->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td data-label="Lesson"><?php echo htmlspecialchars($row['lesson_title']); ?></td>
                    <td data-label="Actions">
                        <a href="?quiz_id=<?php echo $row['id']; ?>">Manage Questions</a> |
                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_quiz" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        <?php if ($selected_quiz_id): ?>
        <div class="question-section">
            <h3>Questions for: <?php echo htmlspecialchars($selected_quiz_title); ?></h3>
            <div class="form-container">
                <h4>Add New Question</h4>
                <form action="" method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="quiz_id" value="<?php echo $selected_quiz_id; ?>">
                    <textarea name="question_text" placeholder="Question Text" rows="3" required></textarea>
                    <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
                </form>
            </div>

            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $question_id => $question_data): ?>
                    <div class="question-item">
                        <p><strong>Q: <?php echo htmlspecialchars($question_data['question_text']); ?></strong>
                            <form action="" method="post" style="display:inline; float:right;" class="confirm-delete">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                <button type="submit" name="delete_question" class="btn btn-danger">Delete Question</button>
                            </form>
                        </p>
                        
                        <h5>Answers:</h5>
                        <div class="answers-list">
                            <?php if (!empty($question_data['answers'])): ?>
                                <?php foreach ($question_data['answers'] as $answer): ?>
                                    <div class="answer-item">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?> (<?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>)
                                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="answer_id" value="<?php echo $answer['answer_id']; ?>">
                                                <button type="submit" name="delete_answer" class="btn btn-danger">Delete</button>
                                            </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No answers yet.</p>
                            <?php endif; ?>
                        </div>
                        <div class="form-container" style="margin-top:15px;">
                            <h6>Add New Answer</h6>
                            <form action="" method="post">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                <input type="text" name="answer_text" placeholder="Answer Text" required>
                                <label><input type="checkbox" name="is_correct" value="1"> Correct Answer</label>
                                <button type="submit" name="add_answer" class="btn btn-primary">Add Answer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No questions yet for this quiz. Add one above!</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include_once 'footer.php'; ?>
