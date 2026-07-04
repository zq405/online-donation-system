<?php
// how_it_works.php
session_start();
include 'connect.php';

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .steps-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 25px;
            margin: 30px 0;
        }
        
        .step-card {
            background: white;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
            border-top: 4px solid #00C3FF;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 195, 255, 0.15);
        }
        
        .step-card .step-number {
            width: 50px;
            height: 50px;
            background: #00C3FF;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        
        .step-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .step-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .info-section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .info-section ul {
            list-style: none;
            padding: 0;
        }
        
        .info-section ul li {
            padding: 8px 0;
            color: #555;
            border-bottom: 1px solid #eee;
        }
        
        .info-section ul li:last-child {
            border-bottom: none;
        }
        
        .info-section ul li strong {
            color: #00C3FF;
        }
        
        .btn-get-started {
            display: inline-block;
            padding: 14px 40px;
            background: #00C3FF;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .btn-get-started:hover {
            background: #0099cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 195, 255, 0.4);
        }
        
        @media (max-width: 768px) {
            .steps-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .steps-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <div class="nav">
        <a href="donor_dashboard.php">My Dashboard</a>
        <a href="browse_campaign.php">Browse Campaigns</a>
        <a href="my_donations.php">My Donations</a>
        <a href="profile.php">Profile</a>
        <a href="how_it_works.php" style="background: rgba(255,255,255,0.2);">How It Works</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>How It Works</h2>
            <p style="color: #666; margin-bottom: 10px;">Making a difference is simple. Follow these four easy steps to start helping animals in need.</p>
            
            <!-- 步骤 -->
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Browse Campaigns</h3>
                    <p>Explore animal rescue campaigns and find a cause you care about.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Make a Donation</h3>
                    <p>Donate securely through our payment gateway. Every Ringgit counts.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Track Impact</h3>
                    <p>Receive updates on how your donation helped animals in need.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Earn Rewards</h3>
                    <p>Earn points and badges for your generosity and support.</p>
                </div>
            </div>
            
            <!-- 了解更多 -->
            <div class="info-section">
                <h3>Why Donate Through Us?</h3>
                <ul>
                    <li><strong>Transparency:</strong> All campaigns are verified by our team before going live.</li>
                    <li><strong>Direct Impact:</strong> Your donations go directly to the animal shelters in need.</li>
                    <li><strong>Real-time Updates:</strong> Shelters upload photos and videos to show their progress.</li>
                    <li><strong>Rewards Program:</strong> Earn points and badges for every donation you make.</li>
                    <li><strong>Secure Payments:</strong> All transactions are encrypted and secure.</li>
                </ul>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="browse_campaign.php" class="btn-get-started">Start Making a Difference</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>