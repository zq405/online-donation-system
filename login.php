<?php
session_start();
if(isset($_SESSION['user'])){
    header('index.php');
    if($_SESSION['role']=='admin'){
        header('admin_dashboard.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <div class="login-container">
        <h2>Online Donation</h2>
        <h3>Login</h3>
        <form method="POST" action="login_process.php">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input typr="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="login-link">
            <a href="register.html">Create an account</a>
        </div>
    </div>
</body>
</html>