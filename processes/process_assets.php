<?php
session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['submit_assets'])) {
    // Process asset return
    $exit_request_id = $_POST['exit_request_id'];
    $laptop_returned = isset($_POST['laptop_returned']) ? 1 : 0;
    $id_card_returned = isset($_POST['id_card_returned']) ? 1 : 0;
    $other_assets = $_POST['other_assets'];
    $return_date = $_POST['return_date'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO asset_returns (exit_request_id, laptop_returned, id_card_returned, other_assets, return_date) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$exit_request_id, $laptop_returned, $id_card_returned, $other_assets, $return_date]);
        
        $_SESSION['message'] = "Asset return recorded successfully";
        header("Location: ../asset_return.php?exit_id=".$exit_request_id);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../asset_return.php?exit_id=".$exit_request_id);
        exit;
    }
}
?>