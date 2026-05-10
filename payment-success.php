<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$order_id = isset($_GET["order_id"]) ? (int)$_GET["order_id"] : 0;

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die("Query prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: dashboard.php");
    exit();
}

$paymentMethod = !empty($order["payment_method"]) ? trim($order["payment_method"]) : "Not Available";
$paymentStatus = !empty($order["payment_status"]) ? trim($order["payment_status"]) : "Pending";
$transactionId = !empty($order["transaction_id"]) ? trim($order["transaction_id"]) : "";

$normalizedMethod = strtolower($paymentMethod);
$isCOD = ($normalizedMethod === "cash on delivery");
$isUPI = ($normalizedMethod === "upi");
$isCard = ($normalizedMethod === "credit card" || $normalizedMethod === "debit card");

$showTransactionDetails = !$isCOD && !empty($transactionId);

$headline = $isCOD ? "Order Placed Successfully" : "Payment Completed Successfully";

$subText = $isCOD
    ? "Your Cash on Delivery order has been placed successfully. Please keep the payable amount ready at the time of delivery."
    : "Your order has been placed successfully and a confirmation email has been sent to your registered email address.";

$totalLabel = $isCOD ? "Order Total" : "Total Paid";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Payment Success</title>
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
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:linear-gradient(135deg,#fff7f8,#fff0f4);
      padding:20px;
    }

    .box{
      width:100%;
      max-width:700px;
      background:#fff;
      border-radius:30px;
      padding:40px;
      box-shadow:0 20px 60px rgba(0,0,0,0.08);
    }

    .icon{
      width:84px;
      height:84px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#ecfdf3;
      color:#16a34a;
      font-size:40px;
      margin:0 auto 18px;
    }

    h1{
      text-align:center;
      font-size:34px;
      margin-bottom:12px;
      color:#111827;
    }

    .sub{
      text-align:center;
      color:#6b7280;
      line-height:1.8;
      margin-bottom:24px;
    }

    .info{
      background:#fff5f8;
      border:1px solid #f3d7e3;
      border-radius:18px;
      padding:18px;
      margin-bottom:22px;
    }

    .row{
      display:flex;
      justify-content:space-between;
      gap:14px;
      padding:12px 0;
      border-bottom:1px solid #f7e2ea;
    }

    .row:last-child{
      border-bottom:none;
    }

    .label{
      color:#6b7280;
      font-weight:500;
    }

    .value{
      font-weight:700;
      color:#111827;
      text-align:right;
      word-break:break-word;
    }

    .badge{
      display:inline-block;
      padding:6px 12px;
      border-radius:999px;
      font-size:12px;
      font-weight:700;
    }

    .paid{
      background:#ecfdf3;
      color:#166534;
    }

    .pending{
      background:#fff7ed;
      color:#c2410c;
    }

    .method-note{
      margin-top:14px;
      padding:14px 16px;
      border-radius:14px;
      font-size:13px;
      line-height:1.7;
    }

    .method-note.upi{
      background:#f5f3ff;
      color:#6d28d9;
    }

    .method-note.card{
      background:#eff6ff;
      color:#1d4ed8;
    }

    .method-note.cod{
      background:#ecfdf3;
      color:#166534;
    }

    .actions{
      display:flex;
      gap:12px;
      justify-content:center;
      flex-wrap:wrap;
      margin-top:24px;
    }

    .btn{
      display:inline-block;
      padding:14px 22px;
      border-radius:24px;
      font-weight:700;
      text-decoration:none;
    }

    .primary{
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
    }

    .outline{
      background:#fff;
      border:1px solid #f0d6e2;
      color:#444;
    }

    @media(max-width:600px){
      .box{
        padding:28px 20px;
      }

      h1{
        font-size:28px;
      }

      .row{
        flex-direction:column;
        align-items:flex-start;
      }

      .value{
        text-align:left;
      }
    }
  </style>
</head>
<body>
  <div class="box">
    <div class="icon">✓</div>

    <h1><?php echo htmlspecialchars($headline); ?></h1>
    <p class="sub"><?php echo htmlspecialchars($subText); ?></p>

    <div class="info">
      <div class="row">
        <div class="label">Order ID</div>
        <div class="value">#<?php echo (int)$order["id"]; ?></div>
      </div>

      <?php if ($showTransactionDetails) : ?>
        <div class="row">
          <div class="label">Transaction ID</div>
          <div class="value"><?php echo htmlspecialchars($transactionId); ?></div>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="label">Payment Method</div>
        <div class="value"><?php echo htmlspecialchars($paymentMethod); ?></div>
      </div>

      <div class="row">
        <div class="label">Payment Status</div>
        <div class="value">
          <span class="badge <?php echo strtolower($paymentStatus) === 'paid' ? 'paid' : 'pending'; ?>">
            <?php echo htmlspecialchars(ucfirst($paymentStatus)); ?>
          </span>
        </div>
      </div>

      <div class="row">
        <div class="label"><?php echo htmlspecialchars($totalLabel); ?></div>
        <div class="value">₹<?php echo number_format((float)$order["total_amount"], 2); ?></div>
      </div>
    </div>

    <?php if ($isUPI) : ?>
      <div class="method-note upi">
        Your UPI payment was processed successfully and your order is confirmed.
      </div>
    <?php elseif ($isCard) : ?>
      <div class="method-note card">
        Your card payment was verified successfully and your order is confirmed.
      </div>
    <?php elseif ($isCOD) : ?>
      <div class="method-note cod">
        Your order is confirmed. Payment will be collected at the time of delivery.
      </div>
    <?php endif; ?>

    <div class="actions">
      <a href="dashboard.php" class="btn primary">Go to Dashboard</a>
      <a href="shop.php" class="btn outline">Continue Shopping</a>
    </div>
  </div>
</body>
</html>