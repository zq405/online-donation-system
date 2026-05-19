
<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user_id']))
    {
        header("Location:login.php");
        exit();
    }

if($_SESSION['user_role']!='admin' && $_SESSION['user_role']!='supper_admin')
    {
        if($_SESSION['user_role']=='donor')
            {
                header("Location:donor_dashboard.php");
            }
        else
            {
                header("Location:login.php");
            }
        exit();
    }

$user_name=$_SESSION['user_name'];
$total_donors=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count from donors"))['count'];
$total_campaigns=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*)as count FROM campaign"))['count'];
$pending_campaigns=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM campaign WHERE end_date>NOW()"))['count'];
$total_donations=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount)as total FROM donations WHERE Status='completed'"))['total']??0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="event.php">Event</a>
        <a href="user.php">User</a>
        <a href="report.php">Export Report</a>
        <a href="log.php">Logs</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>

    <div class="container">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($user_name);?>!</h1>
            <p>Here's what's happening with your donation platform today.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Donors</h3>
                <div class="number"><?php echo $total_donors;?></div>
            </div>
            <div class="stat-card">
                <h3>Total Donations</h3>
                <div class="number">RM<?php echo number_format($total_donations,2);?></div>
            </div>
            <div class="stat-card">
                <h3>Total events</h3>
                <div class="number"><?php echo $total_campaigns;?></div>
            </div>
            <div class="stat-card">
                <h3>Active Event</h3>
                <div class="number"><?php echo $pending_campaigns;?></div>
            </div>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <div class="quick-links">
                <a href="event.php">Manage Event</a>
                <a href="user.php">Manage Users</a>
                <a href="report.php">Export Report</a>
                <a href="log.php">View Logs</a>
            </div>
        </div>

        <div class="card">
            <h2>System Information</h2>
            <p>You are logged in as <strong><?php echo $_SESSION['user_role'];?></strong></p>
            <p>Email : <?php echo $_SESSION['user_email'];?></p>
        </div>
    </div>
    <?php include 'footer.php';?>
</body>
</html>