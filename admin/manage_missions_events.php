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
    if (isset($_POST['add_mission'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $points = $_POST['points'];
        $deadline = $_POST['deadline'];
        $sql = "INSERT INTO missions (title, description, points, deadline) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $title, $description, $points, $deadline);
        $stmt->execute();
    } elseif (isset($_POST['edit_mission'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $points = $_POST['points'];
        $deadline = $_POST['deadline'];
        $sql = "UPDATE missions SET title = ?, description = ?, points = ?, deadline = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $title, $description, $points, $deadline, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_mission'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM missions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header('Location: manage_missions_events.php');
    exit();
}

// Fetch all missions
$missions = $conn->query("SELECT * FROM missions ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Missions</title>
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
            <h2>Add New Mission</h2>
            <form action="" method="post">
                <?php echo csrf_input(); ?>
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="description" placeholder="Description" required></textarea>
                <input type="number" name="points" placeholder="Points" required>
                <input type="date" name="deadline" required>
                <button type="submit" name="add_mission" class="btn btn-primary">Add Mission</button>
            </form>
        </div>

        <div id="edit-mission-form" class="form-container edit-form">
            <h2>Edit Mission</h2>
            <form action="" method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="id" id="edit-id">
                <input type="text" name="title" id="edit-title" placeholder="Title" required>
                <textarea name="description" id="edit-description" placeholder="Description" required></textarea>
                <input type="number" name="points" id="edit-points" placeholder="Points" required>
                <input type="date" name="deadline" id="edit-deadline">
                <button type="submit" name="edit_mission" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <h2>Existing Missions</h2>
        <div class="table-card">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Description</th><th>Points</th><th>Deadline</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $missions->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td data-label="Points"><?php echo $row['points']; ?></td>
                    <td data-label="Deadline"><?php echo htmlspecialchars($row['deadline']); ?></td>
                    <td data-label="Actions">
                        <button onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>', '<?php echo htmlspecialchars(addslashes($row['description'])); ?>', <?php echo $row['points']; ?>, '<?php echo htmlspecialchars(addslashes($row['deadline'])); ?>')" class="btn btn-ghost">Edit</button>
                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_mission" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function showEditForm(id, title, description, points, deadline) {
            document.getElementById('edit-mission-form').style.display = 'block';
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-points').value = points;
            document.getElementById('edit-deadline').value = deadline;
        }
    </script>
</body>
</html>
<?php include_once 'footer.php'; ?>
