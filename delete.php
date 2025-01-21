<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$photo_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if photo belongs to user
$sql = "SELECT image_path FROM photos WHERE id = '$photo_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0) {
    $photo = mysqli_fetch_assoc($result);
    
    // Delete image file
    unlink("uploads/" . $photo['image_path']);
    
    // Delete from database
    $sql = "DELETE FROM photos WHERE id = '$photo_id'";
    mysqli_query($conn, $sql);
}

header("Location: index.php");
exit();
?> 