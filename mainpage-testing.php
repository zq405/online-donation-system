<?php
session_start();
include 'connect.php';

$total_campaigns=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM campaign WHERE Status='active'"))['count']??0;
$total_donors=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM donors"))['count']??0;
$total_donations=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount)as total FROM donations WHERE Status='completed'"))['total']??0;
$popular_campaigns=mysqli_query($conn,"SELECT * FROM campaign WHERE Status='active' ORDER BY Raised_Amount DESC LIMIT 3");
$total_animals=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(Animal_Count)as total FROM campaign"))['total']??0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-nav">
        <div class="nav-container">
            <a href="mainpage-testing.php" class="logo"><span>Animal Shelters House</span></a>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
            <ul class="nav-links" id="navLinks">
                <li><a href="#impact">Our Impact</a></li>
                <li><a href="#featured">Featured Campaign</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <?php if(isset($_SESSION['user_id'])&&($_SESSION['user_role']=='admin')):?>
                    <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <?php endif;?>
            </ul>
            <div class="user-menu">
                <?php if(isset($_SESSION['user_id'])):?>
                    <div class="user-welcome">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']??'User');?></span>
                    </div>
                    <a href="dashboard.php" class="btn-user">Dashboard</a>
                    <a href="logout.php" class="btn-user btn-logout">Logout</a>
                <?php else:?>
                    <button class="btn-user" onclick="window.location.href='login.php'">Login</button>
                    <button class="btn-user" onclick="window.location.href='register.php'">Register</button>
                <?php endif;?>
            </div>
        </div>
    </div>

    <section class="hero">
        <div class="hero-content">
            <h1>Make a Real Difference Today<span style="display:inline-block;"></span></h1>
            <p>Your donation helps us rescue, rehabilitate and rehomw abandoned and stray animals. Every contribution gives a second chance to an animal in need.</p>
            <div class="cta-buttons">
                <?php if(isset($_SESSION['user_id'])):?>
                    <button class="btn-primary" onclick="window.location.href='campaign.php'">Donate Now</button>
                <?php else:?>
                    <button class="btn-primary" onclick="window.location.href='register.php'">Donate Now</button>
                <?php endif;?>
                <button class="btn-secondary" onclick="document.querySelector('#featured').scrollIntoView({behavior:'smooth'})">View Campaigns</button>
            </div>
        </div>
    </section>

    <seciton class="impact-section" id="impact">
        <div class="container" style="max-width:1200px;margin:0 auto;">
            <h2>Our Impact So Far</h2>
            <p class="subtittle">Thanks to you generous support, we've achieved these milestones</p>
            <div class="stat-grid-main">
                <div class="stat-item-main">
                    <h3><?php echo number_format($total_animals);?>+</h3>
                    <p>Animals Rescued & Helped</p>
                </div>
                <div class="stat-item-main">
                    <h3>RM<?php echo number_format($total_donations,0);?>+</h3>
                    <p>Funds Collected</p>
                </div>
                <div class="stat-item-main">
                    <h3><?php echo number_format($total_donors);?>+</h3>
                    <p>Generous Donors</p>
                </div>
                <div class="stat-item-main">
                    <h3><?php echo number_format($total_campaigns);?>+</h3>
                    <p>Active Campaigns</p>
                </div>
            </div>
        </div>
    </seciton>

    <section class="featured-section" id="featured">
        <h2>Featured Rescue Campaigns</h2>
        <p class="subtittle">Your donation directly impacts these animals in need</p>
        <div class="campaigns-grid">
            <?php if($popular_campaigns && mysqli_num_rows($popular_campaigns)>0):?>
                <?php while($campaign=mysqli_fetch_assoc($popular_campaigns)):
                    $progress=($campaign['Raised_Amount']/$campaign['Goal_Amount'])*100;?>
                    <div class="campaign-card-main">
                        <div class="campaign-image">
                            <?php echo htmlspecialchars($campaign['Animal_Type']??'Animal');?>Rescue
                        </div>
                        <div class="campaign-info">
                            <h3><?php echo htmlspecialchars($campaign['Title']);?></h3>
                            <p><?php echo htmlspecialchars(substr($campaign['Description']??'',0,100)).'...';?></p>
                            <div class="campaign-meta">
                                <span><?php echo htmlspecialchars($campaign['Shelter_Name']??'Animal_Shelter');?></span>
                                <span><?php echo $campaign['Animal_Count'];?> animals</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:<?php echo min($progress,100);?>%"></div>
                            </div>
                            <div class="cmapaign-meta">
                                <span>Raised: <strong>RM <?php echo number_format($campaign['Raised_Amount'],2);?></strong></span>
                                <span>Goal: RM<?php echo number_format($campaign['Goal_Amount'],2);?></span>
                            </div>
                            <button class="donate-btn-card" onlclikck="window.location.href='donate.php?campign_id=<?php echo $campaign['Campaign_ID'];?>'">Donate Now</button>
                        </div>
                    </div>
                    <?php endwhile;?>
                    <?php else:?>
                        <div class="card" style="text-align:center;grid-column:1/-1;">
                            <p>No active campaigns at the moment. Please check back soon!</p>
                        </div>
                    <?php endif;?>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Browse Campaign</h3>
                <p>Explore animal rescue campaigns and find a cause you care about.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Make a Donation</h3>
                <p>Donate security through our payment gateway. Every Ringgit counts</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Track Impact</h3>
                <p>Receive updates on how your donation helped animals is need</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Earn Rewards</h3>
                <p>Earn points and badges for your generosity and support</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php';?>

    <script>
        function toggleMobileMenu()
        {
            var navLinks=document.getElementById('nav-links');
            navLinks.classList.toggle('active');
        }
    </script>
</body>
</html>