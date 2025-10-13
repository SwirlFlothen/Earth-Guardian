<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $sql_insert_message = "INSERT INTO global_chat (user_id, message) VALUES (?, ?)";
        $stmt_insert_message = $conn->prepare($sql_insert_message);
        $stmt_insert_message->bind_param("is", $user_id, $message);
        $stmt_insert_message->execute();
        $stmt_insert_message->close();
    }
    header('Location: global_chat.php');
    exit();
}

$sql_messages = "SELECT gc.message, u.username, gc.created_at FROM global_chat gc JOIN users u ON gc.user_id = u.id ORDER BY gc.created_at ASC";
$result_messages = $conn->query($sql_messages);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Chat - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #1D2A21;
            color: #fff;
        }
        .container { max-width: 800px; margin: 100px auto 40px; padding: 0 20px; }
        .chat-box { background: rgba(0,0,0,0.2); border-radius: 15px; padding: 20px; height: 400px; overflow-y: auto; margin-bottom: 20px; }
        .message { margin-bottom: 15px; }
        .message .username { font-weight: bold; color: #2ECC71; }
        .message .timestamp { font-size: 0.8em; color: #aaa; }
        .message-form { display: flex; }
        .message-form input { flex-grow: 1; background: rgba(0,0,0,0.3); border: 1px solid #2ECC71; color: #fff; padding: 10px; border-radius: 10px 0 0 10px; }
        .message-form button { background: #2ECC71; border: none; color: #fff; padding: 10px 20px; border-radius: 0 10px 10px 0; cursor: pointer; }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            z-index: 10;
            background: rgba(29, 42, 33, 0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .navbar .logo img {
            height: 45px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
            transition: color 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
            padding: 5px 0;
        }

        .nav-links li a:hover {
            color: #2ECC71;
            transform: translateY(-3px);
        }

        .burger {
            display: none;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 25px;
            z-index: 11;
        }

        .burger div {
            width: 25px;
            height: 3px;
            background-color: #fff;
            border-radius: 5px;
            transition: all 0.3s linear;
        }

        .nav-active {
            transform: translateX(0%) !important;
        }

        .toggle .line1 {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .toggle .line2 {
            opacity: 0;
        }

        .toggle .line3 {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Adjust for fixed navbar height */
            }
            .navbar {
                padding: 15px 20px;
            }
            .nav-links {
                position: absolute;
                right: 0;
                height: 92vh;
                top: 70px;
                background-color: rgba(29, 42, 33, 0.95);
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 50%;
                transform: translateX(100%);
                transition: transform 0.5s ease-in;
                padding-top: 20px;
            }
            .nav-links li {
                opacity: 0;
                margin: 20px 0;
            }
            .nav-links li a {
                font-size: 1.2em;
            }
            .burger {
                display: flex;
            }
            .container {
                margin-top: 100px;
            }
        }

        @keyframes navLinkFade {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0px);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
            <li><a href="../missions/missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
            <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
            <li><a href="forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
            <li><a href="global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>
            <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
            <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
            <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
        </ul>
        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>

    <div class="container">
        <h1>Global Chat</h1>
        <div class="chat-box">
            <?php if ($result_messages->num_rows > 0): ?>
                <?php while($msg = $result_messages->fetch_assoc()): ?>
                    <div class="message">
                        <span class="username"><?php echo htmlspecialchars($msg['username']); ?>:</span>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <span class="timestamp"><?php echo $msg['created_at']; ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages yet. Be the first to say something!</p>
            <?php endif; ?>
        </div>
        <form action="" method="post" class="message-form">
            <input type="text" name="message" placeholder="Type your message..." autocomplete="off">
            <button type="submit" name="send_message">Send</button>
        </form>
    </div>

    <script>
        const navSlide = () => {
            const burger = document.querySelector('.burger');
            const nav = document.querySelector('.nav-links');
            const navLinks = document.querySelectorAll('.nav-links li');

            burger.addEventListener('click', () => {
                // Toggle Nav
                nav.classList.toggle('nav-active');

                // Animate Links
                navLinks.forEach((link, index) => {
                    if (link.style.animation) {
                        link.style.animation = '';
                    } else {
                        link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.3}s`;
                    }
                });

                // Burger Animation
                burger.classList.toggle('toggle');
            });
        }

    </script>

    <script src="../../public/assets/js/main.js"></script>

</body>
</html>
