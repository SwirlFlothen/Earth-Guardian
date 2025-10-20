<?php
session_start();
require_once '../../includes/db_connect.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';

// Fetch clubs the user is a member of
$sql_user_clubs = "SELECT ec.id, ec.name FROM eco_clubs ec JOIN club_members cm ON ec.id = cm.club_id WHERE cm.user_id = ?";
$stmt_user_clubs = $conn->prepare($sql_user_clubs);
$stmt_user_clubs->bind_param("i", $user_id);
$stmt_user_clubs->execute();
$result_user_clubs = $stmt_user_clubs->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $club_id = filter_input(INPUT_POST, 'club_id', FILTER_VALIDATE_INT);

    if (empty($title) || empty($content)) {
        $error_message = "Please fill in both the title and content.";
    } else {
        $sql = "INSERT INTO forum_posts (user_id, title, content, club_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $user_id, $title, $content, $club_id);

        if ($stmt->execute()) {
            header("Location: forum.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: white;
            background: url('../../public/assets/Images/Dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }
        input[type='text'], textarea, select {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .submit-btn {
            padding: 15px 30px;
            background: linear-gradient(45deg, #f9ca24, #f39c12);
            color: #333;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .submit-btn:hover {
            transform: scale(1.05);
        }
        .error-message {
            color: #ff4d4d;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="create_post.php" method="post">
            <h1 style="text-align:center; font-size: 2.5em; margin-bottom: 30px;">Create New Post</h1>
            <?php if(!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="8" required></textarea>
            </div>
            <div class="form-group">
                <label for="club_id">Post to Club (Optional)</label>
                <select id="club_id" name="club_id">
                    <option value="">No Club (Public Forum)</option>
                    <?php while($club = $result_user_clubs->fetch_assoc()): ?>
                        <option value="<?php echo $club['id']; ?>"><?php echo htmlspecialchars($club['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="text-align:center;">
                <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Create Post</button>
            </div>
        </form>
    </div>
</body>
</html>
