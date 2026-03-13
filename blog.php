<?php  session_start();
include('link.php');
include('admin/db.php'); // adjust path if needed

// Get slug from URL
 $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug == '') {
    echo "<script>alert('Invalid blog request'); window.location.href='blog.php';</script>";
    exit;
}

// Fetch blog details
$stmt = $pdo->prepare("SELECT * FROM tbl_blogs WHERE `slug` = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<script>alert('Blog not found!'); window.location.href='blog.php';</script>";
    exit;
}

// Dynamic meta values from DB
$pageTitle = htmlspecialchars($row['page_title']);
$metaKeywords = htmlspecialchars($row['keywords']);
$metaDescription = htmlspecialchars($row['page_description']);
$canonicalURL = $link."blog/".$slug;
include('includes/header.php');



 $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
include('admin/db.php');
// Fetch blog details
$stmt = $pdo->prepare("SELECT * FROM tbl_blogs WHERE `slug` = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); 

?>

<style>
p, li, h3, h2, h4,h5{
    color: #fff!important;
}
.Areas_pages_data a span{
    color: #00d4ff!important;
}
</style>

<div class="Areas_pages_data">
    
    <h1><?php echo htmlspecialchars($row['title']); ?></h1>

    <p>
        <strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?> |
        <strong>Date:</strong> <?php echo date("M d, Y", strtotime($row['article_date'])); ?> |
        <strong>Read Time:</strong> <?php echo htmlspecialchars($row['read_minutes']); ?> min read
    </p>

    <hr>

    <?php if (!empty($row['degree_icon'])) { ?>
        <img src="<?php echo BASE_URL; ?>admin/uploads/<?php echo htmlspecialchars($row['degree_icon']); ?>" loading="lazy"  decoding="async"  alt="<?php echo htmlspecialchars($row['title']); ?>" class="img-fluid mb-4">
    <?php } ?>

    <div class="blog-detail-content">
        <?php echo $row['detail']; ?>
    </div>

    <hr>

    <h2>Other Blogs</h2>
    <ul>
        <?php
        $other = $pdo->prepare("SELECT title, slug FROM tbl_blogs WHERE slug != :slug ORDER BY id DESC LIMIT 5");
        $other->execute([':slug' => $slug]);
        while ($b = $other->fetch(PDO::FETCH_ASSOC)) {
            echo '<li><a href="'. BASE_URL .'/blog/' . htmlspecialchars($b['slug']) . '">' . htmlspecialchars($b['title']) . '</a></li>';
        }
        ?>
    </ul>
</div>


<?php include('includes/footer.php'); ?>
