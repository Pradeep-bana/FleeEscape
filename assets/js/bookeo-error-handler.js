(function (window, document) {
    "use strict";

    const MODAL_ID = "bookeoErrorModal";
    const MESSAGE_ID = "bookeoErrorMessage";
    let reloadOnClose = false;
    let modalEventsBound = false;

    function getModalElement() {
        return document.getElementById(MODAL_ID);
    }

    function getMessageElement() {
        return document.getElementById(MESSAGE_ID);
    }

    function bindModalEvents() {
        const modalEl = getModalElement();
        if (!modalEl || modalEventsBound) {
            return;
        }

        modalEl.addEventListener("click", function (event) {
            const dismissButton = event.target.closest('.modal-footer [data-bs-dismiss="modal"]');
            if (!dismissButton || !modalEl.contains(dismissButton)) {
                return;
            }

            reloadOnClose = true;
        });

        modalEl.addEventListener("show.bs.modal", function () {
            reloadOnClose = false;
        });

        modalEl.addEventListener("hidden.bs.modal", function () {
            if (!reloadOnClose) {
                return;
            }

            reloadOnClose = false;
            window.location.reload();
        });

        modalEventsBound = true;
    }

    function showBookeoError(message) {
        const modalEl = getModalElement();
        const messageEl = getMessageElement();
        const fallbackMessage = message || "Something went wrong. Please try again.";

        if (messageEl) {
            messageEl.textContent = fallbackMessage;
        }

        if (!modalEl || !window.bootstrap || !window.bootstrap.Modal) {
            window.alert(fallbackMessage);
            return null;
        }

        bindModalEvents();
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        return modal;
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bindModalEvents);
    } else {
        bindModalEvents();
    }

    window.showBookeoError = showBookeoError;
})(window, document);
