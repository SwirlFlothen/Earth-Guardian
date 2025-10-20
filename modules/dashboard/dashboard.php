<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/api_config.php'; // Should define OPEN_WEATHER_API_KEY, NASA_API_KEY
require_once '../../includes/api_helpers.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql_user = "SELECT username, points FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Define level thresholds
$level_thresholds = [
    1 => 0,
    2 => 100,
    3 => 250,
    4 => 500,
    5 => 1000,
    // Add more levels as needed
];

// Calculate user's current level and progress
$current_level = 1;
$xp_for_current_level = 0;
$xp_for_next_level = 100; // Default for level 1 to 2

foreach ($level_thresholds as $level => $threshold) {
    if ($user['points'] >= $threshold) {
        $current_level = $level;
        $xp_for_current_level = $threshold;
    }
}

if (array_key_exists($current_level + 1, $level_thresholds)) {
    $xp_for_next_level = $level_thresholds[$current_level + 1];
} else {
    // User is at max level or beyond defined levels
    $xp_for_next_level = $user['points']; // No next level, progress is full
}

// Get mission counts and stats
$sql_mission_stats = "SELECT 
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as completed_missions,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_missions,
    SUM(CASE WHEN status = 'approved' THEN m.points ELSE 0 END) as total_points
FROM user_missions um
JOIN missions m ON um.mission_id = m.id
WHERE user_id = ?";

$stmt_stats = $conn->prepare($sql_mission_stats);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$mission_stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

$progress_to_next_level = $user['points'] - $xp_for_current_level;
$total_xp_for_level_up = $xp_for_next_level - $xp_for_current_level;
$progress_percentage = ($total_xp_for_level_up > 0) ? round(($progress_to_next_level / $total_xp_for_level_up) * 100) : 100;

// Fetch approved missions
$sql_approved_missions = "SELECT m.title, m.points, um.completed_at 
                 FROM user_missions um
                 JOIN missions m ON um.mission_id = m.id
                 WHERE um.user_id = ? AND um.status = 'approved'
                 ORDER BY um.completed_at DESC";
$stmt_approved_missions = $conn->prepare($sql_approved_missions);
$stmt_approved_missions->bind_param("i", $user_id);
$stmt_approved_missions->execute();
$result_approved_missions = $stmt_approved_missions->get_result();
$stmt_approved_missions->close();

// Fetch pending missions
$sql_pending_missions = "SELECT m.title, m.points, um.completed_at 
                 FROM user_missions um
                 JOIN missions m ON um.mission_id = m.id
                 WHERE um.user_id = ? AND um.status = 'pending'
                 ORDER BY um.completed_at DESC";
$stmt_pending_missions = $conn->prepare($sql_pending_missions);
$stmt_pending_missions->bind_param("i", $user_id);
$stmt_pending_missions->execute();
$result_pending_missions = $stmt_pending_missions->get_result();
$stmt_pending_missions->close();

// Fetch earned badges
$sql_badges = "SELECT b.name, b.description, b.image_url 
               FROM user_badges ub
               JOIN badges b ON ub.badge_id = b.id
               WHERE ub.user_id = ?";
$stmt_badges = $conn->prepare($sql_badges);
$stmt_badges->bind_param("i", $user_id);
$stmt_badges->execute();
$result_badges = $stmt_badges->get_result();

// API Keys from config
$openweathermap_api_key = defined('OPEN_WEATHER_API_KEY') ? OPEN_WEATHER_API_KEY : '';
$nasa_api_key = defined('NASA_API_KEY') ? NASA_API_KEY : '';

// Dhaka coordinates
$dhaka_lat = 23.8103;
$dhaka_lon = 90.4125;

// Chattogram coordinates
$chattogram_lat = 22.3569;
$chattogram_lon = 91.7863;

$aqi_data = [];
$weather_data = [];

// Helper to fetch external URLs safely using cURL with timeouts and error handling
function fetch_url($url, &$error = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8); // total timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    // optional: set a sensible user agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'EarthGuardian/1.0 (+https://example.com)');

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code >= 400) {
        $error = 'HTTP error: ' . $http_code;
        return false;
    }
    return $response;
}

if ($openweathermap_api_key) {
    // Fetch AQI for Dhaka
    $aqi_url_dhaka = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$dhaka_lat}&lon={$dhaka_lon}&appid={$openweathermap_api_key}";
    $aqi_error = null;
    $aqi_json_dhaka = fetch_url($aqi_url_dhaka, $aqi_error);
    if ($aqi_json_dhaka !== false) {
        $decoded = json_decode($aqi_json_dhaka, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $aqi_data['Dhaka'] = $decoded;
        } else {
            $aqi_data['Dhaka_error'] = 'Failed to decode AQI JSON for Dhaka: ' . json_last_error_msg();
        }
    } else {
        $aqi_data['Dhaka_error'] = $aqi_error ?: 'Unknown error fetching AQI for Dhaka';
    }

    // Fetch AQI for Chattogram
    $aqi_url_chattogram = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$chattogram_lat}&lon={$chattogram_lon}&appid={$openweathermap_api_key}";
    $aqi_error = null;
    $aqi_json_chattogram = fetch_url($aqi_url_chattogram, $aqi_error);
    if ($aqi_json_chattogram !== false) {
        $decoded = json_decode($aqi_json_chattogram, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $aqi_data['Chattogram'] = $decoded;
        } else {
            $aqi_data['Chattogram_error'] = 'Failed to decode AQI JSON for Chattogram: ' . json_last_error_msg();
        }
    } else {
        $aqi_data['Chattogram_error'] = $aqi_error ?: 'Unknown error fetching AQI for Chattogram';
    }

    // Fetch Weather for Dhaka
    $weather_url_dhaka = "http://api.openweathermap.org/data/2.5/weather?lat={$dhaka_lat}&lon={$dhaka_lon}&appid={$openweathermap_api_key}&units=metric";
    $weather_error = null;
    $weather_json_dhaka = fetch_url($weather_url_dhaka, $weather_error);
    if ($weather_json_dhaka !== false) {
        $decoded = json_decode($weather_json_dhaka, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $weather_data['Dhaka'] = $decoded;
        } else {
            $weather_data['Dhaka_error'] = 'Failed to decode weather JSON for Dhaka: ' . json_last_error_msg();
        }
    } else {
        $weather_data['Dhaka_error'] = $weather_error ?: 'Unknown error fetching weather for Dhaka';
    }

    // Fetch Weather for Chattogram
    $weather_url_chattogram = "http://api.openweathermap.org/data/2.5/weather?lat={$chattogram_lat}&lon={$chattogram_lon}&appid={$openweathermap_api_key}&units=metric";
    $weather_error = null;
    $weather_json_chattogram = fetch_url($weather_url_chattogram, $weather_error);
    if ($weather_json_chattogram !== false) {
        $decoded = json_decode($weather_json_chattogram, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $weather_data['Chattogram'] = $decoded;
        } else {
            $weather_data['Chattogram_error'] = 'Failed to decode weather JSON for Chattogram: ' . json_last_error_msg();
        }
    } else {
        $weather_data['Chattogram_error'] = $weather_error ?: 'Unknown error fetching weather for Chattogram';
    }
}

// NASA EPIC API Integration
$epic_data = [];
$epic_image_url = '';
$epic_error_message = '';

if ($nasa_api_key) {
    $epic_data_url = 'https://epic.gsfc.nasa.gov/api/natural';
    $epic_error = null;
    $epic_json = fetch_url($epic_data_url, $epic_error);

    if ($epic_json === false) {
        $epic_error_message = "cURL Error: " . $epic_error;
    } else {
        $epic_data = json_decode($epic_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $epic_error_message = "Failed to decode JSON from NASA EPIC API: " . json_last_error_msg();
        } elseif (empty($epic_data) || !isset($epic_data[0]['date']) || !isset($epic_data[0]['image'])) {
            $epic_error_message = "No image data or incomplete data returned from NASA EPIC API. The API key might be invalid or there are no images available for today.";
        } else {
            $latest_image = $epic_data[0];
            $date = new DateTime($latest_image['date']);
            $image_date_path = $date->format('Y/m/d');
            $image_name = $latest_image['image'] . '.png';
            $epic_image_url = "https://epic.gsfc.nasa.gov/archive/natural/{$image_date_path}/png/{$image_name}?api_key={$nasa_api_key}";
        }
    }
} else {
    $epic_error_message = "NASA API key not configured.";
}

// NASA POWER API Integration (Earth Climate Data)
$power_data = [];
$power_error_message = '';

// Using Dhaka coordinates for consistency
$power_lat = $dhaka_lat;
$power_lon = $dhaka_lon;
$power_start_date = date('Ymd', strtotime('-365 days')); // 365 days ago
$power_end_date = date('Ymd', strtotime('-7 days')); // 7 days ago
$power_parameters = 'T2M_MAX,PRECTOTCORR'; // Max Temperature at 2 meters, Total Precipitation

$power_api_url = "https://power.larc.nasa.gov/api/temporal/daily/point?parameters={$power_parameters}&community=AG&longitude={$power_lon}&latitude={$power_lat}&start={$power_start_date}&end={$power_end_date}&format=JSON";


// Fetch NASA POWER data using cURL helper
$power_error = null;
$power_json = fetch_url($power_api_url, $power_error);
if ($power_json === false) {
    $power_error_message = "Failed to fetch data from NASA POWER API: " . ($power_error ?: 'unknown');
} else {
    $power_data = json_decode($power_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $power_error_message = "Failed to decode JSON from NASA POWER API: " . json_last_error_msg();
    } elseif (empty($power_data) || !isset($power_data['properties']['parameter']['T2M_MAX']) || !isset($power_data['properties']['parameter']['PRECTOTCORR'])) {
        $power_error_message = "No climate data or incomplete data returned from NASA POWER API.";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Earth Guardian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/dashboard.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <li><a href="../user/my_account.php">My Account</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
        <p>This is your command center, Guardian.</p>
    </div>

    <div class="dashboard-container">
        <div class="profile-summary">
            <h2><i class="fas fa-chart-line"></i> Your Stats</h2>
            <div class="stat-cards">
                <div class="card stat-card">
                    <div class="label">Total Experience Points</div>
                    <div class="value"><i class="fas fa-star"></i> <?php echo $user['points']; ?> XP</div>
                </div>
                <div class="card stat-card">
                    <div class="label">Current Level</div>
                    <div class="value"><i class="fas fa-trophy"></i> <?php echo $current_level; ?></div>
                </div>
                <div class="card stat-card xp-chart-card">
                    <div class="label">Progress to Next Level</div>
                    <canvas id="xpChart" width="140" height="140"></canvas>
                    <div class="level-progress" style="width:100%;">
                        <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
                    </div>
                </div>
            </div>
            
            <div class="earned-badges card">
                <h2><i class="fas fa-medal"></i> Earned Badges</h2>
                <div class="badges-grid">
                    <?php if ($result_badges->num_rows > 0): ?>
                        <?php while($badge = $result_badges->fetch_assoc()): ?>
                            <div class="badge-item">
                            <i class="fas fa-award"></i>
                                <p><?php echo htmlspecialchars($badge['name']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No badges earned yet. Keep completing missions!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="completed-missions card">
            <h2><i class="fas fa-flag-checkered"></i> Approved Missions</h2>
            <div class="mission-list">
                <?php if ($result_approved_missions->num_rows > 0): ?>
                    <?php while($mission = $result_approved_missions->fetch_assoc()): ?>
                        <div class="mission-list-item">
                            <strong><?php echo htmlspecialchars($mission['title']); ?></strong> (+<?php echo $mission['points']; ?> XP)
                            <span style="float:right; color: #aaa;"><?php echo date('M d, Y', strtotime($mission['completed_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No approved missions yet.</p>
                <?php endif; ?>
            </div>

            <h2><i class="fas fa-hourglass-half"></i> Pending Missions</h2>
            <div class="mission-list">
                <?php if ($result_pending_missions->num_rows > 0): ?>
                    <?php while($mission = $result_pending_missions->fetch_assoc()): ?>
                        <div class="mission-list-item">
                            <strong><?php echo htmlspecialchars($mission['title']); ?></strong> (<?php echo $mission['points']; ?> XP pending)
                            <span style="float:right; color: #aaa;"><?php echo date('M d, Y', strtotime($mission['completed_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No pending missions.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="aqi-data card">
            <h2>Air Quality Index (AQI)</h2>
            <canvas id="aqiChart"></canvas>
        </div>

        <div class="weather-data card">
            <h2>Local Weather</h2>
            <canvas id="weatherChart"></canvas>
        </div>

        <div class="nasa-epic-data card">
            <h2>Earth from Space (NASA EPIC)</h2>
            <?php if (!empty($epic_image_url)): ?>
                <img src="<?php echo htmlspecialchars($epic_image_url); ?>" alt="Earth from Space">
                <p>Latest image from NASA's EPIC camera.</p>
            <?php elseif (!empty($epic_error_message)): ?>
                <p><?php echo $epic_error_message; ?></p>
            <?php else: ?>
                <p>Unable to fetch NASA EPIC image. Please check your API key and internet connection.</p>
            <?php endif; ?>
        </div>

        <div class="climate-charts card">
            <h2>Yearly Climate Data</h2>
            <canvas id="temperatureChart"></canvas>
            <canvas id="precipitationChart"></canvas>
        </div>


    </div>


    <script src="../../public/assets/js/main.js"></script>

    <?php if (!empty($aqi_data)): ?>
    <script>
        const aqiData = <?php echo json_encode($aqi_data); ?>;
        const aqiLabels = Object.keys(aqiData).filter(key => !key.endsWith('_error'));
        if (aqiLabels.length > 0) {
            const aqiValues = aqiLabels.map(city => aqiData[city].list[0].main.aqi);

            const aqiCtx = document.getElementById('aqiChart').getContext('2d');
            new Chart(aqiCtx, {
                type: 'bar',
                data: {
                    labels: aqiLabels,
                    datasets: [{
                        label: 'AQI',
                        data: aqiValues,
                        backgroundColor: ['#5DADE2', '#AF7AC5'],
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>

    <?php if (!empty($weather_data)): ?>
    <script>
        const weatherData = <?php echo json_encode($weather_data); ?>;
        const weatherLabels = Object.keys(weatherData).filter(key => !key.endsWith('_error'));
        if (weatherLabels.length > 0) {
            const weatherTemps = weatherLabels.map(city => weatherData[city].main.temp);
            const weatherDescriptions = weatherLabels.map(city => weatherData[city].weather[0].description);

            const weatherCtx = document.getElementById('weatherChart').getContext('2d');
            new Chart(weatherCtx, {
                type: 'bar',
                data: {
                    labels: weatherLabels,
                    datasets: [{
                        label: 'Temperature (°C)',
                        data: weatherTemps,
                        backgroundColor: ['#FFCE56', '#FF6384'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y + '°C, ' + weatherDescriptions[context.dataIndex];
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>

    <?php if (!empty($power_data) && empty($power_error_message)): ?>
    <script>
        const powerData = <?php echo json_encode($power_data); ?>;
        const labels = Object.keys(powerData.properties.parameter.T2M_MAX).map(date => {
            const year = date.substring(0, 4);
            const month = date.substring(4, 6);
            const day = date.substring(6, 8);
            return `${year}-${month}-${day}`;
        });

        // Temperature Chart
        const tempData = Object.values(powerData.properties.parameter.T2M_MAX);
        const tempCtx = document.getElementById('temperatureChart').getContext('2d');
        new Chart(tempCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Max Temperature (°C)',
                    data: tempData,
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        }
                    }
                }
            }
        });

        // Precipitation Chart
        const precipData = Object.values(powerData.properties.parameter.PRECTOTCORR);
        const precipCtx = document.getElementById('precipitationChart').getContext('2d');
        new Chart(precipCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Precipitation (mm)',
                    data: precipData,
                    backgroundColor: '#36A2EB',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Precipitation (mm)'
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>
<script>
    // Animate level progress bar
    document.addEventListener('DOMContentLoaded', function(){
        const bar = document.querySelector('.progress-bar');
        if(bar){
            // Width is set inline already by PHP; ensure transition from 0
            requestAnimationFrame(()=>{ bar.style.width = bar.getAttribute('style').replace(/width:\s*/,''); });
        }

        // Placeholder: initialize charts if canvas exists
        if (typeof Chart !== 'undefined') {
            const xpCanvas = document.getElementById('xpChart');
            if (xpCanvas) {
                // Example small chart (data can be replaced by server-side dataset)
                new Chart(xpCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Current', 'To next level'],
                        datasets: [{ data: [<?php echo max(0, $progress_percentage); ?>, <?php echo max(0, 100 - $progress_percentage); ?>], backgroundColor: ['#2ECC71', '#123123'] }]
                    },
                    options: { plugins: { legend: { position: 'bottom', labels: { color: '#fff' } } } }
                });
            }
        }
    });
</script>