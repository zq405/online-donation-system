<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_role'] != 'admin') {
    if ($_SESSION['user_role'] == 'donor') {
        header("Location: donor_dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$admin_id = $_SESSION['user_id'];
$error = '';
$success = '';

// ============================================
// 处理审核操作
// ============================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $donation_id = intval($_GET['id']);
    
    if ($action == 'verify') {
        $sql = "UPDATE donations SET 
                    Status = 'verified', 
                    Verified_By = $admin_id, 
                    Verified_At = NOW() 
                WHERE Donation_ID = $donation_id AND Status = 'pending'";
        
        if (mysqli_query($conn, $sql)) {
            // 更新活动筹款金额
            $donation_sql = "SELECT Campaign_ID, Amount FROM donations WHERE Donation_ID = $donation_id";
            $donation_result = mysqli_query($conn, $donation_sql);
            $donation = mysqli_fetch_assoc($donation_result);
            
            if ($donation) {
                $update_campaign = "UPDATE campaign SET Raised_Amount = Raised_Amount + " . $donation['Amount'] . " WHERE Campaign_ID = " . $donation['Campaign_ID'];
                mysqli_query($conn, $update_campaign);
                
                // 更新捐赠者积分
                $points = floor($donation['Amount']);
                $update_donor = "UPDATE donors SET Points = Points + $points WHERE Donors_ID = (SELECT Donors_ID FROM donations WHERE Donation_ID = $donation_id)";
                mysqli_query($conn, $update_donor);
            }
            
            $success = "Donation verified successfully!";
        } else {
            $error = "Verification failed: " . mysqli_error($conn);
        }
        
    } elseif ($action == 'reject') {
        $reason = mysqli_real_escape_string($conn, $_GET['reason'] ?? 'No reason provided');
        $sql = "UPDATE donations SET 
                    Status = 'rejected', 
                    Verified_By = $admin_id, 
                    Verified_At = NOW(),
                    Rejection_Reason = '$reason'
                WHERE Donation_ID = $donation_id AND Status = 'pending'";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Donation rejected.";
        } else {
            $error = "Rejection failed: " . mysqli_error($conn);
        }
    }
    
    header("Location: verify_donations.php?message=" . urlencode($success ? $success : $error) . "&type=" . ($success ? 'success' : 'error'));
    exit();
}

// ============================================
// 获取筛选参数
// ============================================
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// ============================================
// 构建查询
// ============================================
$where = "1=1";
if ($status_filter != 'all') {
    $where .= " AND d.Status = '$status_filter'";
}
if (!empty($search)) {
    $where .= " AND (dn.Name LIKE '%$search%' OR dn.Email LIKE '%$search%' OR c.Title LIKE '%$search%')";
}

$sql = "SELECT d.Donation_ID, d.Amount, d.Transfer_Reference, d.Transfer_Date, 
               d.Bank_Name, d.Account_Holder, d.Receipt_Image, d.Status, 
               d.Created_At, d.Verified_At, d.Rejection_Reason,
               dn.Name AS Donor_Name, dn.Email AS Donor_Email,
               c.Title AS Campaign_Title, c.Campaign_ID
        FROM donations d
        LEFT JOIN donors dn ON d.Donors_ID = dn.Donors_ID
        LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
        WHERE $where
        ORDER BY d.Created_At DESC";

$donations = mysqli_query($conn, $sql);

// ============================================
// 统计
// ============================================
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations WHERE Status = 'pending'"))['count'] ?? 0;
$verified_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations WHERE Status = 'verified'"))['count'] ?? 0;
$rejected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations WHERE Status = 'rejected'"))['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Donations - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-row .stat-box {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-bottom: 3px solid #00C3FF;
        }
        
        .stats-row .stat-box.pending { border-bottom-color: #f59e0b; }
        .stats-row .stat-box.verified { border-bottom-color: #22c55e; }
        .stats-row .stat-box.rejected { border-bottom-color: #dc2626; }
        
        .stats-row .stat-box .number {
            font-size: 28px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .stats-row .stat-box.pending .number { color: #f59e0b; }
        .stats-row .stat-box.verified .number { color: #22c55e; }
        .stats-row .stat-box.rejected .number { color: #dc2626; }
        
        .stats-row .stat-box .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
        }
        
        .filter-bar input[type="text"]:focus {
            outline: none;
            border-color: #00C3FF;
        }
        
        .filter-bar select {
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 160px;
        }
        
        .filter-bar .btn-filter {
            padding: 10px 25px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: auto;
            margin: 0;
        }
        
        .filter-bar .btn-filter:hover {
            background: #0099cc;
        }
        
        .filter-bar .btn-filter.reset {
            background: #e0e0e0;
            color: #333;
        }
        
        .filter-bar .btn-filter.reset:hover {
            background: #ccc;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .donation-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .donation-table th {
            background: #00C3FF;
            color: white;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .donation-table td {
            padding: 10px 16px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .donation-table tr:hover td {
            background: #f8f9fa;
        }
        
        .badge-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-status.pending { background: #fff3cd; color: #856404; }
        .badge-status.verified { background: #d4edda; color: #155724; }
        .badge-status.rejected { background: #f8d7da; color: #721c24; }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        
        .btn-sm:hover {
            transform: translateY(-2px);
        }
        
        .btn-verify {
            background: #22c55e;
            color: white;
        }
        .btn-verify:hover { background: #16a34a; }
        
        .btn-reject {
            background: #dc2626;
            color: white;
        }
        .btn-reject:hover { background: #b91c1c; }
        
        .btn-view {
            background: #00C3FF;
            color: white;
        }
        .btn-view:hover { background: #0099cc; }
        
        .btn-download {
            background: #6b7280;
            color: white;
        }
        .btn-download:hover { background: #4b5563; }
        
        .action-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .receipt-thumb {
            max-width: 50px;
            max-height: 50px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .modal .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .modal .detail-row .label {
            color: #666;
        }
        
        .modal .detail-row .value {
            color: #333;
            font-weight: 500;
        }
        
        .modal .receipt-image {
            max-width: 100%;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .modal .reject-reason-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
            }
            .filter-bar input,
            .filter-bar select {
                width: 100%;
            }
            .donation-table {
                font-size: 13px;
            }
            .donation-table th,
            .donation-table td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="campaign.php">Campaigns</a>
        <a href="user.php">Users</a>
        <a href="report.php">Export Report</a>
        <a href="verify_donations.php" style="background: rgba(255,255,255,0.2);">Verify Donations</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Verify Donations</h2>
            <p style="color: #666; margin-bottom: 20px;">Review and verify manual donation receipts submitted by donors.</p>
            
            <?php if (isset($_GET['message'])): ?>
                <?php if ($_GET['type'] == 'success'): ?>
                    <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php else: ?>
                    <div class="error"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- 统计 -->
            <div class="stats-row">
                <div class="stat-box pending">
                    <div class="number"><?php echo $pending_count; ?></div>
                    <div class="label">Pending Verification</div>
                </div>
                <div class="stat-box verified">
                    <div class="number"><?php echo $verified_count; ?></div>
                    <div class="label">Verified</div>
                </div>
                <div class="stat-box rejected">
                    <div class="number"><?php echo $rejected_count; ?></div>
                    <div class="label">Rejected</div>
                </div>
            </div>
            
            <!-- 筛选 -->
            <div class="filter-bar">
                <form method="GET" action="verify_donations.php" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%; align-items: center;">
                    <input type="text" name="search" placeholder="Search donor or campaign..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status">
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="verified" <?php echo $status_filter == 'verified' ? 'selected' : ''; ?>>Verified</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                    </select>
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="verify_donations.php" class="btn-filter reset" style="padding: 10px 25px; background: #e0e0e0; color: #333; border-radius: 12px; text-decoration: none; font-weight: 600;">Reset</a>
                </form>
            </div>
            
            <!-- 列表 -->
            <div class="table-container">
                <table class="donation-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor</th>
                            <th>Campaign</th>
                            <th>Amount</th>
                            <th>Receipt</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($donations) > 0): ?>
                            <?php while ($donation = mysqli_fetch_assoc($donations)): ?>
                                <tr>
                                    <td>#<?php echo $donation['Donation_ID']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donation['Donor_Name'] ?? 'Unknown'); ?></strong>
                                        <br><small style="color:#888;"><?php echo htmlspecialchars($donation['Donor_Email'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($donation['Campaign_Title'] ?? 'N/A'); ?></td>
                                    <td>RM <?php echo number_format($donation['Amount'], 2); ?></td>
                                    <td>
                                        <?php if ($donation['Receipt_Image']): ?>
                                            <img src="<?php echo htmlspecialchars($donation['Receipt_Image']); ?>" class="receipt-thumb" onclick="viewReceipt('<?php echo htmlspecialchars($donation['Receipt_Image']); ?>')">
                                        <?php else: ?>
                                            <span style="color:#888;">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-status <?php echo $donation['Status']; ?>"><?php echo ucfirst($donation['Status']); ?></span></td>
                                    <td>
                                        <div class="action-group">
                                            <button class="btn-sm btn-view" onclick="viewDonation(<?php echo $donation['Donation_ID']; ?>)">View</button>
                                            <?php if ($donation['Status'] == 'pending'): ?>
                                                <button class="btn-sm btn-verify" onclick="verifyDonation(<?php echo $donation['Donation_ID']; ?>)">Verify</button>
                                                <button class="btn-sm btn-reject" onclick="rejectDonation(<?php echo $donation['Donation_ID']; ?>)">Reject</button>
                                            <?php endif; ?>
                                            <?php if ($donation['Receipt_Image']): ?>
                                                <a href="<?php echo htmlspecialchars($donation['Receipt_Image']); ?>" target="_blank" class="btn-sm btn-download">Download</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                                    No donations found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- ============================================ -->
    <!-- 模态框：查看捐赠详情 -->
    <!-- ============================================ -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Donation Details</h2>
                <button class="modal-close" onclick="closeModal('detailModal')">&times;</button>
            </div>
            <div id="detailContent">
                <p style="color:#888;">Loading...</p>
            </div>
        </div>
    </div>
    
    <!-- ============================================ -->
    <!-- 模态框：查看收据 -->
    <!-- ============================================ -->
    <div class="modal" id="receiptModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Receipt Image</h2>
                <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
            </div>
            <div style="text-align: center;">
                <img id="receiptImage" src="" alt="Receipt" style="max-width: 100%; border-radius: 8px;">
            </div>
        </div>
    </div>
    
    <!-- ============================================ -->
    <!-- 模态框：拒绝原因 -->
    <!-- ============================================ -->
    <div class="modal" id="rejectModal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h2>Reject Donation</h2>
                <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <p style="color:#555; margin-bottom: 15px;">Please provide a reason for rejecting this donation:</p>
            <input type="text" id="rejectReason" class="reject-reason-input" placeholder="e.g., Receipt unclear, incorrect amount">
            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <button class="btn-sm btn-reject" style="flex: 1; padding: 10px;" onclick="confirmReject()">Confirm Reject</button>
                <button class="btn-sm btn-view" style="flex: 1; padding: 10px; background:#e0e0e0; color:#333;" onclick="closeModal('rejectModal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <script>
        var currentDonationId = null;
        var currentRejectId = null;
        
        // ============================================
        // 查看捐赠详情
        // ============================================
        function viewDonation(donationId) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            modal.classList.add('active');
            content.innerHTML = '<p style="color:#888;">Loading...</p>';
            
            fetch('get_donation_details.php?id=' + donationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const d = data.donation;
                        content.innerHTML = `
                            <div class="detail-row"><span class="label">Donation ID</span><span class="value">#${d.Donation_ID}</span></div>
                            <div class="detail-row"><span class="label">Donor</span><span class="value">${escapeHtml(d.Donor_Name || 'Unknown')}</span></div>
                            <div class="detail-row"><span class="label">Email</span><span class="value">${escapeHtml(d.Donor_Email || 'N/A')}</span></div>
                            <div class="detail-row"><span class="label">Campaign</span><span class="value">${escapeHtml(d.Campaign_Title || 'N/A')}</span></div>
                            <div class="detail-row"><span class="label">Amount</span><span class="value">RM ${Number(d.Amount).toFixed(2)}</span></div>
                            <div class="detail-row"><span class="label">Bank Name</span><span class="value">${escapeHtml(d.Bank_Name || 'N/A')}</span></div>
                            <div class="detail-row"><span class="label">Account Holder</span><span class="value">${escapeHtml(d.Account_Holder || 'N/A')}</span></div>
                            <div class="detail-row"><span class="label">Transfer Reference</span><span class="value">${escapeHtml(d.Transfer_Reference || 'N/A')}</span></div>
                            <div class="detail-row"><span class="label">Transfer Date</span><span class="value">${d.Transfer_Date || 'N/A'}</span></div>
                            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge-status ${d.Status}">${d.Status}</span></span></div>
                            ${d.Rejection_Reason ? `<div class="detail-row"><span class="label">Rejection Reason</span><span class="value">${escapeHtml(d.Rejection_Reason)}</span></div>` : ''}
                            <div class="detail-row"><span class="label">Submitted</span><span class="value">${d.Created_At}</span></div>
                            ${d.Receipt_Image ? `<div style="margin-top:15px;"><img src="${escapeHtml(d.Receipt_Image)}" style="max-width:100%; border-radius:8px;"></div>` : ''}
                        `;
                    } else {
                        content.innerHTML = '<p style="color:#dc2626;">Failed to load donation details.</p>';
                    }
                })
                .catch(() => {
                    content.innerHTML = '<p style="color:#dc2626;">Failed to load donation details.</p>';
                });
        }
        
        // ============================================
        // 查看收据
        // ============================================
        function viewReceipt(imageUrl) {
            document.getElementById('receiptImage').src = imageUrl;
            document.getElementById('receiptModal').classList.add('active');
        }
        
        // ============================================
        // 验证捐赠
        // ============================================
        function verifyDonation(donationId) {
            if (confirm('Are you sure you want to verify this donation? The donation amount will be added to the campaign.') && confirm('This action cannot be undone. Continue?')) {
                window.location.href = 'verify_donations.php?action=verify&id=' + donationId;
            }
        }
        
        // ============================================
        // 拒绝捐赠
        // ============================================
        function rejectDonation(donationId) {
            currentRejectId = donationId;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectModal').classList.add('active');
        }
        
        function confirmReject() {
            const reason = document.getElementById('rejectReason').value.trim() || 'No reason provided';
            window.location.href = 'verify_donations.php?action=reject&id=' + currentRejectId + '&reason=' + encodeURIComponent(reason);
        }
        
        // ============================================
        // 关闭模态框
        // ============================================
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // 点击外部关闭
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>