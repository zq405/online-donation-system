<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['user_id'];

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$animal_type = isset($_GET['animal_type']) ? mysqli_real_escape_string($conn, $_GET['animal_type']) : 'all';
$urgency = isset($_GET['urgency']) ? mysqli_real_escape_string($conn, $_GET['urgency']) : 'all';

$where = "Status = 'active' AND End_Date > CURDATE()";

if (!empty($search)) {
    $where .= " AND (Title LIKE '%$search%' OR Description LIKE '%$search%' OR Shelter_Name LIKE '%$search%')";
}

if ($animal_type != 'all') {
    $where .= " AND Animal_Type = '$animal_type'";
}

if ($urgency != 'all') {
    $where .= " AND Urgency_Level = '$urgency'";
}

$sql = "SELECT Campaign_ID, Title, Description, Goal_Amount, Raised_Amount, 
               Animal_Type, Animal_Count, Animal_Name, Animal_Age, 
               Shelter_Name, Shelter_Location, Urgency_Level, Created_At,
               ROUND((Raised_Amount / Goal_Amount) * 100, 2) AS Progress 
        FROM campaign 
        WHERE $where 
        ORDER BY Urgency_Level = 'urgent' DESC, Created_At DESC";

$campaigns = mysqli_query($conn, $sql);

$total_campaigns = mysqli_num_rows($campaigns);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Campaigns - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-bar input[type="text"]:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
        }
        
        .filter-bar select {
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 140px;
        }
        
        .filter-bar .btn-filter {
            padding: 10px 25px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: auto;
            margin: 0;
        }
        
        .filter-bar .btn-filter:hover {
            background: #0099cc;
            transform: translateY(-2px);
        }
        
        .filter-bar .btn-filter.reset {
            background: #e0e0e0;
            color: #333;
        }
        
        .filter-bar .btn-filter.reset:hover {
            background: #ccc;
        }
        
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .campaign-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
            border-left: 4px solid #00C3FF;
            position: relative;
        }
        
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 195, 255, 0.15);
        }
        
        .campaign-card .urgency-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .urgency-tag.low { background: #d4edda; color: #155724; }
        .urgency-tag.normal { background: #cce5ff; color: #004085; }
        .urgency-tag.high { background: #fff3cd; color: #856404; }
        .urgency-tag.urgent { background: #f8d7da; color: #721c24; }
        
        .campaign-card h3 {
            color: #333;
            margin-bottom: 8px;
            padding-right: 80px;
        }
        
        .campaign-card .description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        
        .campaign-card .meta {
            color: #555;
            font-size: 13px;
            margin-bottom: 4px;
        }
        
        .campaign-card .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            height: 8px;
            margin: 12px 0;
            overflow: hidden;
        }
        
        .campaign-card .progress-fill {
            background: #00C3FF;
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .campaign-card .amount-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #555;
        }
        
        .campaign-card .btn-donate {
            display: inline-block;
            padding: 10px 25px;
            background: #00C3FF;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        
        .campaign-card .btn-donate:hover {
            background: #0099cc;
            transform: scale(1.02);
        }
        
        .campaign-card .btn-view {
            display: inline-block;
            padding: 8px 20px;
            background: #e0e0e0;
            color: #333;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            margin-top: 10px;
            margin-right: 8px;
            transition: all 0.3s ease;
        }
        
        .campaign-card .btn-view:hover {
            background: #ccc;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
            grid-column: 1/-1;
        }
        
        .empty-state .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .result-count {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .campaign-grid {
                grid-template-columns: 1fr;
            }
            .filter-bar {
                flex-direction: column;
            }
            .filter-bar input,
            .filter-bar select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="donor_dashboard.php">My Dashboard</a>
        <a href="browse_campaigns.php" style="background: rgba(255,255,255,0.2);">Browse Campaigns</a>
        <a href="my_donations.php">My Donations</a>
        <a href="profile.php">Profile</a>
        <a href="how_it_works.php">How It Works</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Browse Rescue Campaigns</h2>
            <p style="color: #666; margin-bottom: 20px;">Find a campaign that touches your heart and make a donation today.</p>
            
            <div class="filter-bar">
                <form method="GET" action="browse_campaigns.php" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%; align-items: center;">
                    <input type="text" name="search" placeholder="Search campaigns..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="animal_type">
                        <option value="all" <?php echo $animal_type == 'all' ? 'selected' : ''; ?>>All Animals</option>
                        <option value="Dog" <?php echo $animal_type == 'Dog' ? 'selected' : ''; ?>>Dog</option>
                        <option value="Cat" <?php echo $animal_type == 'Cat' ? 'selected' : ''; ?>>Cat</option>
                        <option value="Rabbit" <?php echo $animal_type == 'Rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                        <option value="Bird" <?php echo $animal_type == 'Bird' ? 'selected' : ''; ?>>Bird</option>
                        <option value="Other" <?php echo $animal_type == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <select name="urgency">
                        <option value="all" <?php echo $urgency == 'all' ? 'selected' : ''; ?>>All Urgency</option>
                        <option value="low" <?php echo $urgency == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="normal" <?php echo $urgency == 'normal' ? 'selected' : ''; ?>>Normal</option>
                        <option value="high" <?php echo $urgency == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="urgent" <?php echo $urgency == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="browse_campaigns.php" class="btn-filter reset" style="padding: 10px 25px; background: #e0e0e0; color: #333; border-radius: 12px; text-decoration: none; font-weight: 600;">Reset</a>
                </form>
            </div>
            
            <div class="result-count">Showing <?php echo $total_campaigns; ?> active campaign(s)</div>
            
            <div class="campaign-grid">
                <?php if ($total_campaigns > 0): ?>
                    <?php while ($campaign = mysqli_fetch_assoc($campaigns)): 
                        $progress = min($campaign['Progress'], 100);
                        $animal_type = $campaign['Animal_Type'] ?? 'Animal';
                        $urgency_class = $campaign['Urgency_Level'] ?? 'normal';
                    ?>
                        <div class="campaign-card">
                            <span class="urgency-tag <?php echo $urgency_class; ?>"><?php echo ucfirst($campaign['Urgency_Level'] ?? 'Normal'); ?></span>
                            <h3><?php echo htmlspecialchars($campaign['Title']); ?></h3>
                            <div class="description"><?php echo htmlspecialchars(substr($campaign['Description'] ?? '', 0, 120)) . '...'; ?></div>
                            <div class="meta">Animal: <?php echo htmlspecialchars($animal_type); ?></div>
                            <div class="meta">Shelter: <?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'N/A'); ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                            <div class="amount-info">
                                <span>Raised: $<?php echo number_format($campaign['Raised_Amount'], 0); ?></span>
                                <span>Goal: $<?php echo number_format($campaign['Goal_Amount'], 0); ?></span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="campaign_detail.php?id=<?php echo $campaign['Campaign_ID']; ?>" class="btn-view">View Details</a>
                                <a href="campaign_detail.php?id=<?php echo $campaign['Campaign_ID']; ?>" class="btn-donate" style="flex: 1;">Donate Now</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">No campaigns found</div>
                        <h3>No Campaigns Available</h3>
                        <p>There are no active campaigns matching your criteria. Please check back later.</p>
                        <a href="browse_campaigns.php" style="color: #00C3FF; text-decoration: none; font-weight: 600;">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>