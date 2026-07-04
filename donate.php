<?php
// donate.php
session_start();
include 'connect.php';

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['campaign_id'])) {
    header("Location: browse_campaigns.php");
    exit();
}

$campaign_id = intval($_GET['campaign_id']);
$donor_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 获取活动信息
$sql = "SELECT Campaign_ID, Title, Goal_Amount, Raised_Amount, Status, End_Date FROM campaign WHERE Campaign_ID = $campaign_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Campaign not found.";
    header("Location: browse_campaigns.php");
    exit();
}

$campaign = mysqli_fetch_assoc($result);

// 检查活动是否可捐赠
$can_donate = ($campaign['Status'] == 'active' && strtotime($campaign['End_Date']) > time());

if (!$can_donate) {
    $_SESSION['error'] = "This campaign is not currently accepting donations.";
    header("Location: campaign_detail.php?id=" . $campaign['Campaign_ID']);
    exit();
}

// 获取 e-wallet 账户信息（仅 1 个）
$ewallet_sql = "SELECT * FROM ewallet_accounts WHERE Status = 'active' LIMIT 1";
$ewallet_result = mysqli_query($conn, $ewallet_sql);
$ewallet = mysqli_fetch_assoc($ewallet_result);

// 如果没有 e-wallet 账户信息，显示默认
if (!$ewallet) {
    $ewallet = [
        'Name' => 'Touch n Go',
        'Account_No' => '012-345-6789',
        'Account_Holder' => 'Animal Shelters House'
    ];
}

// 处理捐赠提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $ewallet_account = mysqli_real_escape_string($conn, $_POST['ewallet_account'] ?? '');
    $transfer_reference = mysqli_real_escape_string($conn, $_POST['transfer_reference'] ?? '');
    $transfer_date = mysqli_real_escape_string($conn, $_POST['transfer_date'] ?? '');
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $donor_message = mysqli_real_escape_string($conn, $_POST['donor_message'] ?? '');
    
    // 验证
    if ($amount <= 0) {
        $error = "Please enter a valid donation amount.";
    } elseif (empty($ewallet_account)) {
        $error = "Please enter your e-wallet account name.";
    } elseif (empty($transfer_reference)) {
        $error = "Please enter the transfer reference number.";
    } elseif (empty($transfer_date)) {
        $error = "Please select the transfer date.";
    } elseif (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] != 0) {
        $error = "Please upload a receipt/screenshot image.";
    } else {
        // 处理收据上传
        $target_dir = "uploads/receipts/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $error = "Only JPG, PNG, and PDF files are allowed.";
        } else {
            $new_filename = 'receipt_' . $donor_id . '_' . date('YmdHis') . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $target_file)) {
                // 插入捐赠记录 (状态为 pending)
                $sql = "INSERT INTO donations (
                            Donors_ID, Campaign_ID, Amount, Payment_Method, 
                            Bank_Name, Account_Holder, Transfer_Reference, Transfer_Date,
                            Receipt_Image, Is_Anonymous, Donor_Message, Status, Created_At
                        ) VALUES (
                            $donor_id, $campaign_id, $amount, 'E-Wallet',
                            '" . mysqli_real_escape_string($conn, $ewallet['Name']) . "', 
                            '$ewallet_account', '$transfer_reference', '$transfer_date',
                            '$target_file', $is_anonymous, '$donor_message', 'pending', NOW()
                        )";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "Your donation has been submitted for verification. Our team will review your receipt and approve it within 1-2 business days.";
                } else {
                    $error = "Donation submission failed. Please try again. Error: " . mysqli_error($conn);
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
            } else {
                $error = "Failed to upload receipt. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .donate-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .donate-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .ewallet-info-box {
            background: #f0f7ff;
            border: 2px solid #00C3FF;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .ewallet-info-box .ewallet-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .ewallet-info-box h4 {
            color: #00C3FF;
            margin-bottom: 5px;
        }
        
        .ewallet-info-box .account-detail {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 5px 0;
        }
        
        .ewallet-info-box .account-label {
            font-size: 13px;
            color: #666;
        }
        
        .donate-box .campaign-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .donate-box .campaign-info h3 {
            margin: 0 0 5px;
            color: #333;
        }
        
        .donate-box .campaign-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group label .required {
            color: #dc2626;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-group .amount-presets {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        
        .form-group .amount-presets button {
            padding: 8px 20px;
            background: #f0f7ff;
            color: #00C3FF;
            border: 2px solid #00C3FF;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: auto;
            margin: 0;
        }
        
        .form-group .amount-presets button:hover {
            background: #00C3FF;
            color: white;
        }
        
        .form-group .amount-presets button.active {
            background: #00C3FF;
            color: white;
        }
        
        .form-group .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-group .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }
        
        .form-group .file-input-wrapper {
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .form-group .file-input-wrapper:hover {
            border-color: #00C3FF;
            background: #f0f7ff;
        }
        
        .form-group .file-input-wrapper input[type="file"] {
            display: none;
        }
        
        .form-group .file-input-wrapper .file-name {
            color: #00C3FF;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .form-group .file-input-wrapper .hint {
            color: #888;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #0099cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 195, 255, 0.4);
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
        }
        
        .success-box .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .success-box h2 {
            margin-bottom: 10px;
        }
        
        .success-box .btn-continue {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .success-box .btn-continue:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        
        .instruction-box {
            background: #fef3c7;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
        }
        
        .instruction-box p {
            font-size: 14px;
            color: #78350f;
            margin: 0;
        }
        
        .instruction-box ol {
            margin: 10px 0 0 20px;
            color: #78350f;
            font-size: 14px;
        }
        
        .instruction-box ol li {
            margin-bottom: 3px;
        }
        
        @media (max-width: 768px) {
            .donate-box .form-group .amount-presets {
                gap: 8px;
            }
            .donate-box .form-group .amount-presets button {
                padding: 6px 14px;
                font-size: 13px;
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
        <a href="profile.php">Profile</a>
        <a href="how_it_works.php">How It Works</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="donate-container">
            <?php if ($success): ?>
                <div class="success-box">
                    <div class="icon">Thank you</div>
                    <h2>Donation Submitted!</h2>
                    <p><?php echo $success; ?></p>
                    <p style="font-size: 14px; margin-top: 10px;">You will receive a confirmation once your donation is verified.</p>
                    <a href="campaign_detail.php?id=<?php echo $campaign['Campaign_ID']; ?>" class="btn-continue">Back to Campaign</a>
                </div>
            <?php else: ?>
                <div class="donate-box">
                    <h2>Make a Donation</h2>
                    <p style="color: #666; margin-bottom: 20px;">Transfer your donation via e-wallet and upload the screenshot/receipt for verification.</p>
                    
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="campaign-info">
                        <h3><?php echo htmlspecialchars($campaign['Title']); ?></h3>
                        <p>Goal: RM <?php echo number_format($campaign['Goal_Amount'], 0); ?> | Raised: RM <?php echo number_format($campaign['Raised_Amount'], 0); ?></p>
                    </div>
                    
                    <!-- E-Wallet 账户信息（仅1个） -->
                    <div class="ewallet-info-box">
                        <div class="ewallet-icon">E-Wallet</div>
                        <h4><?php echo htmlspecialchars($ewallet['Name']); ?></h4>
                        <div class="account-detail"><?php echo htmlspecialchars($ewallet['Account_No']); ?></div>
                        <div class="account-label">Account Holder: <?php echo htmlspecialchars($ewallet['Account_Holder']); ?></div>
                        <p style="font-size: 12px; color: #888; margin-top: 10px;">Please use your full name as the reference when transferring.</p>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- 金额 -->
                        <div class="form-group">
                            <label>Donation Amount (RM) <span class="required">*</span></label>
                            <input type="number" id="amount" name="amount" placeholder="Enter amount (e.g., 50)" min="1" step="0.01" required>
                            <div class="amount-presets">
                                <button type="button" onclick="setAmount(10)">RM 10</button>
                                <button type="button" onclick="setAmount(25)">RM 25</button>
                                <button type="button" onclick="setAmount(50)">RM 50</button>
                                <button type="button" onclick="setAmount(100)">RM 100</button>
                                <button type="button" onclick="setAmount(200)">RM 200</button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>E-Wallet Account Name <span class="required">*</span></label>
                            <input type="text" name="ewallet_account" placeholder="Your full name as per e-wallet account" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Transfer Reference Number <span class="required">*</span></label>
                            <input type="text" name="transfer_reference" placeholder="e.g., TXN1234567890" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Transfer Date <span class="required">*</span></label>
                            <input type="date" name="transfer_date" required>
                        </div>
                        
                        <!-- 收据上传 -->
                        <div class="form-group">
                            <label>Upload Receipt / Screenshot <span class="required">*</span></label>
                            <div class="file-input-wrapper" onclick="document.getElementById('receipt_file').click()">
                                <div style="font-size: 40px;">Upload</div>
                                <p>Click to upload receipt screenshot</p>
                                <div class="file-name" id="fileName">No file selected</div>
                                <div class="hint">Supported formats: JPG, PNG, PDF (Max 5MB)</div>
                                <input type="file" id="receipt_file" name="receipt_image" accept=".jpg,.jpeg,.png,.pdf" required onchange="updateFileName()">
                            </div>
                        </div>
                        
                        <!-- 匿名选项 -->
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
                                <label for="is_anonymous">Make this donation anonymous</label>
                            </div>
                        </div>
                        
                        <!-- 留言 -->
                        <div class="form-group">
                            <label>Donor Message (Optional)</label>
                            <textarea name="donor_message" placeholder="Leave a message of encouragement..."></textarea>
                        </div>
                        
                        <div class="instruction-box">
                            <p><strong>How to donate:</strong></p>
                            <ol>
                                <li>Transfer your donation amount to the e-wallet account above.</li>
                                <li>Take a screenshot of the transaction receipt.</li>
                                <li>Fill in the form below and upload the screenshot.</li>
                                <li>Our team will verify your donation within 1-2 business days.</li>
                            </ol>
                        </div>
                        
                        <button type="submit" class="btn-submit">Submit for Verification</button>
                    </form>
                    
                    <div style="margin-top: 15px; text-align: center; font-size: 13px; color: #888;">
                        Your donation will be verified within 1-2 business days.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function setAmount(amount) {
            document.getElementById('amount').value = amount;
            
            const buttons = document.querySelectorAll('.amount-presets button');
            buttons.forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            buttons.forEach(function(btn) {
                if (btn.textContent.trim() === 'RM ' + amount) {
                    btn.classList.add('active');
                }
            });
        }
        
        function updateFileName() {
            const fileInput = document.getElementById('receipt_file');
            const fileName = document.getElementById('fileName');
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
                fileName.style.color = '#00C3FF';
            } else {
                fileName.textContent = 'No file selected';
                fileName.style.color = '#00C3FF';
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>