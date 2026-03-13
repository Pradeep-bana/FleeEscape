<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Escape Room Modal - Demo</title>

  <!-- Bootstrap CSS (remove if already loaded on your page) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome (optional - remove if already loaded) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    .modal-box { padding: 1rem; background: rgba(0,0,0,0.15); border-radius: .5rem; }
    .room-item { display:block; margin: .35rem 0; cursor: pointer; }
    #selectionTextarea { width:100%; height:140px; resize:vertical; }
  </style>
</head>
<body class="bg-light p-4">

  <!-- Button to open modal (for testing) -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#partymodalform">
    Open Escape Room Modal
  </button>

  <!-- Modal -->
  <div class="modal fade" id="partymodalform" tabindex="-1" aria-labelledby="partymodalformLabel" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
      <div class="modal-content text-dark">
        <div class="modal-header">
          <h4 class="modal-title" id="partymodalformLabel">🗝️ Escape Room Choice</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p>Please select your preferred escape rooms (multiple allowed). Your choices will appear on the right.</p>

          <div class="row g-4">
            <div class="col-md-6">
              <div class="modal-box">
                <h5>Available Escape Rooms</h5>
                <div class="room-list">
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="The Lift"> <i class="fa-solid fa-door-open"></i> The Lift
                  </label>
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="Ice Walker - GOT"> <i class="fa-solid fa-snowflake"></i> Ice Walker - GOT
                  </label>
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="Prison Escape"> <i class="fa-solid fa-lock"></i> Prison Escape
                  </label>
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="Steampunk Submarine"> <i class="fa-solid fa-gears"></i> Steampunk Submarine
                  </label>
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="Museum Heist"> <i class="fa-solid fa-landmark"></i> Museum Heist
                  </label>
                  <label class="room-item">
                    <input type="checkbox" class="room-checkbox" value="Ancient Egypt"> <i class="fa-solid fa-monument"></i> Ancient Egypt
                  </label>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="modal-box">
                <h5>Your Selection</h5>
                <textarea id="selectionTextarea" readonly placeholder="Your selected rooms will appear here..."></textarea>
                <small>💡 If you don’t have a preference, just type <b>N/A</b>.</small>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <a href="#to_book_scroll" onclick="changeStep && changeStep(1)" data-bs-dismiss="modal" class="btn btn-outline-secondary">Skip</a>
          <button class="btn btn-primary" data-bs-dismiss="modal">Next</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS bundle (remove if already loaded) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function () {
      // selected array keeps selection in the order the user clicked
      let selected = [];

      // initialize selected from any pre-checked boxes (DOM order)
      function initSelectedFromDOM() {
        selected = Array.from(document.querySelectorAll('.room-checkbox:checked')).map(b => b.value.trim());
        render();
      }

      function render() {
        document.getElementById('selectionTextarea').value = selected.join("\n");
      }

      // Use event delegation so this works even if checkboxes are injected later
      document.addEventListener('change', function (e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('room-checkbox')) return;

        const value = el.value ? el.value.trim() : '';

        if (el.checked) {
          // add at end if not present
          if (!selected.includes(value)) selected.push(value);
        } else {
          // remove if unchecked
          selected = selected.filter(v => v !== value);
        }
        render();
      });

      // When modal opens re-sync with the DOM (useful if checkboxes changed externally)
      const modalEl = document.getElementById('partymodalform');
      if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', initSelectedFromDOM);
      }

      // init on page load
      document.addEventListener('DOMContentLoaded', initSelectedFromDOM);
    })();
  </script>
</body>
</html>
