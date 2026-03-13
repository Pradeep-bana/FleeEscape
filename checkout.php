<!DOCTYPE html>
<html>
<head>
  <title>Square Payment Example</title>
  <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
</head>
<body>
  <form id="payment-form">
    <div id="card-container"></div>
    <button id="card-button" type="button">Pay $1.00</button>
  </form>

  <div id="payment-status-container" style="margin-top:20px;"></div>

  <script>
    const appId = "sandbox-sq0idb-VwqgN_zOnEPVQGzbPNMKDQ";
    const locationId = "L8XX876JN6ZSH";

    async function initializeCard(payments) {
      try {
        const card = await payments.card();
        await card.attach('#card-container');
        return card;
      } catch (err) {
        throw new Error(`Card initialization failed: ${err.message}`);
      }
    }

    async function createPayment(token) {
      try {
        const res = await fetch("payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ sourceId: token, amount: 100, currency: "USD" })
        });

        const text = await res.text();
        console.log("Raw response from payment.php:", text);

        if (!res.ok) {
          throw new Error(`HTTP error: ${res.status} ${res.statusText} - ${text}`);
        }

        try {
          const json = JSON.parse(text);
          return json;
        } catch (e) {
          throw new Error(`Invalid JSON response: ${text}`);
        }
      } catch (err) {
        throw new Error(`Fetch error: ${err.message}`);
      }
    }

    async function tokenize(paymentMethod) {
      try {
        const result = await paymentMethod.tokenize();
        if (result.status === "OK") return result.token;
        throw new Error(JSON.stringify(result.errors));
      } catch (err) {
        throw new Error(`Tokenization failed: ${err.message}`);
      }
    }

    document.addEventListener("DOMContentLoaded", async () => {
      const statusContainer = document.getElementById("payment-status-container");
      try {
        const payments = window.Square.payments(appId, locationId);
        const card = await initializeCard(payments);

        document.getElementById("card-button").addEventListener("click", async () => {
          try {
            const token = await tokenize(card);
            console.log("Token generated:", token);
            const paymentResult = await createPayment(token);

            if (paymentResult.errors) {
              statusContainer.innerText = "Payment Failed: " + JSON.stringify(paymentResult.errors);
            } else if (paymentResult.error) {
              statusContainer.innerText = "Payment Failed: " + JSON.stringify(paymentResult.error);
            } else {
              statusContainer.innerText = "Payment Success: " + JSON.stringify(paymentResult);
            }
          } catch (err) {
            console.error("Error:", err);
            statusContainer.innerText = "Payment Failed: " + err.message;
          }
        });
      } catch (err) {
        console.error("Initialization error:", err);
        statusContainer.innerText = "Initialization Failed: " + err.message;
      }
    });
  </script>
</body>
</html>