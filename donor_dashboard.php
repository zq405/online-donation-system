<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_role'] != 'donor') {
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$donor_id = $_SESSION['user_id'];
$donor_name = $_SESSION['user_name'];

$stats_sql = "SELECT Points, Badge FROM donors WHERE Donors_ID = $donor_id";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$donation_count_sql = "SELECT COUNT(*) as count, SUM(Amount) as total FROM donations WHERE Donors_ID = $donor_id AND Status = 'completed'";
$donation_count_result = mysqli_query($conn, $donation_count_sql);
$donation_stats = mysqli_fetch_assoc($donation_count_result);

$total_donations = $donation_stats['count'] ?? 0;
$total_amount = $donation_stats['total'] ?? 0;
$points = $stats['Points'] ?? 0;
$badge = $stats['Badge'] ?? 'None';

$active_campaigns_sql = "SELECT Campaign_ID, Title, Description, Goal_Amount, Raised_Amount, 
                                Animal_Type, Animal_Name, Shelter_Name, Urgency_Level,
                                ROUND((Raised_Amount / Goal_Amount) * 100, 2) AS Progress 
                         FROM campaign 
                         WHERE Status = 'active' AND End_Date > CURDATE() 
                         ORDER BY Created_At DESC LIMIT 3";
$active_campaigns = mysqli_query($conn, $active_campaigns_sql);

$recent_donations_sql = "SELECT d.Donation_ID, d.Amount, d.Status, d.Created_At, c.Title AS Campaign_Title
                          FROM donations d
                          LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
                          WHERE d.Donors_ID = $donor_id
                          ORDER BY d.Created_At DESC LIMIT 5";
$recent_donations = mysqli_query($conn, $recent_donations_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-bottom: 3px solid #00C3FF;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .stat-card .badge-display {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .badge-gold {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-silver {
            background: #e5e7eb;
            color: #374151;
        }
        
        .badge-bronze {
            background: #fde68a;
            color: #78350f;
        }
        
        .badge-none {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .campaign-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
            border-left: 4px solid #00C3FF;
        }
        
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 195, 255, 0.15);
        }
        
        .campaign-card h3 {
            color: #333;
            margin-bottom: 8px;
        }
        
        .campaign-card .meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
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
            padding: 8px 20px;
            background: #00C3FF;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: 12px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .campaign-card .btn-donate:hover {
            background: #0099cc;
            transform: scale(1.05);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .donation-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .donation-table th {
            background: #00C3FF;
            color: white;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .donation-table td {
            padding: 10px 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .donation-table tr:hover td {
            background: #f8f9fa;
        }
        
        .badge-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-status.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-status.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 10px 25px;
            background: #e0e0e0;
            color: #333;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #ccc;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }
        
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .campaign-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-stats {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="nav" style="position:center;">
        <a href="donor_dashboard.php" style="background: rgba(255,255,255,0.2);">My Dashboard</a>
        <a href="browse_campaign.php">Browse Campaigns</a>
        <a href="my_donations.php">My Donations</a>
        <a href="profile.php">Profile</a>
        <a href="how_it_works.php">How It Works</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="welcome" style="background: linear-gradient(135deg, #00C3FF 0%, #0099cc 100%);">
            <h1>Welcome back, <?php echo htmlspecialchars($donor_name); ?>!</h1>
            <p>Thank you for supporting animal welfare. Your kindness makes a difference.</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="number"><?php echo $total_donations; ?></div>
                <div class="label">Total Donations Made</div>
            </div>
            <div class="stat-card">
                <div class="number">$<?php echo number_format($total_amount, 0); ?></div>
                <div class="label">Total Amount Donated</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $points; ?></div>
                <div class="label">Points Earned</div>
            </div>
            <div class="stat-card">
                <div class="label">Current Badge</div>
                <?php
                $badge_class = 'badge-none';
                if ($badge == 'Gold') $badge_class = 'badge-gold';
                elseif ($badge == 'Silver') $badge_class = 'badge-silver';
                elseif ($badge == 'Bronze') $badge_class = 'badge-bronze';
                ?>
                <div class="badge-display <?php echo $badge_class; ?>"><?php echo $badge; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2>Active Rescue Campaigns</h2>
            <p style="color: #666; margin-bottom: 20px;">Your donation can save a life. Browse the campaigns below.</p>
            
            <div class="campaign-grid">
                <?php if (mysqli_num_rows($active_campaigns) > 0): ?>
                    <?php while ($campaign = mysqli_fetch_assoc($active_campaigns)): 
                        $progress = min($campaign['Progress'], 100);
                        $animal_type = $campaign['Animal_Type'] ?? 'Animal';
                    ?>
                        <div class="campaign-card">
                            <h3><?php echo htmlspecialchars($campaign['Title']); ?></h3>
                            <div class="meta">Animal: <?php echo htmlspecialchars($animal_type); ?></div>
                            <div class="meta">Shelter: <?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'N/A'); ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                            <div class="amount-info">
                                <span>Raised: $<?php echo number_format($campaign['Raised_Amount'], 0); ?></span>
                                <span>Goal: $<?php echo number_format($campaign['Goal_Amount'], 0); ?></span>
                            </div>
                            <a href="campaign_detail.php?id=<?php echo $campaign['Campaign_ID']; ?>" class="btn-donate">Donate Now</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 30px; color: #888;">
                        <p>No active campaigns at the moment. Please check back soon.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="browse_campaigns.php" class="btn-secondary">View All Campaigns</a>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Donations</h2>
            
            <div class="table-container">
                <table class="donation-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_donations) > 0): ?>
                            <?php while ($donation = mysqli_fetch_assoc($recent_donations)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donation['Campaign_Title'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format($donation['Amount'], 2); ?></td>
                                    <td><span class="badge-status <?php echo $donation['Status']; ?>"><?php echo ucfirst($donation['Status']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($donation['Created_At'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: #888;">
                                    You haven't made any donations yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px; text-align: center;">
                <a href="my_donations.php" class="btn-secondary">View All Donations</a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>