<?php
session_start();
require_once 'config/database.php';

$sql = "SELECT p.*, u.username FROM photos p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);

include 'includes/header.php';
?>


<!-- Hero Section -->
<div class="hero-section text-center py-5" style="background: linear-gradient(45deg, #2193b0, #6dd5ed);"> <!-- Ocean Blue -->
    <div class="container">
        <h1 class="display-4 mb-4">Welcome to Photo Gallery</h1>
        <p class="lead mb-4">Discover and share amazing photos with our community</p>
       
    </div>
</div>


<!--SEARCH BAR-->
<!-- Tambahkan fungsi search di bagian PHP -->
<?php
// Fungsi untuk mencari foto
function searchPhotos($search = '') {
    global $conn;
    
    if(!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql = "SELECT p.*, u.username 
                FROM photos p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.title LIKE '%$search%' 
                OR p.description LIKE '%$search%' 
                ORDER BY p.created_at DESC";
    } else {
        $sql = "SELECT p.*, u.username 
                FROM photos p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC";
    }
    
    return mysqli_query($conn, $sql);
}

// Ambil parameter search jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';
$result = searchPhotos($search);
?>

<!-- Search Bar HTML -->
<div class="container mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="" method="GET" class="search-form">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-lg" 
                           placeholder="Search photos..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary btn-dark" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Search Results Info -->
    <?php if(!empty($search)): ?>
        <div class="row mt-3">
            <div class="col text-center">
                <p class="text-muted">
                    Found <?php echo mysqli_num_rows($result); ?> results for "<?php echo htmlspecialchars($search); ?>"
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Style untuk search bar -->
<style>
.search-form .input-group {
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    border-radius: 50px;
    overflow: hidden;
}

.search-form .form-control {
    border: none;
    padding: 15px 25px;
    font-size: 16px;
}

.search-form .form-control:focus {
    box-shadow: none;
}

.search-form .btn {
    padding: 12px 25px;
    border: none;
}

.search-form .btn-primary {
    background: linear-gradient(to right, #2c3e50, #3498db) ;
}

.search-form .btn-secondary {
    background: #6c757d;
}

/* Animasi */
.search-form .input-group:focus-within {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

/* Loading animation */
.search-form.searching .input-group::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(to right, #2c3e50, #3498db);
    animation: loading 1s infinite;
}

@keyframes loading {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-form .input-group {
        flex-direction: column;
        gap: 10px;
        padding: 10px;
    }
    
    .search-form .btn {
        width: 100%;
        border-radius: 25px;
    }
}
</style>

<!-- JavaScript untuk real-time search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = searchForm.querySelector('input[name="search"]');
    const photoGrid = document.getElementById('photoGrid');
    let searchTimeout;

    // Real-time search dengan debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchForm.classList.add('searching');
        
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.toLowerCase();
            const cards = document.getElementsByClassName('col-md-4');
            let foundCount = 0;

            Array.from(cards).forEach(card => {
                const title = card.querySelector('.card-title').innerText.toLowerCase();
                const description = card.querySelector('.card-text').innerText.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.5s ease forwards';
                    foundCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update results count
            updateResultsCount(foundCount, searchTerm);
            searchForm.classList.remove('searching');
        }, 300);
    });

    // Function to update results count
    function updateResultsCount(count, term) {
        let countElement = document.querySelector('.search-results-count');
        if (!countElement) {
            countElement = document.createElement('p');
            countElement.className = 'text-muted text-center mt-3 search-results-count';
            searchForm.parentNode.appendChild(countElement);
        }

        if (term) {
            countElement.textContent = `Found ${count} results for "${term}"`;
        } else {
            countElement.textContent = '';
        }
    }

    // Clear search
    const clearButton = document.querySelector('.btn-secondary');
    if (clearButton) {
        clearButton.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            window.location.href = 'index.php';
        });
    }
});
</script>
    

    <!-- Photo Grid -->
    <div class="row" id="photoGrid">
        <?php while($photo = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
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
                                By <?php echo $photo['username']; ?> | 
                                <?php echo date('d M Y', strtotime($photo['created_at'])); ?>
                            </small>
                        </p>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $photo['user_id']): ?>
                            <div class="d-flex gap-2">
                                <a href="edit.php?id=<?php echo $photo['id']; ?>" 
                                   class="btn btn-primary flex-fill">Edit</a>
                                <a href="delete.php?id=<?php echo $photo['id']; ?>" 
                                   class="btn btn-danger flex-fill" 
                                   onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Tambahkan CSS -->
<style>
.hero-section {
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                url('assets/images/hero-bg.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 100px 0;
    margin-bottom: 40px;
}

.card {
    transition: transform 0.2s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.btn {
    padding: 8px 20px;
    font-size: 14px;
}

.flex-fill {
    flex: 1 1 auto;
}

.gap-2 {
    gap: 0.5rem !important;
}
</style>














<div class="container mt-4">
    <div class="row">
        <?php while($photo = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                
                  
                  
                
                   <!-- Tambahkan data-fancybox dan href untuk Fancybox -->
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
                        <p class="card-text"><small class="text-muted">By <?php echo $photo['username']; ?></small></p>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $photo['user_id']): ?>
                            <a href="edit.php?id=<?php echo $photo['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete.php?id=<?php echo $photo['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 