<?php
session_start();

if(!isset($_SESSION['user']))
    {
        header("Location:login.php");
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