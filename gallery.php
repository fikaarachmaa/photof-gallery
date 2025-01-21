<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil foto-foto milik user yang sedang login
$user_id = $_SESSION['user_id'];
$per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$sql = "SELECT * FROM photos WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT $start, $per_page";
$result = mysqli_query($conn, $sql);

// Hitung total halaman
$sql_count = "SELECT COUNT(*) as total FROM photos WHERE user_id = '$user_id'";
$count_result = mysqli_query($conn, $sql_count);
$count_row = mysqli_fetch_assoc($count_result);
$total_pages = ceil($count_row['total'] / $per_page);

include 'includes/header.php';
?>




<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>My Gallery</h2>
        </div>
        <div class="col text-end">
            <a href="upload.php" class="btn btn-primary">Upload New Photo</a>
        </div>
    </div>




    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-select" id="sortSelect">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="title">By Title</option>
            </select>
        </div>
    </div>

   
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3><?php echo mysqli_num_rows($result); ?></h3>
                    <p class="mb-0">Total Photos</p>
                </div>
            </div>
        </div>
    </div>

    <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="alert alert-info">
            You haven't uploaded any photos yet. <a href="upload.php">Upload your first photo!</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php while($photo = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <a href="uploads/<?php echo $photo['image_path']; ?>" 
                           data-fancybox="gallery" 
                           data-caption="<?php echo $photo['title']; ?>">
                            <img src="uploads/<?php echo $photo['image_path']; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $photo['title']; ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $photo['title']; ?></h5>
                            <p class="card-text"><?php echo $photo['description']; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Uploaded on <?php echo date('d M Y', strtotime($photo['created_at'])); ?>
                                </small>
                            </p>
                            <div class="btn-group">
                                <a href="edit.php?id=<?php echo $photo['id']; ?>" 
                                   class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete.php?id=<?php echo $photo['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 