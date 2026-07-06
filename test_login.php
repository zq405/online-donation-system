<?php
// test_login.php
session_start();
include 'connect.php';

// 模拟登录测试 - 直接查询 admin
$test_email = 'admin@donation.com';  // 改成你数据库中的 admin 邮箱
$test_password = 'admin123';          // 改成你的密码

echo "<h2>登录调试工具</h2>";

// 查询 admin 表
$sql = "SELECT Admin_ID AS user_id, Name, Email, Role AS role, Password FROM admin WHERE Email='$test_email'";
echo "<p>SQL: " . htmlspecialchars($sql) . "</p>";

$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<p style='color:red'>查询失败: " . mysqli_error($conn) . "</p>";
} else {
    echo "<p>查询成功，行数: " . mysqli_num_rows($result) . "</p>";
    
    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // 验证密码
        if(password_verify($test_password, $user['Password'])) {
            echo "<p style='color:green'>✅ 密码验证成功！</p>";
            
            // 设置 Session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<p style='color:green'>✅ Session 已设置</p>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
            
            echo "<p><a href='admin_dashboard.php'>点击这里进入 Admin Dashboard</a></p>";
        } else {
            echo "<p style='color:red'>❌ 密码验证失败！</p>";
            echo "<p>数据库中的密码哈希: " . $user['Password'] . "</p>";
        }
    } else {
        echo "<p style='color:red'>❌ 未找到 admin 用户，请先在数据库中插入 admin 账号</p>";
    }
}
?>