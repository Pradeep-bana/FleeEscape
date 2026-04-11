<?php session_start();
include('link.php');
// ini_set('display_errors',1);
// error_reporting(E_ALL);
// Get the slug from the URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$slug = str_replace('.php', '', $slug);

//  $slug = 'haunted';
$docRoot = $_SERVER['DOCUMENT_ROOT'];

// If no slug, redirect to a default page or show 404
if (empty($slug)) {
    header('Location: /'); // or a 404 page
    exit;
}

// Database connection
include('admin/db.php');

// exit();
try {
    // Get the VR experience data
    $stmt = $pdo->prepare("SELECT * FROM vr_experiences WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $experience = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$experience) {
        // Experience not found, show 404
        echo "<script>
            alert('Experience not found');
            window.history.back();
        </script>";
        exit;
    }
    
    // Get sections for this experience
    $stmt = $pdo->prepare("SELECT * FROM vr_experience_sections WHERE experience_id = ? ORDER BY section_order");
    $stmt->execute([$experience['id']]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get leaderboard if exists
    $stmt = $pdo->prepare("SELECT * FROM vr_leaderboards WHERE experience_id = ? AND is_active = 1");
    $stmt->execute([$experience['id']]);
    $leaderboard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($leaderboard) {
        // Get leaderboard entries
        $stmt = $pdo->prepare("SELECT * FROM vr_leaderboard_entries WHERE leaderboard_id = ? ORDER BY rank_position");
        $stmt->execute([$leaderboard['id']]);
        $leaderboardEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $leaderboard['entries'] = $leaderboardEntries;
    }
    
    // Get related experiences
    $stmt = $pdo->prepare("
        SELECT ve.* 
        FROM vr_related_experiences re 
        JOIN vr_experiences ve ON re.related_experience_id = ve.id 
        WHERE re.experience_id = ? AND ve.is_active = 1
        ORDER BY re.display_order
    ");
    $stmt->execute([$experience['id']]);
    $relatedExperiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
$pageTitle = !empty($experience['meta_title']) 
    ? htmlspecialchars($experience['meta_title']) 
    : $experience['title'];

$metaKeywords = !empty($experience['meta_keywords']) 
    ? htmlspecialchars($experience['meta_keywords']) 
    : $experience['title'];

$metaDescription = !empty($experience['meta_description']) 
    ? htmlspecialchars($experience['meta_description']) 
    : $experience['description'];
$canonicalURL = $link."vr/".$slug;
include('includes/header.php');
?>

<section>
    <div class="vr_page_banner" style="background-image: url(<?php echo "../admin/".$experience['banner_image']; ?>);">
        <div class="container">
            <div class="vr_page_banner_content">
                <img src="../admin/<?php echo $experience['logo_image']; ?>" loading="lazy" alt="">
                <p><?php echo $experience['tagline']; ?></p>
                <a class=" bg_bnt_custom mt-3"
                    href="<?php echo $experience['booking_url']; ?>"
                    target="new">
                    BOOK NOW
                </a>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="vr_game_deatils_page_layer">
            <div class="vr_game_deatils_page_layer_item">
                <h3>PLAYERS</h3>
                <p><?php echo $experience['min_players']; ?>-<?php echo $experience['max_players']; ?></p>
            </div>
            <div class="vr_game_deatils_page_layer_item">
                <h3>CATEGORY</h3>
                <p><?php echo $experience['category']; ?></p>
            </div>
            <div class="vr_game_deatils_page_layer_item">
                <h3>EXPERIENCE TIME</h3>
                <p><?php echo $experience['duration_minutes']; ?> min</p>
            </div>
            <?php if ($experience['age_restriction'] > 0): ?>
            <div class="vr_game_deatils_page_layer_item">
                <h3>AGE</h3>
                <p><?php echo $experience['age_restriction']; ?>+</p>
            </div>
            <div class="vr_game_deatils_page_layer_item">
                <h3>Difficulty</h3>
                <p><?php echo $experience['difficulty']; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="vr_game_video_modal">
            <img src="../admin/<?php echo $experience['video_banner_image']; ?>" loading="lazy" alt="">
            <div class="vr_game_video_modal_content" data-bs-toggle="modal" data-bs-target="#videoModal">
                <i class="fa-regular fa-circle-play"></i>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="all_box_full_width SPACE_MARINE_VR_section vr_game_zik_zak_lable">
        <div class="container">
            <div class="SPACE_MARINE_VR">
                <h6><?php echo $experience['category']; ?></h6>
                <h1><?php echo $experience['title']; ?></h1>
                <p><?php echo $experience['description']; ?></p>
                <a class=" bg_bnt_custom mt-2 mb-5"
                 href="<?php echo $experience['booking_url']; ?>"
                    target="new">
                BOOK NOW</a>
            </div>
            
            <?php foreach ($sections as $index => $section): ?>
            <div class="row align-items-center">
                <?php if ($section['image_position'] === 'right'): ?>
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?php echo $section['title']; ?></h3>
                        <p><?php echo $section['description']; ?></p>
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_img ">
                        <img src="../admin/<?php echo $section['image_url']; ?>" loading="lazy" alt="<?php echo $section['title']; ?>" class="img-fluid">
                    </div>
                </div>
                <?php else: ?>
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="../admin/<?php echo $section['image_url']; ?>" loading="lazy" alt="<?php echo $section['title']; ?>" class="img-fluid">
                    </div>
                </div>
                <div class="col-sm-6 " data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?php echo $section['title']; ?></h3>
                        <p><?php echo $section['description']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($leaderboard): ?>
<section class="leaderboard_section">
    <div class="container">
        <div class="SPACE_MARINE_VR">
            <h6><?php echo $leaderboard['location']; ?></h6>
            <h3><?php echo $leaderboard['title']; ?></h3>
            <p><?php echo $leaderboard['subtitle']; ?></p>
        </div>

        <!-- Filters -->
        <div class="filters_Standard flex-wrap">
            <div class="filters_Standard_select">
                <select class="form-select custom-select">
                    <option selected>Choose Standard</option>
                    <option value="1">Standard 1</option>
                    <option value="2">Standard 2</option>
                    <option value="3">Standard 3</option>
                </select>
            </div>

            <div class="filters_Standard_select">
                <select class="form-select custom-select">
                    <option selected>Filter By</option>
                    <option value="today">Today</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="leaderboard-table">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard['entries'] as $entry): ?>
                    <tr>
                        <td><?php echo $entry['rank_position']; ?></td>
                        <td><?php echo $entry['player_name']; ?></td>
                        <td><?php echo number_format($entry['score']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="Immersive_Escap_Adventures Ultimate_Birthday_Experience gaming-container">
    <div class="container ">
        <div class="SPACE_MARINE_VR">
            <h3> experience the thrill</h3>
            <p>There’s something for everyone</p>
        </div>
        <div class="owl-carousel owl-theme new_all_game_slider">
          <?php
include('admin/db.php');

// Fetch all active VR experiences
$sql = "SELECT id, slug, title, prime_category, tagline, description, banner_image, logo_image, video_banner_image, 
        video_url, booking_url, min_players, max_players, category, duration_minutes, age_restriction, price, difficulty, 
        bottom_heading, meta_title, meta_keywords, meta_description, is_active, created_at, updated_at 
        FROM vr_experiences 
        WHERE is_active = 1 
        ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Use default image if banner_image is empty
    $bannerImage = $row['banner_image'] ?: 'path/to/default-image.jpg';
    $difficultyClass = strtolower($row['difficulty']);
?>
    <div class="item">
        <div class="room-card">
            <div class="room-image haunted-hotel" style="background-image: url('<?= BASE_URL ?>admin/<?php echo $bannerImage; ?>');">
            </div>
           
            <div class="room-content">
                <div class="room-content_all_ah">
                    <h3 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <div class="room-details">
                        
                        <p class="d-price">Category: <span class="price <?php echo $difficultyClass; ?>"><?php echo htmlspecialchars($row['category']); ?></span></p>
                        <p class="d-price">Game Duration: <span class="price"><?php echo htmlspecialchars($row['duration_minutes']); ?> min</span></p>
                        <p class="d-price">Difficulty: <span class="price"><?php echo htmlspecialchars($row['difficulty']); ?> </span></p>
                        <p class="d-price">Players: <span class="price"><?php echo htmlspecialchars($row['min_players']); ?>-<?php echo htmlspecialchars($row['max_players']); ?></span></p>
                        <a href="<?php echo htmlspecialchars($row['slug']); ?>" class="book-btn">LEARN MORE</a>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($row['bottom_heading'])): ?>
        <h6 class="color_combination_vr_function text-center"><?php echo $row['bottom_heading']; ?></h6>
        <?php endif; ?>
    </div>
<?php } ?>  
            
            
            
                       
                    </div>
    </div>
</section>

<section>
    <div class="Release_your_inner_section">
        <svg width="100%" height="60px" viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="xMidYMid slice">
            <rect width="100%" height="60" fill="#000000"></rect>
            <path
                d="M864.567 242.791C877.178 258.411 866.061 281.676 845.985 281.676H-115.569C-122.742 281.676 -129.535 278.452 -134.071 272.895L-505.47 -182.164C-518.199 -197.76 -507.085 -221.147 -486.947 -221.147H-486.46H-485.973H-485.487H-485.002H-484.516H-484.032H-483.547H-483.064H-482.581H-482.098H-481.616H-481.134H-480.653H-480.172H-479.691H-479.212H-478.732H-478.253H-477.775H-477.297H-476.819H-476.342H-475.866H-475.39H-474.914H-474.439H-473.964H-473.49H-473.016H-472.543H-472.07H-471.597H-471.125H-470.653H-470.182H-469.711H-469.241H-468.771H-468.301H-467.832H-467.363H-466.895H-466.427H-465.96H-465.493H-465.026H-464.56H-464.094H-463.628H-463.163H-462.699H-462.234H-461.771H-461.307H-460.844H-460.381H-459.919H-459.457H-458.995H-458.534H-458.073H-457.612H-457.152H-456.693H-456.233H-455.774H-455.315H-454.857H-454.399H-453.941H-453.484H-453.027H-452.57H-452.114H-451.658H-451.202H-450.747H-450.292H-449.837H-449.383H-448.929H-448.475H-448.022H-447.569H-447.116H-446.664H-446.211H-445.76H-445.308H-444.857H-444.406H-443.955H-443.505H-443.055H-442.605H-442.155H-441.706H-441.257H-440.809H-440.36H-439.912H-439.464H-439.017H-438.569H-438.122H-437.676H-437.229H-436.783H-436.337H-435.891H-435.445H-435H-434.555H-434.11H-433.666H-433.221H-432.777H-432.334H-431.89H-431.446H-431.003H-430.56H-430.118H-429.675H-429.233H-428.791H-428.349H-427.907H-427.466H-427.024H-426.583H-426.143H-425.702H-425.261H-424.821H-424.381H-423.941H-423.501H-423.062H-422.623H-422.183H-421.744H-421.306H-420.867H-420.428H-419.99H-419.552H-419.114H-418.676H-418.238H-417.801H-417.363H-416.926H-416.489H-416.052H-415.615H-415.178H-414.742H-414.306H-413.869H-413.433H-412.997H-412.561H-412.125H-411.69H-411.254H-410.819H-410.383H-409.948H-409.513H-409.078H-408.643H-408.208H-407.774H-407.339H-406.905H-406.47H-406.036H-405.602H-405.167H-404.733H-404.299H-403.865H-403.431H-402.998H-402.564H-402.13H-401.697H-401.263H-400.83H-400.396H-399.963H-399.53H-399.096H-398.663H-398.23H-397.797H-397.364H-396.931H-396.498H-396.065H-395.632H-395.199H-394.766H-394.333H-393.9H-393.467H-393.035H-392.602H-392.169H-391.736H-391.303H-390.871H-390.438H-390.005H-389.572H-389.14H-388.707H-388.274H-387.841H-387.408H-386.976H-386.543H-386.11H-385.677H-385.244H-384.811H-384.378H-383.945H-383.512H-383.079H-382.646H-382.212H-381.779H-381.346H-380.913H-380.479H-380.046H-379.612H-379.179H-378.745H-378.311H-377.877H-377.444H-377.01H-376.576H-376.142H-375.708H-375.273H-374.839H-374.405H-373.97H-373.535H-373.101H-372.666H-372.231H-371.796H-371.361H-370.926H-370.491H-370.055H-369.62H-369.184H-368.748H-368.312H-367.876H-367.44H-367.004H-366.568H-366.131H-365.695H-365.258H-364.821H-364.384H-363.947H-363.509H-363.072H-362.634H-362.196H-361.758H-361.32H-360.882H-360.443H-360.005H-359.566H-359.127H-358.688H-358.249H-357.809H-357.369H-356.93H-356.49H-356.049H-355.609H-355.168H-354.727H-354.286H-353.845H-353.404H-352.962H-352.52H-352.078H-351.636H-351.194H-350.751H-350.308H-349.865H-349.422H-348.978H-348.534H-348.09H-347.646H-347.202H-346.757H-346.312H-345.867H-345.421H-344.976H-344.53H-344.083H-343.637H-343.19H-342.743H-342.296H-341.848H-341.401H-340.953H-340.504H-340.056H-339.607H-339.158H-338.708H-338.259H-337.809H-337.358H-336.908H-336.457H-336.006H-335.554H-335.103H-334.65H-334.198H-333.745H-333.292H-332.839H-332.385H-331.932H-331.477H-331.023H-330.568H-330.113H-329.657H-329.201H-328.745H-328.288H-327.831H-327.374H-326.917H-326.459H-326H-325.542H-325.083H-324.624H-324.164H-323.704H-323.243H-322.783H-322.321H-321.86H-321.398H-320.936H-320.473H-320.01H-319.547H-319.083H-318.619H-318.154H-317.689H-317.224H-316.758H-316.292H-315.825H-315.358H-314.891H-314.423H-313.955H-313.486H-313.017H-312.548H-312.078H-311.608H-311.137H-310.666H-310.195H-309.723H-309.25H-308.777H-308.304H-307.83H-307.356H-306.881H-306.406H-305.931H-305.455H-304.978H-304.501H-304.024H-303.546H-303.068H-302.589H-302.11H-301.63H-301.15H-300.669H-300.188H-299.707H-299.225H-298.742H-298.259H-297.775H-297.291H-296.807H-296.322H-295.836H-295.35H-294.863H-294.376H-293.889H-293.401H-292.912H-292.423H-291.933H-291.443H-290.952H-290.461H-289.969H-289.477H-288.984H-288.491H-287.997H-287.502H-287.007H-286.512H-286.016H-285.519H-285.022H-284.524H-284.026H-283.527H-283.027H-282.527H-282.027H-281.525H-281.024H-280.521H-280.018H-279.515H-279.011H-278.506H-278.001H-277.495H-276.989H-276.482H-275.974H-275.466H-274.957H-274.448H-273.938H-273.427H-272.916H-272.404H-271.891H-271.378H-270.865H-270.35H-269.835H-269.32H-268.803H-268.286H-267.769H-267.251H-266.732H-266.213H-265.693H-265.172H-264.65H-264.128H-263.606H-263.082H-262.558H-262.034H-261.508H-260.982H-260.456H-259.928H-259.4H-258.872H-258.342H-257.812H-257.282H-256.75H-256.218H-255.685H-255.152H-254.617H-254.083H-253.547H-253.011H-252.474H-251.936H-251.398H-250.858C-244.392 -221.147 -238.266 -218.241 -234.177 -213.232L-41.6058 22.6717C-36.7573 28.6111 -29.4962 32.0571 -21.829 32.0571H675.142C687.337 32.0571 698.878 37.5722 706.539 47.0608L864.567 242.791Z"
                fill="#00d4ff" stroke="#00d4ff" stroke-width="4px"></path>
        </svg>
        <div class="Release_your_inner">
            <div class="container">
                <h3>DARE TO ENTER THE <?php echo strtoupper($experience['title']); ?>?</h3>
                <p>Hurry secure your tickets now.</p>
                <button>Book now</button>
            </div>
        </div>
        <svg width="100%" height="60px" viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="xMidYMid slice">
            <rect width="100%" height="60" fill="#00d4ff"></rect>
            <path
                d="M864.567 242.791C877.178 258.411 866.061 281.676 845.985 281.676H-115.569C-122.742 281.676 -129.535 278.452 -134.071 272.895L-505.47 -182.164C-518.199 -197.76 -507.085 -221.147 -486.947 -221.147H-486.46H-485.973H-485.487H-485.002H-484.516H-484.032H-483.547H-483.064H-482.581H-482.098H-481.616H-481.134H-480.653H-480.172H-479.691H-479.212H-478.732H-478.253H-477.775H-477.297H-476.819H-476.342H-475.866H-475.39H-474.914H-474.439H-473.964H-473.49H-473.016H-472.543H-472.07H-471.597H-471.125H-470.653H-470.182H-469.711H-469.241H-468.771H-468.301H-467.832H-467.363H-466.895H-466.427H-465.96H-465.493H-465.026H-464.56H-464.094H-463.628H-463.163H-462.699H-462.234H-461.771H-461.307H-460.844H-460.381H-459.919H-459.457H-458.995H-458.534H-458.073H-457.612H-457.152H-456.693H-456.233H-455.774H-455.315H-454.857H-454.399H-453.941H-453.484H-453.027H-452.57H-452.114H-451.658H-451.202H-450.747H-450.292H-449.837H-449.383H-448.929H-448.475H-448.022H-447.569H-447.116H-446.664H-446.211H-445.76H-445.308H-444.857H-444.406H-443.955H-443.505H-443.055H-442.605H-442.155H-441.706H-441.257H-440.809H-440.36H-439.912H-439.464H-439.017H-438.569H-438.122H-437.676H-437.229H-436.783H-436.337H-435.891H-435.445H-435H-434.555H-434.11H-433.666H-433.221H-432.777H-432.334H-431.89H-431.446H-431.003H-430.56H-430.118H-429.675H-429.233H-428.791H-428.349H-427.907H-427.466H-427.024H-426.583H-426.143H-425.702H-425.261H-424.821H-424.381H-423.941H-423.501H-423.062H-422.623H-422.183H-421.744H-421.306H-420.867H-420.428H-419.99H-419.552H-419.114H-418.676H-418.238H-417.801H-417.363H-416.926H-416.489H-416.052H-415.615H-415.178H-414.742H-414.306H-413.869H-413.433H-412.997H-412.561H-412.125H-411.69H-411.254H-410.819H-410.383H-409.948H-409.513H-409.078H-408.643H-408.208H-407.774H-407.339H-406.905H-406.47H-406.036H-405.602H-405.167H-404.733H-404.299H-403.865H-403.431H-402.998H-402.564H-402.13H-401.697H-401.263H-400.83H-400.396H-399.963H-399.53H-399.096H-398.663H-398.23H-397.797H-397.364H-396.931H-396.498H-396.065H-395.632H-395.199H-394.766H-394.333H-393.9H-393.467H-393.035H-392.602H-392.169H-391.736H-391.303H-390.871H-390.438H-390.005H-389.572H-389.14H-388.707H-388.274H-387.841H-387.408H-386.976H-386.543H-386.11H-385.677H-385.244H-384.811H-384.378H-383.945H-383.512H-383.079H-382.646H-382.212H-381.779H-381.346H-380.913H-380.479H-380.046H-379.612H-379.179H-378.745H-378.311H-377.877H-377.444H-377.01H-376.576H-376.142H-375.708H-375.273H-374.839H-374.405H-373.97H-373.535H-373.101H-372.666H-372.231H-371.796H-371.361H-370.926H-370.491H-370.055H-369.62H-369.184H-368.748H-368.312H-367.876H-367.44H-367.004H-366.568H-366.131H-365.695H-365.258H-364.821H-364.384H-363.947H-363.509H-363.072H-362.634H-362.196H-361.758H-361.32H-360.882H-360.443H-360.005H-359.566H-359.127H-358.688H-358.249H-357.809H-357.369H-356.93H-356.49H-356.049H-355.609H-355.168H-354.727H-354.286H-353.845H-353.404H-352.962H-352.52H-352.078H-351.636H-351.194H-350.751H-350.308H-349.865H-349.422H-348.978H-348.534H-348.09H-347.646H-347.202H-346.757H-346.312H-345.867H-345.421H-344.976H-344.53H-344.083H-343.637H-343.19H-342.743H-342.296H-341.848H-341.401H-340.953H-340.504H-340.056H-339.607H-339.158H-338.708H-338.259H-337.809H-337.358H-336.908H-336.457H-336.006H-335.554H-335.103H-334.65H-334.198H-333.745H-333.292H-332.839H-332.385H-331.932H-331.477H-331.023H-330.568H-330.113H-329.657H-329.201H-328.745H-328.288H-327.831H-327.374H-326.917H-326.459H-326H-325.542H-325.083H-324.624H-324.164H-323.704H-323.243H-322.783H-322.321H-321.86H-321.398H-320.936H-320.473H-320.01H-319.547H-319.083H-318.619H-318.154H-317.689H-317.224H-316.758H-316.292H-315.825H-315.358H-314.891H-314.423H-313.955H-313.486H-313.017H-312.548H-312.078H-311.608H-311.137H-310.666H-310.195H-309.723H-309.25H-308.777H-308.304H-307.83H-307.356H-306.881H-306.406H-305.931H-305.455H-304.978H-304.501H-304.024H-303.546H-303.068H-302.589H-302.11H-301.63H-301.15H-300.669H-300.188H-299.707H-299.225H-298.742H-298.259H-297.775H-297.291H-296.807H-296.322H-295.836H-295.35H-294.863H-294.376H-293.889H-293.401H-292.912H-292.423H-291.933H-291.443H-290.952H-290.461H-289.969H-289.477H-288.984H-288.491H-287.997H-287.502H-287.007H-286.512H-286.016H-285.519H-285.022H-284.524H-284.026H-283.527H-283.027H-282.527H-282.027H-281.525H-281.024H-280.521H-280.018H-279.515H-279.011H-278.506H-278.001H-277.495H-276.989H-276.482H-275.974H-275.466H-274.957H-274.448H-273.938H-273.427H-272.916H-272.404H-271.891H-271.378H-270.865H-270.35H-269.835H-269.32H-268.803H-268.286H-267.769H-267.251H-266.732H-266.213H-265.693H-265.172H-264.65H-264.128H-263.606H-263.082H-262.558H-262.034H-261.508H-260.982H-260.456H-259.928H-259.4H-258.872H-258.342H-257.812H-257.282H-256.75H-256.218H-255.685H-255.152H-254.617H-254.083H-253.547H-253.011H-252.474H-251.936H-251.398H-250.858C-244.392 -221.147 -238.266 -218.241 -234.177 -213.232L-41.6058 22.6717C-36.7573 28.6111 -29.4962 32.0571 -21.829 32.0571H675.142C687.337 32.0571 698.878 37.5722 706.539 47.0608L864.567 242.791Z"
                fill="#000000" stroke="#000000" stroke-width="4px"></path>
        </svg>
    </div>
</section>

<!-- Video Modal -->
<div class="modal fade blur-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars($experience['title'])?></h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    >X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <video id="localVideo"  controls>
                        <source src="<?php echo "../admin/".$experience['video_url']; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include('includes/footer.php'); ?>