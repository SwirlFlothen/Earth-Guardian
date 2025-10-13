<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf()) {
        die('Invalid CSRF token');
    }
    if (isset($_POST['add_lesson'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "INSERT INTO lessons (title, content) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
    } elseif (isset($_POST['edit_lesson'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "UPDATE lessons SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_lesson'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM lessons WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header('Location: manage_lessons.php');
    exit();
}

// Fetch all lessons
$lessons = $conn->query("SELECT * FROM lessons ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lessons</title>
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
            <h2>Add New Lesson</h2>
            <form action="" method="post">
                <?php echo csrf_input(); ?>
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="content" placeholder="Content" rows="5" required></textarea>
                <button type="submit" name="add_lesson" class="btn btn-primary">Add Lesson</button>
            </form>
        </div>

        <div id="edit-lesson-form" class="form-container edit-form">
            <h2>Edit Lesson</h2>
            <form action="" method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="id" id="edit-id">
                <input type="text" name="title" id="edit-title" placeholder="Title" required>
                <textarea name="content" id="edit-content" placeholder="Content" rows="5" required></textarea>
                <button type="submit" name="edit_lesson" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <h2>Existing Lessons</h2>
        <div class="table-card">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Content</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $lessons->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td data-label="Content"><?php echo substr(htmlspecialchars($row['content']), 0, 100); ?>...</td>
                    <td data-label="Actions">
                        <button onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>', '<?php echo htmlspecialchars(addslashes($row['content'])); ?>')" class="btn btn-ghost">Edit</button>
                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_lesson" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function showEditForm(id, title, content) {
            document.getElementById('edit-lesson-form').style.display = 'block';
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-content').value = content;
        }
    </script>
</body>
</html>
<?php include_once 'footer.php'; ?>
