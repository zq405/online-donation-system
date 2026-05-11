<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user_id']))
    {
        header("Location:login.php");
        exit();
    }

$role=$_SESSION['user_role'];
$name=$_SESSION['user_name'];

switch($role)
{
    case 'donor':
        header('Location:donor_dashboard.php');
        break;
    case 'recipient':
        header('Location: recipient_dashboard.php');
        break;
    case 'admin':
        header('Location:admin_dashboard.php');
        break;
    default:
        header('Location:login.php');
        break;
}
exit();
?>