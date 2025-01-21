<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$photo_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if photo belongs to user
$sql = "SELECT * FROM photos WHERE id = '$photo_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$photo = mysqli_fetch_assoc($result);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    if(isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $uploadOk = 1;
        
        // Check if image file is actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            $error = "File bukan gambar.";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES["image"]["size"] > 5000000) {
            $error = "File terlalu besar.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg"
        && $file_extension != "gif" ) {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old image
                unlink("uploads/" . $photo['image_path']);
                
                $sql = "UPDATE photos SET title = '$title', description = '$description', 
                        image_path = '$new_filename' WHERE id = '$photo_id'";
            }
        }
    } else {
        $sql = "UPDATE photos SET title = '$title', description = '$description' 
                WHERE id = '$photo_id'";
    }
    
    if(mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Terjadi kesalahan! Silakan coba lagi.";
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Photo</div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo $photo['title']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo $photo['description']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Current Image</label>
                            <img src="uploads/<?php echo $photo['image_path']; ?>" class="img-thumbnail d-block" style="max-width: 200px">
                        </div>
                        <div class="mb-3">
                            <label>New Image (optional)</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 