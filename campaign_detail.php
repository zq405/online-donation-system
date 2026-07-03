<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: browse_campaigns.php");
    exit();
}

$campaign_id = intval($_GET['id']);

$sql = "SELECT Campaign_ID, Admin_ID, Title, Description, Goal_Amount, Raised_Amount,
               Start_Date, End_Date, Status, Animal_Type, Animal_Count, Animal_Name,
               Animal_Age, Animal_Image, Shelter_Name, Shelter_Location,
               Shelter_Phone, Medical_Need, Urgency_Level, Created_At,
               ROUND((Raised_Amount / Goal_Amount) * 100, 2) AS Progress
        FROM campaign WHERE Campaign_ID = $campaign_id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Campaign not found.";
    header("Location: browse_campaigns.php");
    exit();
}

$campaign = mysqli_fetch_assoc($result);

$is_active = ($campaign['Status'] == 'active' && strtotime($campaign['End_Date']) > time());
$donor_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campaign['Title']); ?> - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .campaign-header {
            background: linear-gradient(135deg, #00C3FF 0%, #0099cc 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
        }
        
        .campaign-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .campaign-header .meta {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .campaign-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .detail-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .detail-section h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .detail-section p {
            color: #555;
            line-height: 1.8;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .detail-item .label {
            color: #666;
        }
        
        .detail-item .value {
            color: #333;
            font-weight: 600;
        }
        
        .donation-box {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 20px;
        }
        
        .donation-box .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 15px 0;
        }
        
        .donation-box .progress-fill {
            background: #00C3FF;
            height: 100%;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            font-weight: 600;
        }
        
        .donation-box .amount-display {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 10px 0;
        }
        
        .donation-box .btn-donate {
            display: block;
            width: 100%;
            padding: 14px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
        }
        
        .donation-box .btn-donate:hover {
            background: #0099cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 195, 255, 0.4);
        }
        
        .donation-box .btn-donate:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .urgency-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .urgency-badge.low { background: #d4edda; color: #155724; }
        .urgency-badge.normal { background: #cce5ff; color: #004085; }
        .urgency-badge.high { background: #fff3cd; color: #856404; }
        .urgency-badge.urgent { background: #f8d7da; color: #721c24; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-badge.active { background: #d4edda; color: #155724; }
        .status-badge.completed { background: #e8d5f5; color: #6b21a8; }
        .status-badge.pending { background: #fff3cd; color: #856404; }
        
        @media (max-width: 768px) {
            .campaign-details {
                grid-template-columns: 1fr;
            }
            .campaign-header h1 {
                font-size: 24px;
            }
            .donation-box {
                position: static;
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
        <div class="campaign-header">
            <h1><?php echo htmlspecialchars($campaign['Title']); ?></h1>
            <div class="meta">
                <?php echo htmlspecialchars($campaign['Animal_Type'] ?? 'Animal'); ?> Rescue 
                | Shelter: <?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'N/A'); ?>
                | Status: <span class="status-badge <?php echo $campaign['Status']; ?>"><?php echo ucfirst($campaign['Status']); ?></span>
            </div>
        </div>
        
        <div class="campaign-details">
            <div>
                <div class="detail-section">
                    <h3>About This Campaign</h3>
                    <p><?php echo nl2br(htmlspecialchars($campaign['Description'] ?? 'No description available.')); ?></p>
                </div>
                
                <div class="detail-section">
                    <h3>Animal Details</h3>
                    <div class="detail-item">
                        <span class="label">Animal Type</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Animal_Type'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($campaign['Animal_Name'])): ?>
                    <div class="detail-item">
                        <span class="label">Animal Name</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Animal_Name']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($campaign['Animal_Age'])): ?>
                    <div class="detail-item">
                        <span class="label">Animal Age</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Animal_Age']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <span class="label">Number of Animals</span>
                        <span class="value"><?php echo $campaign['Animal_Count'] ?? 1; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Urgency Level</span>
                        <span class="value"><span class="urgency-badge <?php echo $campaign['Urgency_Level'] ?? 'normal'; ?>"><?php echo ucfirst($campaign['Urgency_Level'] ?? 'Normal'); ?></span></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Shelter Information</h3>
                    <div class="detail-item">
                        <span class="label">Shelter Name</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Location</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Shelter_Location'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($campaign['Shelter_Phone'])): ?>
                    <div class="detail-item">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($campaign['Shelter_Phone']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($campaign['Medical_Need'])): ?>
                <div class="detail-section">
                    <h3>Medical Needs</h3>
                    <p><?php echo nl2br(htmlspecialchars($campaign['Medical_Need'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <div class="donation-box">
                    <h3>Make a Donation</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min($campaign['Progress'], 100); ?>%;">
                            <?php echo number_format($campaign['Progress'], 0); ?>%
                        </div>
                    </div>
                    <div class="amount-display">
                        <span>Raised: $<?php echo number_format($campaign['Raised_Amount'], 0); ?></span>
                        <span>Goal: $<?php echo number_format($campaign['Goal_Amount'], 0); ?></span>
                    </div>
                    
                    <?php if ($is_active): ?>
                        <a href="donate.php?campaign_id=<?php echo $campaign['Campaign_ID']; ?>" class="btn-donate">Donate Now</a>
                    <?php else: ?>
                        <button class="btn-donate" disabled>
                            <?php 
                            if ($campaign['Status'] != 'active') {
                                echo 'Campaign is ' . ucfirst($campaign['Status']);
                            } else {
                                echo 'Campaign Has Ended';
                            }
                            ?>
                        </button>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 12px; font-size: 13px; color: #666;">
                        <p style="margin: 0;">Every donation, no matter the size, makes a difference. Your contribution goes directly to helping animals in need.</p>
                    </div>
                    
                    <div style="margin-top: 15px; font-size: 13px; color: #888; text-align: center;">
                        <?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'Animal Shelter'); ?> | Started: <?php echo date('d M Y', strtotime($campaign['Created_At'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>