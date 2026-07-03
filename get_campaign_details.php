<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing campaign ID']);
    exit();
}

$campaign_id = intval($_GET['id']);

$sql = "SELECT Campaign_ID, Admin_ID, Title, Description, Goal_Amount, Raised_Amount,
               Start_Date, End_Date, Status, Animal_Type, Animal_Count, Animal_Name,
               Animal_Age, Animal_Image, Shelter_Name, Shelter_Location,
               Medical_Need, Urgency_Level, Verified_By, Verified_At, Created_At
        FROM campaign WHERE Campaign_ID = $campaign_id";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) == 1) {
    $campaign = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'campaign' => $campaign]);
} else {
    echo json_encode(['success' => false, 'message' => 'Campaign not found']);
}
?>