<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$userName = $_SESSION["user_name"];

$brandFilter = isset($_GET['brand']) ? trim($_GET['brand']) : 'All';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'All';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($brandFilter !== "All") {
    $sql .= " AND brand = ?";
    $params[] = $brandFilter;
    $types .= "s";
}

if ($categoryFilter !== "All") {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$message = "";

/* Add to cart */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {
    $product_id = (int) $_POST["product_id"];
    $user_id = $_SESSION["user_id"];

    $checkCart = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $checkCart->bind_param("ii", $user_id, $product_id);
    $checkCart->execute();
    $cartResult = $checkCart->get_result();

    if ($cartResult->num_rows > 0) {
        $cartItem = $cartResult->fetch_assoc();
        $newQty = $cartItem["quantity"] + 1;

        $updateCart = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $updateCart->bind_param("ii", $newQty, $cartItem["id"]);
        $updateCart->execute();
    } else {
        $insertCart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insertCart->bind_param("ii", $user_id, $product_id);
        $insertCart->execute();
    }

    header("Location: shop.php?added=1");
    exit();
}

$added = isset($_GET["added"]) ? "Product added to cart successfully." : "";

$cartCount = 0;
$countStmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
$countStmt->bind_param("i", $_SESSION["user_id"]);
$countStmt->execute();
$countResult = $countStmt->get_result();
if ($countRow = $countResult->fetch_assoc()) {
    $cartCount = $countRow["total_items"] ? $countRow["total_items"] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Shop</title>
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
      max-width:1200px;
      margin:auto;
    }

    header{
      background:#fff;
      box-shadow:0 2px 18px rgba(0,0,0,0.05);
      position:sticky;
      top:0;
      z-index:1000;
    }

    .navbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:20px 0;
      gap:20px;
    }

    .logo{
      font-size:30px;
      font-weight:800;
      color:#ff2f7e;
    }

    .nav-links{
      display:flex;
      gap:24px;
      align-items:center;
    }

    .nav-links a{
      font-size:14px;
      font-weight:500;
      color:#4b5563;
    }

    .nav-right{
      display:flex;
      align-items:center;
      gap:14px;
    }

    .user-pill{
      background:#fff3f7;
      color:#ff2f7e;
      padding:10px 14px;
      border-radius:25px;
      font-size:13px;
      font-weight:600;
    }

    .cart-btn, .logout-btn{
      padding:10px 16px;
      border-radius:24px;
      font-size:14px;
      font-weight:600;
      border:none;
      cursor:pointer;
    }

    .cart-btn{
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
    }

    .logout-btn{
      background:#fff;
      border:1px solid #f0d6e2;
      color:#444;
    }

    .shop-section{
      padding:40px 0 70px;
    }

    .success-msg{
      background:#ecfdf3;
      color:#15803d;
      border:1px solid #bbf7d0;
      padding:14px 18px;
      border-radius:16px;
      margin-bottom:20px;
      font-weight:600;
    }

    .filter-box{
      background:#fff;
      border:1px solid #f1dce5;
      border-radius:28px;
      padding:28px;
      box-shadow:0 10px 30px rgba(0,0,0,0.03);
      margin-bottom:36px;
    }

    .filter-group{
      margin-bottom:26px;
      padding-bottom:22px;
      border-bottom:1px solid #f7e7ed;
    }

    .filter-group:last-child{
      margin-bottom:0;
      padding-bottom:0;
      border-bottom:none;
    }

    .filter-title{
      font-size:15px;
      font-weight:700;
      margin-bottom:16px;
      color:#374151;
    }

    .chips{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
    }

    .chip{
      padding:10px 18px;
      border-radius:24px;
      border:1px solid #ebd7df;
      background:#fff;
      color:#4b5563;
      font-size:13px;
      font-weight:500;
      transition:0.25s;
    }

    .chip:hover, .chip.active{
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      border-color:transparent;
      box-shadow:0 10px 20px rgba(255,79,147,0.20);
    }

    .products-grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:24px;
    }

    .product-card{
      background:#fff;
      border-radius:24px;
      overflow:hidden;
      border:1px solid #f2e6eb;
      box-shadow:0 10px 28px rgba(0,0,0,0.05);
      transition:0.3s;
      position:relative;
    }

    .product-card:hover{
      transform:translateY(-8px);
    }

    .brand-badge{
      position:absolute;
      top:14px;
      left:14px;
      background:#4b5563;
      color:#fff;
      font-size:11px;
      font-weight:600;
      padding:8px 12px;
      border-radius:18px;
      z-index:2;
    }

    .product-image{
      height:250px;
      background:#fafafa;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:20px;
    }

    .product-image img{
      max-width:100%;
      max-height:210px;
      object-fit:contain;
    }

    .product-content{
      padding:18px;
    }

    .rating{
      font-size:14px;
      color:#f59e0b;
      margin-bottom:8px;
    }

    .rating span{
      color:#6b7280;
      margin-left:6px;
      font-size:13px;
    }

    .product-content h3{
      font-size:20px;
      margin-bottom:8px;
      color:#1f2937;
    }

    .product-content p{
      font-size:13px;
      color:#6b7280;
      line-height:1.7;
      min-height:42px;
      margin-bottom:12px;
    }

    .product-bottom{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
    }

    .price{
      font-size:20px;
      font-weight:700;
      color:#ff2f7e;
    }

    .add-btn{
      padding:10px 16px;
      border:none;
      border-radius:22px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-weight:600;
      cursor:pointer;
    }

    .empty-state{
      background:#fff;
      border-radius:24px;
      padding:50px 30px;
      text-align:center;
      border:1px solid #f1dce5;
      box-shadow:0 10px 25px rgba(0,0,0,0.04);
    }

    .empty-state h2{
      margin-bottom:10px;
      color:#1f2937;
    }

    .empty-state p{
      color:#6b7280;
    }

    @media (max-width:1100px){
      .products-grid{
        grid-template-columns:repeat(2,1fr);
      }
    }

    @media (max-width:768px){
      .navbar{
        flex-direction:column;
        align-items:flex-start;
      }

      .nav-links, .nav-right{
        flex-wrap:wrap;
      }

      .products-grid{
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
      <a href="index.php">Home</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="shop.php">Shop</a>
    </div>

    <div class="nav-right">
      <div class="user-pill">Hi, <?php echo htmlspecialchars($userName); ?></div>
      <a href="cart-empty.php" class="cart-btn">Cart (<?php echo $cartCount; ?>)</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
</header>

<section class="shop-section">
  <div class="container">

    <?php if (!empty($added)) : ?>
      <div class="success-msg"><?php echo htmlspecialchars($added); ?></div>
    <?php endif; ?>

    <div class="filter-box">
      <div class="filter-group">
        <div class="filter-title">Brand</div>
        <div class="chips">
          <?php
          $brands = ["All", "Mamaearth", "Minimalist", "Dot & Key", "Pilgrim"];
          foreach ($brands as $brand) {
              $active = ($brandFilter === $brand) ? "active" : "";
              echo '<a class="chip '.$active.'" href="shop.php?brand='.urlencode($brand).'&category='.urlencode($categoryFilter).'">'.$brand.'</a>';
          }
          ?>
        </div>
      </div>

      <div class="filter-group">
        <div class="filter-title">Category</div>
        <div class="chips">
          <?php
          $categories = ["All", "Face Wash", "Cleanser", "Moisturizer", "Serum"];
          foreach ($categories as $category) {
              $active = ($categoryFilter === $category) ? "active" : "";
              echo '<a class="chip '.$active.'" href="shop.php?brand='.urlencode($brandFilter).'&category='.urlencode($category).'">'.$category.'</a>';
          }
          ?>
        </div>
      </div>
    </div>

    <?php if ($result->num_rows > 0) : ?>
      <div class="products-grid">
        <?php while ($row = $result->fetch_assoc()) : ?>
          <div class="product-card">
            <div class="brand-badge"><?php echo htmlspecialchars($row["brand"]); ?></div>

            <div class="product-image">
              <img src="<?php echo htmlspecialchars($row["image"]); ?>" alt="<?php echo htmlspecialchars($row["name"]); ?>">
            </div>

            <div class="product-content">
              <div class="rating">★★★★★ <span>(<?php echo htmlspecialchars($row["rating"]); ?>)</span></div>
              <h3><?php echo htmlspecialchars($row["name"]); ?></h3>
              <p><?php echo htmlspecialchars($row["description"]); ?></p>

              <div class="product-bottom">
                <div class="price">₹<?php echo htmlspecialchars($row["price"]); ?></div>

                <form method="POST">
                  <input type="hidden" name="product_id" value="<?php echo $row["id"]; ?>">
                  <button type="submit" name="add_to_cart" class="add-btn">Add</button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else : ?>
      <div class="empty-state">
        <h2>No products found</h2>
        <p>Try changing the selected brand or category.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

</body>
</html>