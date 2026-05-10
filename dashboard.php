<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];

$paymentSuccessMessage = "";
if (isset($_SESSION["payment_success"])) {
    $paymentSuccessMessage = $_SESSION["payment_success"];
    unset($_SESSION["payment_success"]);
}

/* Cart count */
$cartCount = 0;
$cartStmt = $conn->prepare("SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?");
$cartStmt->bind_param("i", $user_id);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
if ($cartRow = $cartResult->fetch_assoc()) {
    $cartCount = $cartRow["total_items"] ? $cartRow["total_items"] : 0;
}

/* Orders count */
$orderCount = 0;
$orderStmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
if ($orderRow = $orderResult->fetch_assoc()) {
    $orderCount = $orderRow["total_orders"];
}

/* Total products */
$productCount = 0;
$productQuery = $conn->query("SELECT COUNT(*) AS total_products FROM products");
if ($productRow = $productQuery->fetch_assoc()) {
    $productCount = $productRow["total_products"];
}

/* Recent products */
$recentProducts = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Dashboard</title>
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
      background:#faf7f8;
      min-height:100vh;
      display:flex;
      color:#2b2b2b;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    .sidebar{
      width:260px;
      background:#fff;
      padding:30px 20px;
      box-shadow:0 10px 30px rgba(0,0,0,0.05);
      position:sticky;
      top:0;
      height:100vh;
    }

    .logo{
      font-size:30px;
      font-weight:800;
      color:#ff2f7e;
      margin-bottom:40px;
    }

    .nav{
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .nav a{
      padding:12px 14px;
      border-radius:14px;
      font-weight:500;
      color:#555;
      transition:0.3s;
    }

    .nav a:hover,
    .nav a.active{
      background:#ffe7f0;
      color:#ff2f7e;
    }

    .main{
      flex:1;
      padding:30px 35px;
    }

    .topbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:20px;
      margin-bottom:28px;
      flex-wrap:wrap;
    }

    .welcome-box h1{
      font-size:30px;
      margin-bottom:6px;
    }

    .welcome-box p{
      color:#6b7280;
      font-size:14px;
    }

    .top-actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
    }

    .btn{
      padding:12px 18px;
      border-radius:24px;
      font-weight:600;
      font-size:14px;
      display:inline-block;
      transition:0.3s;
    }

    .btn-primary{
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      box-shadow:0 10px 20px rgba(255,79,147,0.20);
    }

    .btn-outline{
      border:1px solid #f0d6e2;
      background:#fff;
      color:#444;
    }

    .success-banner{
      background:#ecfdf3;
      color:#166534;
      border:1px solid #bbf7d0;
      padding:14px 18px;
      border-radius:16px;
      margin-bottom:20px;
      font-weight:600;
      box-shadow:0 8px 20px rgba(0,0,0,0.03);
    }

    .stats{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:20px;
      margin-bottom:30px;
    }

    .card{
      background:#fff;
      border-radius:22px;
      padding:24px;
      box-shadow:0 10px 25px rgba(0,0,0,0.05);
    }

    .card h3{
      font-size:15px;
      color:#777;
      margin-bottom:10px;
      font-weight:600;
    }

    .card h2{
      font-size:32px;
      color:#111827;
    }

    .section{
      background:#fff;
      border-radius:22px;
      padding:25px;
      box-shadow:0 10px 25px rgba(0,0,0,0.05);
    }

    .section-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:20px;
      flex-wrap:wrap;
      gap:12px;
    }

    .section-header h2{
      font-size:24px;
    }

    .products{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:20px;
    }

    .product{
      border:1px solid #f1e5ea;
      border-radius:18px;
      overflow:hidden;
      background:#fff;
      transition:0.3s;
    }

    .product:hover{
      transform:translateY(-6px);
    }

    .product-image{
      height:170px;
      background:#fafafa;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:18px;
    }

    .product-image img{
      max-width:100%;
      max-height:140px;
      object-fit:contain;
    }

    .product-content{
      padding:16px;
    }

    .product-content h4{
      font-size:16px;
      margin-bottom:6px;
      color:#1f2937;
    }

    .product-content p{
      color:#6b7280;
      font-size:13px;
      margin-bottom:8px;
    }

    .price{
      color:#ff2f7e;
      font-weight:700;
      font-size:18px;
    }

    @media (max-width:1100px){
      .products{
        grid-template-columns:repeat(2,1fr);
      }
    }

    @media (max-width:900px){
      body{
        display:block;
      }

      .sidebar{
        width:100%;
        height:auto;
        position:relative;
      }

      .stats{
        grid-template-columns:1fr;
      }
    }

    @media (max-width:650px){
      .products{
        grid-template-columns:1fr;
      }

      .main{
        padding:20px;
      }

      .welcome-box h1{
        font-size:24px;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <div class="logo">Glowly</div>

    <div class="nav">
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="shop.php">Shop</a>
      <a href="cart-empty.php">Cart</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <div class="welcome-box">
        <h1>Welcome, <?php echo htmlspecialchars($userName); ?> 👋</h1>
        <p>Manage your skincare journey, cart, and shopping activity.</p>
      </div>

      <div class="top-actions">
        <a href="shop.php" class="btn btn-primary">Shop Products</a>
        <a href="logout.php" class="btn btn-outline">Logout</a>
      </div>
    </div>

    <?php if (!empty($paymentSuccessMessage)) : ?>
      <div class="success-banner">
        <?php echo htmlspecialchars($paymentSuccessMessage); ?>
      </div>
    <?php endif; ?>

    <div class="stats">
      <div class="card">
        <h3>Cart Items</h3>
        <h2><?php echo $cartCount; ?></h2>
      </div>

      <div class="card">
        <h3>Total Orders</h3>
        <h2><?php echo $orderCount; ?></h2>
      </div>

      <div class="card">
        <h3>Available Products</h3>
        <h2><?php echo $productCount; ?></h2>
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <h2>Recently Added Products</h2>
        <a href="shop.php" class="btn btn-primary">View All</a>
      </div>

      <div class="products">
        <?php if ($recentProducts->num_rows > 0) : ?>
          <?php while ($row = $recentProducts->fetch_assoc()) : ?>
            <div class="product">
              <div class="product-image">
                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
              </div>
              <div class="product-content">
                <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                <p><?php echo htmlspecialchars($row['brand']); ?> • <?php echo htmlspecialchars($row['category']); ?></p>
                <div class="price">₹<?php echo htmlspecialchars($row['price']); ?></div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <p>No products found in database.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</body>
</html>