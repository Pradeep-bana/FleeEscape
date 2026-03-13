let timerInterval = null;
let timerEndTime = null;
window.cartTimerInterval = null;

function startPersistentTimer() {
    if (localStorage.getItem('cartTimerExpired') === 'true'){
        cartCount = 0;
        return;
    }
    const minutes = window.CART_TIMER_MINUTES || 10; 
    const TIMER_DURATION = minutes * 60 * 1000;
    let endTime = parseInt(localStorage.getItem('cartTimerEnd'));
    if (!endTime || Date.now() > endTime) {
        endTime = Date.now() + TIMER_DURATION;
        localStorage.setItem('cartTimerEnd', endTime);
    }
    const timerWrapper = document.querySelector(".timer_wrapper");
    if(timerWrapper) timerWrapper.style.display = "block";
    // Clear BOTH intervals so the old startTimer() never conflicts
    if (window.cartTimerInterval) clearInterval(window.cartTimerInterval);
    if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }

    // Update function
    function updateDisplay() {
        const now = Date.now();
        const remaining = endTime - now;

        if (remaining <= 0) {
            // Time is up
            clearInterval(window.cartTimerInterval);
            localStorage.removeItem('cartTimerEnd'); // Clear storage
            localStorage.setItem('cartTimerExpired', 'true');
            // document.querySelector(".timer_wrapper").style.display = "block";
            window.location.reload();
            return;
        }

        const minutes = Math.floor(remaining / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);
        document.querySelector(".timer_display").innerText =
            `${minutes}:${seconds.toString().padStart(2, "0")}`;
    }
    updateDisplay(); 
    window.cartTimerInterval = setInterval(updateDisplay, 1000);
}

function stopPersistentTimer() {
    if (window.cartTimerInterval) clearInterval(window.cartTimerInterval);
    if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
    localStorage.removeItem('cartTimerEnd');
    localStorage.removeItem('cartTimerExpired'); 
    const timerWrapper = document.querySelector(".timer_wrapper");
    if(timerWrapper) timerWrapper.style.display = "none";
}
// Add this at the very top of booking.js
function logBookeoError(msg, details) {
    // If details is an object (like the full response), convert to string
    if (typeof details === 'object') {
        details = JSON.stringify(details);
    }
    const formData = new FormData();
    formData.append('error', msg);
    formData.append('context', details);
    navigator.sendBeacon('log_bookeo_error.php', formData);
}

function updateBundleOffers(cartCount) {
    const offer1 = document.querySelector('.offer-card[data-offer="1"]');
    const offer2 = document.querySelector('.offer-card[data-offer="2"]');

    if (!offer1 || !offer2) return;

    if (cartCount === 1) {
        offer1.style.display = 'block';
        offer2.style.display = 'block';
    } else if (cartCount === 2) {
        offer1.style.display = 'none';
        offer2.style.display = 'block';
    } else {
        offer1.style.display = 'none';
        offer2.style.display = 'none';
    }
}

// Ã¢Å“â€¦ Update buttons (disable booked + disable guest count controls + highlight slot)
// 1. Create a global variable to store cart items locally
window.latestCart = [];

// 2. Updated updateBookedButtons function
function updateBookedButtons(cartItems) {
    // 1. Save cart to global variable
    window.latestCart = cartItems || [];

    // 2. Select ALL types of Continue buttons (Escape, Party, and Event)
    const allButtons = document.querySelectorAll(".continue_nex_step, .continue_next_step_party, .continue_next_step_event");

    allButtons.forEach(btn => {
        const gameId = btn.getAttribute("data-game-id");
        
        // Safety check: if button has no game ID, skip it
        if (!gameId) return;

        // Find the CURRENTLY selected radio button for this specific game
        // (This works for all tabs because they all use the name="lift-time-{id}" format)
        const selectedSlot = document.querySelector(`input[name="lift-time-${gameId}"]:checked`);

        let isBooked = false;

        // Check if this specific Slot (EventID) is in the cart
        if (selectedSlot) {
            const selectedEventId = selectedSlot.getAttribute("data-eventid");
            
            isBooked = window.latestCart.some(item => 
                item.gameId === gameId && item.eventId === selectedEventId
            );
        }

        // Logic to handle Guest count wrappers (works for all tabs if they use this structure)
        const guestWrapper = document.querySelector(`#guest-count-${gameId}`)?.closest(".guest-count-wrapper") 
                          || document.querySelector(`#guest-count-display-${gameId}`)?.closest(".guest-count-wrapper");

        if (isBooked) {
            // DISABLE BUTTON
            btn.innerText = "Added to Cart";
            btn.classList.add("booked_btn");
            btn.disabled = true;
            btn.style.opacity = "0.5";

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn, .plus-btn, .minus-btn").forEach(b => b.disabled = true);
            }
        } else {
            // ENABLE BUTTON
            btn.innerText = "Continue";
            btn.classList.remove("booked_btn");
            btn.disabled = false;
            btn.style.opacity = "1";

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn, .plus-btn, .minus-btn").forEach(b => b.disabled = false);
            }
        }
    });
}

// Helper to visually disable/style radio buttons that are already in the cart
function markInCartSlots(cartItems) {
    cartItems.forEach(item => {
        // Find input with matching Event ID
        const slotInput = document.querySelector(`input[data-eventid="${item.eventId}"]`);
        if (slotInput) {
            // You can choose to disable the input or just style the label
            // slotInput.disabled = true; 
            slotInput.closest("label")?.classList.add("slot-in-cart"); // Add CSS for this class if you want
        }
    });
}
function loadCart() {
    fetch("cart_view.php?live=1")
        .then(res => res.text())
        .then(html => {
            // --- BLOCK 1: Check for Redirect (Empty Cart) ---
            try {
                const json = JSON.parse(html);
                if (json.redirect) {
                    console.log("Cart is empty. Resetting UI.");
                    stopPersistentTimer();
                    document.querySelectorAll('.cart-count').forEach(badge => {
                        badge.textContent = "0";
                        badge.style.display = 'none';
                    });
                    const progressSteps = document.querySelectorAll('.progress-step');
                    progressSteps.forEach((step, index) => {
                        const circle = step.querySelector('.step-circle');
                        if (index === 0) {
                            if(circle) circle.classList.add('active');
                            if(circle) circle.classList.remove('visited');
                        } else {
                            if(circle) circle.classList.remove('active', 'visited');
                        }
                    });
                    const stepContents = document.querySelectorAll('.step-content');
                    let activeIndex = 0;
                    stepContents.forEach((content, index) => {
                        if (content.classList.contains('active')) activeIndex = index;
                    });
                    if (activeIndex > 0) {
                         const firstStepBtn = document.querySelector('.progress-step'); 
                         if(firstStepBtn) firstStepBtn.click();
                    }

                    document.getElementById("summary-output").innerHTML = "<p class='text-center mt-3 text-white'>Your cart is empty.</p>";
                    return;
                }
            } catch (e) {
                // Not JSON (It's HTML), so proceed normally.
            }
            document.getElementById("summary-output").innerHTML = html;

            const cartItemsEls = document.querySelectorAll('#summary-output .summary-row-group');
            const cartCount = cartItemsEls.length;
            
            if (localStorage.getItem('cartTimerExpired') === 'true') {
                fetch('clear_expired_cart.php')   // ← new dedicated file
                    .then(() => {
                        document.querySelectorAll('.cart-count').forEach(badge => {
                            badge.textContent = "0";
                            badge.style.display = 'none';
                        });
                        document.getElementById("summary-output").innerHTML = 
                            "<p class='text-center mt-3 text-white'>Your cart is empty.</p>";
                    });
                return;
            }

            // *** NEW: Handle Timer Logic Here ***
            if (cartCount > 0) {
                startPersistentTimer(); // <--- Resume or Start Timer
            } else {
                stopPersistentTimer();  // <--- Safety check
            }
            document.querySelectorAll('.cartUrl').forEach(link => {
                if (cartCount > 0) {
                    link.href = "/booking?customer-details";
                }
            });
            document.querySelectorAll('.cart-count').forEach(badge => {
                badge.textContent = cartCount;
                badge.style.display = cartCount > 0 ? 'inline-block' : 'block';
            });

            updateBundleOffers(cartCount);
            fetch("get_cart.php")
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data.cart)) {
                        updateBookedButtons(data.cart);
                    }
                });
            const totalsDiv = document.getElementById("bookeo-totals");
            if (!totalsDiv) return;
    
            let totals = {};
            try {
                totals = JSON.parse(totalsDiv.dataset.totals);
                window.bookeoTotals = totals;
            } catch (err) {
                return;
            }
            document.getElementById("subtotal").innerText = "$" + Number(totals.subtotal).toFixed(2);

            let admTax = 0, redTax = 0;
            if (Array.isArray(totals.taxes)) {
                totals.taxes.forEach(t => {
                    if (t.label.includes("Admission")) admTax = t.amount;
                    if (t.label.includes("Redmond")) redTax = t.amount;
                });
            }

            document.getElementById("admission-tax").innerText = "$" + Number(admTax).toFixed(2);
            document.getElementById("redmond-tax").innerText = "$" + Number(redTax).toFixed(2);
            document.getElementById("grand-total").innerText = "$" + Number(totals.grandTotal).toFixed(2);

            if (totals.discount && totals.discount > 0) {
                document.getElementById("discount-amount").innerText = "-$" + Number(totals.discount).toFixed(2);
                document.getElementById("discount-row").style.display = "grid";
            } else {
                document.getElementById("discount-row").style.display = "none";
            }
        })
        .catch(err => console.error("Cart load error:", err));
}

function loadAddons() {
    fetch("load_addons.php")
        .then(res => res.text())
        .then(html => {
            document.querySelector(".add_on_section").innerHTML = html;
        });
}


document.addEventListener("DOMContentLoaded", loadCart);

function startTimer() {
    if (timerInterval) return;
    timerEndTime = Date.now() + (3 * 60 * 1000);
    document.querySelector(".timer_wrapper").style.display = "block";

    timerInterval = setInterval(() => {
        let remaining = timerEndTime - Date.now();
        if (remaining <= 0) {
            clearInterval(timerInterval);
            timerInterval = null;
            // document.querySelector(".timer_wrapper").style.display = "none";
            window.location.reload();
            return;
        }
        let minutes = Math.floor(remaining / 1000 / 60);
        let seconds = Math.floor((remaining / 1000) % 60);
        document.querySelector(".timer_display").innerText =
            `${minutes}:${seconds.toString().padStart(2, "0")}`;
    }, 1000);
}

document.addEventListener("click", function(e) {
  // -------------------------------
  // REGULAR GAME CONTINUE BUTTON
  // -------------------------------
  if (e.target.classList.contains("continue_nex_step") && !e.target.disabled) {
    const btn = e.target;
    const productCode = btn.getAttribute("data-game-id");
    const gameName    = btn.getAttribute("data-game-name");
    const guestCount  = document.getElementById(`guest-count-${productCode}`).innerText;
    const unitPrice   = document.getElementById(`price-${productCode}`)
                            .innerText.replace("/Guest", "").trim();
    const selectedSlot = document.querySelector(`input[name="lift-time-${productCode}"]:checked`);

    let slot = "No slot";
    let eventId = "";
    if (selectedSlot) {
        slot = selectedSlot.value;
        eventId = selectedSlot.getAttribute("data-eventid");
         dataAvailable = selectedSlot.getAttribute("data-available");
    }

    document.getElementById("stepLoader").style.display = "flex";

    fetch("cart_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "add_to_cart",
            gameId: productCode,
            gameName: gameName,
            slot: slot,
            eventId: eventId,
            guests: guestCount,
            price: unitPrice,
            dataAvailable: dataAvailable
        })
    })
.then(res => res.json())
.then(response => {

    if (response.status !== 'success') {
        // DB save itself failed (duplicate, validation, etc.)
        console.error("Cart error:", response.message);
        document.getElementById("stepLoader").style.display = "none";
        alert(response.message || "Failed to add item.");
        return;
    }

    // cart_session.php succeeded (DB saved).
    // Now let apply_code.php create ALL holds correctly in one pass.
    // Pass the promo code cart_session detected (BMSM_10/BMSM_20/empty)
    // OR any existing voucher the user already applied.
    const existingCode = document.getElementById("giftCodeInput")?.value?.trim() || "";
    const promoCode    = response.promo || "";
    const codeToSend   = existingCode || promoCode;

    fetch("apply_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "code=" + encodeURIComponent(codeToSend)
    })
    .then(r => r.json())
    .then(holdResponse => {

        if (holdResponse.status === 'hold_error') {
            // Slot unavailable — item was auto-removed from cart by apply_code.php
            const failed = holdResponse.failed_items || [];
            const names  = failed.map(f => f.message || "Unknown slot").join("\n");
            alert("Sorry, the following could not be reserved and were removed:\n" + names);
        }

        // Reload cart regardless — apply_code already cleaned up any failed items
        localStorage.removeItem('cartTimerExpired');
        loadCart();
        loadAddons();
        if (response.cart) updateBookedButtons(response.cart);

        // Modal / step logic
        if (response.cart) {
            if (response.cart.length < 3) {
                const modalEl = document.getElementById('timeslotModal');
                if (modalEl) new bootstrap.Modal(modalEl).show();
            } else {
                changeStep(1);
            }
        }
    })
    .catch(err => {
        console.error("apply_code.php error:", err);
        // Even if apply_code fails unexpectedly, reload cart so UI stays consistent
        loadCart();
    })
    .finally(() => {
        document.getElementById("stepLoader").style.display = "none";
    });

})
.catch(err => {
    console.error("cart_session.php error:", err);
    document.getElementById("stepLoader").style.display = "none";
    alert("Something went wrong. Please try again.");
});
  }

  // -------------------------------
  // PARTY PACKAGE CONTINUE BUTTON
  // -------------------------------
async function handleContinueNextStepParty(e) {
  const btn = e.target.closest(".continue_next_step_party");
  if (!btn || btn.disabled) return;

  // ←←← यही 3 लाइनें जोड़ी हैं – यही आपकी पूरी समस्या खत्म कर देंगी!
  if (btn.dataset.processing === "true") return;           // डबल क्लिक रोकता है
  btn.dataset.processing = "true";                          // फ्लैग सेट करो
  btn.disabled = true;                                      // बटन disable करो

  try {
    const productCode = btn.dataset.gameId;
    const gameName = btn.dataset.gameName;
    const guestCount = document.getElementById(`guest-count-display-${productCode}`).innerText;
    const extraPrice = document.getElementById(`extra-price-${productCode}`).innerText || 0;
    const perGuestPrice = document.getElementById(`per-guest-price-${productCode}`).value || 0;
    const totalPrice = document.getElementById(`price-${productCode}`).value;
    const selectedSlot = document.querySelector(`input[name="lift-time-${productCode}"]:checked`);
    let slot = "No slot";
    let eventId = "";
    if (selectedSlot) {
       slot = selectedSlot.value;
        eventId = selectedSlot.getAttribute("data-eventid");
    }

    document.getElementById("stepLoader")?.style?.setProperty("display", "flex");

    let req = await fetch("party_cart_session.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "add_party_cart",
        slot: slot,
        gameId: productCode,
        eventId: eventId,
        gameName: gameName,
        price: totalPrice,
        additional_guest: guestCount,
        per_guest_price: perGuestPrice,
        total_additional_price: extraPrice
      })
    });

    const res = await req.json();
    document.getElementById("stepLoader")?.style?.setProperty("display", "none");

    // API FAILURE → SHOW BOOKEO ERROR MODAL
    if (res.status === "slot_error") {
            const msg = res.message || "Failed to reserve slot.";
            // --- LOGGING CODE ---
            logBookeoError(msg, {
                type: "Party Package",
                gameId: productCode,
                guests: guestCount,
                full_response: res 
            });
            // --------------------

        document.getElementById("bookeoErrorMessage").innerText =
            res.message || "Failed to reserve slot. Please try again.";
        const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
        errorModal.show();
        return;
    }
  if (productCode === "41551LAM3LY18570132661") {
       loadCart();
    loadAddons();
        changeStep(1);
        return;
    }
    // SUCCESS → load addons + show booking modal
    
    fetch("apply_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
    })
    .then(() => {
        if (typeof loadCart === "function") loadCart();
    });
    loadAddons();
    const modalEl = document.getElementById("partymodalform");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

  } finally {
    // ←←← हमेशा processing flag हटाओ (success या fail दोनों में)
    btn.dataset.processing = "false";
    btn.disabled = false;
  }
}

// ←←← पहले पुराना listener हटाओ, फिर नया लगाओ → duplicate attach कभी नहीं होगा
document.removeEventListener("click", handleContinueNextStepParty);
document.addEventListener("click", handleContinueNextStepParty);









document.addEventListener("click", async function(e) {
    const btn1 = e.target.closest(".continue_next_step_event");
    if (btn1 && !btn1.disabled) {

        // Prevent double click
        if (btn1.dataset.processing === "true") return;
        btn1.dataset.processing = "true";
        btn1.disabled = true;

        const productCode = btn1.getAttribute("data-game-id");
        const gameName = btn1.getAttribute("data-game-name");
        const players = document.getElementById(`players-${productCode}`).value;
        const totalPrice = document.getElementById(`price-${productCode}`).value;
        const selectedSlot = document.querySelector(`#timeSlots-${productCode} input[name="lift-time-${productCode}"]:checked`);
        
        let slot = "No slot";
        let eventId = "";
        if (selectedSlot) {
            slot = selectedSlot.value;
            eventId = selectedSlot.getAttribute("data-eventid");
        }

        document.getElementById("stepLoader").style.display = "flex";

        try {
            const response = await fetch("event_cart_session.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "add_party_cart",
                    gameId: productCode,
                    gameName: gameName,
                    slot: slot,
                    eventId: eventId,
                    players: players,
                    price: totalPrice
                })
            });

            const res = await response.json();

            if (res.status === "slot_error") {
                console.log('kaka error agya');
                console.log(res);
                
                const msg = res.message || "Failed to reserve slot.";
                // --- LOGGING CODE ---
                logBookeoError(msg, {
                    type: "Event Room",
                    gameId: productCode,
                    guests: guestCount,
                    full_response: res 
                });
                // --------------------
                
                // SHOW ERROR MODAL (NOT ALERT)
                document.getElementById("bookeoErrorMessage").innerText = 
                    res.message || "Failed to reserve slot. Please try again.";
                const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
                errorModal.show();
                return;
            }

            if (res.status === "success") {
                loadCart();
                changeStep(1);
                 changeStep(1);
            }

        } 
        // catch (err) {
        //     console.error(err);
        //     document.getElementById("bookeoErrorMessage").innerText = "Network error. Please try again.";
        //     const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
        //     errorModal.show();
        // }
        finally {
            document.getElementById("stepLoader").style.display = "none";
            btn1.dataset.processing = "false";
            btn1.disabled = false;
        }
    }
});

  // -------------------------------
  // DELETE CART ITEM
  // -------------------------------
  if (e.target.closest(".delete_card")) {
      console.log("delete card clicked");
    const index = e.target.closest(".delete_card").getAttribute("data-index");

    const modal = document.getElementById("deleteConfirmModal");
    const modalBox = document.getElementById("deleteModalBox");

    const title = document.getElementById("deleteModalTitle");
    const text = document.getElementById("deleteConfirmText");
    const actions = document.getElementById("deleteActions");
    const loading = document.getElementById("deleteLoading");

    modal.style.display = "flex";

    document.getElementById("confirmDeleteBtn").onclick = function () {
        title.style.display = "none";
        text.style.display = "none";
        actions.style.display = "none";
        loading.style.display = "flex";
        modalBox.classList.add("loading-mode");

        fetch("remove_from_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "index=" + index
        })
        .then(res => res.json())
        .then(data => {
            document.querySelector(".applied_code .code").textContent = "Have a promotion or voucher code?";
            document.getElementById("giftCodeInput").value = "";
            modal.style.display = "none";
            title.style.display = "block";
            text.style.display = "block";
            actions.style.display = "flex";
            loading.style.display = "none";
            modalBox.classList.remove("loading-mode");

            loadCart();

            setTimeout(() => {
                const cartCount = data.cartCount;
                // alert(cartCount);
                if (cartCount === 0) {
                    window.location.href = "booking.php?choose-experience";
                }
            }, 300);
        });
    };

    document.getElementById("cancelDeleteBtn").onclick = function () {
        modal.style.display = "none";
    };
  }
});
