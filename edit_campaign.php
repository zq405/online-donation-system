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

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No campaign specified.";
    header("Location: campaign.php");
    exit();
}

$campaign_id = intval($_GET['id']);

$sql = "SELECT * FROM campaign WHERE Campaign_ID = $campaign_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Campaign not found.";
    header("Location: campaign.php");
    exit();
}

$campaign = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $goal_amount = floatval($_POST['goal_amount']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    
    $animal_type = mysqli_real_escape_string($conn, $_POST['animal_type'] ?? '');
    $animal_count = intval($_POST['animal_count'] ?? 1);
    $animal_name = mysqli_real_escape_string($conn, $_POST['animal_name'] ?? '');
    $animal_age = mysqli_real_escape_string($conn, $_POST['animal_age'] ?? '');
    
    $shelter_name = mysqli_real_escape_string($conn, $_POST['shelter_name'] ?? '');
    $shelter_location = mysqli_real_escape_string($conn, $_POST['shelter_location'] ?? '');
    $shelter_phone = mysqli_real_escape_string($conn, $_POST['shelter_phone'] ?? '');
    
    $medical_need = mysqli_real_escape_string($conn, $_POST['medical_need'] ?? '');
    $urgency_level = mysqli_real_escape_string($conn, $_POST['urgency_level'] ?? 'normal');
    
    if (empty($title) || empty($goal_amount) || empty($start_date) || empty($end_date)) {
        $error = "Please fill in all required fields.";
    } elseif ($goal_amount <= 0) {
        $error = "Goal amount must be greater than 0.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = "End date must be after start date.";
    } else {
        $sql = "UPDATE campaign SET 
                    Title = '$title',
                    Description = '$description',
                    Goal_Amount = $goal_amount,
                    Start_Date = '$start_date',
                    End_Date = '$end_date',
                    Animal_Type = '$animal_type',
                    Animal_Count = $animal_count,
                    Animal_Name = '$animal_name',
                    Animal_Age = '$animal_age',
                    Shelter_Name = '$shelter_name',
                    Shelter_Location = '$shelter_location',
                    Shelter_Phone = '$shelter_phone',
                    Medical_Need = '$medical_need',
                    Urgency_Level = '$urgency_level',
                    Updated_At = NOW()
                WHERE Campaign_ID = $campaign_id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign updated successfully!";
            header("Location: campaign.php");
            exit();
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
    <title>Edit Campaign - Animal Shelters House</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group label .required {
            color: #dc2626;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
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
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            padding: 14px 40px;
            background: #00C3FF;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: auto;
        }
        
        .btn-submit:hover {
            background: #0099cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 195, 255, 0.4);
        }
        
        .btn-cancel {
            padding: 14px 40px;
            background: #e0e0e0;
            color: #333;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: auto;
        }
        
        .btn-cancel:hover {
            background: #ccc;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .form-section-title {
            color: #00C3FF;
            font-size: 18px;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .status-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .status-info .label {
            color: #666;
            font-weight: 500;
        }
        
        .status-info .value {
            font-weight: 600;
        }
        
        .hint {
            font-size: 13px;
            color: #888;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn-submit,
            .form-actions .btn-cancel {
                width: 100%;
                text-align: center;
            }
            
            .status-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="campaign.php" style="background: rgba(255,255,255,0.2);">Campaigns</a>
        <a href="user.php">Users</a>
        <a href="report.php">Export Report</a>
        <a href="verify_donations.php">Verify Donations</a>
        <a href="mainpage-testing.php">Main Page</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <div class="container">
        <div class="card form-container">
            <h2>Edit Campaign</h2>
            <p style="color: #666; margin-bottom: 20px;">Update the details of this campaign.</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="status-info">
                <span><span class="label">Status:</span> <span class="value"><span class="badge-status <?php echo $campaign['Status']; ?>"><?php echo ucfirst($campaign['Status']); ?></span></span></span>
                <span><span class="label">Raised:</span> <span class="value">$<?php echo number_format($campaign['Raised_Amount'], 2); ?></span></span>
                <span><span class="label">Created:</span> <span class="value"><?php echo date('d M Y', strtotime($campaign['Created_At'])); ?></span></span>
            </div>
            
            <form method="POST" action="">
                <div class="form-section-title">Basic Information</div>
                
                <div class="form-group">
                    <label>Campaign Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($campaign['Title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" required><?php echo htmlspecialchars($campaign['Description']); ?></textarea>
                </div>
                
                <div class="form-section-title">Financial & Date Details</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Goal Amount ($) <span class="required">*</span></label>
                        <input type="number" name="goal_amount" value="<?php echo $campaign['Goal_Amount']; ?>" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Urgency Level</label>
                        <select name="urgency_level">
                            <option value="low" <?php echo $campaign['Urgency_Level'] == 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="normal" <?php echo $campaign['Urgency_Level'] == 'normal' ? 'selected' : ''; ?>> Normal</option>
                            <option value="high" <?php echo $campaign['Urgency_Level'] == 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $campaign['Urgency_Level'] == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" value="<?php echo $campaign['Start_Date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" value="<?php echo $campaign['End_Date']; ?>" required>
                    </div>
                </div>
                
                <div class="form-section-title">Animal Information</div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Animal Type</label>
                        <select name="animal_type">
                            <option value="">Select...</option>
                            <option value="Dog" <?php echo $campaign['Animal_Type'] == 'Dog' ? 'selected' : ''; ?>>Dog</option>
                            <option value="Cat" <?php echo $campaign['Animal_Type'] == 'Cat' ? 'selected' : ''; ?>>Cat</option>
                            <option value="Rabbit" <?php echo $campaign['Animal_Type'] == 'Rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                            <option value="Bird" <?php echo $campaign['Animal_Type'] == 'Bird' ? 'selected' : ''; ?>>Bird</option>
                            <option value="Other" <?php echo $campaign['Animal_Type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Animal Name</label>
                        <input type="text" name="animal_name" value="<?php echo htmlspecialchars($campaign['Animal_Name'] ?? ''); ?>" placeholder="e.g., Max">
                    </div>
                    <div class="form-group">
                        <label>Animal Age</label>
                        <input type="text" name="animal_age" value="<?php echo htmlspecialchars($campaign['Animal_Age'] ?? ''); ?>" placeholder="e.g., 3 years">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Number of Animals</label>
                    <input type="number" name="animal_count" value="<?php echo $campaign['Animal_Count'] ?? 1; ?>" min="1">
                </div>
                
                <div class="form-section-title">Shelter Information</div>
                
                <div class="form-group">
                    <label>Shelter Name</label>
                    <input type="text" name="shelter_name" value="<?php echo htmlspecialchars($campaign['Shelter_Name'] ?? ''); ?>" placeholder="e.g., Happy Paws Shelter">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Shelter Location</label>
                        <input type="text" name="shelter_location" value="<?php echo htmlspecialchars($campaign['Shelter_Location'] ?? ''); ?>" placeholder="e.g., Kuala Lumpur">
                    </div>
                    <div class="form-group">
                        <label>Shelter Phone</label>
                        <input type="tel" name="shelter_phone" value="<?php echo htmlspecialchars($campaign['Shelter_Phone'] ?? ''); ?>" placeholder="e.g., 012-3456789">
                    </div>
                </div>
                
                <div class="form-section-title">Medical & Special Needs</div>
                
                <div class="form-group">
                    <label>Medical Needs</label>
                    <textarea name="medical_need" placeholder="Describe any medical treatments, surgeries, or special care needed..."><?php echo htmlspecialchars($campaign['Medical_Need'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a href="campaign.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>