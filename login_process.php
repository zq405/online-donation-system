<?php
session_start();
include 'connect.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

$user = null;

$result = mysqli_query($conn, "SELECT Donors_ID AS user_id, Name, Email, 'donor' AS role, Password FROM donors WHERE Email='$email'");
if($result && mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
}

if(!$user) {
    $result = mysqli_query($conn, "SELECT Recipient_ID AS user_id, Name, Email, 'recipient' AS role, Password FROM recipient WHERE Email='$email'");
    if($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
    }
}

if(!$user) {
    $result = mysqli_query($conn, "SELECT Admin_ID AS user_id, Name, Email, Role AS role, Password FROM admin WHERE Email='$email'");
    if($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
    }
}

if($user && password_verify($password, $user['Password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['Name'];
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $user['role'];
    
    header('Location: dashboard.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid email or password";
    header('Location: login.php');
    exit();
}
?>