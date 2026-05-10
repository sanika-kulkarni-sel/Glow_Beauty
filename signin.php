<?php
session_start();
require 'config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, first_name, password, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ((int)$user["is_verified"] !== 1) {
            $message = "Please verify your email first.";
        } elseif (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["first_name"];

            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Sign In</title>
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
      min-height:100vh;
      background:linear-gradient(135deg,#fff7f8,#fff0f4);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:30px 15px;
      color:#2b2b2b;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    .page-wrap{
      width:100%;
      max-width:1100px;
      display:grid;
      grid-template-columns:1fr 1fr;
      background:#fff;
      border-radius:32px;
      overflow:hidden;
      box-shadow:0 20px 60px rgba(0,0,0,0.08);
    }

    .left-panel{
      background:linear-gradient(135deg,#ff4f93,#ff7aae);
      color:#fff;
      padding:60px 50px;
      position:relative;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      justify-content:center;
    }

    .left-panel::before{
      content:"";
      position:absolute;
      width:260px;
      height:260px;
      border-radius:50%;
      background:rgba(255,255,255,0.12);
      top:-70px;
      right:-70px;
    }

    .left-panel::after{
      content:"";
      position:absolute;
      width:220px;
      height:220px;
      border-radius:50%;
      background:rgba(255,255,255,0.08);
      bottom:-80px;
      left:-80px;
    }

    .brand{
      font-size:36px;
      font-weight:800;
      letter-spacing:1px;
      margin-bottom:18px;
      position:relative;
      z-index:1;
    }

    .brand span{
      color:#ffe2ef;
    }

    .left-panel h1{
      font-size:42px;
      line-height:1.2;
      margin-bottom:16px;
      position:relative;
      z-index:1;
    }

    .left-panel p{
      font-size:15px;
      line-height:1.9;
      color:#fff5f8;
      max-width:440px;
      position:relative;
      z-index:1;
      margin-bottom:30px;
    }

    .feature-list{
      position:relative;
      z-index:1;
      display:grid;
      gap:14px;
    }

    .feature-item{
      background:rgba(255,255,255,0.14);
      border:1px solid rgba(255,255,255,0.18);
      padding:14px 16px;
      border-radius:18px;
      font-size:14px;
      backdrop-filter:blur(8px);
    }

    .right-panel{
      padding:50px 42px;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#fff;
    }

    .form-box{
      width:100%;
      max-width:410px;
    }

    .form-box h2{
      font-size:34px;
      color:#2b2b2b;
      margin-bottom:10px;
    }

    .form-box .subtext{
      color:#6b7280;
      font-size:14px;
      line-height:1.7;
      margin-bottom:28px;
    }

    .input-group{
      margin-bottom:18px;
    }

    .input-group label{
      display:block;
      font-size:14px;
      font-weight:600;
      color:#374151;
      margin-bottom:8px;
    }

    .input-group input{
      width:100%;
      padding:15px 16px;
      border-radius:16px;
      border:1px solid #ecd7e0;
      outline:none;
      font-size:14px;
      transition:0.25s;
      background:#fffafd;
    }

    .input-group input:focus{
      border-color:#ff4f93;
      box-shadow:0 0 0 4px rgba(255,79,147,0.10);
    }

    .extras{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin:6px 0 22px;
      flex-wrap:wrap;
    }

    .remember{
      display:flex;
      align-items:center;
      gap:8px;
      color:#6b7280;
      font-size:13px;
    }

    .remember input{
      accent-color:#ff4f93;
    }

    .forgot{
      color:#ff2f7e;
      font-size:13px;
      font-weight:600;
    }

    .submit-btn{
      width:100%;
      border:none;
      cursor:pointer;
      padding:15px 18px;
      border-radius:18px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-size:15px;
      font-weight:700;
      box-shadow:0 14px 28px rgba(255,79,147,0.24);
      transition:0.3s;
    }

    .submit-btn:hover{
      transform:translateY(-3px);
    }

    .bottom-text{
      text-align:center;
      margin-top:20px;
      color:#6b7280;
      font-size:14px;
    }

    .bottom-text a{
      color:#ff2f7e;
      font-weight:600;
    }

    .home-link{
      display:inline-block;
      margin-top:18px;
      color:#ff2f7e;
      font-size:14px;
      font-weight:600;
    }

    @media (max-width:950px){
      .page-wrap{
        grid-template-columns:1fr;
      }

      .left-panel{
        padding:42px 30px;
      }

      .right-panel{
        padding:40px 22px;
      }
    }

    @media (max-width:600px){
      .left-panel h1{
        font-size:32px;
      }

      .form-box h2{
        font-size:28px;
      }
    }
  </style>
</head>
<body>

  <div class="page-wrap">
    <div class="left-panel">
      <div class="brand">Glow<span>ly</span></div>
      <h1>Welcome Back To Glowly</h1>
      <p>
        Sign in to continue exploring skincare products, manage your cart, and enjoy
        your smooth shopping experience across all top skincare categories.
      </p>

      <div class="feature-list">
        <div class="feature-item">🛒 Access your shopping dashboard quickly</div>
        <div class="feature-item">🌸 Browse products from top skincare brands</div>
        <div class="feature-item">💖 Continue where you left off anytime</div>
      </div>
    </div>

    <div class="right-panel">
      <div class="form-box">
        <h2>Sign In</h2>
        <p class="subtext">Enter your email and password to access your account.</p>

        <?php if (!empty($message)) : ?>
      <p style="color:#e11d48; font-weight:600; margin-bottom:15px;">
          <?php echo htmlspecialchars($message); ?>
      </p>
  <?php endif; ?>


        <form action="#" method="POST">
          <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
          </div>

          <div class="extras">
            <label class="remember">
              <input type="checkbox" name="remember">
              Remember me
            </label>

            <a href="#" class="forgot">Forgot Password?</a>
          </div>

          <button type="submit" class="submit-btn">Sign In</button>
        </form>

        <div class="bottom-text">
          Don’t have an account? <a href="signup.php">Sign Up</a>
        </div>

        <a href="index.php" class="home-link">← Back to Home</a>
      </div>
    </div>
  </div>

</body>
</html>