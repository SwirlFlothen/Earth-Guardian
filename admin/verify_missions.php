<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf()) {
        die('Invalid CSRF token');
    }
    $user_mission_id = $_POST['user_mission_id'];
    $user_id = $_POST['user_id'];
    $points = $_POST['points'];

    if (isset($_POST['approve'])) {
        $sql = "UPDATE user_missions SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_mission_id);
        $stmt->execute();

        $sql_update_points = "UPDATE users SET points = points + ? WHERE id = ?";
        $stmt_update_points = $conn->prepare($sql_update_points);
        $stmt_update_points->bind_param("ii", $points, $user_id);
        $stmt_update_points->execute();

        // Check if this is the user's first approved mission
        $sql_count_missions = "SELECT COUNT(*) AS total_approved FROM user_missions WHERE user_id = ? AND status = 'approved'";
        $stmt_count_missions = $conn->prepare($sql_count_missions);
        $stmt_count_missions->bind_param("i", $user_id);
        $stmt_count_missions->execute();
        $result_count_missions = $stmt_count_missions->get_result();
        $row_count_missions = $result_count_missions->fetch_assoc();
        $stmt_count_missions->close();

        if ($row_count_missions['total_approved'] == 1) {
            // Award 'First Mission Complete' badge
            $sql_badge_id = "SELECT id FROM badges WHERE name = 'First Mission Complete'";
            $result_badge_id = $conn->query($sql_badge_id);
            $badge_id = $result_badge_id->fetch_assoc()['id'];

            $sql_award_badge = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
            $stmt_award_badge = $conn->prepare($sql_award_badge);
            $stmt_award_badge->bind_param("ii", $user_id, $badge_id);
            $stmt_award_badge->execute();
            $stmt_award_badge->close();
        }

        // Check for Eco-Explorer badge (5 approved missions)
        if ($row_count_missions['total_approved'] == 5) {
            $sql_eco_explorer_badge_id = "SELECT id FROM badges WHERE name = 'Eco-Explorer'";
            $result_eco_explorer_badge_id = $conn->query($sql_eco_explorer_badge_id);
            $eco_explorer_badge_id = $result_eco_explorer_badge_id->fetch_assoc()['id'];

            // Check if user already has the badge
            $sql_check_eco_explorer = "SELECT * FROM user_badges WHERE user_id = ? AND badge_id = ?";
            $stmt_check_eco_explorer = $conn->prepare($sql_check_eco_explorer);
            $stmt_check_eco_explorer->bind_param("ii", $user_id, $eco_explorer_badge_id);
            $stmt_check_eco_explorer->execute();
            $stmt_check_eco_explorer->store_result();

            if ($stmt_check_eco_explorer->num_rows == 0) {
                $sql_award_eco_explorer = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
                $stmt_award_eco_explorer = $conn->prepare($sql_award_eco_explorer);
                $stmt_award_eco_explorer->bind_param("ii", $user_id, $eco_explorer_badge_id);
                $stmt_award_eco_explorer->execute();
                $stmt_award_eco_explorer->close();
            }
            $stmt_check_eco_explorer->close();
        }

    } elseif (isset($_POST['reject'])) {
        $sql = "UPDATE user_missions SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_mission_id);
        $stmt->execute();
    }
    header('Location: verify_missions.php');
    exit();
}

// Fetch pending missions
$sql = "SELECT um.id, u.username, m.title, m.points, um.proof, um.user_id
        FROM user_missions um
        JOIN users u ON um.user_id = u.id
        JOIN missions m ON um.mission_id = m.id
        WHERE um.status = 'pending'";
$pending_missions = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Missions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="../public/assets/js/admin.js"></script>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container">
        <h2>Pending Submissions</h2>
        <div class="table-card">
        <table class="admin-table">
            <thead><tr><th>User</th><th>Mission</th><th>Proof</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $pending_missions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><a href="../<?php echo htmlspecialchars($row['proof']); ?>" target="_blank">View Proof</a></td>
                    <td>
                        <form action="" method="post" style="display:inline;" class="confirm-delete">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="user_mission_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <input type="hidden" name="points" value="<?php echo $row['points']; ?>">
                                                        <div class="controls">
                                                            <button type="submit" name="approve" class="btn btn-primary">Approve</button>
                                                            <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                                                        </div>
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
