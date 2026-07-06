<?php
session_start();
include 'connect.php';
if(isset($_SESSION['user_id']))
    {
        header("Location:dashboard.php");
        exit();
    }

$showBlockedModal = isset($_SESSION['blocked']) && $_SESSION['blocked'] === true;
$blockedMessage = $_SESSION['error'] ?? 'Your account has been suspended. All transactions and activity are currently blocked.';
if ($showBlockedModal) {
    unset($_SESSION['blocked']);
    unset($_SESSION['error']);
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

        .modal
        {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }
        .modal.active
        {
            display: flex;
        }
        @keyframes fadeIn
        {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content
        {
            background: white;
            border-radius: 24px;
            padding: 35px;
            width: 90%;
            max-width: 420px;
            text-align: center;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp
        {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <?php if ($showBlockedModal): ?>
    <div class="modal active" id="blockedModal">
        <div class="modal-content">
            <div style="font-size:60px; margin-bottom:10px;">🚫</div>
            <h2 style="color:#dc2626; margin-bottom:10px;">Account Suspended</h2>
            <p style="color:#555; line-height:1.6; margin:0 0 20px;">
                <?php echo htmlspecialchars($blockedMessage); ?>
            </p>
            <button type="button" style="background:#00C3FF; color:white; padding:10px 24px; border:none; border-radius:30px; cursor:pointer; font-weight:600;"
                    onclick="document.getElementById('blockedModal').classList.remove('active')">
                OK
            </button>
        </div>
    </div>
    <?php endif; ?>

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