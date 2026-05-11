
<?php
session_start();
include 'connect.php';
//test code
echo "<pre>";
echo "=== SESSION DEBUG ===\n";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
echo "user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET') . "\n";
echo "user_name: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'NOT SET') . "\n";
echo "=== END DEBUG ===\n";
echo "</pre>";

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
        else if($_SESSION['user_role']=='recipient')
            {
                header("Location:recipient_dashboard.php");
            }
        else
            {
                header("Location:login.php");
            }
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        .nav{
            display: flex;
            justify-content: center;
            gap: 20px;
            background: #00C3FF;
            padding: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="campaign.php">Campaign</a>
        <a href="user.php">User</a>
        <a href="report.php">Export Report</a>
        <a href="log.php">Logs</a>
        <a href="logout.php">Log Out</a>
</body>
</html>