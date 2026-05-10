<?php
ob_start();
session_start();
require 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$user_id = (int) $_SESSION["user_id"];
$message = "";

/* Remove item */
if (isset($_GET["remove"])) {
    $cart_id = (int) $_GET["remove"];

    $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if (!$deleteStmt) {
        die("Delete prepare failed: " . $conn->error);
    }

    $deleteStmt->bind_param("ii", $cart_id, $user_id);

    if ($deleteStmt->execute()) {
        header("Location: cart-empty.php");
        exit();
    } else {
        $message = "Could not remove item from cart.";
    }
}

/* Update quantity */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_quantity"])) {
    $cart_id = (int) ($_POST["cart_id"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 1);

    if ($quantity <= 0) {
        $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        if (!$deleteStmt) {
            die("Delete prepare failed: " . $conn->error);
        }

        $deleteStmt->bind_param("ii", $cart_id, $user_id);
        $deleteStmt->execute();
    } else {
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        if (!$updateStmt) {
            die("Update prepare failed: " . $conn->error);
        }

        $updateStmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $updateStmt->execute();
    }

    header("Location: cart-empty.php");
    exit();
}

/* Fetch cart items */
$sql = "SELECT 
            cart.id AS cart_id,
            cart.quantity,
            products.id AS product_id,
            products.name,
            products.brand,
            products.price,
            products.image
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?
        ORDER BY cart.id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Cart query prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$totalAmount = 0;
$totalQuantity = 0;

while ($row = $result->fetch_assoc()) {
    $price = (float) $row["price"];
    $quantity = (int) $row["quantity"];

    $row["subtotal"] = $price * $quantity;
    $totalAmount += $row["subtotal"];
    $totalQuantity += $quantity;
    $cartItems[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Cart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
      max-width:1150px;
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
      align-items:center;
      flex-wrap:wrap;
    }

    .nav-links a{
      font-size:14px;
      font-weight:500;
      color:#4b5563;
    }

    .page{
      padding:45px 0 70px;
    }

    .page-title{
      font-size:34px;
      margin-bottom:24px;
      color:#1f2937;
    }

    .msg{
      margin-bottom:16px;
      padding:14px 16px;
      border-radius:14px;
      background:#fff1f2;
      color:#e11d48;
      font-weight:600;
    }

    .empty-cart-box{
      max-width:600px;
      margin:auto;
      background:#fff;
      border-radius:28px;
      padding:50px 30px;
      text-align:center;
      box-shadow:0 14px 30px rgba(0,0,0,0.06);
      border:1px solid #f1dce5;
    }

    .empty-cart-box .icon{
      font-size:62px;
      margin-bottom:18px;
    }

    .empty-cart-box h1{
      font-size:32px;
      margin-bottom:12px;
    }

    .empty-cart-box p{
      color:#6b7280;
      line-height:1.8;
      margin-bottom:24px;
    }

    .btn{
      display:inline-block;
      padding:14px 24px;
      border-radius:28px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-weight:600;
    }

    .cart-layout{
      display:grid;
      grid-template-columns:2fr 1fr;
      gap:24px;
    }

    .cart-list,
    .summary-box{
      background:#fff;
      border-radius:24px;
      padding:24px;
      box-shadow:0 10px 25px rgba(0,0,0,0.05);
      border:1px solid #f1dce5;
    }

    .cart-item{
      display:grid;
      grid-template-columns:110px 1fr auto;
      gap:18px;
      align-items:center;
      padding:18px 0;
      border-bottom:1px solid #f6e8ee;
    }

    .cart-item:last-child{
      border-bottom:none;
    }

    .cart-item img{
      width:100%;
      height:100px;
      object-fit:contain;
      background:#fafafa;
      border-radius:16px;
      padding:10px;
    }

    .cart-info h3{
      font-size:18px;
      margin-bottom:6px;
    }

    .cart-info p{
      color:#6b7280;
      font-size:14px;
      margin-bottom:8px;
    }

    .cart-info .price{
      color:#ff2f7e;
      font-weight:700;
    }

    .cart-actions{
      text-align:right;
    }

    .cart-actions form{
      margin-bottom:10px;
    }

    .qty-input{
      width:80px;
      padding:10px;
      border-radius:12px;
      border:1px solid #ecd7e0;
      outline:none;
      text-align:center;
    }

    .update-btn,
    .remove-btn,
    .checkout-btn{
      padding:10px 16px;
      border:none;
      border-radius:20px;
      cursor:pointer;
      font-weight:600;
    }

    .update-btn{
      background:#fff3f7;
      color:#ff2f7e;
      border:1px solid #f3cfdd;
      margin-left:8px;
    }

    .remove-btn{
      background:#fff;
      color:#dc2626;
      border:1px solid #fecaca;
      display:inline-block;
    }

    .summary-box h2{
      margin-bottom:18px;
      font-size:24px;
    }

    .summary-row{
      display:flex;
      justify-content:space-between;
      margin-bottom:14px;
      color:#4b5563;
      gap:10px;
    }

    .summary-row.total{
      font-size:20px;
      font-weight:700;
      color:#111827;
      border-top:1px solid #f3e4ea;
      padding-top:16px;
      margin-top:16px;
    }

    .checkout-btn{
      width:100%;
      margin-top:18px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      padding:14px;
      border-radius:18px;
    }

    @media (max-width:900px){
      .cart-layout{
        grid-template-columns:1fr;
      }

      .cart-item{
        grid-template-columns:1fr;
        text-align:center;
      }

      .cart-actions{
        text-align:center;
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
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<section class="page">
  <div class="container">

    <?php if (!empty($message)) : ?>
      <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (count($cartItems) === 0) : ?>
      <div class="empty-cart-box">
        <div class="icon">🛒</div>
        <h1>Your Cart is Empty</h1>
        <p>Add your favorite skincare products and start building your glow routine.</p>
        <a href="shop.php" class="btn">Continue Shopping</a>
      </div>
    <?php else : ?>
      <h1 class="page-title">Your Cart</h1>

      <div class="cart-layout">
        <div class="cart-list">
          <?php foreach ($cartItems as $item) : ?>
            <div class="cart-item">
              <img src="<?php echo htmlspecialchars($item["image"]); ?>" alt="<?php echo htmlspecialchars($item["name"]); ?>">

              <div class="cart-info">
                <h3><?php echo htmlspecialchars($item["name"]); ?></h3>
                <p><?php echo htmlspecialchars($item["brand"]); ?></p>
                <div class="price">₹<?php echo number_format((float)$item["price"], 2); ?></div>
                <p>Subtotal: ₹<?php echo number_format((float)$item["subtotal"], 2); ?></p>
              </div>

              <div class="cart-actions">
                <form method="POST">
                  <input type="hidden" name="cart_id" value="<?php echo (int)$item["cart_id"]; ?>">
                  <input type="number" name="quantity" value="<?php echo (int)$item["quantity"]; ?>" min="1" class="qty-input">
                  <button type="submit" name="update_quantity" class="update-btn">Update</button>
                </form>

                <a href="cart-empty.php?remove=<?php echo (int)$item["cart_id"]; ?>" class="remove-btn">Remove</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-box">
          <h2>Order Summary</h2>
          <div class="summary-row">
            <span>Total Quantity</span>
            <span><?php echo (int)$totalQuantity; ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span>₹0.00</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span>₹<?php echo number_format((float)$totalAmount, 2); ?></span>
          </div>

          <a href="checkout.php" class="checkout-btn" style="display:block;text-align:center;text-decoration:none;">Proceed to Checkout</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

</body>
</html>
<?php ob_end_flush(); ?>