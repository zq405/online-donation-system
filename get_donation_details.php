<?php
// get_donation_details.php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing donation ID']);
    exit();
}

$donation_id = intval($_GET['id']);

$sql = "SELECT d.*, dn.Name AS Donor_Name, dn.Email AS Donor_Email, c.Title AS Campaign_Title
        FROM donations d
        LEFT JOIN donors dn ON d.Donors_ID = dn.Donors_ID
        LEFT JOIN campaign c ON d.Campaign_ID = c.Campaign_ID
        WHERE d.Donation_ID = $donation_id";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) == 1) {
    $donation = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'donation' => $donation]);
} else {
    echo json_encode(['success' => false, 'message' => 'Donation not found']);
}
?>