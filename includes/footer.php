<?php include_once(__DIR__ . "/../config.php"); ?>
</div> <!-- content close -->

<!-- footer begin -->
<footer>
    <div class="container">
        <div class="row">

            <!-- Areas + Pages -->
            <div class="col-lg-3">
                <div class="row reponsive_footer_grid">
                    <div class="col-lg-6 col-sm-6">
                        <div class="widget">
                            <h5>Pages</h5>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>indoor-real-life-escape-games">Escape Rooms</a></li>
                                <li><a href="<?php echo BASE_URL; ?>vr-games-at-flee-escape-vr-games">Virtual Reality</a></li>
                                <li><a href="<?php echo BASE_URL; ?>blog-list">Blog</a></li>
                                <li><a href="<?php echo BASE_URL; ?>contact-us">Contact</a></li>
                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hours + Contact -->
            <div class="col-lg-4" >
                <div class="footer_content_add" style="background-color: #333333;">
                    <div class="mb-8">
                        <h2 class="footer_address__box_headeign">HOURS OF OPERATION</h2>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300">MON - THURS</span>
                            <span class="text-gray-300">1pm - 9pm</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300">FRIDAY</span>
                            <span class="text-gray-300">1pm - 10pm</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300">SAT - SUN</span>
                            <span class="text-gray-300">12pm - 10pm</span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="footer_address__box_headeign mt-2">HOW TO FIND US</h2>
                        <p class="footer_address__box_p">
                            2222 152nd Ave NE, #112, Redmond, WA 98052
                        </p>
                    </div>

                    <div class="cont_footer_add">
                        <h2 class="footer_address__box_headeign">CONTACT</h2>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300"><b>Phone</b></span>
                            <span class="text-gray-300">
                                <a href="tel:4252871426" style="color:#00d4ff" class="text-gray-300" >425-287-1426</a>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300"><b>Email</b></span>
                            <span>
                                 <a class="text-gray-300" style="color:#00d4ff" href="mailto:info@fleeescape.com" >info@fleeescape.com</a>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-300"><b>Address</b></span>
                            <div class="text-end" style="width: 80%;">
                                <span class="d-block text-gray-300">2222 152nd Ave NE,</span>
                                <span class="d-block text-gray-300">#112, Redmond WA 98052</span>
                                <span class="d-block text-gray-300">(Next to Goodwill Redmond)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="col-lg-5">
                <div class="widget">
                    <div class="footer_map_grid">
                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2688.771104985403!2d-122.13695600000001!3d47.6305805!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54906da768f115c9%3A0x2b340163b55c9bae!2sFlee%20Escape%20Rooms%20%26%20VR%20Arena%20Redmond!5e0!3m2!1sen!2sin!4v1762327728378!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Subfooter -->
    <div class="subfooter">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-sm-6">
                    <!--Copyright ﾃつｩ <?php echo date("Y"); ?> <?php echo SITE_TITLE; ?>. All Rights Reserved. || Developed By <a href="https://www.indiawebsoft.com/" class="copy_right_text">India Websoft</a>-->
                </div>
                <div class="col-lg-6">
                    <div class="footer_soci_media">
                        <ul>
                            <li><a href="https://www.facebook.com/fleeescape?rdid=0vr7NRmHjw1fvQ7d&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1BLezmed8S%2F#" target="_blank"><i
                                        class="fa-brands fa-facebook-f"></i></a></li>
                            <li><a href="http://www.yelp.com/biz/flee-room-escape-game-redmond" target="_blank"><i
                                        class="fa-brands fa-yelp"></i></a></li>
                            <li><a href="https://www.youtube.com/@fleeescaperoom3366?si=iE8JFOgHh52pa91A" target="_blank"><i
                                        class="fa-brands fa-youtube"></i></a></li>
                            <li><a href="https://www.instagram.com/flee_escapegame?igsh=MWM2YWNveGNjM3RzMA%3D%3D" target="_blank"><i class="fab fa-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
                <!--<div class="col-lg-4 col-sm-6 text-lg-end text-sm-start">-->
                <!--    <ul class="menu-simple">-->
                <!--        <li><a href="#">Terms &amp; Conditions</a></li>-->
                <!--        <li><a href="#">Privacy Policy</a></li>-->
                <!--    </ul>-->
                <!--</div>-->
            </div>
        </div>
    </div>
</footer>
<!-- footer close -->

<!-- === Video Modal ====== -->


<!-- JS Files -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/plugins.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/designesia.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/swiper.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/custom-marquee.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/escape-room.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/bookeo-error-handler.js"></script>
<script src="<?php echo ASSETS_URL; ?>js/custom-swiper-1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>






<script>
$(document).ready(function() {
    var owl = $('#Game_Servers');
    owl.owlCarousel({
        items: 8,
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: false
    });
    $('.play').on('click', function() {
        owl.trigger('play.owl.autoplay', [5000])
    })
    $('.stop').on('click', function() {
        owl.trigger('stop.owl.autoplay')
    })
});

$(window).on('load', function () {

    var owl = $('#ourclinets');

    owl.owlCarousel({
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 2000,
        autoplayHoverPause: false,

        smartSpeed: 600,         
        slideTransition: 'linear',

        responsive: {
            0: {
                items: 3,
                margin: 5,
            },
            768: {
                items: 4
            },
            992: {
                items: 6
            }
        }
    });

    $('.play').on('click', function () {
        owl.trigger('play.owl.autoplay', [2000]);
    });

    $('.stop').on('click', function () {
        owl.trigger('stop.owl.autoplay');
    });

});

</script>

<script>
// Counter animation on scroll
(function() {
    var counters = document.querySelectorAll('.counter');
    var started = false;

    function animateCounters() {
        if (started) return;
        var section = document.getElementById('counter-section');
        if(!section) return;
        var rect = section.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            counters.forEach(function(counter) {
                var target = +counter.getAttribute('data-target');
                var duration = 2000;
                var start = 0;
                var increment = target / (duration / 16);

                function updateCounter() {
                    start += increment;
                    if (start < target) {
                        counter.innerText = Math.floor(start).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                }
                updateCounter();
            });
            started = true;
        }
    }
    window.addEventListener('scroll', animateCounters);
    window.addEventListener('load', animateCounters);
})();
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.scroll_main_class_Swc[style*="overflow: scroll"]');
    if(!container) return;
    const boxes = container.querySelectorAll('.scroll_box_anim');

    let isScrolling = false;

    function checkBoxes() {
        const scrollTop = container.scrollTop;
        const scrollHeight = container.scrollHeight - container.clientHeight;
        const scrollPercent = scrollTop / scrollHeight;

      
        boxes.forEach(box => box.classList.remove('active'));

     
        if (scrollTop > 0) {
            boxes[0].classList.add('active');
        }

        // Dusra box: 40%
        if (boxes[1] && scrollPercent >= 0.4) {
            boxes[1].classList.add('active');
        }

        // Teesra box: 80% 
        if (boxes[2] && scrollPercent >= 0.8) {
            boxes[2].classList.add('active');
        }
    }

    container.addEventListener('scroll', function() {
        isScrolling = true;
        checkBoxes();
    });

    window.addEventListener('resize', checkBoxes);
});
</script>

<!-- ===============video========================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoContainer = document.querySelector('.video_play_add');
    if (!videoContainer) return; 
    const video = videoContainer.querySelector('video');
    const playButton = videoContainer.querySelector('.play_button_video');

    playButton.addEventListener('click', function() {
        if (video.paused) {
            video.play();
            playButton.style.display = 'none';
            playButton.classList.remove('no-overlay');
        } else {
            video.pause();
            playButton.style.display = 'flex';
            playButton.classList.add('no-overlay'); // Remove overlay
        }
    });

    video.addEventListener('pause', function() {
        playButton.style.display = 'flex';
        playButton.classList.add('no-overlay'); // Remove overlay
    });

    video.addEventListener('ended', function() {
        playButton.style.display = 'flex';
        playButton.classList.add('no-overlay'); // Remove overlay
    });

    video.addEventListener('play', function() {
        playButton.classList.remove('no-overlay'); // Restore overlay
    });
});
</script>

<!-- ============================================== -->
<script>
$(document).ready(function () {

    // --- 1. Escape Room Reviews Slider ---
    const escapeRoomSlider = $('.escapeRoomReviewsSlider').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        dotsEach: true,
        smartSpeed: 500,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            1200: { items: 2 }
        }
    });

    // Mouse Wheel Control for Escape Room
    let lastScrollTime3 = 0;
    $('.escapeRoomReviewsSlider').on('wheel', '.owl-stage', function (e) {
        const deltaX = e.originalEvent.deltaX;
        if (Math.abs(deltaX) < 30) return;
        e.preventDefault();
        const now = Date.now();
        if (now - lastScrollTime3 < 400) return;
        lastScrollTime3 = now;
        if (deltaX > 0) escapeRoomSlider.trigger('next.owl.carousel');
        else escapeRoomSlider.trigger('prev.owl.carousel');
    });

    // --- 2. Our Customers Love Slider ---
    const ourcustomerSlider = $('.Our_customers_love').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        dotsEach: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        smartSpeed: 500,
        responsive: {
            0: { items: 1 },
            600: { items: 3 },
            1000: { items: 4 }
        }
    });

    // Mouse Wheel Control for Our Customers
    let lastScrollTime1 = 0;
    $('.Our_customers_love').on('wheel', '.owl-stage', function (e) {
        const deltaX = e.originalEvent.deltaX;
        if (Math.abs(deltaX) < 30) return;
        e.preventDefault();
        const now = Date.now();
        if (now - lastScrollTime1 < 400) return;
        lastScrollTime1 = now;
        if (deltaX > 0) ourcustomerSlider.trigger('next.owl.carousel');
        else ourcustomerSlider.trigger('prev.owl.carousel');
    });

    // --- 3. New All Game Slider ---
    const newGameSlider = $('.new_all_game_slider').owlCarousel({
        loop: true,
        margin: 20,
        center: false,
        nav: true,
        dots: true,
        dotsEach: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        smartSpeed: 500,
        responsive: {
            0: { items: 1 },
            600: { items: 3 },
            1000: { items: 3 }
        }
    });

    // Mouse Wheel Control for New Games
    let lastScrollTime2 = 0;
    $('.new_all_game_slider').on('wheel', '.owl-stage', function (e) {
        const deltaX = e.originalEvent.deltaX;
        if (Math.abs(deltaX) < 30) return;
        e.preventDefault();
        const now = Date.now();
        if (now - lastScrollTime2 < 400) return;
        lastScrollTime2 = now;
        if (deltaX > 0) newGameSlider.trigger('next.owl.carousel');
        else newGameSlider.trigger('prev.owl.carousel');
    });

});
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {

    if (window.innerWidth >= 786) {
        // Only desktop par AOS enable
        AOS.init({
            duration: 700,
            once: true,
            offset: 120,
            easing: 'ease-in-out',
        });
    } else {
        document.querySelectorAll("[data-aos]").forEach(el => {
            el.removeAttribute("data-aos");
        });
    }

});
</script>





<!-- =========  picker TOP NEXT ================ -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const dateInput = document.getElementById('bookingDateInput');
    const pickDateBtn = document.getElementById('pickDateBtn');
    if (!dateInput || !pickDateBtn) return;

    // Init flatpickr
    const fp = flatpickr(dateInput, {
        dateFormat: "D, F j, Y",
        defaultDate: "today",
        minDate: "today",
        disableMobile: true,
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                const selectedDate = selectedDates[0];

                // Set all datepickers to this date
                allDatepickers.forEach(instance => {
                    instance.setDate(selectedDate, true);
                });

                // Update prev-all visibility
                togglePrevAllVisibility();
            }
        }
    });

    // Show flatpickr on button click
    pickDateBtn.addEventListener('click', function() {
        fp.open();
    });
});
</script>

 <script>
    window.addEventListener('scroll', function () {
      const scrollY = window.scrollY;

      if (scrollY > 200) {
        document.body.classList.add('scrolledclass');
      } else {
        document.body.classList.remove('scrolledclass');
      }
    });
  </script>
  
<script>
document.addEventListener("DOMContentLoaded", function () {


    document.querySelectorAll('.modal').forEach(modal => {

        modal.addEventListener('shown.bs.modal', function () {
            const video = modal.querySelector("video");
            if (video) {
                video.muted = false;    
                video.play().catch(() => {
                 
                    video.muted = true;
                    video.play();
                });
            }
        });


        modal.addEventListener('hidden.bs.modal', function () {

            modal.querySelectorAll("video").forEach(oldVideo => {

                const src = oldVideo.querySelector("source")?.getAttribute("src");

                oldVideo.pause();
                oldVideo.currentTime = 0;

                // fresh video element create
                const newVideo = oldVideo.cloneNode(false);
                newVideo.setAttribute("controls", "");
                newVideo.setAttribute("playsinline", "");
                newVideo.muted = false;

                if (src) {
                    const source = document.createElement("source");
                    source.src = src;
                    source.type = "video/mp4";
                    newVideo.appendChild(source);
                }

                oldVideo.parentNode.replaceChild(newVideo, oldVideo);
            });

        });

    });

});
</script>






    <!--// --- FIX: Remove Flatpickr duplicate input (keep only first one) ----->
<script>
setTimeout(() => {
  const duplicateInputs = document.querySelectorAll('.flatpickr-mobile');
  duplicateInputs.forEach(input => input.remove());
  const mainInputs = document.querySelectorAll('.custom-datepicker_input');
  mainInputs.forEach(input => {
    input.classList.remove('d-none');
    input.style.display = 'block';
    input.style.visibility = 'visible';
    input.style.opacity = '1';
    input.type = 'text';
  });
  console.log("✅ Removed Flatpickr duplicate inputs — only main input kept.");
}, 200);
</script>

<!--// =============== scroling ================-->

<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🎉 Book Birthday Party scroll
    document.querySelectorAll(".scrollToParty").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            const target = document.getElementById("party-package");
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        });
    });

    // 📩 Request Custom Quote scroll
    document.querySelectorAll(".scrollToContact").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            const target = document.getElementById("birt_scroll_opne_contact");
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        });
    });

});
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Dono specific IDs ko target karein
    const inputs = document.querySelectorAll('#event_date, #enq_date');

    inputs.forEach(input => {
        // 2. Type ko 'text' mein badlein (Taaki browser ka default dd-mm-yyyy hat jaye)
        input.type = "text";
        
        // 3. Placeholder set karein
        input.setAttribute("placeholder", "Planned Event Date");

        // 4. Flatpickr ko initialize/re-initialize karein
        flatpickr(input, {
            dateFormat: "m-d-Y", // Ya jo bhi aapka format ho
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
                // Ensure placeholder stays "Planned Event Date"
                instance.input.placeholder = "Planned Event Date";
            }
        });
    });
});
</script>

</body>

</html>
