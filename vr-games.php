<div class="home_vr_game_tab_show">
   <?php
include "admin/db.php";

$sql = "SELECT * FROM vr_experiences WHERE is_active = 1 ORDER BY id ASC";
// $sql = "SELECT * FROM vr_experiences  ORDER BY id asc";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
<?php foreach ($games as $g): ?>

    <div class="col-md-6 col-sm-12">
        <div class="booking_card">
            <div>
            <!-- Image Section -->
            <div class="booking_card_img">
                <img src="admin/<?= htmlspecialchars($g['logo_image']) ?>" loading="lazy" alt="<?= htmlspecialchars($g['title']) ?>" />
                <div class="booking_card_time_and_price">
                    <p></p>
                    <p id="price-41551F9C679173BC114D28">$<?= htmlspecialchars($g['price']) ?>/Guest</p>
                </div>
                <div class="booking_card_overlay">
                    <h5 class="d-flex align-items-center justify-content-between">
                        <span><?= htmlspecialchars($g['title']) ?></span>

                      
                    </h5>

                    <p><?= htmlspecialchars($g['tagline']) ?></p>

                    <div class="player-count-display d-flex align-items-center">
                        <div>
                            <span class="player-label">
                                <img class="palyear_tem_img" src="./assets/images/fleeescape_img/teampay.png" loading="lazy" alt="">
                            </span>
                           <span class="player-value">
    <?= $g['min_players'] ?>-<?= $g['max_players'] ?> Players
</span>

                        </div>

                        <div class="icon_buttons_wrapper">
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#infoModal<?= $g['id'] ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                <span class="label">Learn more</span>
                            </div>

                            <?php if (!empty($g['video_url'])): ?>
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#videoModal<?= $g['id'] ?>">
                                <i class="fa-solid fa-circle-play"></i>
                                <span class="label">Watch Trailer</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Bottom Data -->
            <div class="p-3">
                <div class="guest_and_button">
                    <div class="vr_games_party_list">
                        <!--<p class="d-price">Difficulty:-->
                        <!--    <span class="price <?= htmlspecialchars($g['difficulty']) ?>">-->
                        <!--        <?= htmlspecialchars($g['difficulty']) ?>-->
                        <!--    </span>-->
                        <!--</p>-->

                        <p class="d-price">Game Duration:
                            <span class="price"><?= htmlspecialchars($g['duration_minutes']) ?> Min</span>
                        </p>

                        <p class="d-price">Players:
                            <span class="price"><?= $g['min_players'] ?>-<?= $g['max_players'] ?></span>
                        </p>
                        <p class="d-price">Category:
                            <span class="price"><?= htmlspecialchars($g['category']) ?></span>
                        </p>
                    </div>

                    
                </div>
            </div>
            </div>
                    <div class="next-button-wrapper">
                        <a href="<?php echo $g['booking_url']; ?>" 
                           class="bg_bnt_custom bg_bnt_custom_tran"  target="new">
                           Book Now
                        </a>
                    </div>
        </div>
    </div>

 <div class="modal fade blur-modal videoModal_z " id="videoModal<?= $g['id'] ?>" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel"><?= htmlspecialchars($g['title']) ?></h5> 
                    <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close" onclick="stopLocalVideo(this)">X</button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <?php if (!empty($g['video_url'])) { ?>
                            <video id="localVideo"  controls>
                                <source src="admin/<?= htmlspecialchars($g['video_url']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php } else { ?>
                            <div class="d-flex align-items-center justify-content-center" style="height:300px;">
                                <p class="text-center text-muted">No trailer video available for this game.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
   

    <!-- VIDEO MODAL -->
  

<div class="modal fade" id="infoModal<?= $g['id'] ?>" 
     tabindex="-1" aria-labelledby="liftInfoModal" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content custom-modal">

            <div class="modal-header border-0" style="align-items: flex-start!important;">
                <div class="info_modal_content" style="width: 90%;">

                    <h2 class="modal-title custom-heading" id="liftInfoModalLabel">
                        <?= htmlspecialchars($g['title']) ?>
                    </h2>

                    <?php if (!empty($g['tagline'])): ?>
                        <p><?= htmlspecialchars($g['tagline']) ?></p>
                    <?php endif; ?>

                </div>

                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">
                    X
                </button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- Game Image -->
                    <div class="col-md-5 text-center mb-3 mb-md-0">
                        <img src="admin/<?= htmlspecialchars($g['banner_image']) ?>" 
                        loading="lazy"
                             class="img-fluid rounded" 
                             alt="<?= htmlspecialchars($g['title']) ?>">
                    </div>

                    <!-- Game Info -->
                    <div class="col-md-7">

                        <!--<?php if (!empty($g['difficulty'])): ?>-->
                        <!--<div class="modal_Beginner_badel">-->
                        <!--    <p><?= htmlspecialchars($g['difficulty']) ?></p>-->
                        <!--</div>-->
                        <!--<?php endif; ?>-->

                        <div class="game-stats d-flex flex-wrap gap-3 mb-3">

                            <div class="stat-box">
                                <span class="stat-label">Players</span>
                                <span class="stat-value">
                                    <?= htmlspecialchars($g['min_players']) ?> - 
                                    <?= htmlspecialchars($g['max_players']) ?>
                                </span>
                            </div>

                            <div class="stat-box">
                                <span class="stat-label">Price</span>
                                <span class="stat-value">$<?= htmlspecialchars($g['price']) ?></span>
                            </div>

                            <div class="stat-box">
                                <span class="stat-label">
                                    <i class="fas fa-clock me-1"></i> Duration
                                </span>
                                <span class="stat-value"><?= htmlspecialchars($g['duration_minutes']) ?> Min </span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">
                                    <i class="fa-solid fa-table-cells-large"></i> category
                                </span>
                                <span class="stat-value"><?= htmlspecialchars($g['category']) ?> </span>
                            </div>

                        </div>

                        <div class="modal_info_p">
                            <p><?= nl2br(htmlspecialchars($g['description'])) ?></p>
                        </div>

                    </div>
                </div>

                <div class="all_button_main_header text-end" 
                     style="background-size: cover; background-repeat: no-repeat;">
                    
                    <a style="border-radius: 30px!important" class="bg_bnt_custom bg_bnt_custom_tran" data-bs-toggle="modal" data-bs-target="#videoModal<?= $g['id'] ?>">
                                <i class="fa-solid fa-play m-2"></i> Watch Trailer
                            </a>

                    <a class="bg_bnt_custom" style="border-radius: 30px!important" 
                       data-bs-dismiss="modal" aria-label="Close">OK</a>

                </div>
            </div>

        </div>
    </div>
</div>
 

<?php endforeach; ?>
</div>

</div>


<style>
    .vr_games_party_list p.d-price {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
    gap:10px
}
.vr_games_party_list p.d-price span {
    color: #00d4ff;
    font-weight: 600;
        text-align: right;
}
</style>













