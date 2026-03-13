<?php session_start();
include('link.php');
$pageTitle = 'Gallery';
$metaKeywords = 'Gallery';
$metaDescription = 'Gallery';
$canonicalURL = $link."gallery";
include('includes/header.php');
?>

<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: url(https://images.squarespace-cdn.com/content/v1/55e536b5e4b00e62524eaf0b/1591616441967-WYWOA6SID6P4NCZ14W7Z/IMG_4112.jpg?format=2500w); height:450px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative;z-index: 1;">
                <h1>Explore Our Facility </h1>
                <p>Take a virtual tour of our state-of-the-art escape rooms, VR arena, and event spaces</p>
            </div>
        </div>
    </div>
</section>


<section>
    <div class="gaming-container Gallery_tabs Experience_Gallery" style="background: transparent; padding:0">
        <div class="container">
            <div class="section_heading_page text-center mb-5">
                <h2 class="section-title">Experience Gallery</h2>
                <p class="section-subtitle">Immersive themed adventures that challenge your mind</p>
            </div>
            <?php
// --- DB connection ---
include("admin/db.php");

// Fetch all gallery data
$stmt = $pdo->query("SELECT id, category, first_heading, second_heading, image FROM tbl_banner_images ORDER BY category, id ASC");
$galleryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group data by category
$grouped = [];
foreach ($galleryData as $row) {
    $cat = trim($row['category']);
    $grouped[$cat][] = $row;
}

// Normalize categories (keys)
$categories = array_keys($grouped);
?>
            <!-- Tabs Navigation -->
          <ul class="nav nav-tabs justify-content-center mb-4" id="galleryTabs" role="tablist">
    <?php
    $i = 0;
 foreach ($categories as $cat):
    $tabId = strtolower(str_replace(' ', '_', $cat)); // ID safe
    $catLabel = ucwords(str_replace('_', ' ', $cat)); // Display label beautiful
?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($i === 0) ? 'active' : ''; ?>" 
                id="<?php echo $tabId; ?>-tab" 
                data-bs-toggle="tab" 
                data-bs-target="#<?php echo $tabId; ?>" 
                type="button" 
                role="tab">
            <?php echo htmlspecialchars($catLabel); ?>
        </button>
    </li>
<?php $i++; endforeach; ?>
</ul>

<!-- Gallery Content -->
<div class="tab-content all-gallerry_dala_list" id="galleryTabContent">
    <?php
    $i = 0;
    foreach ($grouped as $category => $items):
        $tabId = strtolower(str_replace(' ', '_', $category));
        ?>
        <div class="tab-pane fade <?php echo ($i === 0) ? 'show active' : ''; ?>" id="<?php echo $tabId; ?>" role="tabpanel">
            <!--<div class="row g-4">-->
            <!--    <?php foreach ($items as $item): ?>-->
            <!--        <div class="col-md-3 col-6">-->
            <!--            <div class="gallery-item position-relative">-->
            <!--                <a href="admin/uploads/<?php echo htmlspecialchars($item['image']); ?>" data-fancybox="<?php echo $tabId; ?>">-->
            <!--                    <img src="admin/uploads/<?php echo htmlspecialchars($item['image']); ?>"-->
            <!--                     loading="lazy" decoding="async"-->
            <!--                    class="img-fluid rounded shadow-sm" -->
            <!--                         alt="<?php echo htmlspecialchars($item['first_heading']); ?>">-->
            <!--                    <div class="overlay"><i class="fa-solid fa-eye"></i></div>-->
            <!--                    <div class="overlay-text">-->
            <!--                        <h6><?php echo htmlspecialchars($item['first_heading']); ?></h6>-->
            <!--                        <p><?php echo htmlspecialchars($item['second_heading']); ?></p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    <?php endforeach; ?>-->
            <!--</div>-->
            <div class="row g-4 gallery-list" data-page="1">
    <?php foreach ($items as $item): ?>
        <div class="col-md-3 col-6 gallery-box">
            <div class="gallery-item position-relative">
                <a href="admin/uploads/<?php echo htmlspecialchars($item['image']); ?>" data-fancybox="<?php echo $tabId; ?>">
                    <img src="admin/uploads/<?php echo htmlspecialchars($item['image']); ?>"
                        loading="lazy" decoding="async"
                        class="img-fluid rounded shadow-sm"
                        alt="<?php echo htmlspecialchars($item['first_heading']); ?>">
                    <div class="overlay"><i class="fa-solid fa-eye"></i></div>
                    <div class="overlay-text">
                        <h6><?php echo htmlspecialchars($item['first_heading']); ?></h6>
                        <p><?php echo htmlspecialchars($item['second_heading']); ?></p>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<div class="gallery-pagination text-center mt-4"></div>
<style>
.gallery-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.gallery-pagination button {
    background: transparent!important;
    color: #fff;
    border: none;
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    border: 1px solid #00d4ff!important;
}

.gallery-pagination button.active {
    background: #00d4ff!important;
    color: #000;
    font-weight: 600;
}

.gallery-pagination button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.gallery-pagination .arrow {
    font-size: 18px;
    padding: 0 14px;
}


</style>
        </div>
    <?php $i++; endforeach; ?>
</div>

        </div>
    </div>
</section>


<section class="adventure-section">
    <div class="container">
        <div class="adventure-content">
            <h2>Ready to Experience the Adventure?</h2>
            <p>Book your escape room, VR experience, or party package today</p>
            <div class="all_button_main_header order_summart_main_button">
                <a href="booking" class="bg_bnt_custom">Book Now</a>
                <a href="https://maps.app.goo.gl/wLQBAEprUetD7t3U7" 
                       class="bg_bnt_custom bg_bnt_custom_tran" 
                       target="_blank" 
                       rel="noopener noreferrer">
                       <i class="fas fa-location-dot"></i> Visit Us
                    </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const ITEMS_PER_PAGE = 8;

    function initPagination(tabPane) {
        const items = tabPane.querySelectorAll(".gallery-box");
        const pagination = tabPane.querySelector(".gallery-pagination");

        if (!items.length || !pagination) return;

        let currentPage = 1;
        const totalPages = Math.ceil(items.length / ITEMS_PER_PAGE);

        function renderPage(page) {
            currentPage = page;

            items.forEach((item, index) => {
                item.style.display =
                    index >= (page - 1) * ITEMS_PER_PAGE &&
                    index < page * ITEMS_PER_PAGE
                        ? "block"
                        : "none";
            });

            renderButtons();
        }

        function renderButtons() {
            pagination.innerHTML = "";

            // ⬅ Prev Button
            const prevBtn = document.createElement("button");
            prevBtn.innerHTML = "&laquo;";
            prevBtn.classList.add("arrow");
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => renderPage(currentPage - 1);
            pagination.appendChild(prevBtn);

            // Page Numbers
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement("button");
                btn.innerText = i;
                btn.classList.toggle("active", i === currentPage);
                btn.onclick = () => renderPage(i);
                pagination.appendChild(btn);
            }

            // ➡ Next Button
            const nextBtn = document.createElement("button");
            nextBtn.innerHTML = "&raquo;";
            nextBtn.classList.add("arrow");
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => renderPage(currentPage + 1);
            pagination.appendChild(nextBtn);
        }

        renderPage(1);
    }

    // Init for all tabs
    document.querySelectorAll(".tab-pane").forEach(tab => {
        initPagination(tab);
    });

    // Re-init when tab changes
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabBtn => {
        tabBtn.addEventListener("shown.bs.tab", function (e) {
            const targetId = e.target.getAttribute("data-bs-target");
            const tabPane = document.querySelector(targetId);
            if (tabPane) initPagination(tabPane);
        });
    });

});
</script>



<?php include('includes/footer.php'); ?>