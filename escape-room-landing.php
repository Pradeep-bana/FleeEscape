<?php
$pageTitle = 'Escape Room';
include('includes/header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<section class="choose-adventure ">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- LEFT: Tabs list (grid same) -->
            <div class="col-md-3">
                <h5 class="ca-title mb-3">Choose Your Adventure</h5>
                <div class="nav flex-column nav-pills" id="caTabs" role="tablist" aria-orientation="vertical">

                    <!-- Prison Escape -->
                    <button class="nav-link ca-item active" id="tab-prison" data-bs-toggle="pill"
                        data-bs-target="#pane-prison" type="button" role="tab">
                        <div class="ca-item-head">
                            <i class="bi bi-lock"></i>
                            <div class="ca-item-name_heding">
                                <div class="ca-item-name">Prison Escape</div>
                                <div class="ca-item-sub">Jailbreak</div>
                            </div>
                        </div>
                        <div class="ca-item-meta">
                            <div>
                                <span class="ca-difficulty ca-medium">Medium</span>
                                <span class="ca-item-meta_minutes"><span>•</span> 60 minutes</span>
                            </div>
                            <span class="ca-price">$32</span>
                        </div>
                        <div class="ca-rating"><i class="bi bi-star-fill"></i> 4.6 (189 reviews)</div>
                    </button>

                    <!-- The Lift -->
                    <button class="nav-link ca-item" id="tab-lift" data-bs-toggle="pill" data-bs-target="#pane-lift"
                        type="button" role="tab">
                        <div class="ca-item-head">
                            <i class="bi bi-arrow-up-circle"></i>
                            <div class="ca-item-name_heding">
                                <div class="ca-item-name">The Lift</div>
                                <div class="ca-item-sub">Psychological Thriller</div>
                            </div>
                        </div>
                        <div class="ca-item-meta">
                            <div>
                                <span class="ca-difficulty ca-hard">Hard</span>
                                <span class="ca-item-meta_minutes"><span>•</span> 45 minutes</span>
                            </div>
                            <span class="ca-price">$35</span>
                        </div>
                        <div class="ca-rating"><i class="bi bi-star-fill"></i> 4.8 (234 reviews)</div>
                    </button>

                    <!-- Museum Heist -->
                    <button class="nav-link ca-item" id="tab-museum" data-bs-toggle="pill" data-bs-target="#pane-museum"
                        type="button" role="tab">
                        <div class="ca-item-head">
                            <i class="bi bi-bank"></i>
                            <div class="ca-item-name_heding">
                                <div class="ca-item-name">Museum Heist</div>
                                <div class="ca-item-sub">Art Theft</div>
                            </div>
                        </div>
                        <div class="ca-item-meta">
                            <div>
                                <span class="ca-difficulty ca-hard">Hard</span>
                                <span class="ca-item-meta_minutes"><span>•</span> 60 minutes</span>
                            </div>
                            <span class="ca-price">$38</span>
                        </div>
                        <div class="ca-rating"><i class="bi bi-star-fill"></i> 4.9 (267 reviews)</div>
                    </button>

                    <!-- Ice Walker GOT -->
                    <button class="nav-link ca-item" id="tab-ice" data-bs-toggle="pill" data-bs-target="#pane-ice"
                        type="button" role="tab">
                        <div class="ca-item-head">
                            <i class="bi bi-book"></i>
                            <div class="ca-item-name_heding">
                                <div class="ca-item-name">Ice Walker GOT</div>
                                <div class="ca-item-sub">Fantasy/Medieval</div>
                            </div>
                        </div>
                        <div class="ca-item-meta">
                            <div>
                                <span class="ca-difficulty ca-medium">Medium</span>
                                <span class="ca-item-meta_minutes"><span>•</span> 60 minutes</span>
                            </div>
                            <span class="ca-price">$36</span>
                        </div>
                        <div class="ca-rating"><i class="bi bi-star-fill"></i> 4.7 (198 reviews)</div>
                    </button>

                </div>

            </div>

            <!-- RIGHT: Tab content (grid same) -->
            <div class="col-md-9">
                <div class="tab-content" id="caTabsContent">
                    <div class="tab-pane fade show active" id="pane-prison" role="tabpanel"
                        aria-labelledby="tab-prison">
                        <div class="choose-adventure_tab_data">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="ca-chip">Medium Difficulty</span>
                                <span class="ca-chip">Kid’s Favorite</span>
                            </div>
                            <h1 class="ca-heading">PRISON ESCAPE</h1>
                            <p class="ca-subtitle">Break out of maximum security prison before the guards return from
                                their shift.</p>

                            <div class="ca-badges">
                                <span class="ca-badge"><i class="bi bi-clock"></i> 60 minutes</span>
                                <span class="ca-badge"><i class="bi bi-people"></i> 2–6 players</span>
                                <span class="ca-badge"><i class="bi bi-graph-up"></i> Medium Difficulty</span>
                                <span class="ca-badge"><i class="bi bi-star-fill"></i> 4.6/5 (189 reviews)</span>
                            </div>

                            <div class="all_button_main_header"
                                style="background-size: cover; background-repeat: no-repeat;">
                                <a href="#to_book_scroll" data-bs-toggle="modal" data-bs-target="#videoModal"
                                    class="bg_bnt_custom bg_bnt_custom_tran">Watch Trailer</a>
                                <a href="booking.php?payment-details" class="bg_bnt_custom">Book Now – $35/person</a>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-lift" role="tabpanel" aria-labelledby="tab-lift">
                        <div class="choose-adventure_tab_data">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="ca-chip">Medium Difficulty</span>
                                <span class="ca-chip">Kid’s Favorite</span>
                            </div>
                            <h1 class="ca-heading">THE LIFT</h1>
                            <p class="ca-subtitle">A psychological thriller where every floor takes you deeper into
                                mystery.</p>

                            <div class="ca-badges">
                                <span class="ca-badge"><i class="bi bi-clock"></i> 45 minutes</span>
                                <span class="ca-badge"><i class="bi bi-people"></i> 2–5 players</span>
                                <span class="ca-badge"><i class="bi bi-graph-up"></i> Hard Difficulty</span>
                                <span class="ca-badge"><i class="bi bi-star-fill"></i> 4.8/5 (234 reviews)</span>
                            </div>

                            <div class="all_button_main_header"
                                style="background-size: cover; background-repeat: no-repeat;">
                                <a href="#to_book_scroll" data-bs-toggle="modal" data-bs-target="#videoModal"
                                    class="bg_bnt_custom bg_bnt_custom_tran">Watch Trailer</a>
                                <a href="booking.php?payment-details" class="bg_bnt_custom">Book Now – $35/person</a>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-museum" role="tabpanel" aria-labelledby="tab-museum">
                        <div class="choose-adventure_tab_data">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="ca-chip">Hard Difficulty</span>
                                <span class="ca-chip">Kid’s Favorite</span>
                            </div>
                            <h1 class="ca-heading">MUSEUM HEIST</h1>
                            <p class="ca-subtitle">Steal the priceless artifact before security locks down the building.
                            </p>

                            <div class="ca-badges">
                                <span class="ca-badge"><i class="bi bi-clock"></i> 60 minutes</span>
                                <span class="ca-badge"><i class="bi bi-people"></i> 3–7 players</span>
                                <span class="ca-badge"><i class="bi bi-graph-up"></i> Hard Difficulty</span>
                                <span class="ca-badge"><i class="bi bi-star-fill"></i> 4.9/5 (267 reviews)</span>
                            </div>

                            <div class="all_button_main_header"
                                style="background-size: cover; background-repeat: no-repeat;">
                                <a href="#to_book_scroll" data-bs-toggle="modal" data-bs-target="#videoModal"
                                    class="bg_bnt_custom bg_bnt_custom_tran">Watch Trailer</a>
                                <a href="booking.php?payment-details" class="bg_bnt_custom">Book Now – $38/person</a>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-ice" role="tabpanel" aria-labelledby="tab-ice">
                        <div class="choose-adventure_tab_data">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="ca-chip">Medium Difficulty</span>
                                <span class="ca-chip">Kid’s Favorite</span>
                            </div>
                            <h1 class="ca-heading">ICE WALKER GOT</h1>
                            <p class="ca-subtitle">Survive the fantasy realm of ice and shadows—or freeze forever.</p>

                            <div class="ca-badges">
                                <span class="ca-badge"><i class="bi bi-clock"></i> 60 minutes</span>
                                <span class="ca-badge"><i class="bi bi-people"></i> 2–8 players</span>
                                <span class="ca-badge"><i class="bi bi-graph-up"></i> Medium Difficulty</span>
                                <span class="ca-badge"><i class="bi bi-star-fill"></i> 4.7/5 (198 reviews)</span>
                            </div>

                            <div class="all_button_main_header"
                                style="background-size: cover; background-repeat: no-repeat;">
                                <a href="#to_book_scroll" data-bs-toggle="modal" data-bs-target="#videoModal"
                                    class="bg_bnt_custom bg_bnt_custom_tran">Watch Trailer</a>
                                <a href="booking.php?payment-details" class="bg_bnt_custom">Book Now – $36/person</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /right -->
        </div>
    </div>
</section>

<div class="container">
    <ul class="nav nav-tabs card_deatils_tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                type="button" role="tab">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="story-tab" data-bs-toggle="tab" data-bs-target="#story" type="button"
                role="tab">Story</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="gallery-cardDetails-tab" data-bs-toggle="tab"
                data-bs-target="#gallery-cardDetails" type="button" role="tab">Gallery</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button"
                role="tab">FAQ</button>
        </li>
    </ul>

    <div class="tab-content card_datals_main_data" id="myTabContent">
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="Room_Detail_overview_tabs p-4 h-100">
                        <h5 class="Room_Detail_overview_tabs-title"><i class="bi bi-lock-fill"></i> Room Details
                        </h5>
                        <div class="row mt-3">
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Duration</p>
                                <h6 class="fw-bold">60 minutes</h6>
                            </div>
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Team Size</p>
                                <h6 class="fw-bold">2-6 players</h6>
                            </div>
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Difficulty</p>
                                <h6 class="fw-bold text-warning">Medium</h6>
                            </div>
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Success Rate</p>
                                <h6 class="fw-bold text-danger">42%</h6>
                            </div>
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Age Recommendation</p>
                                <h6 class="fw-bold">14+</h6>
                            </div>
                            <div class="col-6 mb-3">
                                <p class="mb-1 text-muted">Price</p>
                                <h6 class="fw-bold text-success">$32/person</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
                    <div class="Room_Detail_overview_tabs p-4 h-100">
                        <h5 class="Room_Detail_overview_tabs-title">Features</h5>
                        <ul class="list-unstyled mt-3">
                            <li class="d-flex align-items-center mb-2"><i class="bi bi-check-lg me-2 text-success"></i>
                                Authentic prison environment</li>
                            <li class="d-flex align-items-center mb-2"><i class="bi bi-check-lg me-2 text-success"></i>
                                Guard timing mechanisms</li>
                            <li class="d-flex align-items-center mb-2"><i class="bi bi-check-lg me-2 text-success"></i>
                                Improvised tool puzzles</li>
                            <li class="d-flex align-items-center mb-2"><i class="bi bi-check-lg me-2 text-success"></i>
                                Multiple escape routes</li>
                            <li class="d-flex align-items-center mb-2"><i class="bi bi-check-lg me-2 text-success"></i>
                                Stealth-based challenges</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 mt-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="Room_Detail_overview_tabs p-4">
                        <h5 class="Room_Detail_overview_tabs-title">Description</h5>
                        <p class="mt-3">You've been wrongfully convicted and sentenced to life in Blackgate
                            Penitentiary. Your only chance at freedom is during the 60-minute guard shift change.
                            Use improvised tools, navigate through cell blocks, and avoid detection systems to
                            escape before the guards return.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="story" role="tabpanel">
            <div class="Room_Detail_overview_tabs p-4" data-aos="zoom-in-up">
                <h5 class="Room_Detail_overview_tabs-title">Story</h5>
                <p class="mt-3">Blackgate Penitentiary is known as the most secure prison in the state. You've been
                    wrongfully convicted of a crime you didn't commit, and your appeals have been exhausted. During
                    months of careful observation, you've discovered that the guard rotation creates a brief 60-minute
                    window of opportunity during shift change. With the help of a sympathetic janitor who believes in
                    your innocence, you've gathered makeshift tools and hidden them throughout your cell block. Tonight
                    is your only chance to escape before they transfer you to solitary confinement forever. You must
                    work together to navigate the prison's security systems, avoid the remaining guards, and find your
                    way to freedom.</p>
            </div>
        </div>

        <div class="tab-pane fade" id="gallery-cardDetails" role="tabpanel">
            <div class="row">
                <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card_deatils_image_card">
                        <img src="https://picsum.photos/300/200" loading="lazy"  decoding="async"  class="img-fluid rounded">
                    </div>
                </div>
                <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card_deatils_image_card">
                        <img src="https://picsum.photos/301/200" loading="lazy"  decoding="async"  class="img-fluid rounded">
                    </div>
                </div>
                <div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">
                    <div class="card_deatils_image_card">
                        <img src="https://picsum.photos/302/200"  loading="lazy"  decoding="async"  class="img-fluid rounded">
                    </div>
                </div>
                <div class="col-md-3" data-aos="zoom-in" data-aos-delay="400">
                    <div class="card_deatils_image_card">
                        <img src="https://picsum.photos/303/200" loading="lazy"  decoding="async"  class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="faq" role="tabpanel">
            <div class="card_tails_faq">
                <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up">
                    <h5 class="Room_Detail_overview_tabs-title">Is this room claustrophobic?</h5>
                    <p class="mt-3">The room simulates a prison environment but has multiple areas to explore. No one is
                        locked in a small space alone, and you can exit at any time if needed.</p>
                </div>
                <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up" data-aos-delay="100">
                    <h5 class="Room_Detail_overview_tabs-title">Are there live actors?</h5>
                    <p class="mt-3">No, Prison Escape uses audio and lighting effects to simulate guard presence. There
                        are no live actors in this room.</p>
                </div>
                <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up" data-aos-delay="200">
                    <h5 class="Room_Detail_overview_tabs-title">What makes this room challenging?</h5>
                    <p class="mt-3">The challenge comes from timing-based puzzles, stealth elements, and the need for
                        careful coordination between team members.</p>
                </div>
                <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up" data-aos-delay="300">
                    <h5 class="Room_Detail_overview_tabs-title">Is it appropriate for teenagers?</h5>
                    <p class="mt-3">Yes, the room is suitable for ages 14+ and focuses on puzzle-solving rather than
                        violence or inappropriate content.</p>
                </div>
                <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up" data-aos-delay="400">
                    <h5 class="Room_Detail_overview_tabs-title">How realistic is the prison setting?</h5>
                    <p class="mt-3">We've designed an authentic prison environment with real fixtures and props,
                        creating an immersive experience without being uncomfortable.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="Boo_Prison_Escap_main_heading">
        <h2 class="" data-aos="fade-down" data-aos-duration="1000">Book Prison Escape</h2>
        <p class="" data-aos="fade-down" data-aos-duration="1200">Select your preferred date,
            time, and number of guests</p>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-5 col-md-6" data-aos="fade-right" data-aos-duration="1500">
            <div class="Boo_Prison_Escape_time_box h-100">
                <h5 class="sub_heading">Select Date</h5>
                <div class="Boo_Prison_Escape_calendar_box">
                    <div id="Book-Prison-Date"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-md-6">

            <div class="Boo_Prison_Escape_time_box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="200">
                <h5 class="sub_heading">Select Time</h5>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time1" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time1" class="Boo_Prison_Escape_time-slot-label">10:00 AM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time2" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time2" class="Boo_Prison_Escape_time-slot-label">11:30 AM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time3" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time3" class="Boo_Prison_Escape_time-slot-label">1:00 PM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time4" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time4" class="Boo_Prison_Escape_time-slot-label">2:30 PM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time5" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time5" class="Boo_Prison_Escape_time-slot-label">4:00 PM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time6" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time6" class="Boo_Prison_Escape_time-slot-label">5:30 PM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time7" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time7" class="Boo_Prison_Escape_time-slot-label">7:00 PM</label>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" id="Boo_Prison_Escape_time8" class="Boo_Prison_Escape_time-slot" />
                        <label for="Boo_Prison_Escape_time8" class="Boo_Prison_Escape_time-slot-label">8:30 PM</label>
                    </div>
                </div>
            </div>
            <div class="Boo_Prison_Escape_time_box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="400">
                <h5 class="sub_heading">Number of Guests</h5>
                <select class="Boo_Prison_Escape_select" aria-label="Select number of guests">
                    <option selected value="">Select number of guests</option>
                    <option value="1">1 Guest</option>
                    <option value="2">2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                </select>

            </div>

            <div class=" Boo_Prison_Escape_time_box" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="600">
                <div class="Boo_Prison_Escape_booking_summary_box">
                    <h3 class="sub_heading Booking_Summary_ng_summary_box">Booking Summary</h3>
                    <div class="Boo_Prison_Escape_booking_summary_box_row">
                        <span class="text-white">Room:</span>
                        <span class="escape_boo_summart_data">Prison Escape</span>
                    </div>
                    <div class="Boo_Prison_Escape_booking_summary_box_row">
                        <span class="text-white">Date:</span>
                        <span class="escape_boo_summart_data">Tuesday, August 5, 2025 7:00 PM</span>
                    </div>
                    <div class="Boo_Prison_Escape_booking_summary_box_row">
                        <span class="text-white">Time:</span>
                        <span class="escape_boo_summart_data">7:00 PM</span>
                    </div>
                    <div class="Boo_Prison_Escape_booking_summary_box_row">
                        <span class="text-white">Guests:</span>
                        <span class="escape_boo_summart_data">5</span>
                    </div>
                    <div class="Boo_Prison_Escape_booking_summary_box_totals">
                        <span class="escape_boo_summart_data_totale">Total:</span>
                        <span class="escape_boo_summart_data_totale">$0</span>
                    </div>

                    <div class="next-button-wrapper">
                        <a href="booking.php?add-ons-" class="bg_bnt_custom disabled continue_nex_step" disabled> <i
                                class="bi bi-cart-fill"></i> Add to Cart & Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="escape_room_home_comparison">
    <div class="Boo_Prison_Escap_main_heading">
        <h2 class="" data-aos="fade-down" data-aos-duration="1000">Room Comparison</h2>
    </div>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="100">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">Space Station Alpha</h5>
                        <p class="comparison_card-text">Repair the space station before oxygen runs out in this
                            high-tech adventure.</p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-primary">Sci-Fi</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-danger">Hard</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>90 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>6</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$42/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐☆
                                4.9</span></p>

                        <div class="comparison_card_content_features">
                            <span>High-tech props</span><span>Digital puzzles</span><span>Immersive lighting</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="200">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">The Haunted Mansion</h5>
                        <p class="comparison_card-text">Navigate through a terrifying haunted mansion filled with
                            supernatural puzzles.</p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-danger">Horror</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-danger">Hard</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>60 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>6</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$35/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐☆
                                4.8</span></p>

                        <div class="comparison_card_content_features">
                            <span>Live actors</span><span>Special effects</span><span>Multiple rooms</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="300">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">Pirate’s Treasure</h5>
                        <p class="comparison_card-text">Search for buried treasure aboard a mysterious pirate ship.</p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-warning text-dark">Adventure</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-warning text-dark">Medium</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>75 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>8</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$28/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐☆
                                4.6</span></p>

                        <div class="comparison_card_content_features">
                            <span>Physical puzzles</span><span>Hidden compartments</span><span>Pirate props</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="400">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">Detective’s Office</h5>
                        <p class="comparison_card-text">Solve a classic murder mystery in a 1940s detective's office.
                        </p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-info text-dark">Mystery</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-success">Easy</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>60 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>5</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$25/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐☆
                                4.4</span></p>

                        <div class="comparison_card_content_features">
                            <span>Logic puzzles</span><span>Evidence analysis</span><span>Period decor</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="500">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">Jungle Escape</h5>
                        <p class="comparison_card-text">Survive the jungle full of wild traps and hidden mysteries.</p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-success">Adventure</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-warning text-dark">Medium</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>80 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>7</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$30/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐☆
                                4.7</span></p>

                        <div class="comparison_card_content_features">
                            <span>Hidden caves</span><span>Survival puzzles</span><span>Animal sounds</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 " data-aos="fade-up" data-aos-delay="600">
                <div class="comparison_card h-100">
                    <img src="img/1Life_Escape_Room.jpg" loading="lazy" alt="...">
                    <div class="comparison_card_content">
                        <h5 class="comparison_card-title">Time Traveler’s Quest</h5>
                        <p class="comparison_card-text">Solve puzzles across different eras and escape the time loop.
                        </p>

                        <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                class="badge bg-secondary">Sci-Fi</span></p>
                        <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                class="badge bg-danger">Hard</span></p>
                        <p class="comparison_card_content_data_items"><strong>Duration:</strong><span>100 min</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Max Players:</strong><span>6</span></p>
                        <p class="comparison_card_content_data_items"><strong>Price:</strong><span>$40/person</span>
                        </p>
                        <p class="comparison_card_content_data_items"><strong>Rating:</strong><span class="rating">⭐⭐⭐⭐⭐
                                4.9</span></p>

                        <div class="comparison_card_content_features">
                            <span>Era puzzles</span><span>Futuristic gadgets</span><span>Historical props</span>
                        </div>
                    </div>
                    <div class="comparison_card_bnt">
                        <a href="#" class="bg_bnt_custom continue_nex_step">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="escape_room_home_Customer_Reviews_section container">
    <div class="Boo_Prison_Escap_main_heading">
        <h2>Customer Reviews</h2>
        <p>See what other adventurers are saying about Prison Escape</p>
        <div class="d-flex justify-content-center align-items-center gap-2">
            <span class="escape_room_home_Customer_Reviews_stars">
                ★★★★★
            </span>
            <span class="fw-bold">4.6/5</span>
            <small class="text-muted">(5 reviews)</small>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-pills escape_room_home_Customer_Reviews_tabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill"
                href="#escape_room_home_Customer_Reviews_all">All Reviews</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#escape_room_home_Customer_Reviews_five">5
                Stars</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#escape_room_home_Customer_Reviews_four">4
                Stars</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill"
                href="#escape_room_home_Customer_Reviews_recent">Most Recent</a></li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="escape_room_home_Customer_Reviews_all">
            <div class="row g-3">
                <!-- Review 1 -->
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar">S</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Sarah M. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 15, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★★</div>
                        </div>
                        <p>Absolutely incredible experience! The prison setting felt so authentic, and the puzzles were
                            challenging but fair. Our team of 4 escaped with just 3 minutes to spare. The guard timing
                            mechanism really added to the tension.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (12)</span>
                            <span>★ 5/5</span>
                        </div>
                    </div>
                </div>

                <!-- Review 2 -->
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">M</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Mike R. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 10, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★☆</div>
                        </div>
                        <p>Great room with excellent atmosphere. The prison cells looked incredibly realistic. The
                            stealth elements were unique compared to other escape rooms we've done.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (8)</span>
                            <span>★ 4/5</span>
                        </div>
                    </div>
                </div>

                <!-- Review 3 -->
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">J</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Jennifer L. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 8, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★★</div>
                        </div>
                        <p>Perfect for our team building event! The improvised puzzles were clever and kept us guessing.
                            Our group of 6 had an amazing time.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (15)</span>
                            <span>★ 5/5</span>
                        </div>
                    </div>
                </div>

                <!-- Review 4 -->
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">D</div>
                            <div class="ms-2">
                                <h6 class="mb-0">David K. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 5, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★☆</div>
                        </div>
                        <p>Solid escape room experience. The puzzles were logical and well-executed. Only complaint is
                            that some lighting was dim, making clues harder to see.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (6)</span>
                            <span>★ 4/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="escape_room_home_Customer_Reviews_five">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar">S</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Sarah M. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 15, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★★</div>
                        </div>
                        <p>Absolutely incredible experience! The prison setting felt so authentic, and the puzzles were
                            challenging but fair. Our team of 4 escaped with just 3 minutes to spare. The guard timing
                            mechanism really added to the tension.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (12)</span>
                            <span>★ 5/5</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">J</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Jennifer L. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 8, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★★</div>
                        </div>
                        <p>Perfect for our team building event! The improvised puzzles were clever and kept us guessing.
                            Our group of 6 had an amazing time.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (15)</span>
                            <span>★ 5/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="escape_room_home_Customer_Reviews_four">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">M</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Mike R. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 10, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★☆</div>
                        </div>
                        <p>Great room with excellent atmosphere. The prison cells looked incredibly realistic. The
                            stealth elements were unique compared to other escape rooms we've done.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (8)</span>
                            <span>★ 4/5</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar ">D</div>
                            <div class="ms-2">
                                <h6 class="mb-0">David K. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 5, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★☆</div>
                        </div>
                        <p>Solid escape room experience. The puzzles were logical and well-executed. Only complaint is
                            that some lighting was dim, making clues harder to see.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (6)</span>
                            <span>★ 4/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="escape_room_home_Customer_Reviews_recent">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="escape_room_home_Customer_Reviews_card">
                        <div class="d-flex  mb-2">
                            <div class="escape_room_home_Customer_Reviews_avatar">S</div>
                            <div class="ms-2">
                                <h6 class="mb-0">Sarah M. <span class="escape_room_home_Customer_Reviews_verified">✔
                                        Verified</span></h6>
                                <small class="text-muted">January 15, 2024</small>
                            </div>
                            <div class="ms-auto escape_room_home_Customer_Reviews_stars">★★★★★</div>
                        </div>
                        <p>Absolutely incredible experience! The prison setting felt so authentic, and the puzzles were
                            challenging but fair. Our team of 4 escaped with just 3 minutes to spare. The guard timing
                            mechanism really added to the tension.</p>
                        <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                            <span><i class="bi bi-fire"></i> Helpful (12)</span>
                            <span>★ 5/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="escape_room_home_Customer_Reviews_summary">
        <div class="Boo_Prison_Escap_main_heading">
            <h2>Review Summary</h2>
        </div>
        <div class="row text-center">
            <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                <div class="value escape_room_home_Customer_Reviews_avg">4.6/5</div>
                <small class="text-muted">Average Rating</small>
            </div>
            <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                <div class="value escape_room_home_Customer_Reviews_recommend">80%</div>
                <small class="text-muted">Recommend</small>
            </div>
            <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                <div class="value escape_room_home_Customer_Reviews_total">5</div>
                <small class="text-muted">Total Reviews</small>
            </div>
        </div>
    </div>
</div>

<div class="escape_room_home_contact_section">
    <div class="container">
        <div class="Boo_Prison_Escap_main_heading">
            <h2>Ready for Prison Escape?</h2>
        </div>
        <div class="row">
            <div class="col-md-4 escape_room_home_contact_item">
                <div class="escape_room_home_contact_icon">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <h5>Location</h5>
                <p>123 Adventure Street</p>
                <p>Downtown, City 12345</p>
            </div>
            <div class="col-md-4 escape_room_home_contact_item">
                <div class="escape_room_home_contact_icon">
                    <i class="bi bi-telephone"></i>
                </div>
                <h5>Phone</h5>
                <p>(555) 123-FLEE</p>
                <p>Available 24/7</p>
            </div>
            <div class="col-md-4 escape_room_home_contact_item">
                <div class="escape_room_home_contact_icon">
                    <i class="bi bi-envelope"></i>
                </div>
                <h5>Email</h5>
                <p>info@fleeescape.com</p>
                <p>Quick response guaranteed</p>
            </div>
        </div>
        <a href="booking.php" class="bg_bnt_custom  continue_nex_step"> Book Prison Escape Now</a>
    </div>
</div>



<script>
function checkSelection() {
    const selectedTime = document.querySelector(".Boo_Prison_Escape_time-slot:checked");
    const guestSelect = document.querySelector(".Boo_Prison_Escape_select").value;
    const button = document.querySelector(".continue_nex_step");

    if (selectedTime && guestSelect !== "") {
        button.classList.remove("disabled");
        button.removeAttribute("disabled");
    } else {
        button.classList.add("disabled");
        button.setAttribute("disabled", true);
    }
}

document.querySelectorAll(".Boo_Prison_Escape_time-slot").forEach(input => {
    input.addEventListener("change", function() {
        if (this.checked) {
            document.querySelectorAll(".Boo_Prison_Escape_time-slot").forEach(i => {
                if (i !== this) i.checked = false;
            });
        }
        checkSelection();
    });
});

document.querySelector(".Boo_Prison_Escape_select").addEventListener("change", checkSelection);
</script>

<style>
/* ================== SCOPED CUSTOM CSS ================== */
.escape_room_home_Customer_Reviews_section {
    margin-top: 50px;
}

.escape_room_home_Customer_Reviews_card {
    background: #161b22;
    height: 100%;
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 28px 25px;
    /* margin-bottom: 22px; */
}

.escape_room_home_Customer_Reviews_card p {
    font-size: 16px;
    color: rgb(209 213 219);
}

.escape_room_home_Customer_Reviews_avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #00d4ff;
    color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.escape_room_home_Customer_Reviews_verified {
    background: #198754;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 5px;
    margin-left: 5px;
}

.escape_room_home_Customer_Reviews_stars {
    color: #ffc107;
    font-size: 18px;
}

.escape_room_home_Customer_Reviews_footer {
    font-size: 18px;
    margin-top: 10px;
}

.escape_room_home_Customer_Reviews_footer i {
    color: #ffc107;
}

.nav-pills .nav-link.active {
    background-color: #dc3545;
}


.escape_room_home_Customer_Reviews_tabs {
    border-bottom: none;
    justify-content: center;
    margin-bottom: 60px;
}

.escape_room_home_Customer_Reviews_tabs a.nav-link {
    background: transparent;
    border: 2px solid #2c3e50;
    border-radius: 15px;
    color: #7fb3d3;
    font-weight: 600;
    font-size: 18px;
    padding: 15px 40px;
    margin: 0 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.escape_room_home_Customer_Reviews_tabs a.nav-link:hover {
    border-color: #00d4ff;
    color: #00d4ff;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
    transform: translateY(-2px);
}

.escape_room_home_Customer_Reviews_tabs a.nav-link.active {
    background: transparent;
    border-color: #00d4ff;
    color: #00d4ff;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from {
        box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
    }

    to {
        box-shadow: 0 0 40px rgba(0, 212, 255, 0.8);
    }
}

.escape_room_home_Customer_Reviews_summary {
    background: #161b22;
    height: 100%;
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 28px 25px;
    margin-top: 50px;
}

.escape_room_home_Customer_Reviews_summary h5 {
    margin-bottom: 20px;
    font-weight: 600;
}

.escape_room_home_Customer_Reviews_counter {
    text-align: center;
}

.escape_room_home_Customer_Reviews_counter .value {
    font-size: 28px;
    font-weight: bold;
}

.escape_room_home_Customer_Reviews_avg {
    color: #ffc107;
    /* yellow */
}

.escape_room_home_Customer_Reviews_recommend {
    color: #28a745;
    /* green */
}

.escape_room_home_Customer_Reviews_total {
    color: #00d4ff;
    /* blue */
}

.escape_room_home_contact_section {
    background: #000;
    color: #fff;
    text-align: center;
    padding: 60px 20px;
}

.escape_room_home_contact_section h2 {
    font-weight: 700;
    margin-bottom: 40px;
}

.escape_room_home_contact_item {
    margin-bottom: 30px;
}

.escape_room_home_contact_icon {
    font-size: 40px;
    color: #00d4ff;
    margin-bottom: 15px;
}

.escape_room_home_contact_item h5 {
    font-weight: 600;
    margin-bottom: 8px;
}

.escape_room_home_contact_item p {
    color: #b0bec5;
    margin: 0;
}

.Boo_Prison_Escap_main_heading {
    margin-bottom: 40px;
    text-align: center;
    color: #00d4ff;
}

.Boo_Prison_Escap_main_heading h2 {
    font-size: 40px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #ffff;
}

.Boo_Prison_Escap_main_heading p {
    font-size: 22px;
    color: #b0b0b0;
}

.Boo_Prison_Escape_calendar_box .flatpickr-calendar {
    width: 100% !important;
    max-width: 100% !important;
}

.Boo_Prison_Escape_calendar_box .flatpickr-days {
    width: 100% !important;
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    background-color: #191919;
}

.Boo_Prison_Escape_calendar_box .flatpickr-weekdays,
.Boo_Prison_Escape_calendar_box .flatpickr-month {
    background-color: #191919 !important;
}

.Boo_Prison_Escape_calendar_box span.flatpickr-weekday {
    font-size: 15px;
    color: rgb(255 255 255);
}

.Boo_Prison_Escape_calendar_box .flatpickr-day {
    width: 100% !important;
    background-color: #191919;
    color: #fff !important;
    max-width: 55px;
    height: 55px;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.Boo_Prison_Escape_calendar_box span.flatpickr-day.prevMonthDay,
.Boo_Prison_Escape_calendar_box span.flatpickr-day.nextMonthDay {
    color: #ffffff75 !important;
}

.Boo_Prison_Escape_calendar_box .dayContainer {
    width: 439px;
    min-width: auto;
    max-width: max-content;
    background-color: #191919;
}

.Boo_Prison_Escape_time_box {
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 28px 25px;
    margin-bottom: 22px;
}

.Boo_Prison_Escape_time_box .sub_heading {
    font-size: 19px;
    font-weight: 600;
    margin-bottom: 18px;
    color: #00d4ff;
    text-align: left;

}

.Booking_Summary_ng_summary_box {
    border-bottom: 1px dashed #00d4ff7d;
    padding-bottom: 14px;
}

.Boo_Prison_Escape_time-slot {
    display: none;
}

.Boo_Prison_Escape_time-slot-label {
    display: block;
    text-align: center;
    padding: 10px;
    border: 1px solid #00d4ff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #fff;
    font-size: 18px;
    animation: alternate 2s ease-in-out infinite alternate;
}

.Boo_Prison_Escape_time-slot:checked+.Boo_Prison_Escape_time-slot-label {
    background-color: #00d4ff;
    color: #000;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: Boo_Prison_Escape_glow 2s ease-in-out infinite alternate;
    font-weight: 600;
    font-size: 20px;
}

select.Boo_Prison_Escape_select {
    width: 100%;
    padding: 0 17px;
    border: 1px solid #00d4ff63;
    border-radius: 5px;
    background-color: #191919;
    color: #fff;
    box-shadow: 0 0 10px rgb(0 212 255 / 43%);
    outline: none;
    border-radius: 10px;
    height: 48px;
}

.Boo_Prison_Escape_booking_summary_box_row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.Boo_Prison_Escape_booking_summary_box_row span {
    font-size: 17px;
    color: #fff;
}

.Boo_Prison_Escape_booking_summary_box_row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
}

.escape_boo_summart_data {
    font-weight: 600;
    color: #00d4ff;
}

.escape_boo_summart_data_totale {
    font-weight: 600;
    color: #00d4ff;
    font-size: 22px;
    text-transform: uppercase;
}

.Boo_Prison_Escape_booking_summary_box_totals {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    border-top: 1px dashed #00d4ff7d;
    padding-top: 11px;
}

.Boo_Prison_Escape_booking_summary_box .next-button-wrapper {
    text-align: center;
    margin-top: 11px;
}

.choose-adventure .ca-title {
    font-weight: 700;
    color: #fff;
}

/* Tabs list cards */
.choose-adventure .ca-item {
    border-radius: 12px;
    text-align: left;
    transition: transform .15s ease, border-color .2s ease, background .2s ease;
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    transition: 0.3s ease;
    margin-bottom: 15px;
    padding: 14px 18px;
}


.choose-adventure .ca-item:hover {
    transform: translateY(-3px);
    background: transparent;
    border-color: #00d4ff;
    color: #00d4ff;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}

.choose-adventure .ca-item.active {
    background: transparent;
    border-color: #00d4ff;
    color: #00d4ff;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}

.choose-adventure .ca-item:focus {
    outline: 0;
    box-shadow: none;
}

.choose-adventure .ca-item-head {
    display: flex;
    align-items: center;
}

.choose-adventure .ca-item-head i {
    background: #534d4d;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #00d4ff;
    font-weight: 700;
    font-size: 15px;
    margin-right: 10px;
}

.choose-adventure .ca-item-name {
    font-weight: 700;
    color: #fff;
    font-size: 18px;
    margin: 0;
    padding: 0;
    text-transform: capitalize;
}

.choose-adventure .ca-item:nth-child(1) .ca-item-name {
    color: #d4a0ff;
}

.choose-adventure .ca-item:nth-child(2) .ca-item-name {
    color: red;
}

.choose-adventure .ca-item:nth-child(3) .ca-item-name {
    color: pink;
}


.choose-adventure .ca-item-sub {
    color: #797c81;
    font-size: 14px;
    margin: 0;
    padding: 0;
    line-height: normal;
}

.choose-adventure .ca-price {
    background: #00d4ff;
    color: #ffffff;
    padding: 0px 7px;
    border-radius: 3px;
    font-weight: 700;
    font-size: 12px;
    border: 1px solid #00d4ff;
}

.choose-adventure .ca-item-meta {
    margin-top: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.choose-adventure span.ca-difficulty.ca-medium,
.choose-adventure .ca-item-meta span.ca-item-meta_minutes {
    font-size: 13px;
}

.choose-adventure .ca-item-meta span.ca-item-meta_minutes {
    display: block;
    margin-left: 14px;
}

.choose-adventure .ca-item-meta>div {
    display: flex;
}

.choose-adventure .ca-rating {
    color: #fbbf24;
    margin-top: 4px;
    font-size: 11px;
    line-height: normal;
}

/* Right hero */
.choose-adventure_tab_data {
    text-align: center;
    /* padding: 20px 10px 10px; */
    /* padding-top: 100px; */
}

div#caTabsContent {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}



.choose-adventure .chip-hard {
    background: #2a1e1e;
    color: #111;
}

.choose-adventure .ca-heading {
    font-size: clamp(2.2rem, 5.5vw, 5rem);
    font-weight: 900;
    letter-spacing: .02em;
    color: #00d4ff;
    margin: 0 0 6px;
    text-transform: uppercase;
}

.choose-adventure .ca-subtitle {
    color: #cfd5de;
    font-size: 15px;
    max-width: 899px;
    margin: 0;
}

.choose-adventure .ca-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: center;
    margin: 26px 0;
}

span.ca-chip {
    background: #191919;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}

.choose-adventure .ca-badge {
    background: #191919;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}


/* small tweaks */
@media (max-width: 991.98px) {
    .choose-adventure .ca-heading {
        font-size: 2.2rem;
    }
}

/* <!-- ========== second part overview ========== --> */
.card_deatils_tabs {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: none;
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.card_deatils_tabs li.nav-item {
    width: 25%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .15s ease, border-color .2s ease, background .2s ease;
    background-color: #191919;
    border: none;
    overflow: hidden;

}

.card_deatils_tabs li.nav-item button {
    border: none;
    background: transparent;
    color: #fff;
    text-transform: uppercase;
    width: 100%;
    padding: 12px 3px;
    border-radius: 0px;
    overflow: hidden;
}

.card_deatils_tabs li.nav-item button:hover {
    color: #000000;
    background-color: #00d5ff7f;
    border: 0;
    border-radius: 0;
    font-weight: 600;
    opacity: 1;
}

.card_deatils_tabs li.nav-item button.active {
    color: #000000;
    background-color: #00d4ff;
    border: 0;
    border-radius: 0;
    font-weight: 600;
    opacity: 1;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}

.card_datals_main_data {
    margin-top: 20px;
}

.Room_Detail_overview_tabs {
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.Room_Detail_overview_tabs-title {
    font-size: 18px;
    color: #00d4ff;
    margin-bottom: 9px;
    text-transform: capitalize;
}

.Room_Detail_overview_tabs p {
    margin: 0 0;
}

.card_tails_faq .Room_Detail_overview_tabs {
    margin-bottom: 21px;
}

.card_deatils_image_card {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    height: 280px;
}

.card_deatils_image_card img {
    display: block;
    width: 100%;
    height: 100%;
    transition: transform 0.4s ease;
}

.card_deatils_image_card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.25);
}

.card_deatils_image_card:hover img {
    transform: scale(1.1);
}

/* ==================  */

.escape_room_home_comparison {
    margin-top: 68px;
    margin-bottom: 75px;
}

.comparison_card {
    background-color: #191919;
    border: 1px solid #00d4ff38;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.comparison_card img {
    width: 100%;
    height: 236px;
    margin-bottom: 10px;
    object-fit: cover;
}

.comparison_card:hover {
    transform: translateY(-5px);
    background: transparent;
    border-color: #00d4ff;
    color: #00d4ff;
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}

.comparison_card_content {
    padding: 0 17px;
}

p.comparison_card_content_data_items {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0;
    margin-bottom: 9px;
}

.badge {
    font-size: 0.75rem;
    padding: 5px 10px;
    border-radius: 10px;
}

.comparison_card_content_features span {
    background: #393938;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.8rem;
    margin-right: 5px;
    margin-bottom: 5px;
    display: inline-block;
    color: #bebebe;
}

.comparison_card_bnt {
    text-align: center;
    width: fit-content;
    margin: 0 auto;
    margin-top: 11px;
    margin-bottom: 10px;
}

.comparison_card .bg_bnt_custom {
    text-align: center;
    width: fit-content;
    margin: 0 auto;
}
</style>

<!-- === Video Modal ====== -->
<div class="modal fade blur-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel">Watch Trailer</h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    onclick="stopLocalVideo()">X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <!-- muted -->
                    <video id="localVideo" autoplay muted loop controls>
                        <source src="./assets/video/video.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include('includes/footer.php'); ?>

<!-- Book Prison Escape date -->
<script>
function getTodayInLosAngeles() {
    const now = new Date();
    const options = {
        timeZone: "America/Los_Angeles",
        year: "numeric",
        month: "2-digit",
        day: "2-digit"
    };
    const parts = new Intl.DateTimeFormat("en-US", options).formatToParts(now);
    const year = parts.find(p => p.type === "year").value;
    const month = parts.find(p => p.type === "month").value;
    const day = parts.find(p => p.type === "day").value;
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#Book-Prison-Date", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: getTodayInLosAngeles(),
        defaultDate: getTodayInLosAngeles()
    });
});
</script>