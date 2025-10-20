<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_post'])) {
    if (!validate_csrf()) {
        die('Invalid CSRF token');
    }
    $id = $_POST['id'];
    $sql = "DELETE FROM forum_posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: manage_forum.php');
    exit();
}

// Fetch all forum posts with usernames
$posts = $conn->query("SELECT fp.id, fp.title, fp.content, u.username FROM forum_posts fp JOIN users u ON fp.user_id = u.id ORDER BY fp.created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Forum Posts</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="../public/assets/js/admin.js"></script>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container">
        <h2>Existing Forum Posts</h2>
        <div class="table-card">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Content</th><th>Author</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $posts->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo substr(htmlspecialchars($row['content']), 0, 100); ?>...</td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_post" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php include_once 'footer.php'; ?>
</body>
</html>
