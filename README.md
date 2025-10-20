# Earth Guardians

Earth Guardians is a web application designed to engage and educate users on environmental issues through gamified missions, learning resources, and community interaction. Users can track their progress, earn badges, join eco-clubs, and participate in a forum.

## Features

*   **User Authentication:** Secure login and signup processes.
*   **Dashboard:** Personalized dashboard displaying user stats (XP, level), earned badges, and completed missions. Integrates with external APIs for real-time AQI, weather, and NASA Earth imagery/climate data.
*   **Missions:** A variety of environmental missions for users to accept and complete, earning experience points.
*   **Leaderboard:** Tracks and displays top-performing users based on accumulated experience points.
*   **Forum:** A community forum for users to discuss environmental topics, share ideas, and interact with other guardians. Supports filtering posts by eco-club.
*   **Learning Hub:** Provides educational lessons and quizzes on various environmental subjects.
*   **Eco Clubs:** Users can create and join eco-clubs to collaborate on missions and discussions.
*   **User Account Management:** Users can view and update their profile information.
*   **Responsive Navigation:** Modern, responsive navigation bar with a hamburger menu for mobile devices, implemented across key user-facing pages like mission completion and quiz taking.
*   **About Us Page:** Information about the Earth Guardians project and its mission.

## Technologies Used

*   **Backend:** PHP
*   **Database:** MySQL (schema and seed data provided)
*   **Frontend:** HTML, CSS, JavaScript
*   **External APIs:**
    *   OpenWeatherMap API (for AQI and Weather data)
    *   NASA EPIC API (for Earth imagery)
    *   NASA POWER API (for climate data)
*   **Other:** Font Awesome (for icons)

## Setup Instructions

To set up the Earth Guardians project locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/SwirlFlothen/Earth-Guardian.git
    cd EG
    ```

2.  **Database Setup:**
    *   Ensure you have MySQL installed and running (e.g., via XAMPP, WAMP, MAMP).
    *   Create a new database (e.g., `earth_guardians`).
    *   Import the `database/schema.sql` file into your new database to create the necessary tables.
    *   Optionally, import `database/seeds.sql` to populate the database with sample data.

3.  **Configure Database Connection:**
    *   Open `includes/db_connect.php`.
    *   Update the database connection details (hostname, username, password, database name) to match your local MySQL setup.

4.  **API Keys:**
    *   **OpenWeatherMap API:**
        *   Sign up for a free API key at [OpenWeatherMap](https://openweathermap.org/api).
        *   Update the `$openweathermap_api_key` variable in `modules/dashboard/dashboard.php` with your key.
    *   **NASA API:**
        *   Obtain a NASA API key from [NASA API Portal](https://api.nasa.gov/).
        *   Update the `$nasa_api_key` variable in `modules/dashboard/dashboard.php` with your key.

5.  **Web Server Setup:**
    *   Place the project folder (`EG`) in your web server's document root (e.g., `htdocs` for XAMPP).
    *   Ensure your web server (Apache) is running.

6.  **Access the Application:**
    *   Open your web browser and navigate to `http://localhost/EG/public/index.php` (or the appropriate URL based on your web server configuration).

## Project Structure

```
.
├── README.md
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── manage_forum.php
│   ├── manage_lessons.php
│   ├── manage_missions_events.php
│   ├── manage_quizzes.php
│   └── verify_missions.php
├── database/
│   ├── schema.sql
│   ├── seeds.sql
│   └── migrations/
│       └── add_lat_lon_to_eco_clubs.sql
├── includes/
│   ├── config.php
│   └── db_connect.php
├── modules/
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── signup.php
│   ├── clubs/
│   │   ├── create_club.php
│   │   └── view_clubs.php
│   ├── dashboard/
│   │   └── dashboard.php
│   ├── forum/
│   │   ├── create_post.php
│   │   └── forum.php
│   ├── leaderboard/
│   │   └── leaderboard.php
│   ├── lessons/
│   │   ├── lessons.php
│   │   └── view_lesson.php
│   ├── missions/
│   │   ├── complete_mission.php
│   │   ├── missions.php
│   │   └── take_quiz.php
│   ├── static/
│   │   ├── about_us.php
│   │   ├── howtoplay.php
│   │   └── rules.php
│   └── user/
│       └── my_account.php
└── public/
    ├── index.php
    └── assets/
        ├── Images/
        │   ├── Dashboard-bg.jpg
        │   ├── Landing-bg.jpg
        │   ├── Login-vector.png
        │   ├── Logo.png
        │   ├── Sales_Representative_Presentation.pptx
        │   └── Signup-vector.png
        └── uploads/
            ├── proof_68e40c89b3e9c8.78636435.jpg
            └── wallpaper_1920x1080_light.png
```
