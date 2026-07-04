<?php
// profile.php
session_start();
include 'connect.php';

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 获取用户信息
$sql = "SELECT Name, Email, Phone, Points, Badge, Register_Date, Status FROM donors WHERE Donors_ID = $donor_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// 处理资料更新
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $error = "Name is required.";
    } else {
        // 更新基本信息
        $update_sql = "UPDATE donors SET Name = '$name', Phone = '$phone' WHERE Donors_ID = $donor_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully!";
            
            // 更新密码（如果提供）
            if (!empty($current_password) && !empty($new_password)) {
                // 验证当前密码
                $pwd_sql = "SELECT Password FROM donors WHERE Donors_ID = $donor_id";
                $pwd_result = mysqli_query($conn, $pwd_sql);
                $pwd_row = mysqli_fetch_assoc($pwd_result);
                
                if (password_verify($current_password, $pwd_row['Password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $pwd_update = "UPDATE donors SET Password = '$hashed_password' WHERE Donors_ID = $donor_id";
                            if (mysqli_query($conn, $pwd_update)) {
                                $success .= " Password updated successfully!";
                            }
                        } else {
                            $error = "New password must be at least 6 characters.";
                        }
                    } else {
                        $error = "New passwords do not match.";
                    }
                } else {
                    $error = "Current password is incorrect.";
                }
            }
            
            // 重新获取用户信息
            $result = mysqli_query($conn, $sql);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .profile-card h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .profile-card .avatar {
            text-align: center;
            font-size: 80px;
            margin-bottom: 15px;
        }
        
        .profile-card .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .profile-card .detail-row .label {
            color: #666;
        }
        
        .profile-card .detail-row .value {
            color: #333;
            font-weight: 500;
        }
        
        .profile-card .badge-display {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .badge-gold { background: #fef3c7; color: #92400e; }
        .badge-silver { background: #e5e7eb; color: #374151; }
        .badge-bronze { background: #fde68a; color: #78350f; }
        .badge-none { background: #f3f4f6; color: #6b7280; }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
        }
        
        .form-group .hint {
            font-size: 12px;
            color: #888;
            margin-top: 3px;
        }
        
        .btn-save {
            padding: 12px 30px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-save:hover {
            background: #0099cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 195, 255, 0.4);
        }
        
        .btn-save:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <div class="nav">
        <a href="donor_dashboard.php">My Dashboard</a>
        <a href="browse_campaign.php">Browse Campaigns</a>
        <a href="my_donations.php">My Donations</a>
        <a href="profile.php" style="background: rgba(255,255,255,0.2);">Profile</a>
        <a href="how_it_works.php">How It Works</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>My Profile</h2>
            <p style="color: #666; margin-bottom: 20px;">View and manage your account information.</p>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- 左侧：用户信息 -->
                <div class="profile-card">
                    <h3>Account Information</h3>
                    
                    <div class="avatar">😀</div>
                    
                    <div class="detail-row">
                        <span class="label">Name</span>
                        <span class="value"><?php echo htmlspecialchars($user['Name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($user['Email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($user['Phone'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Status</span>
                        <span class="value"><?php echo ucfirst($user['Status']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Member Since</span>
                        <span class="value"><?php echo date('d M Y', strtotime($user['Register_Date'])); ?></span>
                    </div>
                </div>
                
                <!-- 右侧：编辑表单 + 捐赠统计 -->
                <div>
                    <div class="profile-card" style="margin-bottom: 20px;">
                        <h3>Edit Profile</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['Phone'] ?? ''); ?>" placeholder="Enter phone number">
                            </div>
                            <hr style="margin: 20px 0; border-color: #eee;">
                            <p style="font-size: 14px; font-weight: 600; color: #333;">Change Password (Optional)</p>
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Enter new password (min 6 chars)">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                            <button type="submit" class="btn-save">Save Changes</button>
                        </form>
                    </div>
                    
                    <div class="profile-card">
                        <h3>Donation Stats</h3>
                        <div class="detail-row">
                            <span class="label">Points Earned</span>
                            <span class="value"><?php echo $user['Points'] ?? 0; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Current Badge</span>
                            <span class="value">
                                <?php
                                $badge = $user['Badge'] ?? 'None';
                                $badge_class = 'badge-none';
                                if ($badge == 'Gold') $badge_class = 'badge-gold';
                                elseif ($badge == 'Silver') $badge_class = 'badge-silver';
                                elseif ($badge == 'Bronze') $badge_class = 'badge-bronze';
                                ?>
                                <span class="badge-display <?php echo $badge_class; ?>"><?php echo $badge; ?></span>
                            </span>
                        </div>
                        <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 13px; color: #666;">
                            <p style="margin: 0;">Earn more points by making donations. Reach 100 points for Bronze, 500 for Silver, and 1000 for Gold badge.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>