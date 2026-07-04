<?php
session_start();
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
        .error
        {
            color: #dc2626;
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success
        {
            color: #16a34a;
            background-color: #f0fdf4;
            border: 1px solid #16a34a;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

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
            <div class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>    
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <h3>Login</h3>
        <form id="loginForm" method="POST" action="login_process.php">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" placeholder="name@exmaple.com" required oninput="validateEmail()">
                <span class="input-icon" id="emailIcon">📧</span>
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
        function validateEmail()
        {
            const emailInput=document.getElementById('email');
            const emailMessage=document.getElementById('emailMessage');
            const emailIcon=document.getElementById('emailIcon');
            const email=emailInput.value.trim();
            const emailPattern=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if(email==='')
            {
                emailInput.className='';
                emailMessage.className='validation-message';
                emailMessage.textContent='';
                emailIcon.textContent='📭';
                return false;
            }

            if(emailPattern.test(email))
            {
                emailInput.className='input-success';
                emailMessage.className='validation-message success';
                emailMessage.textContent='Valid email format';
                emailIcon.textContent='✓';
                return true;
            }
            else
            {
                emailInput.className='input-error';
                emailMessage.className='validation-message error';
                emailMessage.textContent='Please enter a valid email address';
                emailIcon.textContent='❌';
                return false;
            }
        }

        document.getElementById('loginForm').addEventListener('submit',function(e){
            const isValid=validateEmail();
            if(!isValid)
            {
                e.preventDefault();
                document.getElementById('email').focus();
            }
        });
        
    </script>
</body>
</html>