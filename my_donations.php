<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['user_id'];

$sql = "SELECT d.Donation_ID, d.Amount, d.Payment_Method, d.Status, d.Created_At,
               d.Is_Anonymous, d.Donor_Message, c.Title AS Campaign_Title, c.Campaign_ID
        FROM donations d
        LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
        WHERE d.Donors_ID = $donor_id
        ORDER BY d.Created_At DESC";

$donations = mysqli_query($conn, $sql);
$total_donations = mysqli_num_rows($donations);

$stats_sql = "SELECT COUNT(*) as count, SUM(Amount) as total FROM donations WHERE Donors_ID = $donor_id AND Status = 'completed'";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);
$total_amount = $stats['total'] ?? 0;
$donation_count = $stats['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-row .stat-box {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        
        .stats-row .stat-box .number {
            font-size: 28px;
            font-weight: bold;
            color: #00C3FF;
        }
        
        .stats-row .stat-box .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
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
        
        .badge-status.completed { background: #d4edda; color: #155724; }
        .badge-status.pending { background: #fff3cd; color: #856404; }
        .badge-status.failed { background: #f8d7da; color: #721c24; }
        .badge-status.refunded { background: #e2e3e5; color: #383d41; }
        
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
        
        .empty-state .btn-donate {
            display: inline-block;
            padding: 12px 30px;
            background: #00C3FF;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .empty-state .btn-donate:hover {
            background: #0099cc;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
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
    <div class="nav">
        <a href="donor_dashboard.php">My Dashboard</a>
        <a href="browse_campaigns.php">Browse Campaigns</a>
        <a href="my_donations.php" style="background: rgba(255,255,255,0.2);">My Donations</a>
        <a href="profile.php">Profile</a>
        <a href="how_it_works.php">How It Works</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>My Donation History</h2>
            <p style="color: #666; margin-bottom: 20px;">Track all your contributions to animal welfare.</p>

            <div class="stats-row">
                <div class="stat-box">
                    <div class="number"><?php echo $donation_count; ?></div>
                    <div class="label">Total Donations</div>
                </div>
                <div class="stat-box">
                    <div class="number">$<?php echo number_format($total_amount, 0); ?></div>
                    <div class="label">Total Amount Donated</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?php echo $total_donations; ?></div>
                    <div class="label">All Transactions</div>
                </div>
            </div>
            
            <div class="table-container">
                <?php if ($total_donations > 0): ?>
                    <table class="donation-table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($donation = mysqli_fetch_assoc($donations)): ?>
                                <tr>
                                    <td>
                                        <a href="campaign_detail.php?id=<?php echo $donation['Campaign_ID']; ?>" style="color: #00C3FF; text-decoration: none; font-weight: 500;">
                                            <?php echo htmlspecialchars($donation['Campaign_Title'] ?? 'Unknown Campaign'); ?>
                                        </a>
                                    </td>
                                    <td>$<?php echo number_format($donation['Amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($donation['Payment_Method'] ?? 'N/A'); ?></td>
                                    <td><span class="badge-status <?php echo $donation['Status']; ?>"><?php echo ucfirst($donation['Status']); ?></span></td>
                                    <td><?php echo date('d M Y h:i A', strtotime($donation['Created_At'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">No donations yet</div>
                        <h3>You haven't made any donations</h3>
                        <p>Start making a difference today by supporting an animal rescue campaign.</p>
                        <a href="browse_campaigns.php" class="btn-donate">Browse Campaigns</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>