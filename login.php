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
    <link rel="stylesheet" href="style.css">
    <style>
        .input-error
        {
            border-color: #dc2626 !important;
            background-color: #f0fdf4 !important;
        }

        .validation-message
        {
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }

        .validation-message.error
        {
            color: #dc2626;
            display: block;
        }

        .validation-message.success
        {
            color: #22c55e;
            display: block;
        }

        .input-group
        {
            position: relative;
        }

        .input-group .input-icon
        {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }
    </style>
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
                <input type="email" id="email" name="email" autocomplete="email" placeholder="name@exmaple.com" required oninput="validateEmail()">
                <span class="input-icon" id="emailIcon">📭</span>
            </div>
            <div class="validation-message" id="emailMessage"></div>

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

    <script>
        
    </script>
</body>
</html>