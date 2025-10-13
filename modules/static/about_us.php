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
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: white;
            background: url('../../public/assets/Images/Dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 80px; /* Adjust for fixed navbar */
            overflow-x: hidden; /* Prevent horizontal scroll when menu is open */
        }
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

        .nav-links li a i {
            margin-right: 8px;
            font-size: 1.1em;
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

        .about-container {
            background: rgba(29, 42, 33, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin: 40px;
            max-width: 900px;
            text-align: center;
            border: 1px solid rgba(46, 204, 113, 0.2);
            box-shadow: 0 0 30px rgba(46, 204, 113, 0.3);
        }
        .about-container h1 {
            color: #2ECC71;
            font-size: 3em;
            margin-bottom: 20px;
        }
        .about-container p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: left;
        }
        .about-container .team-section {
            margin-top: 30px;
            border-top: 1px solid rgba(46, 204, 113, 0.2);
            padding-top: 30px;
        }
        .about-container .team-section h2 {
            color: #2ECC71;
            margin-bottom: 20px;
        }
        .about-container .team-members {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
        }
        .about-container .member-card {
            background: rgba(46, 204, 113, 0.1);
            padding: 20px;
            border-radius: 15px;
            width: 200px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .about-container .member-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.4);
        }
        .about-container .member-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #2ECC71;
        }
        .about-container .member-card h3 {
            color: #2ECC71;
            margin-bottom: 5px;
        }
        .about-container .member-card p {
            font-size: 0.9em;
            color: #bbb;
            text-align: center;
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
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar">
            <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
            <ul class="nav-links">
                <li><a href="../dashboard/dashboard.php" title="Dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a></li>
                <li><a href="../missions/missions.php" title="Missions" aria-label="Missions"><i class="fas fa-flag"></i><span class="link-text">Missions</span></a></li>
                <li><a href="../leaderboard/leaderboard.php" title="Leaderboard" aria-label="Leaderboard"><i class="fas fa-trophy"></i><span class="link-text">Leaderboard</span></a></li>
                            <li><a href="../forum/forum.php" title="Forum" aria-label="Forum"><i class="fas fa-comments"></i><span class="link-text">Forum</span></a></li>
                            <li><a href="../forum/global_chat.php" title="Global Chat" aria-label="Global Chat"><i class="fas fa-globe"></i><span class="link-text">Global Chat</span></a></li>
                            <li><a href="../lessons/lessons.php" title="Learning Hub" aria-label="Learning Hub"><i class="fas fa-book"></i><span class="link-text">Learning Hub</span></a></li>                <li><a href="../clubs/view_clubs.php" title="Eco Clubs" aria-label="Eco Clubs"><i class="fas fa-leaf"></i><span class="link-text">Eco Clubs</span></a></li>
                <li><a href="../user/my_account.php" title="My Account" aria-label="My Account"><i class="fas fa-user-circle"></i><span class="link-text">My Account</span></a></li>
                <li><a href="about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
                <li><a href="../auth/logout.php" title="Logout" aria-label="Logout"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
            </ul>
            <div class="burger">
                <div class="line1"></div>
                <div class="line2"></div>
                <div class="line3"></div>
            </div>
        </nav>
    <?php else: ?>
        <nav class="navbar">
            <a href="../../public/index.php" class="logo"><img src="../../public/assets/Images/Logo.png" alt="Earth Guardians Logo"></a>
            <ul class="nav-links">
                <li><a href="../auth/login.php" title="Login" aria-label="Login"><i class="fas fa-sign-in-alt"></i><span class="link-text">Login</span></a></li>
                <li><a href="../auth/signup.php" title="Sign Up" aria-label="Sign Up"><i class="fas fa-user-plus"></i><span class="link-text">Sign Up</span></a></li>
                <li><a href="about_us.php" title="About Us" aria-label="About Us"><i class="fas fa-info-circle"></i><span class="link-text">About Us</span></a></li>
            </ul>
            <div class="burger">
                <div class="line1"></div>
                <div class="line2"></div>
                <div class="line3"></div>
            </div>
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