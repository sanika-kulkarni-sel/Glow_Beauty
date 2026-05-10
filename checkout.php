<?php
session_start();
require 'config/db.php';
require 'config/mail.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* fetch user */
$userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userData = $userStmt->get_result()->fetch_assoc();

$sql = "SELECT cart.id AS cart_id, cart.quantity, products.id AS product_id, products.name, products.price
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$totalAmount = 0;

while ($row = $result->fetch_assoc()) {
    $row["subtotal"] = $row["price"] * $row["quantity"];
    $totalAmount += $row["subtotal"];
    $cartItems[] = $row;
}

if (count($cartItems) === 0) {
    header("Location: cart-empty.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address = trim($_POST["address"] ?? "");
    $payment_method = trim($_POST["payment_method"] ?? "");
    $upi_id = trim($_POST["upi_id"] ?? "");
    $card_holder = trim($_POST["card_holder"] ?? "");
    $card_number = trim($_POST["card_number"] ?? "");
    $expiry = trim($_POST["expiry"] ?? "");
    $cvv = trim($_POST["cvv"] ?? "");

    if (empty($address) || empty($payment_method)) {
        $message = "Please fill all details.";
    } elseif ($payment_method === "UPI" && empty($upi_id)) {
        $message = "Please enter your UPI ID.";
    } elseif (($payment_method === "Credit Card" || $payment_method === "Debit Card") &&
        (empty($card_holder) || empty($card_number) || empty($expiry) || empty($cvv))) {
        $message = "Please fill all card details.";
    } else {

        /* CASH ON DELIVERY */
        if ($payment_method === "Cash on Delivery") {
            $payment_status = "pending";
            $transaction_id = NULL;

            $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, transaction_id, address, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
            $orderStmt->bind_param("idssss", $user_id, $totalAmount, $payment_method, $payment_status, $transaction_id, $address);

            if ($orderStmt->execute()) {
                $order_id = $conn->insert_id;

                foreach ($cartItems as $item) {
                    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $itemStmt->bind_param("iiid", $order_id, $item["product_id"], $item["quantity"], $item["price"]);
                    $itemStmt->execute();
                }

                $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $clearCart->bind_param("i", $user_id);
                $clearCart->execute();

                sendOrderConfirmationEmail(
                    $userData["email"],
                    trim($userData["first_name"] . " " . $userData["last_name"]),
                    $order_id,
                    $transaction_id,
                    $totalAmount,
                    $payment_method
                );

                header("Location: payment-success.php?order_id=" . $order_id);
                exit();
            } else {
                $message = "Order could not be placed.";
            }

        /* UPI - NO OTP */
        } elseif ($payment_method === "UPI") {
            $payment_status = "paid";
            $transaction_id = "UPITXN" . time() . rand(100, 999);

            $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, transaction_id, address, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
            $orderStmt->bind_param("idssss", $user_id, $totalAmount, $payment_method, $payment_status, $transaction_id, $address);

            if ($orderStmt->execute()) {
                $order_id = $conn->insert_id;

                foreach ($cartItems as $item) {
                    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $itemStmt->bind_param("iiid", $order_id, $item["product_id"], $item["quantity"], $item["price"]);
                    $itemStmt->execute();
                }

                $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $clearCart->bind_param("i", $user_id);
                $clearCart->execute();

                sendOrderConfirmationEmail(
                    $userData["email"],
                    trim($userData["first_name"] . " " . $userData["last_name"]),
                    $order_id,
                    $transaction_id,
                    $totalAmount,
                    $payment_method
                );

                header("Location: payment-success.php?order_id=" . $order_id);
                exit();
            } else {
                $message = "UPI payment could not be completed.";
            }

        /* CREDIT / DEBIT CARD - OTP */
        } else {
            $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
            $expiryTime = date("Y-m-d H:i:s", time() + 300);

            $payment_status = "otp_pending";
            $transaction_id = "TXN" . time() . rand(100, 999);

            $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, transaction_id, address, status, payment_otp, payment_otp_expires_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
            $orderStmt->bind_param("idssssss", $user_id, $totalAmount, $payment_method, $payment_status, $transaction_id, $address, $hashedOtp, $expiryTime);

            if ($orderStmt->execute()) {
                $order_id = $conn->insert_id;

                foreach ($cartItems as $item) {
                    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $itemStmt->bind_param("iiid", $order_id, $item["product_id"], $item["quantity"], $item["price"]);
                    $itemStmt->execute();
                }

                $fullName = trim($userData["first_name"] . " " . $userData["last_name"]);
                $sent = sendPaymentOtpEmail(
                    $userData["email"],
                    $fullName,
                    $otp,
                    $totalAmount,
                    $payment_method
                );

                if ($sent) {
                    header("Location: payment-otp.php?order_id=" . $order_id);
                    exit();
                } else {
                    $message = "Payment OTP email could not be sent.";
                }
            } else {
                $message = "Could not start payment.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Checkout</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family:'Poppins',sans-serif;
    }

    body{
      background:#fcf8f9;
      color:#2b2b2b;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    .container{
      width:90%;
      max-width:1100px;
      margin:auto;
    }

    header{
      background:#fff;
      box-shadow:0 2px 18px rgba(0,0,0,0.05);
    }

    .navbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:20px 0;
      gap:20px;
      flex-wrap:wrap;
    }

    .logo{
      font-size:30px;
      font-weight:800;
      color:#ff2f7e;
    }

    .nav-links{
      display:flex;
      gap:20px;
      flex-wrap:wrap;
    }

    .nav-links a{
      color:#4b5563;
      font-weight:500;
      font-size:14px;
    }

    .page{
      padding:40px 0 70px;
    }

    .grid{
      display:grid;
      grid-template-columns:2fr 1fr;
      gap:24px;
    }

    .box{
      background:#fff;
      border-radius:24px;
      padding:24px;
      box-shadow:0 10px 25px rgba(0,0,0,0.05);
    }

    h1{
      margin-bottom:20px;
      font-size:32px;
    }

    .msg{
      margin-bottom:14px;
      color:#e11d48;
      font-weight:600;
      background:#fff1f2;
      padding:14px 16px;
      border-radius:14px;
    }

    .input-group{
      margin-bottom:18px;
    }

    label{
      display:block;
      margin-bottom:8px;
      font-weight:600;
      color:#374151;
      font-size:14px;
    }

    textarea,
    input{
      width:100%;
      padding:14px 16px;
      border:1px solid #ecd7e0;
      border-radius:14px;
      outline:none;
      background:#fff;
    }

    textarea:focus,
    input:focus{
      border-color:#ff4f93;
      box-shadow:0 0 0 4px rgba(255,79,147,0.10);
    }

    .payment-grid{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:16px;
      margin-top:6px;
    }

    .pay-card{
      border:2px solid #f1dce5;
      border-radius:18px;
      padding:18px;
      cursor:pointer;
      transition:0.3s;
      background:#fff;
      text-align:center;
    }

    .pay-card:hover{
      transform:translateY(-5px);
    }

    .pay-card.active{
      border-color:#ff2f7e;
      background:#fff4f8;
      box-shadow:0 10px 25px rgba(255,79,147,0.2);
    }

    .pay-card .icon{
      width:52px;
      height:52px;
      margin:0 auto 12px;
      border-radius:14px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:24px;
      font-weight:700;
    }

    .upi-icon{ background:linear-gradient(135deg,#7c3aed,#9333ea); }
    .credit-icon{ background:linear-gradient(135deg,#0f172a,#334155); }
    .debit-icon{ background:linear-gradient(135deg,#2563eb,#1d4ed8); }
    .cod-icon{ background:linear-gradient(135deg,#059669,#10b981); }

    .pay-card h3{
      margin-bottom:6px;
      font-size:18px;
    }

    .pay-card p{
      font-size:13px;
      color:#6b7280;
      line-height:1.6;
    }

    .dynamic-fields{
      margin-top:18px;
      display:none;
      padding:18px;
      border:1px solid #f3d7e3;
      border-radius:18px;
      background:#fff8fb;
    }

    .dynamic-fields.active{
      display:block;
    }

    .row{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:14px;
    }

    .method-note{
      margin-top:14px;
      padding:12px 14px;
      border-radius:14px;
      background:#fff8fb;
      color:#6b7280;
      font-size:13px;
      line-height:1.7;
      border:1px solid #f3d7e3;
      display:none;
    }

    .method-note.show{
      display:block;
    }

    .order-item{
      display:flex;
      justify-content:space-between;
      gap:12px;
      padding:12px 0;
      border-bottom:1px solid #f4e7ed;
      font-size:14px;
    }

    .order-item:last-child{
      border-bottom:none;
    }

    .order-item span:last-child{
      white-space:nowrap;
    }

    .total{
      display:flex;
      justify-content:space-between;
      font-size:20px;
      font-weight:700;
      margin-top:18px;
      padding-top:16px;
      border-top:1px solid #f3e4ea;
    }

    .pay-btn{
      width:100%;
      margin-top:22px;
      padding:15px;
      border:none;
      border-radius:16px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-weight:700;
      cursor:pointer;
      font-size:15px;
      box-shadow:0 14px 28px rgba(255,79,147,0.20);
      transition:0.3s;
    }

    .pay-btn:hover{
      transform:translateY(-2px);
    }

    .summary-note{
      margin-top:16px;
      padding:14px;
      border-radius:14px;
      background:#ecfdf3;
      color:#166534;
      font-size:13px;
      line-height:1.7;
    }

    @media(max-width:900px){
      .grid{
        grid-template-columns:1fr;
      }

      .payment-grid,
      .row{
        grid-template-columns:1fr;
      }
    }
  </style>
</head>
<body>
<header>
  <div class="container navbar">
    <div class="logo">Glowly</div>
    <div class="nav-links">
      <a href="dashboard.php">Dashboard</a>
      <a href="shop.php">Shop</a>
      <a href="cart-empty.php">Cart</a>
    </div>
  </div>
</header>

<section class="page">
  <div class="container">
    <div class="grid">
      <div class="box">
        <h1>Checkout</h1>

        <?php if (!empty($message)) : ?>
          <div class="msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
          <div class="input-group">
            <label for="address">Delivery Address</label>
            <textarea name="address" id="address" rows="5" placeholder="Enter full delivery address" required><?php echo htmlspecialchars($_POST["address"] ?? ""); ?></textarea>
          </div>

          <div class="input-group">
            <label>Select Payment Method</label>

            <div class="payment-grid">
              <div class="pay-card" onclick="selectPayment(this, 'UPI')">
                <div class="icon upi-icon">₹</div>
                <h3>UPI</h3>
                <p>Google Pay, PhonePe, Paytm and more</p>
              </div>

              <div class="pay-card" onclick="selectPayment(this, 'Credit Card')">
                <div class="icon credit-icon">💳</div>
                <h3>Credit Card</h3>
                <p>Visa, MasterCard and RuPay supported</p>
              </div>

              <div class="pay-card" onclick="selectPayment(this, 'Debit Card')">
                <div class="icon debit-icon">🏦</div>
                <h3>Debit Card</h3>
                <p>Pay securely using your bank card</p>
              </div>

              <div class="pay-card" onclick="selectPayment(this, 'Cash on Delivery')">
                <div class="icon cod-icon">🚚</div>
                <h3>Cash on Delivery</h3>
                <p>Pay when your order arrives</p>
              </div>
            </div>

            <input type="hidden" name="payment_method" id="payment_method" value="<?php echo htmlspecialchars($_POST["payment_method"] ?? ""); ?>" required>

            <div class="method-note" id="methodNote"></div>

            <div class="dynamic-fields" id="upiFields">
              <div class="input-group" style="margin-bottom:0;">
                <label for="upi_id">UPI ID</label>
                <input type="text" name="upi_id" id="upi_id" placeholder="example@upi" value="<?php echo htmlspecialchars($_POST["upi_id"] ?? ""); ?>">
              </div>
            </div>

            <div class="dynamic-fields" id="cardFields">
              <div class="row">
                <div class="input-group">
                  <label for="card_holder">Card Holder Name</label>
                  <input type="text" name="card_holder" id="card_holder" placeholder="Enter card holder name" value="<?php echo htmlspecialchars($_POST["card_holder"] ?? ""); ?>">
                </div>
                <div class="input-group">
                  <label for="card_number">Card Number</label>
                  <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" value="<?php echo htmlspecialchars($_POST["card_number"] ?? ""); ?>">
                </div>
              </div>

              <div class="row">
                <div class="input-group">
                  <label for="expiry">Expiry</label>
                  <input type="text" name="expiry" id="expiry" placeholder="MM/YY" value="<?php echo htmlspecialchars($_POST["expiry"] ?? ""); ?>">
                </div>
                <div class="input-group">
                  <label for="cvv">CVV</label>
                  <input type="password" name="cvv" id="cvv" placeholder="123" value="<?php echo htmlspecialchars($_POST["cvv"] ?? ""); ?>">
                </div>
              </div>
            </div>

            <div class="dynamic-fields" id="codFields">
              <div class="method-note show" style="margin-top:0;">
                Cash on Delivery selected. You can pay when the order is delivered to your address.
              </div>
            </div>
          </div>

          <button type="submit" class="pay-btn">Pay Now</button>
        </form>
      </div>

      <div class="box">
        <h1>Order Summary</h1>

        <?php foreach ($cartItems as $item) : ?>
          <div class="order-item">
            <span><?php echo htmlspecialchars($item["name"]); ?> × <?php echo (int)$item["quantity"]; ?></span>
            <span>₹<?php echo number_format((float)$item["subtotal"], 2); ?></span>
          </div>
        <?php endforeach; ?>

        <div class="total">
          <span>Total</span>
          <span>₹<?php echo number_format((float)$totalAmount, 2); ?></span>
        </div>

        <div class="summary-note">
          Your order will be placed securely after selecting a payment method and confirming your address.
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function hideAllDynamicFields() {
    document.querySelectorAll('.dynamic-fields').forEach(field => {
      field.classList.remove('active');
    });
  }

  function selectPayment(element, method) {
    document.querySelectorAll('.pay-card').forEach(card => {
      card.classList.remove('active');
    });

    element.classList.add('active');
    document.getElementById('payment_method').value = method;

    const note = document.getElementById('methodNote');
    note.classList.add('show');

    hideAllDynamicFields();

    if (method === 'UPI') {
      note.textContent = 'You selected UPI. Please enter your UPI ID below.';
      document.getElementById('upiFields').classList.add('active');
    } else if (method === 'Credit Card') {
      note.textContent = 'You selected Credit Card. Please enter your card details.';
      document.getElementById('cardFields').classList.add('active');
    } else if (method === 'Debit Card') {
      note.textContent = 'You selected Debit Card. Please enter your card details.';
      document.getElementById('cardFields').classList.add('active');
    } else if (method === 'Cash on Delivery') {
      note.textContent = 'You selected Cash on Delivery.';
      document.getElementById('codFields').classList.add('active');
    }
  }

  window.addEventListener('load', function () {
    const currentMethod = document.getElementById('payment_method').value;
    if (!currentMethod) return;

    const cards = document.querySelectorAll('.pay-card');
    cards.forEach(card => {
      const title = card.querySelector('h3').innerText.trim();
      if (title === currentMethod) {
        selectPayment(card, currentMethod);
      }
    });
  });
</script>
</body>
</html>