<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How to Play - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
            padding: 15px 50px;
            z-index: 10;
            background: rgba(29, 42, 33, 0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .navbar .logo img {
            height: 45px;
        }

        .navbar .nav-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
        }

        .navbar .nav-links a i {
            margin-right: 8px;
            font-size: 1.1em;
        }

        .navbar .nav-links a:hover {
            color: #2ECC71;
            transform: translateY(-3px);
        }

        .header {
            text-align: center;
            padding: 150px 20px 50px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('../../public/assets/Context-Images/Howtoplay.png') no-repeat center center;
            background-size: cover;
        }

        .header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 4rem;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(46, 204, 113, 0.6);
        }

        .content {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .step {
            background: rgba(46, 204, 113, 0.05);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(46, 204, 113, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .step:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .step h2 {
            font-family: 'Poppins', sans-serif;
            color: #2ECC71;
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            .navbar .nav-links {
                display: none;
            }
            .header {
                padding: 100px 20px 30px;
            }
            .header h1 {
                font-size: 3rem;
            }
            .step {
                padding: 20px;
            }
        }

    </style>
</head>
<body>



    <div class="header">
        <h1>How to Play</h1>
        <p>Your journey to protect the Earth starts here.</p>
    </div>

    <div class="content">
        <div class="step">
            <h2>1. Create Your Guardian Account</h2>
            <p>Start by signing up. A Guardian account is your key to accessing missions, tracking your progress, and connecting with a community of fellow protectors of the Earth.</p>
        </div>
        <div class="step">
            <h2>2. Browse and Accept Missions</h2>
            <p>Navigate to the Missions board to find a list of available eco-challenges. Each mission has a description, point value, and deadline. Choose a mission that resonates with you and accept it to begin.</p>
        </div>
        <div class="step">
            <h2>3. Complete the Mission & Upload Proof</h2>
            <p>Follow the mission instructions. Once you've completed the task, you'll need to upload proof, usually a photo or a short video. Our moderators will verify your submission.</p>
        </div>
        <div class="step">
            <h2>4. Earn Points & Climb the Ranks</h2>
            <p>For every verified mission, you'll earn Experience Points (XP). Accumulate XP to level up and earn badges. Check your standing on the national, district, and school leaderboards!</p>
        </div>
        <div class="step">
            <h2>5. Learn and Grow</h2>
            <p>Visit the Learning Hub to read articles and take quizzes on important environmental topics. Knowledge is your greatest weapon in the fight for our planet.</p>
        </div>
    </div>

</body>
</html>
