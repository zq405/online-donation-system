<?php
// register_success.php
session_start();

// 检查是否直接访问此页面（没有注册成功记录）
if (!isset($_SESSION['register_success']) || $_SESSION['register_success'] !== true) {
    header('Location: register.php');
    exit();
}

// 获取注册信息
$message = $_SESSION['register_message'] ?? "Registration Successful!\n\nWelcome to Animal Shelters House!\n\nYou can now login to your account.";
$email = $_SESSION['user_email'] ?? '';

// 清除 Session 数据（防止刷新页面重复显示）
unset($_SESSION['register_success']);
unset($_SESSION['register_message']);
unset($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Animal Shelters House</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-container {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 195, 255, 0.2);
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
        }
        
        .success-container h1 {
            color: #00C3FF;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .success-container p {
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        
        .success-container .email-box {
            background: #f0f7ff;
            color: #00C3FF;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 12px;
            display: inline-block;
            margin: 10px 0 20px;
            font-size: 16px;
        }
        
        .btn-login {
            display: inline-block;
            background: #00C3FF;
            color: white;
            padding: 14px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 195, 255, 0.3);
        }
        
        .btn-login:hover {
            background: #0099cc;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 195, 255, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease infinite;
            vertical-align: middle;
            margin-right: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="success-container" id="successPage">
        <span class="success-icon">🎉</span>
        <h1>Registration Successful!</h1>
        <p>Your account has been created successfully.</p>
        <p>We're excited to have you join the <strong>Animal Shelters House</strong> community!</p>
        
        <div class="email-box">
            📧 <?php echo htmlspecialchars($email); ?>
        </div>
        
        <p style="font-size: 14px; color: #888;">
            You will be redirected to the login page in <strong id="countdown">5</strong> seconds...
        </p>
        
        <button class="btn-login" id="loginBtn" onclick="redirectToLogin()">
            🔑 Login Now
        </button>
    </div>

    <script>
        // 倒计时自动跳转
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
        
        function redirectToLogin() {
            // 显示加载状态
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner"></span> Redirecting...';
            btn.disabled = true;
            
            // 跳转到登录页面
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>