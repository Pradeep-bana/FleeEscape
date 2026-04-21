<?php  session_start();
include('link.php');
$pageTitle = 'Blog | Flee Escape';
$metaKeywords = 'Blog';
$metaDescription = 'Blog';
$canonicalURL = $link."blog-list";
include('includes/header.php');
?>
<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: url(https://images.squarespace-cdn.com/content/v1/55e536b5e4b00e62524eaf0b/1591616441967-WYWOA6SID6P4NCZ14W7Z/IMG_4112.jpg?format=2500w); height:450px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative;z-index: 1;">
                <h1>THE BLOG </h1>
                <p>Stories from Greater Seattle's premier escape room and VR entertainment destination. Discover
                    insights, <br> adventures, and behind-the-scenes content from our award-winning facility.</p>
            </div>
        </div>
    </div>
</section>

<style>
.blog_card_section .meta svg.lucide.lucide-clock.w-4.h-4 {
    width: 17px;
    position: relative;
    top: -2px;
}

.blog_card_section .meta i.fa-solid.fa-arrow-right {
    margin-left: 4px;
    font-size: 13px !important;
}

.blog-h1 {
    font-size: 40px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #0cdede;

}

.meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.date,
.h4title {
    display: inline-flex;
    align-items: center;
    margin: 0px 4px;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    margin: 1.25rem 0;
    justify-self: center;
}

.pagination a {
    text-decoration: none;
    color: #aaa;
    padding: 0.395rem 0.95rem;
    border: none;
    border-radius: 0.7rem;
    transition: all 0.3s;
    background: transparent;
}

.pagination a:hover {
    background: #00d4ff;
    color: white;
}

.pagination a.active {
    background: #00d4ff;
}

.pagination a:hover:not(.active) {
    background-color: #00d4ff;
}

.blog_card_section .ENTER_NEW_WORLD_img {
    height: 400px;
    margin-bottom: 50px;
}

@media (max-width: 768px) {
.blog_card_section .ENTER_NEW_WORLD_img {
    height: 184px;
    margin-bottom: 0;
}
.blog_list_data_nono .col-sm-6 {
    order: initial!important;
    border: none!important;
    padding: 0!important;
    margin-bottom: 0!important;
}
.blog_list_data_nono .col-sm-6:nth-child(even) {
    border-bottom: 2px solid red ; 
} 
.all_box_full_width .row .col-sm-6:nth-child(1), .all_box_full_width .row .col-sm-6:nth-child(4), .all_box_full_width .row .col-sm-6:nth-child(5) {
    margin-bottom: 20px;
    padding: 0px !important;
    border-bottom: 0px solid #0CDEDE;
    border-bottom-left-radius: 0px!important;
    border-bottom-right-radius: px!important;
    padding-top: 0 !important;
}
.blog_list_data_nono .col-sm-6:nth-child(even) {
    /* border-bottom: 2px solid red!important; */
    margin-bottom: 17px!important;
}
.all_box_full_width.blog_card_section {
    margin-top: 0!important;
}
}
</style>


    <section>
        <div class="all_box_full_width blog_card_section" style='margin-top:2rem;'>
           <?php
include('admin/db.php'); // adjust path if needed

// Pagination setup
$limit = 5; // blogs per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total count
$totalStmt = $pdo->query("SELECT COUNT(*) AS total FROM tbl_blogs");
$total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated blogs
$stmt = $pdo->prepare("SELECT id, title, category, read_minutes, detail, page_title, keywords, page_description, degree_icon, article_date, slug, created_at FROM tbl_blogs ORDER BY id DESC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container blog_list_data_nono">
  <div class="row align-items-center">
    <?php if (!empty($blogs)) { 
        $i = 0;
        foreach ($blogs as $blog) { 
            $i++;
    ?>
    <!-- -------BLOG ITEM------- -->
    <div class="col-sm-6" data-aos="fade-left" style='<?php echo $i > 1 ? "margin-top:1rem;" : ""; ?>'>
        <div class="ENTER_NEW_WORLD_img">
            <img src="<?php echo BASE_URL; ?>admin/uploads/<?php echo htmlspecialchars($blog['degree_icon']); ?>" loading="lazy"  decoding="async"  alt="<?php echo htmlspecialchars($blog['title']); ?>" class="img-fluid">
        </div>
    </div>

    <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
        <div class="ENTER_NEW_WORLD_Content">
            <a href="#" class="date">
                <?php echo !empty($blog['article_date']) ? date('M d Y', strtotime($blog['article_date'])) : ''; ?>
            </a>
            <p class="h4title" style='color: #aaa; font-size:1rem;'><?php echo htmlspecialchars($blog['category']); ?></p>
            <a href="<?php echo BASE_URL; ?>blog/<?php echo urlencode($blog['slug']); ?>">
                <h1 style='font-size:2rem;color:#00d4ff;text-transform:none;'><?php echo htmlspecialchars($blog['title']); ?>
                </h1>
            </a>
            <p style='color:#aaa'><?php echo substr(strip_tags($blog['detail']), 0, 160) . '...'; ?></p>
            <div class="meta">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-clock w-4 h-4">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <a href="#" class="date" style='color:#aaa;font-size:0.9rem;'><?php echo htmlspecialchars($blog['read_minutes']); ?> min read</a>
                </div>
                <a href="<?php echo BASE_URL; ?>blog/<?php echo urlencode($blog['slug']); ?>" class="date"
                    style='display:inline-flex; align-items:center; gap:0.5rem; color:#00d4ff; text-decoration:none;'>
                    Read More 
                    <i class="fa-solid fa-arrow-right" style="font-size:1rem; vertical-align:middle;"></i>
                </a>
            </div>
        </div>
    </div>
    <?php } } else { ?>
        <div class="col-12 text-center">
            <p style="color:#aaa;">No blogs found.</p>
        </div>
    <?php } ?>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <?php if ($page > 1) { ?>
      <a href="?page=<?php echo $page - 1; ?>" style='border: solid 1px #aaa;'>Prev</a>
    <?php } ?>
    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
      <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? "style='border: solid 1px #00d4ff; color:#00d4ff;'" : ""; ?>>
        <?php echo $i; ?>
      </a>
    <?php } ?>
    <?php if ($page < $total_pages) { ?>
      <a href="?page=<?php echo $page + 1; ?>" style='border: solid 1px #aaa;'>Next</a>
    <?php } ?>
  </div>
</div>

    </section>
    <?php include('includes/footer.php'); ?>