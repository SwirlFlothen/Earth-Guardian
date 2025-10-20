<?php
session_start();
require_once '../../includes/db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modules/auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get missions with user completion status
$sql = "SELECT m.*, 
    CASE 
        WHEN um.status = 'approved' THEN 'completed'
        WHEN um.status = 'pending' THEN 'pending'
        ELSE 'available'
    END as mission_status
FROM missions m
LEFT JOIN user_missions um ON m.id = um.mission_id AND um.user_id = ?
ORDER BY m.id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Get mission statistics
$sql_stats = "SELECT
    COUNT(DISTINCT m.id) as total_missions,
    COUNT(CASE WHEN um.status = 'approved' THEN 1 END) as completed_missions,
    COUNT(CASE WHEN um.status = 'pending' THEN 1 END) as pending_missions,
    SUM(CASE WHEN um.status = 'approved' THEN m.points ELSE 0 END) as total_points_earned
FROM missions m
LEFT JOIN user_missions um ON m.id = um.mission_id AND um.user_id = ?";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$mission_stats = $stmt_stats->get_result()->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions - Earth Guardians</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <link rel="stylesheet" href="../../public/assets/css/missions.css">
    <link rel="stylesheet" href="../../public/assets/css/navbar.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="missions-container">
        <div class="header">
            <h1>Environmental Missions</h1>
            <p>Take action, complete missions, and make a real impact on our planet.</p>
        </div>

        <div class="mission-progress">
            <div class="progress-header">
                <h2 class="progress-title">Your Mission Progress</h2>
                <div class="progress-stats">
                    <div class="progress-stat">
                        <p class="stat-number"><?php echo $mission_stats['completed_missions']; ?></p>
                        <p class="stat-label">Completed</p>
                    </div>
                    <div class="progress-stat">
                        <p class="stat-number"><?php echo $mission_stats['pending_missions']; ?></p>
                        <p class="stat-label">Pending</p>
                    </div>
                    <div class="progress-stat">
                        <p class="stat-number"><?php echo number_format($mission_stats['total_points_earned']); ?></p>
                        <p class="stat-label">Points Earned</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mission-filters">
            <button class="filter-btn active" data-filter="all">All Missions</button>
            <button class="filter-btn" data-filter="available">Available</button>
            <button class="filter-btn" data-filter="completed">Completed</button>
            <button class="filter-btn" data-filter="pending">Pending Review</button>
        </div>

        <div class="missions-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($mission = $result->fetch_assoc()): ?>
                    <div class="mission-card" data-status="<?php echo $mission['mission_status']; ?>">
                        <div class="mission-content">
                            <h3 class="mission-title"><?php echo htmlspecialchars($mission['title']); ?></h3>
                            <p class="mission-description"><?php echo htmlspecialchars($mission['description']); ?></p>
                            
                            <div class="mission-footer">
                                <div class="mission-points">
                                    <i class="fas fa-star"></i>
                                    <?php echo number_format($mission['points']); ?> XP
                                </div>
                                <div class="mission-status">
                                    <span class="status-icon status-<?php echo $mission['mission_status']; ?>"></span>
                                    <?php echo ucfirst($mission['mission_status']); ?>
                                </div>
                            </div>

                            <?php if ($mission['mission_status'] === 'available'): ?>
                                <a href="complete_mission.php?id=<?php echo $mission['id']; ?>" class="btn-start-mission">
                                    <i class="fas fa-flag"></i>
                                    Start Mission
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-missions">No missions available at the moment. Check back soon!</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../public/assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const missionCards = document.querySelectorAll('.mission-card');
            
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    const filter = btn.dataset.filter;
                    
                    // Filter mission cards
                    missionCards.forEach(card => {
                        const status = card.dataset.status;
                        if (filter === 'all' || status === filter) {
                            card.style.display = '';
                            setTimeout(() => card.style.opacity = '1', 10);
                        } else {
                            card.style.opacity = '0';
                            setTimeout(() => card.style.display = 'none', 300);
                        }
                    });
                });
            });
            
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            missionCards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>