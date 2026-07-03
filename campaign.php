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

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $campaign_id = intval($_GET['id']);
    
    if ($action == 'delete') {
        $sql = "DELETE FROM campaign WHERE Campaign_ID = $campaign_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign deleted successfully!";
        } else {
            $_SESSION['error'] = "Delete failed: " . mysqli_error($conn);
        }
    } elseif ($action == 'approve') {
        $sql = "UPDATE campaign SET Status = 'active', Verified_By = $admin_id, Verified_At = NOW() WHERE Campaign_ID = $campaign_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign approved successfully!";
        } else {
            $_SESSION['error'] = "Approval failed: " . mysqli_error($conn);
        }
    } elseif ($action == 'reject') {
        $sql = "UPDATE campaign SET Status = 'rejected', Verified_By = $admin_id, Verified_At = NOW() WHERE Campaign_ID = $campaign_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign rejected.";
        } else {
            $_SESSION['error'] = "Rejection failed: " . mysqli_error($conn);
        }
    } elseif ($action == 'complete') {
        $sql = "UPDATE campaign SET Status = 'completed' WHERE Campaign_ID = $campaign_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign marked as completed!";
        } else {
            $_SESSION['error'] = "Update failed: " . mysqli_error($conn);
        }
    }
    
    header("Location: campaign.php");
    exit();
}


$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';


$where_conditions = [];
if ($status_filter != 'all') {
    $where_conditions[] = "Status = '$status_filter'";
}
if (!empty($search_query)) {
    $where_conditions[] = "(Title LIKE '%$search_query%' OR Description LIKE '%$search_query%' OR Shelter_Name LIKE '%$search_query%')";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql = "SELECT Campaign_ID, Admin_ID, Title, Description, Goal_Amount, Raised_Amount, 
               Start_Date, End_Date, Status, Animal_Type, Animal_Count, Animal_Name, 
               Animal_Age, Animal_Image, Shelter_Name, Shelter_Location, 
               Medical_Need, Urgency_Level, Verified_By, Verified_At, Created_At 
        FROM campaign 
        $where_clause 
        ORDER BY Created_At DESC";

$campaigns_result = mysqli_query($conn, $sql);


$total_campaigns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM campaign"))['count'] ?? 0;
$active_campaigns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM campaign WHERE Status='active' AND End_Date > CURDATE()"))['count'] ?? 0;
$pending_campaigns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM campaign WHERE Status='pending'"))['count'] ?? 0;
$completed_campaigns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM campaign WHERE Status='completed'"))['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Management - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .campaign-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .campaign-stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-bottom: 3px solid #00C3FF;
        }
        
        .campaign-stat-card .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .campaign-stat-card .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .campaign-stat-card.pending { border-bottom-color: #f59e0b; }
        .campaign-stat-card.active { border-bottom-color: #22c55e; }
        .campaign-stat-card.completed { border-bottom-color: #8b5cf6; }
        
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
            transition: all 0.3s ease;
        }
        
        .filter-bar input[type="text"]:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
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
            transform: translateY(-2px);
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
        
        .campaign-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .campaign-table th {
            background: #00C3FF;
            color: white;
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .campaign-table td {
            padding: 12px 18px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .campaign-table tr:hover td {
            background: #f8f9fa;
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
        
        .badge-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-status.completed {
            background: #e8d5f5;
            color: #6b21a8;
        }
        
        .badge-status.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-status.cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .urgency-badge {
            padding: 3px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .urgency-badge.low { background: #d4edda; color: #155724; }
        .urgency-badge.normal { background: #cce5ff; color: #004085; }
        .urgency-badge.high { background: #fff3cd; color: #856404; }
        .urgency-badge.urgent { background: #f8d7da; color: #721c24; }
        
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
        
        .btn-view {
            background: #00C3FF;
            color: white;
        }
        .btn-view:hover { background: #0099cc; }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
        }
        .btn-edit:hover { background: #d97706; }
        
        .btn-approve {
            background: #22c55e;
            color: white;
        }
        .btn-approve:hover { background: #16a34a; }
        
        .btn-reject {
            background: #dc2626;
            color: white;
        }
        .btn-reject:hover { background: #b91c1c; }
        
        .btn-complete {
            background: #8b5cf6;
            color: white;
        }
        .btn-complete:hover { background: #7c3aed; }
        
        .btn-delete {
            background: #6b7280;
            color: white;
        }
        .btn-delete:hover { background: #4b5563; }
        
        .action-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
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
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
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
            padding: 10px 0;
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
            max-width: 60%;
        }
        
        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .modal-actions .btn {
            flex: 1;
            min-width: 80px;
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
        
        .modal .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .modal .progress-fill {
            background: #00C3FF;
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            font-weight: 600;
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
        
        @media (max-width: 768px) {
            .campaign-table {
                font-size: 13px;
            }
            .campaign-table th,
            .campaign-table td {
                padding: 10px 12px;
            }
            .filter-bar {
                flex-direction: column;
            }
            .filter-bar input[type="text"],
            .filter-bar select {
                width: 100%;
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
                max-width: 100%;
            }
            .action-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php"> Dashboard</a>
        <a href="campaign.php" style="background: rgba(255,255,255,0.2);">Campaigns</a>
        <a href="user.php">Users</a>
        <a href="report.php">Export Report</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="welcome" style="background: linear-gradient(135deg, #00C3FF 0%, #0099cc 100%);">
            <h1>Campaign Management</h1>
            <p>Create, manage, and monitor animal rescue campaigns. All campaigns are listed below.</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success" style="margin-bottom: 20px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error" style="margin-bottom: 20px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="campaign-stats">
            <div class="campaign-stat-card">
                <div class="stat-number"><?php echo $total_campaigns; ?></div>
                <div class="stat-label">Total Campaigns</div>
            </div>
            <div class="campaign-stat-card pending">
                <div class="stat-number"><?php echo $pending_campaigns; ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
            <div class="campaign-stat-card active">
                <div class="stat-number"><?php echo $active_campaigns; ?></div>
                <div class="stat-label">Active Campaigns</div>
            </div>
            <div class="campaign-stat-card completed">
                <div class="stat-number"><?php echo $completed_campaigns; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <button class="btn-primary" style="padding: 12px 30px; border-radius: 30px; width: auto; display: inline-flex; align-items: center; gap: 8px;" onclick="window.location.href='create_campaign.php'">
                Create New Campaign
            </button>
        </div>
        
        <div class="filter-bar">
            <form method="GET" action="campaign.php" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%; align-items: center;">
                <input type="text" name="search" placeholder="🔍 Search campaigns..." value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="status">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" class="btn-filter">Filter</button>
                <a href="campaign.php" class="btn-filter reset" style="padding: 10px 25px; background: #e0e0e0; color: #333; border-radius: 12px; text-decoration: none; font-weight: 600;">↺ Reset</a>
            </form>
        </div>
        
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 15px 25px; border-bottom: 1px solid #eee;">
                <h2 style="margin: 0; border: none; padding: 0;">Campaign List</h2>
            </div>
            
            <div class="table-container">
                <table class="campaign-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Animal</th>
                            <th>Shelter</th>
                            <th>Goal</th>
                            <th>Raised</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($campaigns_result) > 0): ?>
                            <?php while ($campaign = mysqli_fetch_assoc($campaigns_result)): 
                                $progress = ($campaign['Raised_Amount'] / $campaign['Goal_Amount']) * 100;
                            ?>
                                <tr>
                                    <td>#<?php echo $campaign['Campaign_ID']; ?></td>
                                    <td><strong><?php echo htmlspecialchars(substr($campaign['Title'], 0, 30)); ?></strong></td>
                                    <td><?php echo htmlspecialchars($campaign['Shelter_Name'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format($campaign['Goal_Amount'], 0); ?></td>
                                    <td>$<?php echo number_format($campaign['Raised_Amount'], 0); ?></td>
                                    <td><span class="badge-status <?php echo $campaign['Status']; ?>"><?php echo ucfirst($campaign['Status']); ?></span></td>
                                    <td>
                                        <div class="action-group">
                                            <button class="btn-sm btn-view" onclick="viewCampaign(<?php echo $campaign['Campaign_ID']; ?>)">View</button>
                                            <button class="btn-sm btn-edit" onclick="editCampaign(<?php echo $campaign['Campaign_ID']; ?>)">Edit</button>
                                            
                                            <?php if ($campaign['Status'] == 'pending'): ?>
                                                <button class="btn-sm btn-approve" onclick="confirmAction(<?php echo $campaign['Campaign_ID']; ?>, 'approve')">Approve</button>
                                                <button class="btn-sm btn-reject" onclick="confirmAction(<?php echo $campaign['Campaign_ID']; ?>, 'reject')">Reject</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($campaign['Status'] == 'active'): ?>
                                                <button class="btn-sm btn-complete" onclick="confirmAction(<?php echo $campaign['Campaign_ID']; ?>, 'complete')">Complete</button>
                                            <?php endif; ?>
                                            
                                            <button class="btn-sm btn-delete" onclick="confirmAction(<?php echo $campaign['Campaign_ID']; ?>, 'delete')">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="icon">🐾</div>
                                        <h3>No Campaigns Found</h3>
                                        <p>Try adjusting your search or filter criteria.</p>
                                        <button class="btn-primary" style="padding: 10px 25px; border-radius: 30px; width: auto; margin-top: 15px;" onclick="window.location.href='create_campaign.php'">Create First Campaign</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="text-align: center; color: #888; font-size: 14px; margin-top: 20px;">
            Showing <?php echo mysqli_num_rows($campaigns_result); ?> campaigns
        </div>
    </div>
    
    <div class="modal" id="campaignDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Campaign Details</h2>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div id="campaignDetailContent">
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 60px; margin-bottom: 10px;">⏳</div>
                    <p style="color: #888;">Loading campaign data...</p>
                </div>
            </div>
        </div>
    </div>
    

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
        function viewCampaign(campaignId) {
            const modal = document.getElementById('campaignDetailModal');
            const content = document.getElementById('campaignDetailContent');
            
            content.innerHTML = `<div style="text-align: center; padding: 20px;"><div style="font-size: 60px;">Pending</div><p style="color: #888;">Loading...</p></div>`;
            modal.classList.add('active');
            
            fetch(`get_campaign_details.php?id=${campaignId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const c = data.campaign;
                        const progress = (c.Raised_Amount / c.Goal_Amount) * 100;
                        const statusBadge = `<span class="badge-status ${c.Status}">${ucfirst(c.Status)}</span>`;
                        
                        content.innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px;">
                                <h3 style="margin: 0 0 5px;">${escapeHtml(c.Title)}</h3>
                                <p style="color: #666; font-size: 14px;">${statusBadge}</p>
                            </div>
                            
                            <div class="detail-item">
                                <span class="label">🆔 Campaign ID</span>
                                <span class="value">#${c.Campaign_ID}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">🐾 Animal Type</span>
                                <span class="value">${escapeHtml(c.Animal_Type || 'N/A')}</span>
                            </div>
                            ${c.Animal_Name ? `<div class="detail-item"><span class="label">Animal Name</span><span class="value">${escapeHtml(c.Animal_Name)}</span></div>` : ''}
                            ${c.Animal_Count ? `<div class="detail-item"><span class="label">Animal Count</span><span class="value">${c.Animal_Count}</span></div>` : ''}
                            <div class="detail-item">
                                <span class="label">Shelter</span>
                                <span class="value">${escapeHtml(c.Shelter_Name || 'N/A')}</span>
                            </div>
                            ${c.Shelter_Location ? `<div class="detail-item"><span class="label">Location</span><span class="value">${escapeHtml(c.Shelter_Location)}</span></div>` : ''}
                            <div class="detail-item">
                                <span class="label">💰 Goal Amount</span>
                                <span class="value">$${numberFormat(c.Goal_Amount)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Raised Amount</span>
                                <span class="value">$${numberFormat(c.Raised_Amount)}</span>
                            </div>
                            <div style="padding: 10px 0;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${Math.min(progress, 100)}%;">${Math.round(progress)}%</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <span class="label">Start Date</span>
                                <span class="value">${formatDate(c.Start_Date)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">End Date</span>
                                <span class="value">${formatDate(c.End_Date)}</span>
                            </div>
                            ${c.Medical_Need ? `<div class="detail-item" style="flex-direction: column; align-items: stretch; gap: 5px;"><span class="label">💊 Medical Need</span><span class="value" style="text-align: left; max-width: 100%;">${escapeHtml(c.Medical_Need)}</span></div>` : ''}
                            <div class="detail-item">
                                <span class="label">Created</span>
                                <span class="value">${formatDateTime(c.Created_At)}</span>
                            </div>
                            
                            <div class="modal-actions">
                                <button class="btn" style="background: #e0e0e0; color: #333;" onclick="closeDetailModal()">Close</button>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `
                            <div style="text-align: center; padding: 20px;">
                                <div style="font-size: 60px;">❌</div>
                                <p style="color: #dc2626;">${data.message}</p>
                                <button class="btn-sm btn-view" style="margin-top: 15px;" onclick="closeDetailModal()">Close</button>
                            </div>
                        `;
                    }
                })
                .catch(() => {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 60px;">❌</div>
                            <p style="color: #dc2626;">Failed to load campaign data.</p>
                            <button class="btn-sm btn-view" style="margin-top: 15px;" onclick="closeDetailModal()">Close</button>
                        </div>
                    `;
                });
        }
        
        function editCampaign(campaignId) {
            window.location.href = `edit_campaign.php?id=${campaignId}`;
        }
        
        function confirmAction(campaignId, action) {
            const modal = document.getElementById('confirmModal');
            const title = document.getElementById('confirmTitle');
            const message = document.getElementById('confirmMessage');
            const link = document.getElementById('confirmLink');
            
            const actionMap = {
                'delete': { text: 'Delete', color: '#6b7280', msg: 'Are you sure you want to delete this campaign? This action cannot be undone!' },
                'approve': { text: 'Approve', color: '#22c55e', msg: 'Approve this campaign and make it visible to donors?' },
                'reject': { text: 'Reject', color: '#dc2626', msg: 'Reject this campaign? The organizer will be notified.' },
                'complete': { text: 'Complete', color: '#8b5cf6', msg: 'Mark this campaign as completed?' }
            };
            
            const info = actionMap[action] || actionMap['delete'];
            
            title.textContent = `${info.text} Campaign`;
            message.textContent = info.msg;
            link.textContent = info.text;
            link.style.background = info.color;
            link.href = `campaign.php?action=${action}&id=${campaignId}`;
            
            modal.classList.add('active');
        }
        
        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }
        
        function closeDetailModal() {
            document.getElementById('campaignDetailModal').classList.remove('active');
        }
        
        document.getElementById('campaignDetailModal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
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
        
        function ucfirst(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        function numberFormat(num) {
            return Number(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>
</html>