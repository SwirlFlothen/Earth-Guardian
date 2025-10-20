<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar">
            <ul>
                <li><a href="../../public/index.php">Home</a></li>
                <li><a href="../missions/missions.php">Missions</a></li>
                <li><a href="../leaderboard/leaderboard.php">Leaderboard</a></li>
                <li><a href="../clubs/view_clubs.php">Clubs</a></li>
                <li><a href="../forum/forum.php">Forum</a></li>
                <li><a href="../lessons/lessons.php">Learning Hub</a></li>
                <li><a href="../user/my_account.php">My Account</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    <?php else: ?>
        <nav class="navbar">
            <ul>
                <li><a href="../../public/index.php">Home</a></li>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign Up</a></li>
                <li><a href="about_us.php">About Us</a></li>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="about-container">
        <h1>About Earth Guardians</h1>
        <p>
            Earth Guardians is a revolutionary platform dedicated to empowering individuals to take meaningful action for our planet. 
            We believe that by gamifying environmental challenges, providing educational resources, and fostering a global community, 
            we can inspire and mobilize a new generation of environmental activists.
        </p>
        <p>
            Our mission is to make environmentalism engaging, accessible, and impactful. Through interactive missions, 
            a dynamic leaderboard, and collaborative eco-clubs, we transform complex environmental issues into achievable goals. 
            Join us in defending our planet, one action at a time.
        </p>


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