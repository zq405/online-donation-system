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
        $error = "Please fill in all required fields (Title, Goal Amount, Start Date, End Date).";
    } elseif ($goal_amount <= 0) {
        $error = "Goal amount must be greater than 0.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = "End date must be after start date.";
    } else {
        $sql = "INSERT INTO campaign (
                    Admin_ID, Title, Description, Goal_Amount, Raised_Amount,
                    Start_Date, End_Date, Status, Animal_Type, Animal_Count,
                    Animal_Name, Animal_Age, Shelter_Name, Shelter_Location,
                    Shelter_Phone, Medical_Need, Urgency_Level, Created_At
                ) VALUES (
                    $admin_id, '$title', '$description', $goal_amount, 0,
                    '$start_date', '$end_date', 'pending', '$animal_type', $animal_count,
                    '$animal_name', '$animal_age', '$shelter_name', '$shelter_location',
                    '$shelter_phone', '$medical_need', '$urgency_level', NOW()
                )";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Campaign created successfully! It is now pending approval.";
            header("Location: campaign.php");
            exit();
        } else {
            $error = "Creation failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign - Animal Shelters House</title>
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
            <h2>Create New Campaign</h2>
            <p style="color: #666; margin-bottom: 20px;">Fill in the details below to create a new animal rescue campaign.</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-section-title">Basic Information</div>
                
                <div class="form-group">
                    <label>Campaign Title <span class="required">*</span></label>
                    <input type="text" name="title" placeholder="e.g., Save Max - Abandoned Dog Needs Surgery" required>
                </div>
                
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" placeholder="Describe the campaign, the animal's situation, and how donations will help..." required></textarea>
                </div>
                
                <div class="form-section-title">Financial & Date Details</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Goal Amount ($) <span class="required">*</span></label>
                        <input type="number" name="goal_amount" placeholder="5000" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Urgency Level</label>
                        <select name="urgency_level">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>
                
                <div class="form-section-title">Animal Information</div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Animal Type</label>
                        <select name="animal_type">
                            <option value="">Select...</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Rabbit">Rabbit</option>
                            <option value="Bird">Bird</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Animal Name</label>
                        <input type="text" name="animal_name" placeholder="e.g., Max">
                    </div>
                    <div class="form-group">
                        <label>Animal Age</label>
                        <input type="text" name="animal_age" placeholder="e.g., 3 years">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Number of Animals</label>
                    <input type="number" name="animal_count" value="1" min="1">
                </div>
                
                <div class="form-section-title">Shelter Information</div>
                
                <div class="form-group">
                    <label>Shelter Name</label>
                    <input type="text" name="shelter_name" placeholder="e.g., Happy Paws Shelter">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Shelter Location</label>
                        <input type="text" name="shelter_location" placeholder="e.g., Kuala Lumpur">
                    </div>
                    <div class="form-group">
                        <label>Shelter Phone</label>
                        <input type="tel" name="shelter_phone" placeholder="e.g., 012-3456789">
                    </div>
                </div>
                
                <div class="form-section-title">Medical & Special Needs</div>
                
                <div class="form-group">
                    <label>Medical Needs</label>
                    <textarea name="medical_need" placeholder="Describe any medical treatments, surgeries, or special care needed..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Create Campaign</button>
                    <a href="campaign.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>