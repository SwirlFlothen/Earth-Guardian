<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $location = htmlspecialchars($_POST['location']);
    $city = htmlspecialchars($_POST['city']);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);

    if (!$firstname || !$lastname || !$email || !$location || !$city || !$age) {
        $error_message = "Please fill all the fields correctly.";
    } else {
        $sql_update = "UPDATE users SET firstname = ?, lastname = ?, email = ?, location = ?, city = ?, age = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssii", $firstname, $lastname, $email, $location, $city, $age, $user_id);

        if ($stmt_update->execute()) {
            $success_message = "Account updated successfully.";
        } else {
            $error_message = "Error updating account.";
        }
        $stmt_update->close();
    }
}

// Fetch user data
$sql_user = "SELECT firstname, lastname, email, location, city, age FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Fetch user's clubs
$sql_clubs = "SELECT ec.name, ec.description 
              FROM club_members cm
              JOIN eco_clubs ec ON cm.club_id = ec.id
              WHERE cm.user_id = ?";
$stmt_clubs = $conn->prepare($sql_clubs);
$stmt_clubs->bind_param("i", $user_id);
$stmt_clubs->execute();
$result_clubs = $stmt_clubs->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/account.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>

    <nav class="navbar">
        <ul>
            <li><a href="../../public/index.php">Home</a></li>
            <li><a href="../missions/missions.php">Missions</a></li>
            <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
            <li><a href="../clubs/view_clubs.php">Clubs</a></li>
            <li><a href="../forum/forum.php">Forum</a></li>
            <li><a href="../lessons/lessons.php">Learning Hub</a></li>
            <li><a href="my_account.php">My Account</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <div class="account-header">
            <h1>My Account</h1>
            <p>Manage your profile and view your eco-achievements</p>
        </div>

        <div class="account-grid">
            <div class="account-form">
                <?php if(!empty($error_message)): ?>
                    <div class="message error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($success_message)): ?>
                    <div class="message success-message">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <form action="my_account.php" method="post">
                    <div class="input-grid">
                        <div class="input-group">
                            <label for="firstname">First Name</label>
                            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="input-grid">
                        <div class="input-group">
                            <label for="location">Location</label>
                            <select id="location" name="location">
                                <option value="bangladesh" <?php if($user['location'] == 'bangladesh') echo 'selected'; ?>>Bangladesh</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="city">City</label>
                            <select id="city" name="city">
                                <option value="dhaka" <?php if($user['city'] == 'dhaka') echo 'selected'; ?>>Dhaka</option>
                                <option value="chittagong" <?php if($user['city'] == 'chittagong') echo 'selected'; ?>>Chittagong</option>
                                <option value="khulna" <?php if($user['city'] == 'khulna') echo 'selected'; ?>>Khulna</option>
                                <option value="rajshahi" <?php if($user['city'] == 'rajshahi') echo 'selected'; ?>>Rajshahi</option>
                                <option value="sylhet" <?php if($user['city'] == 'sylhet') echo 'selected'; ?>>Sylhet</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
                    </div>

                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </form>
            </div>

            <div class="account-sidebar">
                <img src="../../public/assets/Images/default-avatar.png" alt="User Avatar" class="user-avatar">
                
                <div class="user-stats">
                    <div class="stat-card">
                        <p class="stat-number"><?php echo isset($mission_stats['completed_missions']) ? $mission_stats['completed_missions'] : '0'; ?></p>
                        <p class="stat-label">Missions Completed</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-number"><?php echo isset($mission_stats['total_points_earned']) ? number_format($mission_stats['total_points_earned']) : '0'; ?></p>
                        <p class="stat-label">Points Earned</p>
                    </div>
                </div>

                <div class="clubs-section">
                    <h2>My Eco Clubs</h2>
                    <div class="clubs-grid">
                        <?php if ($result_clubs->num_rows > 0): ?>
                            <?php while($club = $result_clubs->fetch_assoc()): ?>
                                <div class="club-card">
                                    <h3><i class="fas fa-leaf"></i> <?php echo htmlspecialchars($club['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($club['description']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Not a member of any clubs yet? <a href="../clubs/view_clubs.php">Join a club</a> or <a href="../clubs/create_club.php">create one</a>!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
<?php $conn->close(); ?>
