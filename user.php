<?php
// user.php
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


if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    $new_status = ($action == 'block') ? 'suspended' : 'active';
    
    $sql = "UPDATE donors SET Status = '$new_status' WHERE Donors_ID = $user_id";
    
    if (mysqli_query($conn, $sql)) {
        $action_text = ($action == 'block') ? 'blocked' : 'unblocked';
        $_SESSION['success'] = "User $action_text successfully!";
    } else {
        $_SESSION['error'] = "Action failed: " . mysqli_error($conn);
    }
    
    header("Location: user.php");
    exit();
}


$donors_sql = "SELECT Donors_ID AS id, Name, Email, Phone, Points, Badge, Register_Date, Status FROM donors ORDER BY Donors_ID DESC";
$donors_result = mysqli_query($conn, $donors_sql);


$total_donors = mysqli_num_rows($donors_result);
$active_donors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donors WHERE Status='active'"))['count'] ?? 0;
$suspended_donors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donors WHERE Status='suspended'"))['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .user-stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-bottom: 3px solid #00C3FF;
        }
        
        .user-stat-card .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .user-stat-card .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .user-table th {
            background: #00C3FF;
            color: white;
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-table td {
            padding: 12px 18px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .user-table tr:hover td {
            background: #f8f9fa;
        }
        
        .user-table tr.suspended td {
            background: #fef2f2;
        }
        
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-status.suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 13px;
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
        
        .btn-view {
            background: #00C3FF;
            color: white;
        }
        
        .btn-view:hover {
            background: #0099cc;
        }
        
        .btn-block {
            background: #dc2626;
            color: white;
        }
        
        .btn-block:hover {
            background: #b91c1c;
        }
        
        .btn-unblock {
            background: #22c55e;
            color: white;
        }
        
        .btn-unblock:hover {
            background: #16a34a;
        }
        
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .search-bar input {
            flex: 1;
            min-width: 200px;
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-bar input:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
        }
        
        .search-bar select {
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 140px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-state .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .modal {
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
        
        .modal.active {
            display: flex;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 35px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 22px;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
            padding: 0;
            margin: 0;
            width: auto;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .detail-item .label {
            color: #666;
            font-weight: 500;
        }
        
        .detail-item .value {
            color: #333;
            font-weight: 600;
            text-align: right;
        }
        
        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .modal-actions .btn {
            flex: 1;
            min-width: 100px;
            text-align: center;
            padding: 10px 20px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .modal-actions .btn:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .user-table {
                font-size: 13px;
            }
            .user-table th,
            .user-table td {
                padding: 10px 12px;
            }
            .action-group {
                flex-direction: column;
                gap: 5px;
            }
            .btn-sm {
                font-size: 12px;
                padding: 5px 12px;
            }
            .modal-content {
                padding: 25px;
            }
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            .detail-item .value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="campaign.php">Campaigns</a>
        <a href="user.php" style="background: rgba(255,255,255,0.2);">Users</a>
        <a href="report.php">Export Report</a>
        <a href="log.php">Logs</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="welcome" style="background: linear-gradient(135deg, #00C3FF 0%, #0099cc 100%);">
            <h1>User Management</h1>
            <p>Manage all donors registered on the platform. View details or block/unblock users.</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success" style="margin-bottom: 20px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error" style="margin-bottom: 20px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- 统计卡片 -->
        <div class="user-stats">
            <div class="user-stat-card">
                <div class="stat-number"><?php echo $total_donors; ?></div>
                <div class="stat-label">Total Donors</div>
            </div>
            <div class="user-stat-card">
                <div class="stat-number"><?php echo $active_donors; ?></div>
                <div class="stat-label">Active Donors</div>
            </div>
            <div class="user-stat-card">
                <div class="stat-number"><?php echo $suspended_donors; ?></div>
                <div class="stat-label">Suspended Donors</div>
            </div>
        </div>
        
        <!-- 用户列表 -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2 style="margin: 0; border: none; padding: 0;">All Donors</h2>
                <div class="search-bar" style="margin: 0;">
                    <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="filterTable()">
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="table-container">
                <table class="user-table" id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Points</th>
                            <th>Badge</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if (mysqli_num_rows($donors_result) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($donors_result)): 
                                $status_class = ($user['Status'] == 'active') ? 'active' : 'suspended';
                            ?>
                                <tr class="user-row" data-status="<?php echo $user['Status']; ?>">
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['Name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php echo $user['Points'] ?? 0; ?></td>
                                    <td><?php echo $user['Badge'] ?? 'None'; ?></td>
                                    <td><span class="badge-status <?php echo $status_class; ?>"><?php echo ucfirst($user['Status']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($user['Register_Date'])); ?></td>
                                    <td>
                                        <div class="action-group">
                                            <button class="btn-sm btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">View</button>
                                            <?php if ($user['Status'] == 'active'): ?>
                                                <button class="btn-sm btn-block" onclick="confirmAction(<?php echo $user['id']; ?>, 'block')">Block</button>
                                            <?php else: ?>
                                                <button class="btn-sm btn-unblock" onclick="confirmAction(<?php echo $user['id']; ?>, 'unblock')">Unblock</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="icon">👥</div>
                                        <h3>No Donors Found</h3>
                                        <p>There are no registered donors on the platform yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="text-align: center; color: #888; font-size: 14px; margin-top: 20px;">
            Showing <span id="visibleCount"><?php echo $total_donors; ?></span> of <?php echo $total_donors; ?> donors
        </div>
    </div>
    
    <div class="modal" id="userDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Donor Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="userDetailContent">
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 60px; margin-bottom: 10px;">⏳</div>
                    <p style="color: #888;">Loading user data...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ============================================ -->
    <!-- 确认对话框 -->
    <!-- ============================================ -->
    <div class="modal" id="confirmModal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <div class="modal-header" style="border: none; padding-bottom: 0;">
                <h2 id="confirmTitle">Confirm Action</h2>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <p id="confirmMessage" style="color: #555; margin: 20px 0; font-size: 16px; line-height: 1.6;"></p>
            <div class="modal-actions" style="justify-content: center;">
                <button class="btn" style="background: #e0e0e0; color: #333; flex: 0.5;" onclick="closeConfirmModal()">Cancel</button>
                <a href="#" id="confirmLink" class="btn" style="flex: 0.5;">Confirm</a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.user-row');
            let visibleCount = 0;
            
            rows.forEach(function(row) {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                
                let show = true;
                
                if (searchInput !== '' && !name.includes(searchInput) && !email.includes(searchInput)) {
                    show = false;
                }
                if (statusFilter !== 'all' && status !== statusFilter) {
                    show = false;
                }
                
                if (show) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('visibleCount').textContent = visibleCount;
        }
        
        // ============================================
        // 2. 查看用户详情
        // ============================================
        function viewUser(userId) {
            const modal = document.getElementById('userDetailModal');
            const content = document.getElementById('userDetailContent');
            
            content.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 60px; margin-bottom: 10px;">⏳</div>
                    <p style="color: #888;">Loading user data...</p>
                </div>
            `;
            modal.classList.add('active');
            
            fetch(`get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        const statusBadge = user.Status === 'active' ? 'Active' : 'Suspended';
                        
                        content.innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div style="font-size: 60px;">😀</div>
                                <h3 style="margin: 10px 0 5px;">${escapeHtml(user.Name)}</h3>
                                <span class="badge-status ${user.Status}">${statusBadge}</span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="label">User ID</span>
                                <span class="value">#${user.id}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Email</span>
                                <span class="value">${escapeHtml(user.Email)}</span>
                            </div>
                            ${user.Phone ? `
                            <div class="detail-item">
                                <span class="label">Phone</span>
                                <span class="value">${escapeHtml(user.Phone)}</span>
                            </div>
                            ` : ''}
                            <div class="detail-item">
                                <span class="label">Points</span>
                                <span class="value">${user.Points ?? 0}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Badge</span>
                                <span class="value">${user.Badge || 'None'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Registered</span>
                                <span class="value">${formatDate(user.Register_Date)}</span>
                            </div>
                            
                            <div class="modal-actions">
                                ${user.Status === 'active' 
                                    ? `<a href="user.php?action=block&id=${user.id}" class="btn" style="background: #dc2626; color: white; text-align: center;">🚫 Block User</a>`
                                    : `<a href="user.php?action=unblock&id=${user.id}" class="btn" style="background: #22c55e; color: white; text-align: center;">🔓 Unblock User</a>`
                                }
                                <button class="btn" style="background: #e0e0e0; color: #333;" onclick="closeModal()">Close</button>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `
                            <div style="text-align: center; padding: 20px;">
                                <div style="font-size: 60px; margin-bottom: 10px;">❌</div>
                                <p style="color: #dc2626;">${data.message}</p>
                                <button class="btn-sm btn-view" style="margin-top: 15px;" onclick="closeModal()">Close</button>
                            </div>
                        `;
                    }
                })
                .catch(function(error) {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 60px; margin-bottom: 10px;">❌</div>
                            <p style="color: #dc2626;">Failed to load user data.</p>
                            <button class="btn-sm btn-view" style="margin-top: 15px;" onclick="closeModal()">Close</button>
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }
        
        // ============================================
        // 3. 确认操作
        // ============================================
        function confirmAction(userId, action) {
            const modal = document.getElementById('confirmModal');
            const title = document.getElementById('confirmTitle');
            const message = document.getElementById('confirmMessage');
            const link = document.getElementById('confirmLink');
            
            const actionText = action === 'block' ? 'Block' : 'Unblock';
            const actionColor = action === 'block' ? '#dc2626' : '#22c55e';
            
            title.textContent = `${actionText} User`;
            message.textContent = `Are you sure you want to ${actionText.toLowerCase()} this user? This action can be reversed later.`;
            link.textContent = actionText;
            link.style.background = actionColor;
            link.href = `user.php?action=${action}&id=${userId}`;
            
            modal.classList.add('active');
        }
        
        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }
        
        function closeModal() {
            document.getElementById('userDetailModal').classList.remove('active');
        }
        
        document.getElementById('userDetailModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeConfirmModal();
        });
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    </script>
</body>
</html>