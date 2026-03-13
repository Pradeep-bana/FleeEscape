<?php session_start();
include('link.php');
$pageTitle = 'FAQ | Flee Escape';
$metaKeywords = 'FAQ';
$metaDescription = 'FAQ';
$canonicalURL = $link."faq";
include('includes/header.php');
?>

<style>
.faq-section {
    box-shadow: none;
    border: none;
}
</style>

<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: url(https://images.squarespace-cdn.com/content/v1/55e536b5e4b00e62524eaf0b/1591616441967-WYWOA6SID6P4NCZ14W7Z/IMG_4112.jpg?format=2500w); height:450px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative;z-index: 1;">
                <h1>Frequently Asked Questions</h1>
            </div>
        </div>
    </div>
</section>

<?php
include("admin/db.php");

// Fetch all categories
$stmtCat = $pdo->query("SELECT id, category_name 
                        FROM tbl_faq_category
                        ORDER BY id ASC");
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Fetch all FAQs
$stmtFaq = $pdo->query("SELECT id, category_id, question, answer, status FROM tbl_faq WHERE status = 1 ORDER BY id ASC");
$faqs = $stmtFaq->fetchAll(PDO::FETCH_ASSOC);

// Group FAQs by category_id
$faqGrouped = [];
foreach ($faqs as $faq) {
    $faqGrouped[$faq['category_id']][] = $faq;
}
?>

<section class="faq-main-section py-5">
    <div class="container">
        <?php foreach ($categories as $cat): ?>
            <?php
            $catId = $cat['id'];
            $catName = htmlspecialchars($cat['category_name']);
            $faqList = isset($faqGrouped[$catId]) ? $faqGrouped[$catId] : [];
            if (empty($faqList)) continue; // skip empty categories
            ?>

            <div class="faq-section my-5">
                <h2 class="text-center mb-4"><?php echo $catName; ?></h2>

                <div class="accordion" id="faqAccordion_<?php echo $catId; ?>">
                    <?php
                    $count = 1;
                    foreach ($faqList as $faq):
                        $faqId = $faq['id'];
                        $question = htmlspecialchars($faq['question']);
                        $answer = $faq['answer']; // allow limited HTML inside answer
                        $collapseId = "faqCollapse_{$catId}_{$faqId}";
                        $headingId = "faqHeading_{$catId}_{$faqId}";
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#<?php echo $collapseId; ?>" 
                                        aria-expanded="false" 
                                        aria-controls="<?php echo $collapseId; ?>">
                                    <?php echo $count . ". " . $question; ?>
                                    <span class="faq-toggle-icon ms-auto">
                                        <span class="plus">+</span>
                                        <span class="minus" style="display:none;">−</span>
                                    </span>
                                </button>
                            </h2>
                            <div id="<?php echo $collapseId; ?>" 
                                 class="accordion-collapse collapse" 
                                 aria-labelledby="<?php echo $headingId; ?>" 
                                 data-bs-parent="#faqAccordion_<?php echo $catId; ?>">
                                <div class="accordion-body">
                                    <?php echo $answer; ?>
                                </div>
                            </div>
                        </div>
                        <?php $count++; endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>



<section class="adventure-section">
    <div class="container">
        <div class="adventure-content">
            <h2>Have more questions regarding your booking?</h2>
            <p>Our staff is always available to answer any queries over email or phone</p>
            <div class="all_button_main_header order_summart_main_button">
                <a href="contact-us" class="bg_bnt_custom">CONTACT US</a>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>