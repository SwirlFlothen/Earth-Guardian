<?php
session_start();
$is_new_signup = false;
if (isset($_SESSION['new_user_signup']) && $_SESSION['new_user_signup'] === true) {
    $is_new_signup = true;
    unset($_SESSION['new_user_signup']); // Unset the flag after checking
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Rules - Earth Guardians</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../../public/assets/css/main.css">
    <style>
        body, html {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #1D2A21;
            color: #fff;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            z-index: 10;
            background: rgba(29, 42, 33, 0.3);
            backdrop-filter: blur(10px);
        }

        .navbar .logo img {
            height: 40px;
        }

        .navbar .nav-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-weight: 600;
            transition: color 0.3s, text-shadow 0.3s;
        }

        .navbar .nav-links a:hover {
            color: #2ECC71;
            text-shadow: 0 0 10px #2ECC71;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding-top: 100px;
        }

        .content-box {
            background: rgba(46, 204, 113, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 800px;
            text-align: center;
            border: 1px solid rgba(46, 204, 113, 0.1);
        }

        .content-box h2 {
            font-size: 36px;
            margin-bottom: 30px;
            color: #2ECC71;
        }

        .rules-list {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.8;
        }

        .rules-list li {
            margin-bottom: 15px;
        }

        .btn {
            background: linear-gradient(45deg, #2ECC71, #27AE60);
            color: #fff;
            padding: 15px 60px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            cursor: pointer;
            border-radius: 25px;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #27AE60;
        }

    </style>
</head>
<body>


    <div class="container">
        <div class="content-box">
            <h2>Game Rules</h2>
            <ul class="rules-list">
                <li>- Cheating or using unauthorized tools will result in immediate suspension or ban.</li>
                <li>- All players must show respect and avoid offensive or harassing behavior.</li>
                <li>- Creating multiple accounts for any advantage is strictly prohibited.</li>
                <li>- Players must truthfully report all game-related activities and completions.</li>
                <li>- Manipulating or bypassing game mechanics for unfair advantage is forbidden.</li>
                <li>- Sharing account details or accessing others' accounts is not allowed.</li>
                <li>- Team members must contribute fairly, or the entire team may face penalties.</li>
            </ul>
            <?php if ($is_new_signup): ?>
                <p>Thank you for signing up! Please review the rules before proceeding.</p>
                <a href="../auth/login.php" class="btn">Proceed to Login</a>
            <?php else: ?>
                <a href="../dashboard/dashboard.php" class="btn">Agree</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>