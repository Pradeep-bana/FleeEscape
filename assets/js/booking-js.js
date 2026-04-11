let timerInterval = null;
let timerEndTime = null;
window.cartTimerInterval = null;
let isExpiringCartNow = false;
let lastExpireAttemptAt = 0;
const EXPIRE_RETRY_MS = 5000;

function getTimerDurationMs() {
    const minutes = Number(window.CART_TIMER_MINUTES || 3);
    return Math.max(1, minutes) * 60 * 1000;
}

function refreshSlotsAfterExpire() {
    if (typeof fetchSlotsForProducts !== "function") return;

    const productIds = Array.from(document.querySelectorAll(".custom-datepicker_input[data-product]"))
        .map(el => (el.getAttribute("data-product") || "").trim())
        .filter(Boolean);

    if (!productIds.length) return;

    const globalPicker = document.getElementById("custom-datepicke2");
    const rawDate = (globalPicker && (globalPicker.getAttribute("data-rawdate") || globalPicker.dataset.rawdate))
        || new Date().toISOString().slice(0, 10);

    fetchSlotsForProducts(productIds, rawDate);
}

function forceExpireCart(reason = "timer") {
    if (isExpiringCartNow) return Promise.resolve({ status: "busy" });
    lastExpireAttemptAt = Date.now();
    isExpiringCartNow = true;

    localStorage.removeItem("cartTimerEnd");
    localStorage.setItem("cartTimerExpired", "true");

    return fetch("expire_cart.php?reason=" + encodeURIComponent(reason), { cache: "no-store" })
        .then(r => r.json())
        .catch(() => ({ status: "network_error" }))
        .finally(() => {
            isExpiringCartNow = false;
        });
}

function startPersistentTimer() {
    if (localStorage.getItem('cartTimerExpired') === 'true') {
        return;
    }
    const timerDuration = getTimerDurationMs();
    let endTime = parseInt(localStorage.getItem('cartTimerEnd') || '0', 10);
    if (!endTime || Date.now() > endTime) {
        endTime = Date.now() + timerDuration;
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
            forceExpireCart("persistent_timer")
                .finally(() => {
                    if (typeof loadCart === "function") loadCart();
                    refreshSlotsAfterExpire();
                    window.dispatchEvent(new CustomEvent("flee:cartExpired"));
                });
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
    window.latestCart = cartItems || [];
    const allButtons = document.querySelectorAll(".continue_nex_step, .continue_next_step_party, .continue_next_step_event");

    allButtons.forEach(btn => {
        const gameId = btn.getAttribute("data-game-id");
        if (!gameId) return;

        const selectedSlot = document.querySelector(`input[name="lift-time-${gameId}"]:checked`);
        let isBooked = false;

        if (selectedSlot) {
            const selectedEventId = selectedSlot.getAttribute("data-eventid");
            isBooked = window.latestCart.some(item => 
                item.gameId === gameId && item.eventId === selectedEventId
            );
        }

        const guestWrapper = document.querySelector(`#guest-count-${gameId}`)?.closest(".guest-count-wrapper") 
                          || document.querySelector(`#guest-count-display-${gameId}`)?.closest(".guest-count-wrapper");

        if (isBooked) {
            // IF IN CART: Disable and show "Added to Cart"
            btn.innerText = "Added to Cart";
            btn.classList.add("booked_btn");
            btn.disabled = true;
            btn.style.opacity = "0.5";

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn, .plus-btn, .minus-btn").forEach(b => b.disabled = true);
            }
        } else {
            // NOT IN CART: Restore normal text
            btn.innerText = "Continue";
            btn.classList.remove("booked_btn");

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn, .plus-btn, .minus-btn").forEach(b => b.disabled = false);
            }

            // CRITICAL FIX: Evaluate correctly instead of blindly enabling all buttons!
            let isEnabled = false;
            
            if (btn.classList.contains("continue_next_step_event")) {
                // Event rooms usually just need a slot selected
                isEnabled = !!selectedSlot;
            } else {
                // Escape & Party need a slot AND guests > 0
                let count = 0;
                const valEl = guestWrapper?.querySelector('.guest-value');
                if (valEl) count = parseInt(valEl.innerText) || 0;
                isEnabled = !!selectedSlot && count > 0;
            }

            // Apply the evaluated state properly
            if (isEnabled) {
                btn.disabled = false;
                btn.classList.remove("disabled");
                btn.removeAttribute("disabled");
                btn.style.opacity = "1";
            } else {
                btn.disabled = true;
                btn.classList.add("disabled");
                btn.setAttribute("disabled", "true");
                btn.style.opacity = ""; // Let CSS handle opacity
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

function setCartLinksByCount(cartCount) {
    const target = cartCount > 0
        ? BASE_URL + "booking?customer-details"
        : BASE_URL + "booking?choose-experience";
    document.querySelectorAll('.cartUrl').forEach(link => {
        link.href = target;
    });
}

function resetBookingToFirstStep() {
    const progressSteps = document.querySelectorAll('.progress-step');
    progressSteps.forEach((step, index) => {
        const circle = step.querySelector('.step-circle');
        if (index === 0) {
            if (circle) circle.classList.add('active');
            if (circle) circle.classList.remove('visited');
        } else {
            if (circle) circle.classList.remove('active', 'visited');
        }
    });

    const stepContents = document.querySelectorAll('.step-content');
    let activeIndex = 0;
    stepContents.forEach((content, index) => {
        if (content.classList.contains('active')) activeIndex = index;
    });
    if (activeIndex > 0) {
        const firstStepBtn = document.querySelector('.progress-step');
        if (firstStepBtn) firstStepBtn.click();
    }
}

function applyEmptyCartState(options = {}) {
    const reloadPage = options.reloadPage === true;
    stopPersistentTimer();
    setCartLinksByCount(0);
    document.querySelectorAll('.cart-count').forEach(badge => {
        badge.textContent = "0";
        badge.style.display = 'none';
    });
    resetBookingToFirstStep();
    updateBundleOffers(0);
    const summary = document.getElementById("summary-output");
    if (summary) {
        summary.innerHTML = "<p class='text-center mt-3 text-white'>Your cart is empty.</p>";
    }
    if (reloadPage) {
        setTimeout(() => {
            window.location.reload();
        }, 50);
    }
}

function loadCart() {
    fetch("cart_view.php?live=1", { cache: "no-store" })
        .then(res => res.text())
        .then(html => {
            // --- BLOCK 1: Check for Redirect (Empty Cart) ---
            try {
                const json = JSON.parse(html);
                if (json.redirect) {
                    applyEmptyCartState({
                        reloadPage: localStorage.getItem('cartTimerExpired') === 'true'
                    });
                    return;
                }
            } catch (e) {
                // Not JSON (It's HTML), so proceed normally.
            }
            document.getElementById("summary-output").innerHTML = html;

            const cartItemsEls = document.querySelectorAll('#summary-output .summary-row-group');
            const cartCount = cartItemsEls.length;
            
            if (localStorage.getItem('cartTimerExpired') === 'true') {
                if (cartCount > 0) {
                    const canRetry = (Date.now() - lastExpireAttemptAt) > EXPIRE_RETRY_MS;
                    if (!isExpiringCartNow && canRetry) {
                        forceExpireCart("expired_flag_detected")
                            .finally(() => {
                                if (typeof loadCart === "function") loadCart();
                                refreshSlotsAfterExpire();
                            });
                    }
                } else {
                    applyEmptyCartState({ reloadPage: true });
                    refreshSlotsAfterExpire();
                }
                return;
            }

            // *** NEW: Handle Timer Logic Here ***
            if (cartCount > 0) {
                startPersistentTimer();
                fetch("check_addons.php", { cache: "no-store" })
                    .then(r => r.json())
                    .then(d => {
                        if (typeof setAddonStepEnabled === "function") {
                            setAddonStepEnabled(d.has_addons);
                        }
                    }).catch(()=>{});

            } else {
                stopPersistentTimer();
                if (typeof setAddonStepEnabled === "function") {
                    setAddonStepEnabled(true);
                }
            }
            setCartLinksByCount(cartCount);
            document.querySelectorAll('.cart-count').forEach(badge => {
                badge.textContent = cartCount;
                badge.style.display = cartCount > 0 ? 'inline-block' : 'none';
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

function setAddonStepEnabled(enabled) {
    const steps = document.querySelectorAll('.progress-step');
    const addonStep = steps[1];
    if (!addonStep) return;
    if (enabled) {
        addonStep.classList.remove('step-disabled');
    } else {
        addonStep.classList.add('step-disabled');
    }
}

function scrollToBookingStepArea() {
    const targetEl =
        document.getElementById("stepContents") ||
        document.getElementById("to_book_scroll") ||
        document.getElementById("custom_scroll");

    if (!targetEl) return;

    requestAnimationFrame(() => {
        const yOffset = -120;
        const y =
            targetEl.getBoundingClientRect().top +
            window.pageYOffset +
            yOffset;

        window.scrollTo({
            top: Math.max(0, y),
            behavior: "smooth"
        });
    });
}

async function goToAddonsOrCustomer() {
    try {
        const resp = await fetch("check_addons.php");
        const data = await resp.json();
        if (data.has_addons) {
            setAddonStepEnabled(true);
            goToStep(1); // Always go to index 1 (Add Ons) — absolute
        } else {
            setAddonStepEnabled(false);
            goToStep(2); // Always go to index 2 (Customer Details) — absolute
        }
        scrollToBookingStepArea();
    } catch (e) {
        goToStep(1); // safe fallback
        scrollToBookingStepArea();
    }
}

document.addEventListener("DOMContentLoaded", loadCart);
window.addEventListener("flee:cartExpired", function() {
    if (typeof loadCart === "function") loadCart();
    refreshSlotsAfterExpire();
});

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
        document.getElementById("stepLoader").style.display = "none";
        showBookeoError(response.message);
        return;
    }

    localStorage.removeItem('cartTimerExpired');
    loadCart();
    loadAddons();
    if (response.cart) updateBookedButtons(response.cart);

            // Slot unavailable — item was auto-removed from cart by apply_code.php

        // Reload cart regardless — apply_code already cleaned up any failed items
    if (response.cart) {
        if (response.cart.length < 3) {
            const modalEl = document.getElementById('timeslotModal');
            if (modalEl) new bootstrap.Modal(modalEl).show();
        } else {
            goToAddonsOrCustomer();
        }
    }

    document.getElementById("stepLoader").style.display = "none";

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

    let req = await fetch("cart_session.php", {
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
    if (res.status !== "success") {
        showBookeoError(res.message || "Failed to reserve slot. Please try again.");
        return;
    }
 
    if (productCode === "41551LAM3LY18570132661") {
        if (typeof loadCart === "function") loadCart();
        loadAddons();
        if (typeof goToAddonsOrCustomer === "function") goToAddonsOrCustomer();
        return;
    }

    // SUCCESS → load addons + show booking modal
    if (typeof loadCart === "function") loadCart();
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
            const response = await fetch("cart_session.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "add_event_cart",
                    gameId: productCode,
                    gameName: gameName,
                    slot: slot,
                    eventId: eventId,
                    players: players,
                    price: totalPrice
                })
            });

            const res = await response.json();

            if (res.status !== "success") {
                // SHOW ERROR MODAL (NOT ALERT)
                showBookeoError(res.message || "Failed to reserve slot. Please try again.");
                return;
            }

            if (res.status === "success") {
                loadCart();
                goToAddonsOrCustomer();
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
  // DELETE CART ITEM (Handles both old and new buttons)
  // -------------------------------
  // Find the closest delete button, whether it's the old or new class
  const deleteButton = e.target.closest(".delete_card, .remove-game-btn");

  // If a delete button was clicked...
  if (deleteButton) {
    console.log("Delete button clicked (new or old)");
    // Get the index from the button that was clicked
    const index = deleteButton.getAttribute("data-index");

    // The rest of your existing logic is perfect and remains unchanged
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

        fetch("cart_session.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=remove_from_cart&index=" + encodeURIComponent(index)
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

            if (data.cartCount === 0) {
                window.location.href = "booking.php?escape-room";
            } else {
                loadCart();
                if (typeof loadAddons === "function") {
                    loadAddons();
                }
            }
        });
    };

    document.getElementById("cancelDeleteBtn").onclick = function () {
        modal.style.display = "none";
    };
  }


    // --- Helper to keep Promo UI in sync with Backend Cart State ---
    window.syncPromoUI = async function() {
        let currentCode = document.getElementById("giftCodeInput")?.value || "";
        
        try {
            const res = await fetch("apply_code.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "code=" + encodeURIComponent(currentCode)
            });
            const data = await res.json();
            
            const codeEl = document.querySelector(".applied_code .code");
            const removeBtn = document.querySelector(".applied_code_remove");
            const giftInput = document.getElementById("giftCodeInput");

            // If backend says the code is still valid (or auto-applied a new one)
            if (data.valid_code && data.valid_code !== "") {
                if(codeEl) codeEl.textContent = 'Promotion: ' + data.valid_code;
                if(giftInput) giftInput.value = data.valid_code;
                if(removeBtn) removeBtn.style.display = "block";
            } else {
                // Backend rejected it based on new quantities! Reset UI.
                if(codeEl) codeEl.textContent = "Have a promotion or voucher code?";
                if(giftInput) giftInput.value = "";
                if(removeBtn) removeBtn.style.display = "none";
            }
        } catch (err) {
            console.error("Promo sync error:", err);
        }
    };
});
