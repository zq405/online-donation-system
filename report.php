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

if (isset($_GET['generate']) && isset($_GET['type'])) {
    $report_type = $_GET['type'];
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $report_data = [];
    $report_title = '';
    $headers = [];
    $rows = [];
    
    if ($report_type == 'campaign') {
        $report_title = 'Campaign Report';
        $headers = ['Campaign ID', 'Title', 'Animal Type', 'Shelter', 'Goal Amount', 'Raised Amount', 'Progress %', 'Status', 'Start Date', 'End Date'];
        
        $where = "1=1";
        if (!empty($date_from)) $where .= " AND Created_At >= '$date_from'";
        if (!empty($date_to)) $where .= " AND Created_At <= '$date_to 23:59:59'";
        if ($status != 'all') $where .= " AND Status = '$status'";
        
        $sql = "SELECT Campaign_ID, Title, Animal_Type, Shelter_Name, Goal_Amount, Raised_Amount, 
                       ROUND((Raised_Amount / Goal_Amount) * 100, 2) AS Progress,
                       Status, Start_Date, End_Date 
                FROM campaign 
                WHERE $where 
                ORDER BY Created_At DESC";
        
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                $row['Campaign_ID'],
                $row['Title'],
                $row['Animal_Type'] ?? 'N/A',
                $row['Shelter_Name'] ?? 'N/A',
                '$' . number_format($row['Goal_Amount'], 2),
                '$' . number_format($row['Raised_Amount'], 2),
                number_format($row['Progress'], 2) . '%',
                ucfirst($row['Status']),
                $row['Start_Date'],
                $row['End_Date']
            ];
        }
        
    } elseif ($report_type == 'donation') {
        $report_title = 'Donation Report';
        $headers = ['Donation ID', 'Donor Name', 'Donor Email', 'Campaign', 'Amount', 'Payment Method', 'Status', 'Date'];
        
        $where = "1=1";
        if (!empty($date_from)) $where .= " AND d.Created_At >= '$date_from'";
        if (!empty($date_to)) $where .= " AND d.Created_At <= '$date_to 23:59:59'";
        if ($status != 'all') $where .= " AND d.Status = '$status'";
        
        $sql = "SELECT d.Donation_ID, dn.Name AS Donor_Name, dn.Email AS Donor_Email, 
                       c.Title AS Campaign_Title, d.Amount, d.Payment_Method, d.Status, d.Created_At
                FROM donations d
                LEFT JOIN donors dn ON d.Donors_ID = dn.Donors_ID
                LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
                WHERE $where
                ORDER BY d.Created_At DESC";
        
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                $row['Donation_ID'],
                $row['Donor_Name'] ?? 'Anonymous',
                $row['Donor_Email'] ?? 'N/A',
                $row['Campaign_Title'] ?? 'N/A',
                '$' . number_format($row['Amount'], 2),
                $row['Payment_Method'] ?? 'N/A',
                ucfirst($row['Status']),
                date('d M Y H:i', strtotime($row['Created_At']))
            ];
        }
        
    } elseif ($report_type == 'user') {
        $report_title = 'User Report';
        $headers = ['User ID', 'Name', 'Email', 'Phone', 'Points', 'Badge', 'Status', 'Registered Date'];
        
        $where = "1=1";
        if (!empty($date_from)) $where .= " AND Register_Date >= '$date_from'";
        if (!empty($date_to)) $where .= " AND Register_Date <= '$date_to 23:59:59'";
        if ($status != 'all') $where .= " AND Status = '$status'";
        
        $sql = "SELECT Donors_ID, Name, Email, Phone, Points, Badge, Status, Register_Date 
                FROM donors 
                WHERE $where 
                ORDER BY Register_Date DESC";
        
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                $row['Donors_ID'],
                $row['Name'],
                $row['Email'],
                $row['Phone'] ?? 'N/A',
                $row['Points'] ?? 0,
                $row['Badge'] ?? 'None',
                ucfirst($row['Status']),
                date('d M Y', strtotime($row['Register_Date']))
            ];
        }
    }
    
    if (isset($_GET['export']) && $_GET['export'] == 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $report_title . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }
    
    if (isset($_GET['export']) && $_GET['export'] == 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $report_title . '_' . date('Y-m-d') . '.xls"');
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>' . $report_title . '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        echo '<body>';
        echo '<table border="1">';
        
        echo '<tr><th colspan="' . count($headers) . '" style="font-size:16px; font-weight:bold; text-align:center; background:#00C3FF; color:white;">' . $report_title . '</th></tr>';
        echo '<tr style="background:#e0e0e0; font-weight:bold;">';
        foreach ($headers as $header) {
            echo '<th>' . $header . '</th>';
        }
        echo '</tr>';
        
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</body></html>';
        exit();
    }
}

$total_campaigns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM campaign"))['count'] ?? 0;
$total_donations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations"))['count'] ?? 0;
$total_donors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donors"))['count'] ?? 0;
$total_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM donations WHERE Status='completed'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Report - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-bottom: 3px solid #00C3FF;
        }
        
        .report-stat-card .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .report-stat-card .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .report-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 195, 255, 0.15);
        }
        
        .report-card .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .report-card h3 {
            color: #333;
            margin-bottom: 8px;
        }
        
        .report-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .report-card .filter-group {
            margin: 15px 0;
        }
        
        .report-card .filter-group label {
            font-size: 13px;
            color: #666;
            font-weight: 500;
            display: block;
            margin-bottom: 3px;
        }
        
        .report-card .filter-group input,
        .report-card .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .report-card .filter-group input:focus,
        .report-card .filter-group select:focus {
            outline: none;
            border-color: #00C3FF;
            box-shadow: 0 0 0 3px rgba(0, 195, 255, 0.2);
        }
        
        .report-card .btn-export {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-right: 8px;
            margin-top: 5px;
        }
        
        .btn-export-csv {
            background: #00C3FF;
            color: white;
        }
        
        .btn-export-csv:hover {
            background: #0099cc;
            transform: translateY(-2px);
        }
        
        .btn-export-excel {
            background: #28a745;
            color: white;
        }
        
        .btn-export-excel:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        
        .btn-preview {
            background: #f59e0b;
            color: white;
        }
        
        .btn-preview:hover {
            background: #d97706;
            transform: translateY(-2px);
        }
        
        .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .report-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn-export {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="campaign.php">Campaigns</a>
        <a href="user.php">Users</a>
        <a href="report.php" style="background: rgba(255,255,255,0.2);">Export Report</a>
        <a href="verify_donations.php">Verify Donations</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="welcome" style="background: linear-gradient(135deg, #00C3FF 0%, #0099cc 100%);">
            <h1>Export Report</h1>
            <p>Generate and export reports for campaigns, donations, and users.</p>
        </div>
        
        <div class="report-stats">
            <div class="report-stat-card">
                <div class="stat-number"><?php echo $total_campaigns; ?></div>
                <div class="stat-label">Total Campaigns</div>
            </div>
            <div class="report-stat-card">
                <div class="stat-number"><?php echo $total_donations; ?></div>
                <div class="stat-label">Total Donations</div>
            </div>
            <div class="report-stat-card">
                <div class="stat-number">$<?php echo number_format($total_amount, 0); ?></div>
                <div class="stat-label">Total Funds Raised</div>
            </div>
            <div class="report-stat-card">
                <div class="stat-number"><?php echo $total_donors; ?></div>
                <div class="stat-label">Total Donors</div>
            </div>
        </div>
        
        <div class="report-grid">
            <div class="report-card">
                <h3>Campaign Report</h3>
                <p>Export all campaign data including status, goals, and progress.</p>
                
                <form method="GET" action="report.php">
                    <input type="hidden" name="generate" value="1">
                    <input type="hidden" name="type" value="campaign">
                    
                    <div class="filter-group">
                        <label>Date Range</label>
                        <div class="filter-row">
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-export btn-preview" formtarget="_blank">Preview</button>
                        <button type="submit" name="export" value="csv" class="btn-export btn-export-csv">CSV</button>
                        <button type="submit" name="export" value="excel" class="btn-export btn-export-excel">Excel</button>
                    </div>
                </form>
            </div>
            <div class="report-card">
                <h3>Donation Report</h3>
                <p>Export all donation transactions with donor and campaign details.</p>
                
                <form method="GET" action="report.php">
                    <input type="hidden" name="generate" value="1">
                    <input type="hidden" name="type" value="donation">
                    
                    <div class="filter-group">
                        <label>Date Range</label>
                        <div class="filter-row">
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-export btn-preview" formtarget="_blank">Preview</button>
                        <button type="submit" name="export" value="csv" class="btn-export btn-export-csv">CSV</button>
                        <button type="submit" name="export" value="excel" class="btn-export btn-export-excel">Excel</button>
                    </div>
                </form>
            </div>

            <div class="report-card">
                <h3>User Report</h3>
                <p>Export all donor information including points and badge levels.</p>
                
                <form method="GET" action="report.php">
                    <input type="hidden" name="generate" value="1">
                    <input type="hidden" name="type" value="user">
                    
                    <div class="filter-group">
                        <label>Date Range</label>
                        <div class="filter-row">
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-export btn-preview" formtarget="_blank">Preview</button>
                        <button type="submit" name="export" value="csv" class="btn-export btn-export-csv">CSV</button>
                        <button type="submit" name="export" value="excel" class="btn-export btn-export-excel">Excel</button>
                    </div>
                </form>
            </div>
            
        </div>

        <?php if (isset($_GET['generate']) && isset($_GET['type'])): 
            $report_type = $_GET['type'];
            $report_title = '';
            $headers = [];
            $rows = [];
            
            if ($report_type == 'campaign') {
                $report_title = 'Campaign Report';
                $headers = ['Campaign ID', 'Title', 'Animal Type', 'Shelter', 'Goal Amount', 'Raised Amount', 'Progress %', 'Status', 'Start Date', 'End Date'];
                
                $where = "1=1";
                if (!empty($_GET['date_from'])) $where .= " AND Created_At >= '" . $_GET['date_from'] . "'";
                if (!empty($_GET['date_to'])) $where .= " AND Created_At <= '" . $_GET['date_to'] . " 23:59:59'";
                if (!empty($_GET['status']) && $_GET['status'] != 'all') $where .= " AND Status = '" . $_GET['status'] . "'";
                
                $sql = "SELECT Campaign_ID, Title, Animal_Type, Shelter_Name, Goal_Amount, Raised_Amount, 
                               ROUND((Raised_Amount / Goal_Amount) * 100, 2) AS Progress,
                               Status, Start_Date, End_Date 
                        FROM campaign WHERE $where ORDER BY Created_At DESC";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $rows[] = [
                        $row['Campaign_ID'],
                        $row['Title'],
                        $row['Animal_Type'] ?? 'N/A',
                        $row['Shelter_Name'] ?? 'N/A',
                        '$' . number_format($row['Goal_Amount'], 2),
                        '$' . number_format($row['Raised_Amount'], 2),
                        number_format($row['Progress'], 2) . '%',
                        ucfirst($row['Status']),
                        $row['Start_Date'],
                        $row['End_Date']
                    ];
                }
                
            } elseif ($report_type == 'donation') {
                $report_title = 'Donation Report';
                $headers = ['Donation ID', 'Donor Name', 'Donor Email', 'Campaign', 'Amount', 'Payment Method', 'Status', 'Date'];
                
                $where = "1=1";
                if (!empty($_GET['date_from'])) $where .= " AND d.Created_At >= '" . $_GET['date_from'] . "'";
                if (!empty($_GET['date_to'])) $where .= " AND d.Created_At <= '" . $_GET['date_to'] . " 23:59:59'";
                if (!empty($_GET['status']) && $_GET['status'] != 'all') $where .= " AND d.Status = '" . $_GET['status'] . "'";
                
                $sql = "SELECT d.Donation_ID, dn.Name AS Donor_Name, dn.Email AS Donor_Email, 
                               c.Title AS Campaign_Title, d.Amount, d.Payment_Method, d.Status, d.Created_At
                        FROM donations d
                        LEFT JOIN donors dn ON d.Donors_ID = dn.Donors_ID
                        LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
                        WHERE $where ORDER BY d.Created_At DESC";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $rows[] = [
                        $row['Donation_ID'],
                        $row['Donor_Name'] ?? 'Anonymous',
                        $row['Donor_Email'] ?? 'N/A',
                        $row['Campaign_Title'] ?? 'N/A',
                        '$' . number_format($row['Amount'], 2),
                        $row['Payment_Method'] ?? 'N/A',
                        ucfirst($row['Status']),
                        date('d M Y H:i', strtotime($row['Created_At']))
                    ];
                }
                
            } elseif ($report_type == 'user') {
                $report_title = 'User Report';
                $headers = ['User ID', 'Name', 'Email', 'Phone', 'Points', 'Badge', 'Status', 'Registered Date'];
                
                $where = "1=1";
                if (!empty($_GET['date_from'])) $where .= " AND Register_Date >= '" . $_GET['date_from'] . "'";
                if (!empty($_GET['date_to'])) $where .= " AND Register_Date <= '" . $_GET['date_to'] . " 23:59:59'";
                if (!empty($_GET['status']) && $_GET['status'] != 'all') $where .= " AND Status = '" . $_GET['status'] . "'";
                
                $sql = "SELECT Donors_ID, Name, Email, Phone, Points, Badge, Status, Register_Date 
                        FROM donors WHERE $where ORDER BY Register_Date DESC";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $rows[] = [
                        $row['Donors_ID'],
                        $row['Name'],
                        $row['Email'],
                        $row['Phone'] ?? 'N/A',
                        $row['Points'] ?? 0,
                        $row['Badge'] ?? 'None',
                        ucfirst($row['Status']),
                        date('d M Y', strtotime($row['Register_Date']))
                    ];
                }
            }
        ?>
        
        <div class="card" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2 style="margin: 0; border: none; padding: 0;"><?php echo $report_title; ?></h2>
                <div style="display: flex; gap: 10px;">
                    <?php 
                        $params = $_GET;
                        unset($params['generate']);
                    ?>
                    <a href="report.php?<?php echo http_build_query(array_merge($params, ['export' => 'csv'])); ?>" class="btn-export btn-export-csv" style="padding: 8px 20px; text-decoration: none;">CSV</a>
                    <a href="report.php?<?php echo http_build_query(array_merge($params, ['export' => 'excel'])); ?>" class="btn-export btn-export-excel" style="padding: 8px 20px; text-decoration: none;">Excel</a>
                </div>
            </div>
            <p style="color: #888; font-size: 14px;">Showing <?php echo count($rows); ?> records</p>
            
            <div style="overflow-x: auto;">
                <table class="user-table" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th><?php echo $header; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($headers); ?>" style="text-align: center; padding: 30px; color: #888;">
                                    No records found for the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 30px; background: #f8f9fa;">
            <h2>Report Guide</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <h4>Campaign Report</h4>
                    <ul style="color: #666; font-size: 14px; list-style: none; padding: 0;">
                        <li>• All campaign details</li>
                        <li>• Progress percentage</li>
                        <li>• Status tracking</li>
                    </ul>
                </div>
                <div>
                    <h4>Donation Report</h4>
                    <ul style="color: #666; font-size: 14px; list-style: none; padding: 0;">
                        <li>• Transaction details</li>
                        <li>• Donor information</li>
                        <li>• Payment methods</li>
                    </ul>
                </div>
                <div>
                    <h4>User Report</h4>
                    <ul style="color: #666; font-size: 14px; list-style: none; padding: 0;">
                        <li>• Donor profiles</li>
                        <li>• Points & badges</li>
                        <li>• Account status</li>
                    </ul>
                </div>
                <div>
                    <h4>Export Options</h4>
                    <ul style="color: #666; font-size: 14px; list-style: none; padding: 0;">
                        <li>• CSV (Comma Separated)</li>
                        <li>• Excel (XLS format)</li>
                        <li>• Use filters to customize</li>
                    </ul>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>