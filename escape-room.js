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
    input.addEventListener("change", function () {
        if (this.checked) {
            document.querySelectorAll(".Boo_Prison_Escape_time-slot").forEach(i => {
                if (i !== this) i.checked = false;
            });
        }
        checkSelection();
    });
});

document.querySelector(".Boo_Prison_Escape_select").addEventListener("change", checkSelection);



// <!-- Book Prison Escape date -->
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

document.addEventListener("DOMContentLoaded", function () {
    flatpickr("#Book-Prison-Date", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: getTodayInLosAngeles(),
        defaultDate: getTodayInLosAngeles()
    });
});