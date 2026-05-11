<?php
include 'connect.php';
if(isset($_SESSION['user_id']))
    {
        header("Location:dashboard.php");
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
        <?php if(isset($_SESSION['error'])):?>
            <div class="error"><?php echo $_SESSION['error'];unset($_SESSION['error']); ?></div>    
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="sucsess"><?php echo $_SESSION['success'];unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <h3>Login</h3>
        <form method="POST" action="login_process.php">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" placeholder="name@exmaple.com" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="login-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>