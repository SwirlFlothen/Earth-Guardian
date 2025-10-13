<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="../public/assets/js/admin.js"></script>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container">
        <h2>Content Management</h2>
        <div class="card-container">
            <div class="card">
                <div class="card-top">
                    <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="card-title">Verify Missions</div>
                        <div class="card-desc muted">Approve user submissions and award points/badges.</div>
                    </div>
                </div>
                <div class="card-cta"><a href="verify_missions.php">Open <i class="fas fa-arrow-right"></i></a></div>
            </div>

            <div class="card">
                <div class="card-top">
                    <div class="card-icon"><i class="fas fa-flag"></i></div>
                    <div>
                        <div class="card-title">Manage Missions & Events</div>
                        <div class="card-desc muted">Create and edit missions, set points and deadlines.</div>
                    </div>
                </div>
                <div class="card-cta"><a href="manage_missions_events.php">Open <i class="fas fa-arrow-right"></i></a></div>
            </div>

            <div class="card">
                <div class="card-top">
                    <div class="card-icon"><i class="fas fa-book"></i></div>
                    <div>
                        <div class="card-title">Manage Lessons</div>
                        <div class="card-desc muted">Add or edit learning content visible to users.</div>
                    </div>
                </div>
                <div class="card-cta"><a href="manage_lessons.php">Open <i class="fas fa-arrow-right"></i></a></div>
            </div>

            <div class="card">
                <div class="card-top">
                    <div class="card-icon"><i class="fas fa-comments"></i></div>
                    <div>
                        <div class="card-title">Manage Forum Posts</div>
                        <div class="card-desc muted">Moderate or remove forum posts from users.</div>
                    </div>
                </div>
                <div class="card-cta"><a href="manage_forum.php">Open <i class="fas fa-arrow-right"></i></a></div>
            </div>

            <div class="card">
                <div class="card-top">
                    <div class="card-icon"><i class="fas fa-question-circle"></i></div>
                    <div>
                        <div class="card-title">Manage Quizzes</div>
                        <div class="card-desc muted">Create quizzes and manage questions/answers.</div>
                    </div>
                </div>
                <div class="card-cta"><a href="manage_quizzes.php">Open <i class="fas fa-arrow-right"></i></a></div>
            </div>
        </div>
    </div>
    <?php include_once 'footer.php'; ?>
</body>
</html>
