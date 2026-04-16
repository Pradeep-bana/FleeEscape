
   let timerInterval = null;
let timerEndTime = null;

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
function updateBookedButtons(cartItems) {
    document.querySelectorAll(".continue_nex_step").forEach(btn => {
        const gameId = btn.getAttribute("data-game-id");
        const isBooked = cartItems.some(item => item.gameId === gameId);
        const bookedItem = cartItems.find(item => item.gameId === gameId);

        const guestWrapper = document.querySelector(`#guest-count-${gameId}`)?.closest(".guest-count-wrapper");

        if (isBooked) {
            btn.innerText = "Added to Cart";
            btn.classList.add("booked_btn");
            btn.disabled = true;
            btn.style.opacity = "0.5";

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn").forEach(b => b.disabled = true);
            }

            if (bookedItem && bookedItem.slot) {
                const slotInput = document.querySelector(`input[name="lift-time-${gameId}"][value="${bookedItem.slot}"]`);
                if (slotInput) {
                    slotInput.checked = true;
                    slotInput.closest("label")?.classList.add("slot-selected");
                }
            }
        } else {
            btn.innerText = "Continue";
            btn.classList.remove("booked_btn");
            btn.disabled = false;

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn").forEach(b => b.disabled = false);
            }

            document.querySelectorAll(`input[name="lift-time-${gameId}"]`).forEach(r => {
                r.closest("label")?.classList.remove("slot-selected");
            });
        }
    });
}
function loadCart() {
  // Always reload from backend
  fetch("cart_view.php?live=1")
    .then(res => res.text())
    .then(html => {

      document.getElementById("summary-output").innerHTML = html;

      // ✅ Count total cart items
      const cartItems = document.querySelectorAll('#summary-output .summary-row-group');
      const cartCount = cartItems.length;

      // ✅ Update all cart-count badges everywhere
      document.querySelectorAll('.cart-count').forEach(badge => {
        badge.textContent = cartCount;
        badge.style.display = cartCount > 0 ? 'inline-block' : 'block';
      });

      // ✅ Update bundle offers
      updateBundleOffers(cartCount);

      // ✅ Update booked buttons
      fetch("get_cart.php")
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data.cart)) {
            updateBookedButtons(data.cart);
          }
        });

      // ✅ Parse totals
      const totalsDiv = document.getElementById("bookeo-totals");
      if (!totalsDiv) return;

      let totals = {};
      try {
        totals = JSON.parse(totalsDiv.dataset.totals);
        window.bookeoTotals = totals;
      } catch (err) {
        console.error("Failed to parse totals JSON:", totalsDiv.dataset.totals, err);
        return;
      }

      // ✅ Fill in frontend UI
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
    timerEndTime = Date.now() + (10 * 60 * 1000);
    document.querySelector(".timer_wrapper").style.display = "block";

    timerInterval = setInterval(() => {
        let remaining = timerEndTime - Date.now();
        if (remaining <= 0) {
            clearInterval(timerInterval);
            timerInterval = null;
            document.querySelector(".timer_display").innerText = "00:00";
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
  

if (response.status === "error") {

    console.log("BOOKEO ERROR RECEIVED:", response.message);

    const msg = response.message || "Failed to reserve slot. Please try again.";

    const errEl = document.getElementById("bookeoErrorMessage");
    const modalEl = document.getElementById("bookeoErrorModal");

    if (!errEl || !modalEl) {
        console.error("❌ ERROR MODAL ELEMENT NOT FOUND IN HTML");
    }

    if (errEl) errEl.innerText = msg;

    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    if (response.cart) updateBookedButtons(response.cart);
    return;
}

    // success path
    console.log(response.message);
    localStorage.removeItem('cartTimerExpired');
    localStorage.removeItem('cartTimerEnd');
    loadCart();
    if (typeof loadCart === "function") loadCart();
    loadAddons();
    startTimer();
    if (response.cart) updateBookedButtons(response.cart);

    if (response.cart) {
        if (response.cart.length < 3) {
            const modalEl = document.getElementById('timeslotModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        } else {
            changeStep(1); 
        }
    }
})
.finally(() => {
    document.getElementById("stepLoader").style.display = "none";
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
        document.getElementById("bookeoErrorMessage").innerText =
            res.message || "Failed to reserve slot. Please try again.";
        const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
        errorModal.show();
        return;
    }

    localStorage.removeItem('cartTimerExpired');
    localStorage.removeItem('cartTimerEnd');
   
    if (productCode === "41551LAM3LY18570132661") {
        if (typeof loadCart === "function") loadCart();
        loadAddons();
        window.location.href = "booking?add-ons-";
        return;
    }

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
                document.getElementById("bookeoErrorMessage").innerText = 
                    res.message || "Failed to reserve slot. Please try again.";
                const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
                errorModal.show();
                return;
            }

            if (res.status === "success") {
                localStorage.removeItem('cartTimerExpired');
                localStorage.removeItem('cartTimerEnd');
                loadCart();
                changeStep(1);
            }

        } catch (err) {
            console.error(err);
            document.getElementById("bookeoErrorMessage").innerText = "Network error. Please try again.";
            const errorModal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
            errorModal.show();
        } finally {
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

        fetch("cart_session.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=remove_from_cart&index=" + encodeURIComponent(index)
        })
        .then(res => res.json())
        .then(data => {
            modal.style.display = "none";

            title.style.display = "block";
            text.style.display = "block";
            actions.style.display = "flex";
            loading.style.display = "none";
            modalBox.classList.remove("loading-mode");

            loadCart();

            setTimeout(() => {
                const cartCount = document.querySelectorAll('#summary-output .summary-row-group').length;
                if (cartCount === 0) {
                    window.location.href = "<?= BASE_URL ?>booking.php?choose-experience";
                }
            }, 300);
        });
    };

    document.getElementById("cancelDeleteBtn").onclick = function () {
        modal.style.display = "none";
    };
  }
});
 
