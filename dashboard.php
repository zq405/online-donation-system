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
        header('Location:mainpage-testing.php');
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