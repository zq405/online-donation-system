<?php
// get_user_details.php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user ID']);
    exit();
}

$user_id = intval($_GET['id']);

$sql = "SELECT Donors_ID AS id, Name, Email, Phone, Points, Badge, Register_Date, Status FROM donors WHERE Donors_ID = $user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Donor not found']);
}
?>